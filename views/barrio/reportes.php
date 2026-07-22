<?php
// views/barrio/reportes.php — Redirige al reporte del gestor (mismo contenido)
$user = check_dashboard_access([5]);
$page = 'reportes';
include __DIR__ . '/../gestor/reportes.php';
?>
