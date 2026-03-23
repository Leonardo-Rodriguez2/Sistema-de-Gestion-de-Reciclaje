<?php

// =============================================
// autoload.php — Carga automática de clases
// Convierte namespaces a rutas de archivo.
// Ej: app\controllers\jefeController → app/controllers/jefeController.php
// =============================================

spl_autoload_register(function ($clase) {
    $archivo = __DIR__ . '/' . $clase . '.php';
    $archivo = str_replace('\\', '/', $archivo);

    if (is_file($archivo)) {
        require_once $archivo;
    }
});
