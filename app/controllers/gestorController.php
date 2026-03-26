<?php

namespace app\controllers;
use app\models\mainModel;

class gestorController extends mainModel {

    public function procesarAcciones() {
        global $mensaje_exito, $mensaje_error;

        $action = $_POST['action'] ?? $_POST['form_type'] ?? null;

        // 1. Verificar recaudación enviada por un Jefe de Cuadra
        if ($action === 'verificar_recaudacion') {
            $recaudacion_id = (int)$_POST['recaudacion_id'];
            try {
                $this->ejecutarConsulta(
                    "UPDATE recaudaciones SET estado = 'Verificado' WHERE id = ?",
                    [$recaudacion_id]
                );
                $mensaje_exito = "Recaudación verificada y confirmada.";
            } catch (\PDOException $e) {
                $mensaje_error = "Error al verificar la recaudación.";
            }
        }
    }
}
