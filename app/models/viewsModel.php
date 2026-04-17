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
            'usuario_nuevo', // Keep for general use
            'usuario_editar',
            'usuario_ver',
            'barrios',
            'barrio_nuevo',
            'calles',
            'calle_nueva',
            'viviendas',
            'monitor_pagos',
            'registrar_vivienda',
            'solicitudes',
            'quitar_servicio',
            'reporte_bajas',
        ],
        'gestor' => [
            'dashboard',
            'viviendas',
            'registrar_vivienda',
            'usuarios',
            'usuario_ver',
            'usuario_editar',
            'usuario_nuevo_personal',
            'historial',
            'recibos',
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
            'quitar_servicio',
            'ordenes_baja',
            'solicitudes_renovacion',
            'reporte_bajas',
            'historial_solicitudes',
        ],
        'calle' => [
            'dashboard',
            'viviendas',
            'registrar_vivienda',
            'solicitar_baja',
            'reportar_pago',
            'quitar_servicio',
            'reporte_bajas',
            'solicitudes',
            'historial_solicitudes',
        ],
    ];

    // Devuelve la ruta del archivo de vista si está permitida
    protected function obtenerVista($page, $folder) {
        $permitidas = $this->listaBlanca[$folder] ?? [];

        if (in_array($page, $permitidas)) {
            $ruta = "views/{$folder}/{$page}.php";
            if (file_exists($ruta)) {
                return $ruta;
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
