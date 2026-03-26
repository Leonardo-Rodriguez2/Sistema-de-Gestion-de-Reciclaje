<?php

namespace app\controllers;
use app\models\mainModel;

// =============================================
// app/controllers/adminController.php
// Acciones POST exclusivas del Administrador.
// Gestión completa de usuarios del sistema.
// =============================================

class adminController extends mainModel {

    public function procesarAcciones() {
        global $mensaje_exito, $mensaje_error;

        $action = $_POST['action'] ?? $_POST['form_type'] ?? null;

        // 1. Crear nuevo usuario
        if ($action === 'add_user') {
            $nombre    = $_POST['nombre'];
            $apellido  = $_POST['apellido'];
            $email     = $_POST['email'];
            $genero    = $_POST['genero'] ?: null;
            $fecha_nac = $_POST['fecha_nacimiento'] ?: null;
            $password  = !empty($_POST['password'])
                ? password_hash($_POST['password'], PASSWORD_DEFAULT)
                : password_hash('123456', PASSWORD_DEFAULT);
            $rol_id    = (int)$_POST['rol_id'];

            try {
                $pdo = $this->conectar();
                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    "INSERT INTO usuarios (nombre, apellido, email, genero, fecha_nacimiento, password_hash, rol_id)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$nombre, $apellido, $email, $genero, $fecha_nac, $password, $rol_id]);
                $new_id = $pdo->lastInsertId();

                $this->insertarDetallesRol($pdo, $new_id, $rol_id);
                $pdo->commit();

                header("Location: router.php?page=usuarios&success=Usuario creado correctamente");
                exit;
            } catch (\PDOException $e) {
                $pdo->rollBack();
                header("Location: router.php?page=usuarios&error=" . urlencode($e->getMessage()));
                exit;
            }
        }

        // 2. Editar usuario existente
        if ($action === 'edit_user') {
            $u_id      = (int)$_POST['user_id'];
            $genero    = $_POST['genero'] ?: null;
            $fecha_nac = $_POST['fecha_nacimiento'] ?: null;
            $rol_id    = (int)$_POST['rol_id'];

            try {
                $pdo = $this->conectar();
                $pdo->beginTransaction();

                $sql    = "UPDATE usuarios SET nombre=?, apellido=?, email=?, genero=?, fecha_nacimiento=?, rol_id=?";
                $params = [$_POST['nombre'], $_POST['apellido'], $_POST['email'], $genero, $fecha_nac, $rol_id];
                if (!empty($_POST['password'])) {
                    $sql     .= ", password_hash=?";
                    $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }
                $sql     .= " WHERE id=?";
                $params[] = $u_id;
                $pdo->prepare($sql)->execute($params);

                // Limpiar y re-insertar detalles
                $pdo->prepare("DELETE FROM detalles_jefe_cuadra WHERE usuario_id=?")->execute([$u_id]);
                $pdo->prepare("DELETE FROM detalles_gestor WHERE usuario_id=?")->execute([$u_id]);
                $pdo->prepare("DELETE FROM detalles_recolector WHERE usuario_id=?")->execute([$u_id]);
                $this->insertarDetallesRol($pdo, $u_id, $rol_id);

                $pdo->commit();
                header("Location: router.php?page=usuarios&success=Usuario modificado correctamente");
                exit;
            } catch (\PDOException $e) {
                $pdo->rollBack();
                header("Location: router.php?page=usuarios&error=" . urlencode($e->getMessage()));
                exit;
            }
        }
    }


    // Inserta en la tabla de detalles según el rol
    private function insertarDetallesRol($pdo, $user_id, $rol_id) {
        if ($rol_id == 5) { // Jefe de Cuadra
            $pdo->prepare(
                "INSERT INTO detalles_jefe_cuadra (usuario_id, barrio_id, dni, telefono, direccion)
                 VALUES (?, ?, ?, ?, ?)"
            )->execute([
                $user_id,
                (int)$_POST['barrio_id'],
                $_POST['dni'] ?? null,
                $_POST['telefono'] ?? null,
                $_POST['direccion'] ?? null,
            ]);
        } elseif ($rol_id == 2) { // Gestor
            $pdo->prepare(
                "INSERT INTO detalles_gestor (usuario_id, dni, telefono, area)
                 VALUES (?, ?, ?, ?)"
            )->execute([
                $user_id,
                $_POST['dni_gestor'] ?? null,
                $_POST['telefono_gestor'] ?? null,
                $_POST['area'] ?? null,
            ]);
        } elseif ($rol_id == 3) { // Recolector
            $pdo->prepare(
                "INSERT INTO detalles_recolector (usuario_id, dni, telefono, turno, contacto_emergencia)
                 VALUES (?, ?, ?, ?, ?)"
            )->execute([
                $user_id,
                $_POST['dni_recolector'] ?? null,
                $_POST['telefono_recolector'] ?? null,
                $_POST['turno'] ?? 'Mañana',
                $_POST['contacto_emergencia'] ?? null,
            ]);
        }
    }
}
