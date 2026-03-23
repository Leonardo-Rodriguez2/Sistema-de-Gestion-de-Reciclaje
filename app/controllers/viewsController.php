<?php

namespace app\controllers;
use app\models\viewsModel;
use \PDO;

// =============================================
// app/controllers/viewsController.php
// Prepara todo para la vista: valida sesión,
// determina el rol, procesa POST y devuelve
// los datos necesarios para renderizar.
// =============================================

class viewsController extends viewsModel {

    // Retorna los datos necesarios para que router.php
    // pueda renderizar la vista en el scope global
    public function preparar() {

        // 1. Obtener datos del usuario
        $user = $this->obtenerUsuario($_SESSION['user_id']);

        if (!$user) {
            session_destroy();
            header("Location: /reciclaje/views/public/login.php?error=user_not_found");
            exit;
        }

        // 2. Obtener carpeta del rol
        $folder = $this->obtenerCarpetaRol($user['rol_id']);

        if (!$folder) {
            session_destroy();
            header("Location: /reciclaje/views/public/login.php?error=invalid_role");
            exit;
        }

        // 3. Crear la conexión PDO
        $pdo = $this->conectar();

        // 4. Página solicitada
        $page = $_GET['page'] ?? 'dashboard';

        // 5. Procesar formularios POST del rol
        $this->procesarPost($folder);

        // 6. Obtener ruta de vista validada (lista blanca)
        $vista = $this->obtenerVista($page, $folder);

        // Devolver todo lo que el scope global necesitará
        return [
            'pdo'   => $pdo,
            'user'  => $user,
            'page'  => $page,
            'vista' => $vista,
        ];
    }


    private function procesarPost($folder) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $controllerClass = "app\\controllers\\{$folder}Controller";

        if (class_exists($controllerClass)) {
            $ctrl = new $controllerClass();
            $ctrl->procesarAcciones();
        }
    }
}
