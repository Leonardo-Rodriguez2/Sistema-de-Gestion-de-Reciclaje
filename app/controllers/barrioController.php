<?php

namespace app\controllers;
use app\models\mainModel;

// =============================================
// app/controllers/barrioController.php - ROL 5
// =============================================

class barrioController extends mainModel {

    public function procesarAcciones() {
        global $user, $mensaje_exito, $mensaje_error;

        // Aseguramos columnas antes de procesar cualquier acción
        $this->asegurarColumnasPagos();

        $action = $_POST['action'] ?? $_POST['form_type'] ?? null;
        if (!$action) return;

        // 1. Procesar Solicitud de Vivienda (Alta/Baja/Renovación)
        if ($action === 'procesar_solicitud') {
            $solicitud_id = (int)$_POST['solicitud_id'];
            $estado       = $_POST['estado'];
            try {
                $pdo = $this->conectar();
                $pdo->beginTransaction();

                $solicitud = $this->ejecutarConsulta("SELECT * FROM solicitudes_vivienda WHERE id = ?", [$solicitud_id])->fetch(\PDO::FETCH_ASSOC);
                if (!$solicitud) throw new \Exception("Solicitud no encontrada.");

                if ($estado === 'Aprobado') {
                    if ($solicitud['tipo'] === 'Alta') {
                        $calleData = $this->ejecutarConsulta("SELECT barrio_id, nombre FROM calles WHERE id = ?", [$solicitud['calle_id']])->fetch(\PDO::FETCH_ASSOC);
                        $stmt = $this->ejecutarConsulta(
                            "INSERT INTO viviendas (propietario, barrio_id, calle_id, direccion, numero_casa, referencia, estado_servicio) VALUES (?, ?, ?, ?, ?, ?, 'Activo')",
                            [$solicitud['propietario'], $calleData['barrio_id'], $solicitud['calle_id'], $calleData['nombre'], $solicitud['numero_casa'], $solicitud['referencia']]
                        );
                        $new_vivienda_id = $pdo->lastInsertId();
                        $this->ejecutarConsulta("UPDATE solicitudes_vivienda SET vivienda_id = ? WHERE id = ?", [$new_vivienda_id, $solicitud_id]);
                    } elseif ($solicitud['tipo'] === 'Baja') {
                        $this->ejecutarConsulta("UPDATE viviendas SET estado_servicio = 'Anulado' WHERE id = ?", [$solicitud['vivienda_id']]);
                    } elseif ($solicitud['tipo'] === 'Renovacion') {
                        $this->ejecutarConsulta("UPDATE viviendas SET estado_servicio = 'Activo' WHERE id = ?", [$solicitud['vivienda_id']]);
                    }
                }

                $this->ejecutarConsulta("UPDATE solicitudes_vivienda SET estado = ?, revisado_por = ?, fecha_revision = CURRENT_TIMESTAMP WHERE id = ?", [$estado, $user['id'], $solicitud_id]);
                
                if (!empty($solicitud['vivienda_id'])) {
                    $this->ejecutarConsulta("UPDATE solicitudes_vivienda SET estado = ?, revisado_por = ?, fecha_revision = CURRENT_TIMESTAMP WHERE vivienda_id = ? AND tipo = ? AND estado = 'Pendiente'", [$estado, $user['id'], $solicitud['vivienda_id'], $solicitud['tipo']]);
                }

                $pdo->commit();
                $mensaje_exito = "Solicitud procesada: $estado.";
            } catch (\Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
                $mensaje_error = "Error: " . $e->getMessage();
            }
        }

        // 2. Registrar Nueva Vivienda
        if ($action === 'nuevo_vecino') {
            try {
                $this->ejecutarConsulta("INSERT INTO viviendas (propietario, barrio_id, calle_id, direccion, numero_casa) VALUES (?, ?, ?, ?, ?)",
                    [$_POST['propietario'], (int)$_POST['barrio_id'], (int)$_POST['calle_id'], $_POST['direccion'], $_POST['numero'] ?? null]);
                $mensaje_exito = "Vivienda registrada correctamente.";
            } catch (\Exception $e) { $mensaje_error = "Error: " . $e->getMessage(); }
        }

        // 3. Verificar pago individual
        if ($action === 'verificar_pago_casa') {
            try {
                $cobro_id = (int)$_POST['cobro_id'];
                $decision = $_POST['decision'] ?? 'Verificado';
                $motivo   = trim($_POST['motivo_rechazo'] ?? '');

                $barrio_id = $this->obtenerBarrioId($user['id']);
                $cobro = $this->ejecutarConsulta(
                    "SELECT c.id FROM cobros c 
                     JOIN viviendas v ON c.vivienda_id = v.id 
                     WHERE c.id = ? AND v.barrio_id = ?",
                    [$cobro_id, $barrio_id]
                )->fetch();
                if (!$cobro) throw new \Exception("Acceso denegado: este cobro no pertenece a tu barrio.");

                $estado_cobro = ($decision === 'Rechazado') ? 'Rechazado' : 'Pagado';
                $this->ejecutarConsulta("UPDATE cobros SET estado=?, estado_verificacion=?, verificado_por=?, verificado_en=NOW(), motivo_rechazo=? WHERE id=?",
                    [$estado_cobro, $decision, $user['id'], $motivo, $cobro_id]);
                
                $mensaje_exito = "Pago marcado como: $decision.";
            } catch (\Exception $e) { $mensaje_error = "Error: " . $e->getMessage(); }
        }

        // 4 y 5. Gestión de Lotes de Calle
        if (in_array($action, ['aprobar_lote_calle', 'rechazar_lote_calle'])) {
            $lote_id = (int)$_POST['lote_id'];
            $motivo  = trim($_POST['motivo_rechazo'] ?? '');
            try {
                $this->validarBarrioLoteCalle($lote_id, $user['id']);
                
                if ($action === 'aprobar_lote_calle') {
                    $this->ejecutarConsulta("UPDATE lotes_calle SET estado='Aprobado', fecha_revision=NOW() WHERE id=?", [$lote_id]);
                    $this->ejecutarConsulta("UPDATE cobros SET estado_verificacion='Verificado', verificado_por=?, verificado_en=NOW() WHERE lote_calle_id=?", [$user['id'], $lote_id]);
                    $mensaje_exito = "Lote de calle aprobado.";
                } else {
                    if (empty($motivo)) throw new \Exception("Indique motivo de rechazo.");
                    $this->ejecutarConsulta("UPDATE lotes_calle SET estado='Rechazado', observaciones_barrio=?, fecha_revision=NOW() WHERE id=?", [$motivo, $lote_id]);
                    $this->ejecutarConsulta("UPDATE cobros SET lote_calle_id=NULL, estado_verificacion='Pendiente' WHERE lote_calle_id=?", [$lote_id]);
                    $mensaje_exito = "Lote rechazado.";
                }
            } catch (\Exception $e) { $mensaje_error = $e->getMessage(); }
        }

        // 6. Enviar Lote de Barrio
        if ($action === 'enviar_lote_barrio') {
            $this->procesarEnvioLoteBarrio($_POST, $_FILES);
        }

        // 7. Configurar Cuota y Multa
        if ($action === 'actualizar_configuracion_barrio') {
            try {
                $this->ejecutarConsulta(
                    "INSERT INTO configuraciones_barrio (barrio_id, cuota_mensual, multa_renovacion, actualizado_por) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE cuota_mensual=VALUES(cuota_mensual), multa_renovacion=VALUES(multa_renovacion), actualizado_por=VALUES(actualizado_por)",
                    [$this->obtenerBarrioId($user['id']), (float)$_POST['cuota_mensual'], (float)$_POST['multa_renovacion'], $user['id']]
                );
                $mensaje_exito = "Configuración actualizada.";
            } catch (\Exception $e) { $mensaje_error = "Error: " . $e->getMessage(); }
        }

        // 8. Enviar certificado/factura al encargado de calle
        if ($action === 'enviar_certificado_calle') {
            $lote_calle_id = (int)$_POST['lote_calle_id'];
            try {
                $barrio_id = $this->obtenerBarrioId($user['id']);
                $lote = $this->ejecutarConsulta(
                    "SELECT lc.id, lc.barrio_id, lc.certificado_generado, lc.certificado_enviado_calle,
                            lc.lote_barrio_id, lb.facturas_enviadas_barrio
                     FROM lotes_calle lc
                     JOIN lotes_barrio lb ON lc.lote_barrio_id = lb.id
                     WHERE lc.id=?", [$lote_calle_id]
                )->fetch(\PDO::FETCH_ASSOC);
                if (!$lote) throw new \Exception("Certificado no encontrado.");
                if ($lote['barrio_id'] != $barrio_id) throw new \Exception("Este certificado no pertenece a tu barrio.");
                if (!$lote['certificado_generado']) throw new \Exception("El certificado aún no ha sido generado por el gestor.");
                if (!$lote['facturas_enviadas_barrio']) throw new \Exception("El gestor aún no ha enviado las facturas al barrio.");
                if ($lote['certificado_enviado_calle']) throw new \Exception("Este certificado ya fue enviado al encargado de calle.");

                // Subir factura personalizada si se adjuntó
                $rutaFactura = null;
                if (isset($_FILES['factura_archivo']) && $_FILES['factura_archivo']['error'] === UPLOAD_ERR_OK) {
                    $rutaFactura = $this->guardarArchivoPago($_FILES['factura_archivo'], 'facturas');
                }

                $this->ejecutarConsulta(
                    "UPDATE lotes_calle SET certificado_enviado_calle=1" . ($rutaFactura ? ", factura_personalizada=?" : "") . " WHERE id=?",
                    $rutaFactura ? [$rutaFactura, $lote_calle_id] : [$lote_calle_id]
                );
                $mensaje_exito = "Certificado enviado al encargado de calle." . ($rutaFactura ? " Documento adjunto guardado." : "");
            } catch (\Exception $e) {
                $mensaje_error = $e->getMessage();
            }
        }

        // 9. Aprobar solicitud de exención de cobro
        if ($action === 'aprobar_exencion') {
            try {
                $exencion_id = (int)$_POST['exencion_id'];
                $barrio_id = $this->obtenerBarrioId($user['id']);

                $ex = $this->ejecutarConsulta(
                    "SELECT e.*, v.id as vid FROM exenciones_cobro e 
                     JOIN viviendas v ON e.vivienda_id = v.id 
                     WHERE e.id = ? AND e.barrio_id = ? AND e.estado = 'Pendiente'",
                    [$exencion_id, $barrio_id]
                )->fetch(\PDO::FETCH_ASSOC);
                if (!$ex) throw new \Exception("Solicitud no encontrada o ya procesada.");

                $pdo = $this->conectar();
                $pdo->beginTransaction();

                $this->ejecutarConsulta(
                    "UPDATE exenciones_cobro SET estado = 'Aprobado', aprobado_por = ?, fecha_revision = NOW() WHERE id = ?",
                    [$user['id'], $exencion_id]
                );
                $this->ejecutarConsulta(
                    "UPDATE viviendas SET exento_cobro = 1 WHERE id = ?",
                    [$ex['vid']]
                );

                $pdo->commit();
                $mensaje_exito = "Exención aprobada. La vivienda ya no generará cobros.";
            } catch (\Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
                $mensaje_error = $e->getMessage();
            }
        }

        // 10. Rechazar solicitud de exención de cobro
        if ($action === 'rechazar_exencion') {
            try {
                $exencion_id = (int)$_POST['exencion_id'];
                $motivo = trim($_POST['motivo_rechazo'] ?? '');
                $barrio_id = $this->obtenerBarrioId($user['id']);

                if (empty($motivo)) throw new \Exception("Debes indicar el motivo del rechazo.");

                $ex = $this->ejecutarConsulta(
                    "SELECT id FROM exenciones_cobro WHERE id = ? AND barrio_id = ? AND estado = 'Pendiente'",
                    [$exencion_id, $barrio_id]
                )->fetch();
                if (!$ex) throw new \Exception("Solicitud no encontrada o ya procesada.");

                $this->ejecutarConsulta(
                    "UPDATE exenciones_cobro SET estado = 'Rechazado', aprobado_por = ?, motivo_rechazo = ?, fecha_revision = NOW() WHERE id = ?",
                    [$user['id'], $motivo, $exencion_id]
                );
                $mensaje_exito = "Solicitud de exención rechazada.";
            } catch (\Exception $e) {
                $mensaje_error = $e->getMessage();
            }
        }

        // 11. Agregar exención directa (sin solicitud de calle)
        if ($action === 'agregar_exencion_directa') {
            try {
                $vivienda_id = (int)$_POST['vivienda_id'];
                $barrio_id = $this->obtenerBarrioId($user['id']);
                $tipo = $_POST['tipo_exencion'] ?? 'otro';
                $descripcion = trim($_POST['descripcion'] ?? '');

                // Validar que la vivienda pertenezca al barrio
                $v = $this->ejecutarConsulta(
                    "SELECT id, calle_id, exento_cobro FROM viviendas WHERE id = ? AND barrio_id = ?",
                    [$vivienda_id, $barrio_id]
                )->fetch(\PDO::FETCH_ASSOC);
                if (!$v) throw new \Exception("La vivienda no pertenece a tu barrio.");
                if ($v['exento_cobro']) throw new \Exception("Esta vivienda ya está exenta de cobro.");

                $pdo = $this->conectar();
                $pdo->beginTransaction();

                $this->ejecutarConsulta(
                    "INSERT INTO exenciones_cobro (vivienda_id, calle_id, barrio_id, tipo_exencion, descripcion, creado_por, estado, aprobado_por, fecha_revision)
                     VALUES (?, ?, ?, ?, ?, ?, 'Aprobado', ?, NOW())",
                    [$vivienda_id, $v['calle_id'], $barrio_id, $tipo, $descripcion, $user['id'], $user['id']]
                );
                $this->ejecutarConsulta(
                    "UPDATE viviendas SET exento_cobro = 1 WHERE id = ?",
                    [$vivienda_id]
                );

                $pdo->commit();
                $mensaje_exito = "Vivienda agregada a la lista de exentas directamente.";
            } catch (\Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
                $mensaje_error = $e->getMessage();
            }
        }

        // 12. Quitar exención de cobro (revocar)
        if ($action === 'quitar_exencion') {
            try {
                $exencion_id = (int)$_POST['exencion_id'];
                $barrio_id = $this->obtenerBarrioId($user['id']);

                $ex = $this->ejecutarConsulta(
                    "SELECT e.*, v.id as vid FROM exenciones_cobro e 
                     JOIN viviendas v ON e.vivienda_id = v.id 
                     WHERE e.id = ? AND e.barrio_id = ? AND e.estado = 'Aprobado'",
                    [$exencion_id, $barrio_id]
                )->fetch(\PDO::FETCH_ASSOC);
                if (!$ex) throw new \Exception("Exención no encontrada o ya revocada.");

                $pdo = $this->conectar();
                $pdo->beginTransaction();

                $this->ejecutarConsulta(
                    "UPDATE exenciones_cobro SET estado = 'Rechazado', aprobado_por = ?, fecha_revision = NOW() WHERE id = ?",
                    [$user['id'], $exencion_id]
                );
                $this->ejecutarConsulta(
                    "UPDATE viviendas SET exento_cobro = 0 WHERE id = ?",
                    [$ex['vid']]
                );

                $pdo->commit();
                $mensaje_exito = "Exención revocada. La vivienda volverá a generar cobros.";
            } catch (\Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
                $mensaje_error = $e->getMessage();
            }
        }

        // 13. Ordenar Baja (con cálculo de deuda)
        if ($action === 'ordenar_baja') {
            try {
                $v_id = (int)$_POST['vivienda_id'];
                [$monto, $detalles] = $this->calcularDeudaBarrio($v_id);
                $calle_id = $this->ejecutarConsulta("SELECT calle_id FROM viviendas WHERE id=?", [$v_id])->fetchColumn();
                
                $this->ejecutarConsulta("INSERT INTO solicitudes_vivienda (tipo, calle_id, vivienda_id, creado_por, estado, monto_deuda, detalles_deuda) VALUES ('Baja', ?, ?, ?, 'Aprobado', ?, ?)",
                    [$calle_id, $v_id, $user['id'], $monto, $detalles]);
                $this->ejecutarConsulta("UPDATE viviendas SET estado_servicio='Anulado' WHERE id=?", [$v_id]);
                $mensaje_exito = "Servicio anulado correctamente.";
            } catch (\Exception $e) { $mensaje_error = $e->getMessage(); }
        }
    }

