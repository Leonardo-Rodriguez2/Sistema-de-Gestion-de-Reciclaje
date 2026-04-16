<?php
// views/gestor/usuarios.php
// Reutiliza la vista administrativa universal filtrada para Recolectores (Personal Obrero)
$page = 'usuarios';
$_GET['rol_id'] = 3;
include __DIR__ . '/../admin/usuarios.php';
?>
