<?php

namespace app\controllers;
use app\models\mainModel;

// =============================================
// app/controllers/barrioController.php
// Acciones POST exclusivas del Encargado de Barrio.
// =============================================

class barrioController extends mainModel {

    public function procesarAcciones() {
        global $user, $mensaje_exito, $mensaje_error;

        $action = $_POST['action'] ?? $_POST['form_type'] ?? null;

        // 1. Aprobar Solicitud de Vivienda (Alta/Baja)
        if ($action === 'procesar_solicitud') {
            $solicitud_id = (int)$_POST['solicitud_id'];
            $estado = $_POST['estado']; // 'Aprobado' o 'Rechazado'
            
            try {
                $pdo = $this->conectar();
                $pdo->beginTransaction();

                // Obtener datos de la solicitud
                $stmt = $this->ejecutarConsulta("SELECT * FROM solicitudes_vivienda WHERE id = ?", [$solicitud_id]);
                $solicitud = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($solicitud) {
                    if ($estado === 'Aprobado') {
                        if ($solicitud['tipo'] === 'Alta') {
                            // Registrar vivienda
                            $this->ejecutarConsulta(
                                "INSERT INTO viviendas (propietario, barrio_id, calle_id, numero_casa, referencia, encargado_calle_id)
                                 VALUES (?, (SELECT barrio_id FROM calles WHERE id=?), ?, ?, ?, ?)",
                                [
                                    $solicitud['propietario'],
                                    $solicitud['calle_id'],
                                    $solicitud['calle_id'],
                                    $solicitud['numero_casa'],
                                    $solicitud['referencia'],
                                    $solicitud['creado_por']
                                ]
                            );
                        } else {
                            // Eliminar vivienda (Baja)
                            $this->ejecutarConsulta("DELETE FROM viviendas WHERE id = ?", [$solicitud['vivienda_id']]);
                        }
                    }

                    // Actualizar estado de solicitud
                    $this->ejecutarConsulta(
                        "UPDATE solicitudes_vivienda SET estado = ?, revisado_por = ?, fecha_revision = CURRENT_TIMESTAMP WHERE id = ?",
                        [$estado, $user['id'], $solicitud_id]
                    );

                    $pdo->commit();
                    $mensaje_exito = "Solicitud procesada: $estado.";
                } else {
                    $pdo->rollBack();
                    $mensaje_error = "Solicitud no encontrada.";
                }
            } catch (\PDOException $e) {
                $pdo->rollBack();
                $mensaje_error = "Error al procesar solicitud: " . $e->getMessage();
            }
        }

        // 2. Registrar Nueva Vivienda (Manual o desde Solicitud)
        if ($action === 'nuevo_vecino') {
            try {
                $this->ejecutarConsulta(
                    "INSERT INTO viviendas (propietario, barrio_id, calle_id, direccion, numero_casa, encargado_calle_id)
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [
                        $_POST['propietario'],
                        (int)$_POST['barrio_id'],
                        (int)$_POST['calle_id'],
                        $_POST['direccion'],
                        $_POST['numero'] ?? null,
                        $_POST['encargado_calle_id'] ?? $user['id']
                    ]
                );

                // Si venía de una solicitud, marcarla como aprobada
                if (!empty($_POST['solicitud_id'])) {
                    $this->ejecutarConsulta(
                        "UPDATE solicitudes_vivienda SET estado = 'Aprobado', revisado_por = ?, fecha_revision = CURRENT_TIMESTAMP WHERE id = ?",
                        [$user['id'], (int)$_POST['solicitud_id']]
                    );
                }

                $mensaje_exito = "Vivienda registrada correctamente.";
            } catch (\PDOException $e) {
                $mensaje_error = "Error al registrar vivienda: " . $e->getMessage();
            }
        }

        // 3. Enviar recaudaciones de la calle al Gestor
        if ($action === 'enviar_recaudacion_gestor') {
            try {
                $pdo = $this->conectar();
                $pdo->beginTransaction();

                $stmt = $this->ejecutarConsulta(
                    "SELECT SUM(monto_total) as total, barrio_id
                     FROM recaudaciones 
                     WHERE receptor_id = ? AND estado = 'Pendiente' AND tipo = 'Calle'",
                    [$user['id']]
                );
                $res = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($res && $res['total'] > 0) {
                    // Crear recaudación de Barrio
                    $ins = $pdo->prepare("INSERT INTO recaudaciones (tipo, emisor_id, barrio_id, monto_total, estado) VALUES ('Barrio', ?, ?, ?, 'Pendiente')");
                    $ins->execute([$user['id'], $res['barrio_id'], $res['total']]);
                    $barrio_rec_id = $pdo->lastInsertId();

                    $this->ejecutarConsulta(
                        "UPDATE recaudaciones SET estado = 'Verificado', receptor_id = ? WHERE receptor_id = ? AND estado = 'Pendiente' AND tipo = 'Calle'",
                        [$user['id'], $user['id']]
                    );

                    $pdo->commit();
                    $mensaje_exito = "Recaudación del barrio enviada al Gestor.";
                } else {
                    $pdo->rollBack();
                    $mensaje_error = "No hay pagos de calles para enviar.";
                }
            } catch (\PDOException $e) {
                $pdo->rollBack();
                $mensaje_error = "Error al enviar al gestor.";
            }
        }

        // 4. Marcar pago como Realizado (Barrio Manager)
        if ($action === 'jefe_marcar_pagado') {
            try {
                $cobro_id = (int)$_POST['cobro_id'];
                $this->ejecutarConsulta("UPDATE cobros SET estado = 'Pagado' WHERE id = ?", [$cobro_id]);
                
                // Re-activar servicio si ya no hay deudas
                $vStmt = $this->ejecutarConsulta("SELECT vivienda_id FROM cobros WHERE id = ?", [$cobro_id]);
                $vId = $vStmt->fetchColumn();
                
                $pend = $this->ejecutarConsulta("SELECT COUNT(*) FROM cobros WHERE vivienda_id = ? AND estado != 'Pagado'", [$vId]);
                if ($pend->fetchColumn() == 0) {
                    $this->ejecutarConsulta("UPDATE viviendas SET estado_servicio = 'Activo' WHERE id = ?", [$vId]);
                }
                $mensaje_exito = "Pago registrado. Servicio actualizado si corresponde.";
            } catch (\Exception $e) {
                $mensaje_error = "Error al registrar pago.";
            }
        }

        // 5. Configurar Monto Mensual y Multa
        if ($action === 'configurar_tarifas') {
            try {
                $monto = (float)$_POST['monto_mensual'];
                $this->ejecutarConsulta(
                    "UPDATE barrios b JOIN detalles_encargado_barrio d ON b.id = d.barrio_id 
                     SET b.monto_mensual = ? WHERE d.usuario_id = ?",
                    [$monto, $user['id']]
                );
                $mensaje_exito = "Tarifas de barrio actualizadas.";
            } catch (\Exception $e) {
                $mensaje_error = "Error al actualizar tarifas.";
            }
        }
    }

