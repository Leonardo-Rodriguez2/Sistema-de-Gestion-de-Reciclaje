<?php
// views/gestor/usuario_nuevo_personal.php
// Reutiliza la vista administrativa con permisos para el Gestor
// Reutiliza la vista administrativa universal filtrada para Recolectores (Personal Obrero)
$_GET['rol_id'] = 3;
include __DIR__ . '/../admin/usuario_nuevo.php';
?>
