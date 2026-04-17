<?php

namespace app\controllers;
use app\models\mainModel;

// =============================================
// app/controllers/calleController.php
// Acciones POST exclusivas del Encargado de Calle.
// =============================================

class calleController extends mainModel {

    public function procesarAcciones() {
        global $user, $mensaje_exito, $mensaje_error;

        $action = $_POST['action'] ?? $_POST['form_type'] ?? null;

        // 1. Solicitar Alta de Vivienda
        if ($action === 'solicitar_alta') {
            try {
                // Verificar que el encargado tenga una calle asignada
                $stmt = $this->ejecutarConsulta(
                    "SELECT calle_id FROM detalles_encargado_calle WHERE usuario_id = ?",
                    [$user['id']]
                );
                $calle = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$calle) {
                    throw new \Exception("Usted no posee una calle asignada. Contacta al administrador.");
                }

                $this->ejecutarConsulta(
                    "INSERT INTO solicitudes_vivienda (tipo, calle_id, propietario, numero_casa, referencia, creado_por, estado)
                     VALUES ('Alta', ?, ?, ?, ?, ?, 'Pendiente')",
                    [
                        $calle['calle_id'],
                        $_POST['propietario'],
                        $_POST['numero_casa'],
                        $_POST['referencia'] ?? null,
                        $user['id']
                    ]
                );
                $mensaje_exito = "Solicitud de registro enviada al encargado de barrio.";
            } catch (\Exception $e) {
                $mensaje_error = "Error al enviar solicitud: " . $e->getMessage();
            }
        }

