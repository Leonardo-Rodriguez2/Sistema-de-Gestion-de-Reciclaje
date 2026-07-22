<?php

namespace app\controllers;
use app\models\mainModel;

// =============================================
// app/controllers/gestorController.php
// Acciones POST del Gestor/Administrador (Rol 1, 2)
// =============================================

class gestorController extends mainModel {

    public function procesarAcciones() {
        global $user, $mensaje_exito, $mensaje_error;

        $this->asegurarColumnasPagos();

        $action = $_POST['action'] ?? $_POST['form_type'] ?? null;

        // 1. Aprobar Lote de Barrio → genera Recibo de Finiquito
        if ($action === 'aprobar_lote_barrio') {
            $lote_id = (int)$_POST['lote_id'];
            $pdo = null;
            try {
                $pdo = $this->conectar();
                $pdo->beginTransaction();

                $lote = $this->ejecutarConsulta("SELECT * FROM lotes_barrio WHERE id=?", [$lote_id])
                    ->fetch(\PDO::FETCH_ASSOC);

                if (!$lote) throw new \Exception("Lote no encontrado.");
                if ($lote['estado'] !== 'Enviado') throw new \Exception("Solo se pueden aprobar lotes en estado 'Enviado'.");

                // Aprobar el lote
                $this->ejecutarConsulta(
                    "UPDATE lotes_barrio SET estado='Aprobado', gestor_id=?, fecha_aprobacion=NOW(), recibo_generado=1 WHERE id=?",
                    [$user['id'], $lote_id]
                );

                // Generar número de recibo único: RF-YYYYMM-BARRIO-RAND
                $numero_recibo = sprintf(
                    'RF-%04d%02d-%03d-%04d',
                    $lote['periodo_anio'], $lote['periodo_mes'],
                    $lote['barrio_id'], rand(1000, 9999)
                );

                // Insertar recibo de finiquito
                $this->ejecutarConsulta(
                    "INSERT INTO recibos_finiquito
                     (lote_barrio_id, barrio_id, periodo_mes, periodo_anio, monto_aprobado, generado_por, numero_recibo)
                     VALUES (?,?,?,?,?,?,?)",
                    [
                        $lote_id, $lote['barrio_id'], $lote['periodo_mes'], $lote['periodo_anio'],
                        $lote['monto_total_recolectado'], $user['id'], $numero_recibo
                    ]
                );

                // Generar certificados para cada calle incluida en el lote
                $this->ejecutarConsulta(
                    "UPDATE lotes_calle SET certificado_generado=1, fecha_certificado=NOW() WHERE lote_barrio_id=?",
                    [$lote_id]
                );

                // Aplicar multas/suspensiones a casas morosas del periodo
                $this->aplicarSancionesAlCerrar($pdo, $lote['barrio_id'], $lote['periodo_mes'], $lote['periodo_anio']);

                $pdo->commit();
                $sid = $_SESSION['active_sid'] ?? 'main';
                header("Location: router.php?page=dashboard&sid={$sid}&exito=" . urlencode("Lote aprobado. Recibo #{$numero_recibo} generado."));
                exit;
            } catch (\Exception $e) {
                if ($pdo) $pdo->rollBack();
                $mensaje_error = $e->getMessage();
            }
        }

        // 2. Rechazar Lote de Barrio (devuelve al Encargado de Barrio)
        if ($action === 'rechazar_lote_barrio') {
            $lote_id = (int)$_POST['lote_id'];
            $motivo  = trim($_POST['motivo_rechazo'] ?? '');
            try {
                if (empty($motivo)) throw new \Exception("Debe indicar un motivo de rechazo.");

                $lote = $this->ejecutarConsulta("SELECT * FROM lotes_barrio WHERE id=?", [$lote_id])
                    ->fetch(\PDO::FETCH_ASSOC);

                if (!$lote || $lote['estado'] !== 'Enviado') {
                    throw new \Exception("Solo se pueden rechazar lotes en estado 'Enviado'.");
                }

                $pdo = $this->conectar();
                $pdo->beginTransaction();

                $this->ejecutarConsulta(
                    "UPDATE lotes_barrio SET estado='Rechazado', observaciones_gestor=?, gestor_id=? WHERE id=?",
                    [$motivo, $user['id'], $lote_id]
                );

                // Desenlazar lotes de calle para que el Barrio pueda reenviar
                $this->ejecutarConsulta(
                    "UPDATE lotes_calle SET lote_barrio_id=NULL WHERE lote_barrio_id=?",
                    [$lote_id]
                );

                $pdo->commit();
                $mensaje_exito = "Lote rechazado. El Encargado de Barrio puede corregir y reenviar.";
            } catch (\Exception $e) {
                if (isset($pdo)) $pdo->rollBack();
                $mensaje_error = $e->getMessage();
            }
        }

        // 3. Enviar facturas/certificados al Encargado de Barrio
        if ($action === 'enviar_facturas_barrio') {
            $lote_id = (int)$_POST['lote_id'];
            try {
                $lote = $this->ejecutarConsulta("SELECT * FROM lotes_barrio WHERE id=?", [$lote_id])
                    ->fetch(\PDO::FETCH_ASSOC);
                if (!$lote) throw new \Exception("Lote no encontrado.");
                if ($lote['estado'] !== 'Aprobado') throw new \Exception("Solo se pueden enviar lotes aprobados.");
                if ($lote['facturas_enviadas_barrio']) throw new \Exception("Las facturas ya fueron enviadas a este barrio.");

                $this->ejecutarConsulta(
                    "UPDATE lotes_barrio SET facturas_enviadas_barrio=1 WHERE id=?",
                    [$lote_id]
                );
                $mensaje_exito = "Facturas enviadas al Encargado de Barrio. Ahora puede verlas y descargarlas desde su panel.";
            } catch (\Exception $e) {
                $mensaje_error = $e->getMessage();
            }
        }

    }

