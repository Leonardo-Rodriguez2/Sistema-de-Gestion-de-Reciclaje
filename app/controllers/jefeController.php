<?php

namespace app\controllers;
use app\models\mainModel;

// =============================================
// app/controllers/jefeController.php
// Acciones POST exclusivas del Jefe de Cuadra.
// Para agregar una acción nueva: añade un nuevo
// "if ($action === 'mi_accion')" aquí abajo.
// =============================================

class jefeController extends mainModel {

    public function procesarAcciones() {
        global $user, $mensaje_exito, $mensaje_error;

        $action = $_POST['action'] ?? $_POST['form_type'] ?? null;

        // 1. Registrar nueva vivienda
        if ($action === 'nuevo_vecino') {
            try {
                $this->ejecutarConsulta(
                    "INSERT INTO viviendas (propietario, barrio_id, telefono, direccion, numero_casa, jefe_cuadra_id)
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [
                        $_POST['propietario'],
                        (int)$_POST['barrio_id'],
                        $_POST['telefono'] ?? null,
                        $_POST['direccion'],
                        $_POST['numero'] ?? null,
                        $user['id']
                    ]
                );
                $mensaje_exito = "Vivienda registrada correctamente.";
            } catch (\PDOException $e) {
                $mensaje_error = "Error al registrar la vivienda.";
            }
        }

        // 2. Marcar cobro como pagado (vecino pagó en mano)
        if ($action === 'procesar_pago' || $action === 'jefe_marcar_pagado') {
            $cobro_id = (int)$_POST['cobro_id'];
            try {
                $this->ejecutarConsulta(
                    "UPDATE cobros SET estado = 'Pagado' WHERE id = ?",
                    [$cobro_id]
                );
                $mensaje_exito = "Pago recibido del vecino.";
            } catch (\PDOException $e) {
                $mensaje_error = "Error al marcar el pago.";
            }
        }

        // 3. Enviar recaudación al Gestor
        if ($action === 'enviar_recaudacion_gestor') {
            try {
                $pdo = $this->conectar();
                $pdo->beginTransaction();

                $stmt = $this->ejecutarConsulta(
                    "SELECT SUM(c.monto) as total, v.barrio_id
                     FROM cobros c JOIN viviendas v ON c.vivienda_id = v.id
                     WHERE v.jefe_cuadra_id = ? AND c.estado = 'Pagado' AND c.recaudacion_id IS NULL",
                    [$user['id']]
                );
                $res = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($res && $res['total'] > 0) {
                    $ins = $pdo->prepare("INSERT INTO recaudaciones (jefe_id, barrio_id, monto_total, estado) VALUES (?, ?, ?, 'Pendiente')");
                    $ins->execute([$user['id'], $res['barrio_id'], $res['total']]);
                    $recaudacion_id = $pdo->lastInsertId();

                    $this->ejecutarConsulta(
                        "UPDATE cobros c JOIN viviendas v ON c.vivienda_id = v.id
                         SET c.recaudacion_id = ?
                         WHERE v.jefe_cuadra_id = ? AND c.estado = 'Pagado' AND c.recaudacion_id IS NULL",
                        [$recaudacion_id, $user['id']]
                    );

                    $pdo->commit();
                    $mensaje_exito = "Recaudación enviada al Gestor correctamente.";
                } else {
                    $pdo->rollBack();
                    $mensaje_error = "No hay pagos para enviar.";
                }
            } catch (\PDOException $e) {
                $pdo->rollBack();
                $mensaje_error = "Error al enviar: " . $e->getMessage();
            }
        }
    }
}
