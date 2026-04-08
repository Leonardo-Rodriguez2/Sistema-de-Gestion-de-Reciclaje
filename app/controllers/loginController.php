<?php

namespace app\controllers;
use app\models\mainModel;
use \PDO;

class loginController extends mainModel {

    public function iniciarSesion() {
        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            return 'Por favor, completa todos los campos.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'El correo electrónico no es válido.';
        }

        try {
            $stmt = $this->ejecutarConsulta(
                "SELECT id, password_hash, rol_id FROM usuarios WHERE email = ?",
                [$email]
            );
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // No regenerar ID para no matar otras identidades de la misma sesión
                // session_regenerate_id(true); 
                
                $sid = $_GET['sid'] ?? $_POST['sid'] ?? 'main';
                $_SESSION['identities'][$sid] = [
                    'user_id' => $user['id'],
                    'rol_id'  => $user['rol_id'],
                    'inicio'  => date('Y-m-d H:i:s')
                ];
                
                // Mantener compatibilidad con partes viejas si las hay
                $_SESSION['user_id'] = $user['id']; 

                header("Location: /reciclaje/router.php?sid=" . urlencode($sid));
                exit;
            }

            return 'Correo o contraseña incorrectos.';

        } catch (\PDOException $e) {
            return 'Error al procesar el inicio de sesión.';
        }
    }

    // Cierra la sesión de la identidad actual y redirige
    public function cerrarSesion() {
        $sid = $_GET['sid'] ?? $_POST['sid'] ?? 'main';
        
        if (isset($_SESSION['identities'][$sid])) {
            unset($_SESSION['identities'][$sid]);
        }
        
        // Si no quedan identidades, destruir sesión física
        if (empty($_SESSION['identities'])) {
            session_destroy();
        }
        
        header("Location: /reciclaje/views/public/login.php");
        exit;
    }
}