    // ─── Aplicar multas/suspensiones al cerrar un ciclo ──────────────────────

    private function aplicarSancionesAlCerrar(\PDO $pdo, int $barrio_id, int $mes, int $anio): void {
        // Obtener viviendas activas del barrio que NO tienen cobro pagado en el periodo
        $stmt = $pdo->prepare(
            "SELECT v.id, cb.cuota_mensual FROM viviendas v
             LEFT JOIN configuraciones_barrio cb ON v.barrio_id = cb.barrio_id
             WHERE v.barrio_id = ? AND v.estado_servicio = 'Activo'
             AND v.id NOT IN (
                 SELECT DISTINCT c.vivienda_id FROM cobros c
                 WHERE c.mes = ? AND c.anio = ? AND c.estado = 'Pagado'
             )"
        );
        $stmt->execute([$barrio_id, $mes, $anio]);
        $morosas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($morosas as $v) {
            $vid   = $v['id'];
            $monto = $v['cuota_mensual'] ?? 0;

            // Suspender servicio
            $pdo->prepare("UPDATE viviendas SET estado_servicio='Suspendido' WHERE id=?")->execute([$vid]);

            // Insertar cobro Pendiente del mes si no existe
            $existe = $pdo->prepare(
                "SELECT id FROM cobros WHERE vivienda_id=? AND mes=? AND anio=? AND tipo_cobro='Servicio'"
            );
            $existe->execute([$vid, $mes, $anio]);
            if (!$existe->fetch()) {
                $pdo->prepare(
                    "INSERT INTO cobros (vivienda_id, mes, anio, monto, fecha_emision, fecha_vencimiento, estado, tipo_cobro)
                     VALUES (?,?,?,?,CURDATE(),DATE_ADD(CURDATE(),INTERVAL 30 DAY),'Pendiente','Servicio')"
                )->execute([$vid, $mes, $anio, $monto]);
            }
        }
    }
}