        // 2. Solicitar Baja de Vivienda
        if ($action === 'solicitar_baja') {
            try {
                $vivienda_id = (int)$_POST['vivienda_id'];

                // 1. Verificar calle asignada
                $stmt = $this->ejecutarConsulta(
                    "SELECT calle_id FROM detalles_encargado_calle WHERE usuario_id = ?",
                    [$user['id']]
                );
                $calle = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$calle) {
                    throw new \Exception("Usted no posee una calle asignada.");
                }

                // 1b. Verificar si ya existe una solicitud de baja pendiente
                $check = $this->ejecutarConsulta(
                    "SELECT id FROM solicitudes_vivienda WHERE vivienda_id = ? AND estado = 'Pendiente' AND tipo = 'Baja'",
                    [$vivienda_id]
                );
                if ($check->fetch()) {
                    throw new \Exception("Ya existe una solicitud de baja pendiente para esta vivienda.");
                }

                // 2. Calcular deuda (Meses + Multas)
                $deudaStmt = $this->ejecutarConsulta(
                    "SELECT SUM(monto) as total, COUNT(*) as cantidad, 
                            GROUP_CONCAT(CONCAT(tipo_cobro, ' ', mes, '/', anio) SEPARATOR ', ') as resumen
                     FROM cobros 
                     WHERE vivienda_id = ? AND estado != 'Pagado'",
                    [$vivienda_id]
                );
                $deuda_info = $deudaStmt->fetch(\PDO::FETCH_ASSOC);
                $monto_deuda = $deuda_info['total'] ?? 0;
                $detalles_deuda = $deuda_info['resumen'] ?? 'Sin deudas pendientes';

                // 3. Crear solicitud con deuda
                $this->ejecutarConsulta(
                    "INSERT INTO solicitudes_vivienda (tipo, calle_id, vivienda_id, creado_por, estado, monto_deuda, detalles_deuda)
                     VALUES ('Baja', ?, ?, ?, 'Pendiente', ?, ?)",
                    [
                        $calle['calle_id'],
                        $vivienda_id,
                        $user['id'],
                        $monto_deuda,
                        $detalles_deuda
                    ]
                );
                $mensaje_exito = "Solicitud de retiro enviada. Deuda detectada: S/ " . number_format($monto_deuda, 2);

                // Redirigir para feedback limpio
                $page = $_GET['page'] ?? 'dashboard';
                header("Location: router.php?page=$page&sid=" . ($_SESSION['active_sid'] ?? 'main') . "&exito=" . urlencode($mensaje_exito));
                exit;
            } catch (\Exception $e) {
                $mensaje_error = "Error al enviar solicitud: " . $e->getMessage();
            }
        }


        // 3. Marcar casa como pagada
        if ($action === 'procesar_pago') {
            $cobro_id = (int)$_POST['cobro_id'];
            try {
                $this->ejecutarConsulta(
                    "UPDATE cobros SET estado = 'Pagado' WHERE id = ?",
                    [$cobro_id]
                );

                // Re-activar servicio si ya no hay deudas
                $vStmt = $this->ejecutarConsulta("SELECT vivienda_id FROM cobros WHERE id = ?", [$cobro_id]);
                $vId = $vStmt->fetchColumn();
                
                $pend = $this->ejecutarConsulta("SELECT COUNT(*) FROM cobros WHERE vivienda_id = ? AND estado != 'Pagado'", [$vId]);
                if ($pend->fetchColumn() == 0) {
                    $this->ejecutarConsulta("UPDATE viviendas SET estado_servicio = 'Activo' WHERE id = ?", [$vId]);
                }

                $mensaje_exito = "Pago registrado correctamente.";
            } catch (\PDOException $e) {
                $mensaje_error = "Error al registrar pago.";
            }
        }

        // 4. Enviar recaudación al Barrio
        if ($action === 'enviar_recaudacion_barrio') {
            try {
                $pdo = $this->conectar();
                $pdo->beginTransaction();

                // Obtener datos de la calle y encargado del barrio
                $stmt = $this->ejecutarConsulta(
                    "SELECT SUM(c.monto) as total, v.barrio_id, v.calle_id, db.usuario_id as barrio_manager_id
                     FROM cobros c 
                     JOIN viviendas v ON c.vivienda_id = v.id
                     JOIN detalles_encargado_barrio db ON v.barrio_id = db.barrio_id
                     WHERE v.encargado_calle_id = ? AND c.estado = 'Pagado' AND c.recaudacion_id IS NULL",
                    [$user['id']]
                );
                $res = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($res && $res['total'] > 0) {
                    $ins = $pdo->prepare("INSERT INTO recaudaciones (tipo, emisor_id, receptor_id, barrio_id, calle_id, monto_total, estado) VALUES ('Calle', ?, ?, ?, ?, ?, 'Pendiente')");
                    $ins->execute([$user['id'], $res['barrio_manager_id'], $res['barrio_id'], $res['calle_id'], $res['total']]);
                    $recaudacion_id = $pdo->lastInsertId();

                    $this->ejecutarConsulta(
                        "UPDATE cobros c JOIN viviendas v ON c.vivienda_id = v.id
                         SET c.recaudacion_id = ?
                         WHERE v.encargado_calle_id = ? AND c.estado = 'Pagado' AND c.recaudacion_id IS NULL",
                        [$recaudacion_id, $user['id']]
                    );

                    $pdo->commit();
                    $mensaje_exito = "Recaudación enviada al Encargado de Barrio.";
                } else {
                    $pdo->rollBack();
                    $mensaje_error = "No hay pagos pendientes para enviar.";
                }
            } catch (\PDOException $e) {
                $pdo->rollBack();
                $mensaje_error = "Error al enviar recaudación: " . $e->getMessage();
            }
        }

        // 6. Solicitar Renovación de Servicio
        if ($action === 'solicitar_renovacion') {
            try {
                $vivienda_id = (int)$_POST['vivienda_id'];
                
                // 1. Calcular deuda acumulada
                $deudaStmt = $this->ejecutarConsulta(
                    "SELECT SUM(monto) as total, GROUP_CONCAT(CONCAT(tipo_cobro, ' ', mes, '/', anio) SEPARATOR ', ') as resumen
                     FROM cobros 
                     WHERE vivienda_id = ? AND estado != 'Pagado'",
                    [$vivienda_id]
                );
                $deuda_info = $deudaStmt->fetch(\PDO::FETCH_ASSOC);
                $monto_deuda = $deuda_info['total'] ?? 0;
                $detalles_deuda = $deuda_info['resumen'] ?? 'Sin deudas pendientes';

                // 2. Verificar si ya existe una solicitud pendiente
                $checkStmt = $this->ejecutarConsulta(
                    "SELECT id FROM solicitudes_vivienda WHERE vivienda_id = ? AND estado = 'Pendiente' AND tipo = 'Renovacion'",
                    [$vivienda_id]
                );
                if ($checkStmt->fetch()) {
                    throw new \Exception("Ya existe una solicitud de renovación pendiente para esta vivienda.");
                }

                // 3. Obtener calle_id de la vivienda
                $vStmt = $this->ejecutarConsulta("SELECT calle_id FROM viviendas WHERE id = ?", [$vivienda_id]);
                $calle_id = $vStmt->fetchColumn();

                // 3. Crear solicitud de Renovación
                $this->ejecutarConsulta(
                    "INSERT INTO solicitudes_vivienda (tipo, calle_id, vivienda_id, creado_por, estado, monto_deuda, detalles_deuda)
                     VALUES ('Renovacion', ?, ?, ?, 'Pendiente', ?, ?)",
                    [$calle_id, $vivienda_id, $user['id'], $monto_deuda, $detalles_deuda]
                );

                $mensaje_exito = "Solicitud de renovación enviada. El Encargado de Barrio deberá aprobarla.";
                
                // Redirigir para evitar re-post y asegurar mensajes frescos
                $page = $_GET['page'] ?? 'dashboard';
                header("Location: router.php?page=$page&sid=" . ($_SESSION['active_sid'] ?? 'main') . "&exito=" . urlencode($mensaje_exito));
                exit;

            } catch (\Exception $e) {
                $mensaje_error = "Error al solicitar renovación: " . $e->getMessage();
                // Opcional: registrar en log real si es necesario
            }
        }

    }
}
