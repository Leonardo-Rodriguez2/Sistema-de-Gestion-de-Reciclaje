<?php

function check_dashboard_access($allowed_roles = [1]) {
    global $pdo;
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Manejo de identidad activa
    $sid = $_SESSION['active_sid'] ?? 'main';
    $user_id = $_SESSION['identities'][$sid]['user_id'] ?? $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        header("Location: /reciclaje/views/public/login.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT u.id, u.nombre, u.apellido, u.email, u.rol_id, r.nombre as rol_nombre 
                           FROM usuarios u JOIN roles r ON u.rol_id = r.id 
                           WHERE u.id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !in_array((int)$user['rol_id'], $allowed_roles)) {
        die("Acceso denegado. No tienes permisos para ver este panel.");
    }

    return $user;
}

/**
 * Genera el HTML de las alertas (éxito/error) si están definidas.
 */
function render_dashboard_alerts($exito, $error) {
    if (!$exito && !$error) return;
    include __DIR__ . '/../views/components/dashboard_alerts.php';
}

/**
 * Genera la cuadrícula de estadísticas.
 */
function render_dashboard_stats($stats = []) {
    include __DIR__ . '/../views/components/dashboard_stats.php';
}
?>
