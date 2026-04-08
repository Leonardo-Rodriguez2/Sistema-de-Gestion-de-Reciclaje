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
            'usuario_nuevo_barrio',
            'usuario_nuevo_calle',
            'usuario_nuevo_gestor',
            'usuario_nuevo_personal',
            'usuario_editar',
            'usuario_ver',
            'barrios',
            'barrio_nuevo',
            'calles',
            'viviendas',
            'registrar_vivienda',
            'solicitudes',
        ],
        'gestor' => [
            'dashboard',
            'viviendas',
            'registrar_vivienda',
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
            'reportar_pago',
        ],
        'calle' => [
            'dashboard',
            'viviendas',
            'registrar_vivienda',
            'solicitar_baja',
            'reportar_pago',
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
