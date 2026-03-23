<?php

namespace app\models;
use \PDO;

// =============================================
// app/models/mainModel.php — Modelo Base
// Todas las clases del sistema heredan de aquí.
// Contiene la conexión a BD y métodos CRUD genéricos.
// =============================================

require_once __DIR__ . '/../../app/config.php';

class mainModel {

    private $server = DB_SERVER;
    private $db     = DB_NAME;
    private $user   = DB_USER;
    private $pass   = DB_PASS;


    // --- Conexión PDO (se reutiliza via static) ---
    protected function conectar() {
        static $conexion = null;
        if ($conexion === null) {
            $conexion = new PDO(
                "mysql:host={$this->server};dbname={$this->db};charset=utf8mb4",
                $this->user,
                $this->pass
            );
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $conexion;
    }


    // --- Ejecutar cualquier consulta con parámetros ---
    protected function ejecutarConsulta($sql, $params = []) {
        $stmt = $this->conectar()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }


    // --- Obtener un usuario por ID (usado en todos los roles) ---
    protected function obtenerUsuario($id) {
        $stmt = $this->ejecutarConsulta(
            "SELECT u.id, u.nombre, u.apellido, u.email, u.rol_id, r.nombre as rol_nombre
             FROM usuarios u JOIN roles r ON u.rol_id = r.id
             WHERE u.id = ?",
            [$id]
        );
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // --- Limpiar texto de entrada del usuario ---
    public function limpiarCadena($cadena) {
        $cadena = trim($cadena);
        $cadena = stripslashes($cadena);
        $cadena = htmlspecialchars($cadena, ENT_QUOTES, 'UTF-8');
        return $cadena;
    }


    // --- INSERT genérico ---
    // $datos = [['campo_nombre'=>'col', 'campo_marcador'=>':col', 'campo_valor'=>$val], ...]
    protected function guardarDatos($tabla, $datos) {
        $campos   = implode(',', array_column($datos, 'campo_nombre'));
        $marcadores = implode(',', array_column($datos, 'campo_marcador'));
        $sql = "INSERT INTO $tabla ($campos) VALUES ($marcadores)";
        $stmt = $this->conectar()->prepare($sql);
        foreach ($datos as $d) {
            $stmt->bindParam($d['campo_marcador'], $d['campo_valor']);
        }
        $stmt->execute();
        return $stmt;
    }


    // --- UPDATE genérico ---
    // $condicion = ['condicion_campo'=>'id', 'condicion_marcador'=>':id', 'condicion_valor'=>$val]
    protected function actualizarDatos($tabla, $datos, $condicion) {
        $sets = implode(',', array_map(fn($d) => $d['campo_nombre'] . '=' . $d['campo_marcador'], $datos));
        $sql = "UPDATE $tabla SET $sets WHERE {$condicion['condicion_campo']} = {$condicion['condicion_marcador']}";
        $stmt = $this->conectar()->prepare($sql);
        foreach ($datos as $d) {
            $stmt->bindParam($d['campo_marcador'], $d['campo_valor']);
        }
        $stmt->bindParam($condicion['condicion_marcador'], $condicion['condicion_valor']);
        $stmt->execute();
        return $stmt;
    }


    // --- DELETE genérico ---
    protected function eliminarRegistro($tabla, $campo, $id) {
        $stmt = $this->conectar()->prepare("DELETE FROM $tabla WHERE $campo = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt;
    }
}
