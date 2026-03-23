<?php

// =============================================
// router.php — Área Privada (Usuarios Logueados)
// Punto de entrada única para el área protegida.
// Carga el controlador, obtiene los datos y
// renderiza la vista en el scope global.
// =============================================

session_start();

require_once 'app/config.php';
require_once 'autoload.php';
require_once 'app/helpers.php';

use app\controllers\viewsController;

// 1. Si no hay sesión → login
if (!isset($_SESSION['user_id'])) {
    header("Location: /reciclaje/views/public/login.php");
    exit;
}

// 2. El controlador valida, procesa POST, y devuelve los datos
$ctrl = new viewsController();
$datos = $ctrl->preparar();

// 3. Inyectar variables en scope global para que la vista
//    (y el sidebar incluido dentro de ella) las pueda usar
$pdo            = $datos['pdo'];
$user           = $datos['user'];
$page           = $datos['page'];
$mensaje_exito  = $mensaje_exito ?? null;  // Definidas por el controller de rol (si hubo POST)
$mensaje_error  = $mensaje_error ?? null;

// 4. Renderizar la vista (se ejecuta en scope global → sidebar OK)
require_once $datos['vista'];