    // --- MÉTODOS AUXILIARES ---

    private function procesarEnvioLoteBarrio($post, $files) {
        global $user, $mensaje_exito, $mensaje_error;
        try {
            $barrio_id = $this->obtenerBarrioId($user['id']);
            $mes = (int)($post['periodo_mes'] ?? date('n'));
            $anio = (int)($post['periodo_anio'] ?? date('Y'));
            
            $pdo = $this->conectar();
            $pdo->beginTransaction();
            
            // Obtener lotes de calle aprobados del periodo
            $lotesCalle = $pdo->prepare(
                "SELECT id, monto_esperado, monto_recolectado, total_casas, casas_pagadas, casas_morosas, alerta_deuda
                 FROM lotes_calle WHERE barrio_id=? AND periodo_mes=? AND periodo_anio=? AND estado='Aprobado' AND (lote_barrio_id IS NULL OR lote_barrio_id=0)"
            );
            $lotesCalle->execute([$barrio_id, $mes, $anio]);
            $aprobados = $lotesCalle->fetchAll(\PDO::FETCH_ASSOC);
            
            if (empty($aprobados)) {
                throw new \Exception("No hay lotes de calle aprobados pendientes de envío.");
            }
            
            // Calcular totales
            $totalEsp = 0;
            $totalRec = 0;
            $totalCalles = count($aprobados);
            $alertaDeuda = 0;
            foreach ($aprobados as $lc) {
                $totalEsp += (float)$lc['monto_esperado'];
                $totalRec += (float)$lc['monto_recolectado'];
                if ($lc['alerta_deuda']) $alertaDeuda = 1;
            }
            
            // Manejar comprobante global del lote de barrio
            $comprobanteLote = null;
            if (isset($files['comprobante_lote_barrio']) && $files['comprobante_lote_barrio']['error'] === UPLOAD_ERR_OK) {
                $comprobanteLote = $this->guardarArchivoPago($files['comprobante_lote_barrio'], 'comprobantes');
            }
            
            // Buscar o crear lote_barrio
            $stmt = $pdo->prepare("SELECT id FROM lotes_barrio WHERE barrio_id=? AND periodo_mes=? AND periodo_anio=?");
            $stmt->execute([$barrio_id, $mes, $anio]);
            $loteBarrioId = $stmt->fetchColumn();
            
            if ($loteBarrioId) {
                $upd = $pdo->prepare(
                    "UPDATE lotes_barrio SET estado='Enviado', fecha_envio=NOW(), monto_total_esperado=?, monto_total_recolectado=?, total_calles=?, calles_completas=?, alerta_deuda=?, comprobante_lote=COALESCE(?, comprobante_lote) WHERE id=?"
                );
                $upd->execute([$totalEsp, $totalRec, $totalCalles, $totalCalles, $alertaDeuda, $comprobanteLote, $loteBarrioId]);
            } else {
                $ins = $pdo->prepare(
                    "INSERT INTO lotes_barrio (barrio_id, periodo_mes, periodo_anio, mes, anio, estado, fecha_envio, encargado_barrio_id, monto_total_esperado, monto_total_recolectado, total_calles, calles_completas, alerta_deuda, comprobante_lote)
                     VALUES (?,?,?,?,?, 'Enviado', NOW(), ?,?,?,?,?,?,?)"
                );
                $ins->execute([$barrio_id, $mes, $anio, $mes, $anio, $user['id'], $totalEsp, $totalRec, $totalCalles, $totalCalles, $alertaDeuda, $comprobanteLote]);
                $loteBarrioId = $pdo->lastInsertId();
            }
            
            // Vincular lotes de calle aprobados al lote de barrio
            $link = $pdo->prepare("UPDATE lotes_calle SET lote_barrio_id=? WHERE id=?");
            foreach ($aprobados as $lc) {
                $link->execute([$loteBarrioId, $lc['id']]);
            }
            
            $pdo->commit();
            $mensaje_exito = "Lote de barrio enviado al gestor con " . count($aprobados) . " calle(s).";
        } catch (\Exception $e) { $mensaje_error = $e->getMessage(); }
    }

