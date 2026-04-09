<?php

namespace app\controllers;
use app\models\viewsModel;
use \PDO;

class viewsController extends viewsModel {

    public function preparar() {

        // 1. Obtener datos del usuario desde la identidad activa
        $sid = $_SESSION['active_sid'] ?? 'main';
        $user_id = $_SESSION['identities'][$sid]['user_id'] ?? $_SESSION['user_id'] ?? null;
        
        global $user, $mensaje_exito, $mensaje_error;
        $user = $this->obtenerUsuario($user_id);

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

        // 5. Manejo de AJAX unificado
        if ($page === 'ajax_get_calles') {
            $barrio_id = (int)($_GET['barrio_id'] ?? 0);
            $stmt = $pdo->prepare("SELECT id, nombre FROM calles WHERE barrio_id = ? ORDER BY nombre ASC");
            $stmt->execute([$barrio_id]);
            header('Content-Type: application/json');
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
        }

        // 6. Procesar formularios POST del rol
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