    /**
     * Lógica para verificar deudas y aplicar multas/suspensiones
     * Se llama al cargar la vista de reportar pagos.
     */
    public function verificarDeudasBarrio($usuario_id) {
        $pdo = $this->conectar();
        
        // 1. Obtener viviendas del barrio
        $stmt = $pdo->prepare("SELECT v.id, b.monto_mensual 
                               FROM viviendas v 
                               JOIN barrios b ON v.barrio_id = b.id
                               JOIN detalles_encargado_barrio deb ON b.id = deb.barrio_id
                               WHERE deb.usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $viviendas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($viviendas as $v) {
            $vid = $v['id'];
            $monto = $v['monto_mensual'];
            
            // 2. Contar meses pendientes
            $pendStmt = $pdo->prepare("SELECT COUNT(*) FROM cobros WHERE vivienda_id = ? AND estado != 'Pagado' AND tipo_cobro = 'Servicio'");
            $pendStmt->execute([$vid]);
            $meses_deuda = $pendStmt->fetchColumn();

            // 3. Si tiene >= 1 mes de deuda, suspender servicio
            if ($meses_deuda >= 1) {
                $pdo->prepare("UPDATE viviendas SET estado_servicio = 'Suspendido' WHERE id = ?")->execute([$vid]);
            }

            // 4. Si tiene >= 2 meses de deuda, aplicar multa si no la tiene ya este mes
            if ($meses_deuda >= 2) {
                $mes = date('n'); $anio = date('Y');
                $checkMulta = $pdo->prepare("SELECT id FROM cobros WHERE vivienda_id = ? AND mes = ? AND anio = ? AND tipo_cobro = 'Multa'");
                $checkMulta->execute([$vid, $mes, $anio]);
                
                if (!$checkMulta->fetch()) {
                    $ins = $pdo->prepare("INSERT INTO cobros (vivienda_id, mes, anio, monto, fecha_emision, fecha_vencimiento, estado, tipo_cobro) 
                                          VALUES (?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Pendiente', 'Multa')");
                    $ins->execute([$vid, $mes, $anio, $monto]);
                }
            }
        }
    }
}