    protected function asegurarColumnasPagos() {
        parent::asegurarColumnasPagos();
        $columnas = ['estado_verificacion', 'verificado_por', 'verificado_en', 'motivo_rechazo'];
        foreach ($columnas as $col) {
            try {
                $this->ejecutarConsulta("SELECT $col FROM cobros LIMIT 1");
            } catch (\Exception $e) {
                $tipo = ($col === 'motivo_rechazo') ? 'TEXT' : ($col === 'verificado_en' ? 'DATETIME' : 'INT');
                if($col === 'estado_verificacion') $tipo = 'VARCHAR(50) DEFAULT "Pendiente"';
                $this->ejecutarConsulta("ALTER TABLE cobros ADD COLUMN $col $tipo NULL");
            }
        }
    }

    private function obtenerBarrioId(int $user_id): int {
        $id = $this->ejecutarConsulta("SELECT barrio_id FROM detalles_encargado_barrio WHERE usuario_id=?", [$user_id])->fetchColumn();
        if (!$id) throw new \Exception("No tienes barrio asignado.");
        return (int)$id;
    }

    private function validarBarrioLoteCalle(int $lote_id, int $user_id): void {
        $stmt = $this->ejecutarConsulta("SELECT lc.id FROM lotes_calle lc JOIN detalles_encargado_barrio deb ON lc.barrio_id = deb.barrio_id WHERE lc.id=? AND deb.usuario_id=?", [$lote_id, $user_id]);
        if (!$stmt->fetch()) throw new \Exception("Acceso denegado: Lote no pertenece a tu jurisdicción.");
    }

