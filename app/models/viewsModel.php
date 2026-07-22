<?php

namespace app\models;

// =============================================
// app/models/viewsModel.php — Control de Vistas
// Define qué páginas puede ver cada rol.
// Para añadir una nueva página a un rol, solo
// agrega su nombre aquí y crea el archivo .php
// en views/{rol}/nombrePagina.php
// =============================================

class viewsModel extends mainModel {

    private $listaBlanca = [
        'admin' => [
            'dashboard',
            'usuarios',
            'usuario_nuevo',
            'usuario_editar',
            'usuario_ver',
            'barrios',
            'barrio_nuevo',
            'barrio_editar',
            'calles',
            'calle_nueva',
            'calle_editar',
            'viviendas',
            'monitor_pagos',
            'registrar_vivienda',
            'vivienda_editar',
            'solicitudes',
            'quitar_servicio',
            'reporte_bajas',
            'gestor_dashboard',
            'gestor_viviendas',
            'gestor_usuarios',
            'gestor_usuario_nuevo_personal',
            'gestor_historial',
            'gestor_recibos',
            'gestor_ver_recibo_finiquito',
            'gestor_registrar_vivienda',
            'ver_recibo_finiquito',
            'reportes',
            'certificado_calle',
            'exportar_pagos',
            'revisar_lotes',
        ],
        'gestor' => [
            'reportes',
            'dashboard',
            'viviendas',
            'registrar_vivienda',
            'usuarios',
            'usuario_nuevo_personal',
            'historial',
            'recibos',
            'ver_recibo_finiquito',
            'certificado_calle',
            'barrios',
            'exportar_pagos',
            'revisar_lotes',
        ],
        'personal' => [
            'dashboard',
        ],
        'barrio' => [
            'dashboard',
            'viviendas',
            'calles',
            'solicitudes',
            'registrar_vivienda',
            'reportar_pago',
            'exonerados',
            'quitar_servicio',
            'ordenes_baja',
            'solicitudes_renovacion',
            'reporte_bajas',
            'historial_solicitudes',
            'configuracion',
            'seguimiento_pagos',
            'reportes',
            'certificado_calle',
            'certificados',
        ],
        'calle' => [
            'dashboard',
            'viviendas',
            'registrar_vivienda',
            'solicitar_baja',
            'reportar_pago',
            'solicitar_exoneracion',
            'quitar_servicio',
            'reporte_bajas',
            'solicitudes',
            'historial_solicitudes',
            'facturas',
            'certificado_calle',
            'historial_vivienda',
        ],
    ];

    // Devuelve la ruta del archivo de vista si está permitida
    protected function obtenerVista($page, $folder) {
        $permitidas = $this->listaBlanca[$folder] ?? [];

        if (in_array($page, $permitidas)) {
            // 1. Try folder-specific path
            $ruta = "views/{$folder}/{$page}.php";
            if (file_exists($ruta)) {
                return $ruta;
            }
            // 2. Gestor prefix: strip gestor_ and check views/gestor/
            if ($folder === 'admin' && strpos($page, 'gestor_') === 0) {
                $actualPage = substr($page, 7);
                $ruta = "views/gestor/{$actualPage}.php";
                if (file_exists($ruta)) {
                    return $ruta;
                }
            }
            // 3. Fallback: admin can load from views/gestor/ if page not in views/admin/
            if ($folder === 'admin') {
                $ruta = "views/gestor/{$page}.php";
                if (file_exists($ruta)) {
                    return $ruta;
                }
            }
            // 4. Barrio/Calle can use gestor's certificado_calle
            if (in_array($folder, ['barrio', 'calle']) && $page === 'certificado_calle') {
                $ruta = "views/gestor/certificado_calle.php";
                if (file_exists($ruta)) {
                    return $ruta;
                }
            }
        }

        // Si la página no existe o no está permitida → dashboard del rol
        return "views/{$folder}/dashboard.php";
    }

    // Devuelve el nombre de carpeta según el rol_id
    protected function obtenerCarpetaRol($rol_id) {
        $mapa = [
            1 => 'admin',
            2 => 'gestor',
            3 => 'personal',
            5 => 'barrio',
            6 => 'calle',
        ];
        return $mapa[$rol_id] ?? null;
    }
}
