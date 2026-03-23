<?php

namespace app\controllers;
use app\models\mainModel;

// =============================================
// app/controllers/recolectorController.php
// Acciones POST del Recolector.
// Por ahora sin acciones. Agregar aquí cuando sea necesario.
// =============================================

class recolectorController extends mainModel {

    public function procesarAcciones() {
        global $mensaje_exito, $mensaje_error;

        $action = $_POST['action'] ?? $_POST['form_type'] ?? null;

        // Aquí irán las acciones del Recolector cuando se necesiten
        // Ejemplo:
        // if ($action === 'completar_recoleccion') { ... }
    }
}