    private function calcularDeudaBarrio(int $vivienda_id): array {
        $d = $this->ejecutarConsulta("SELECT SUM(monto) as total, GROUP_CONCAT(CONCAT(tipo_cobro,' ',mes,'/',anio) SEPARATOR ', ') as resumen FROM cobros WHERE vivienda_id=? AND estado!='Pagado'", [$vivienda_id])->fetch(\PDO::FETCH_ASSOC);
        return [$d['total'] ?? 0, $d['resumen'] ?? 'Sin deudas'];
    }

    /**
     * Verificar y actualizar deudas del barrio.
     * Método requerido por viewsController al entrar a reportar_pago.
     */
    public function verificarDeudasBarrio(int $user_id): void {
        try {
            $barrio_id = $this->obtenerBarrioId($user_id);
            
            // Verificar que las tablas y columnas necesarias existan
            $this->asegurarColumnasPagos();
            
            // Obtener configuration si no existe
            $this->ejecutarConsulta(
                "INSERT INTO configuraciones_barrio (barrio_id, cuota_mensual, multa_renovacion) VALUES (?, 10.00, 5.00) ON DUPLICATE KEY UPDATE barrio_id=barrio_id",
                [$barrio_id]
            );
        } catch (\Exception $e) {
            // Silenciar errores para no bloquear el acceso a la vista
            error_log("Error en verificarDeudasBarrio: " . $e->getMessage());
        }
    }
}