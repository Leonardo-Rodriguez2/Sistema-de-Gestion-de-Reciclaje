<?php

namespace app\controllers;
use app\models\mainModel;
use \PDO;

// =============================================
// app/controllers/loginController.php
// Maneja el inicio y cierre de sesión.
// =============================================

class loginController extends mainModel {

    // Intenta iniciar sesión, devuelve mensaje de error o null si exitoso
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
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                header("Location: /reciclaje/router.php");
                exit;
            }

            return 'Correo o contraseña incorrectos.';

        } catch (\PDOException $e) {
            return 'Error al procesar el inicio de sesión.';
        }
    }

    // Cierra la sesión y redirige al inicio público
    public function cerrarSesion() {
        session_destroy();
        header("Location: /reciclaje/index.php");
        exit;
    }
}
