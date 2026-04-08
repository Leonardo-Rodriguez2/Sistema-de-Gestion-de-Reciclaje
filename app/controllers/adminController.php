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
                $pdo->prepare("DELETE FROM detalles_encargado_barrio WHERE usuario_id=?")->execute([$u_id]);
                $pdo->prepare("DELETE FROM detalles_encargado_calle WHERE usuario_id=?")->execute([$u_id]);
                $pdo->prepare("DELETE FROM detalles_gestor WHERE usuario_id=?")->execute([$u_id]);
                $pdo->prepare("DELETE FROM detalles_personal_obrero WHERE usuario_id=?")->execute([$u_id]);
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

        // 3. Crear nueva calle
        if ($action === 'nueva_calle') {
            try {
                $this->ejecutarConsulta(
                    "INSERT INTO calles (nombre, barrio_id) VALUES (?, ?)",
                    [$_POST['nombre'], (int)$_POST['barrio_id']]
                );
                $mensaje_exito = "Calle registrada correctamente.";
            } catch (\PDOException $e) {
                $mensaje_error = "Error al registrar calle.";
            }
        }

        // 4. Crear nuevo barrio
        if ($action === 'nuevo_barrio') {
            try {
                $this->ejecutarConsulta(
                    "INSERT INTO barrios (nombre) VALUES (?)",
                    [$_POST['nombre']]
                );
                $mensaje_exito = "Barrio creado correctamente.";
            } catch (\PDOException $e) {
                $mensaje_error = "Error al crear barrio.";
            }
        }

        // 5. Registrar Vivienda (Directo Admin)
        if ($action === 'nuevo_vecino_admin') {
            try {
                $this->ejecutarConsulta(
                    "INSERT INTO viviendas (propietario, barrio_id, calle_id, direccion, numero_casa)
                     VALUES (?, ?, ?, ?, ?)",
                    [
                        $_POST['propietario'],
                        (int)$_POST['barrio_id'],
                        (int)$_POST['calle_id'],
                        $_POST['direccion'],
                        $_POST['numero_casa'] ?? null
                    ]
                );
                $mensaje_exito = "Vivienda registrada correctamente.";
            } catch (\PDOException $e) {
                $mensaje_error = "Error al registrar vivienda.";
            }
        }
    }


    // Inserta en la tabla de detalles según el rol
    private function insertarDetallesRol($pdo, $user_id, $rol_id) {
        if ($rol_id == 5) { // Encargado de Barrio
            $pdo->prepare(
                "INSERT INTO detalles_encargado_barrio (usuario_id, barrio_id, dni, telefono, direccion)
                 VALUES (?, ?, ?, ?, ?)"
            )->execute([
                $user_id,
                (int)$_POST['barrio_id'],
                $_POST['dni'] ?? null,
                $_POST['telefono'] ?? null,
                $_POST['direccion'] ?? null,
            ]);
        } elseif ($rol_id == 6) { // Encargado de Calle
            $pdo->prepare(
                "INSERT INTO detalles_encargado_calle (usuario_id, calle_id, dni, telefono)
                 VALUES (?, ?, ?, ?)"
            )->execute([
                $user_id,
                (int)$_POST['calle_id'],
                $_POST['dni_calle'] ?? null,
                $_POST['telefono_calle'] ?? null,
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
        } elseif ($rol_id == 3) { // Personal Obrero
            $pdo->prepare(
                "INSERT INTO detalles_personal_obrero (usuario_id, cargo, dni, telefono, turno)
                 VALUES (?, ?, ?, ?, ?)"
            )->execute([
                $user_id,
                $_POST['cargo'] ?? 'Recolector',
                $_POST['dni_personal'] ?? null,
                $_POST['telefono_personal'] ?? null,
                $_POST['turno'] ?? 'Mañana',
            ]);
        }
    }
}




