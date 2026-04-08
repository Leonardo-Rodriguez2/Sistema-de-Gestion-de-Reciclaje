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

// 2. Manejo de Multi-Sesión (Identidad por Pestaña)
$sid = $_GET['sid'] ?? $_POST['sid'] ?? 'main';
$_SESSION['active_sid'] = $sid;

// Sincronizar user_id global para compatibilidad con controladores y modelos existentes
if (isset($_SESSION['identities'][$sid]['user_id'])) {
    $_SESSION['user_id'] = $_SESSION['identities'][$sid]['user_id'];
} else {
    // Si la identidad no existe en la sesión física, limpiar user_id para forzar login
    unset($_SESSION['user_id']);
}

// 3. El controlador valida, procesa POST, y devuelve los datos
$ctrl = new viewsController();
$datos = $ctrl->preparar();

// 4. Inyectar variables en scope global
$pdo            = $datos['pdo'];
$page           = $datos['page'];
$mensaje_exito  = $mensaje_exito ?? null;
$mensaje_error  = $mensaje_error ?? null;

// Obtener el usuario de la identidad específica
$user = $_SESSION['identities'][$sid] ?? null;

// Si por alguna razón la identidad no tiene usuario → login
if (!$user) {
    header("Location: /reciclaje/views/public/login.php");
    exit;
}

// 4. Renderizar la vista (se ejecuta en scope global → sidebar OK)
require_once $datos['vista'];
