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

        // 2. Enviar recaudaciones de la calle al Gestor
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
    }
}
