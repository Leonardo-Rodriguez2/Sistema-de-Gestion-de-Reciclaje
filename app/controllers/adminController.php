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

                header("Location: router.php?page=usuarios&success=Usuario+creado+correctamente");
                exit;
            } catch (\PDOException $e) {
                $pdo->rollBack();
                $mensaje_error = "Error al crear usuario: " . $e->getMessage();
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
                header("Location: router.php?page=usuarios&success=Usuario+modificado+correctamente");
                exit;
            } catch (\PDOException $e) {
                $pdo->rollBack();
                $mensaje_error = "Error al editar usuario: " . $e->getMessage();
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
                // ... (existing code)
                $this->ejecutarConsulta(
                    "INSERT INTO viviendas (propietario, barrio_id, calle_id, direccion, numero_casa, encargado_calle_id)
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [
                        $_POST['propietario'],
                        (int)$_POST['barrio_id'],
                        (int)$_POST['calle_id'],
                        $_POST['direccion'],
                        $_POST['numero_casa'] ?? null,
                        $manager_id
                    ]
                );
                $mensaje_exito = "Vivienda registrada correctamente.";
            } catch (\PDOException $e) {
                $mensaje_error = "Error al registrar vivienda: " . $e->getMessage();
            }
        }

        // 6. Ordenar Baja Directa (Administrador)
        if ($action === 'ordenar_baja') {
            try {
                $vivienda_id = (int)$_POST['vivienda_id'];
                
                // 1. Obtener datos básicos
                $vStmt = $this->ejecutarConsulta("SELECT v.calle_id, v.barrio_id FROM viviendas v WHERE v.id = ?", [$vivienda_id]);
                $vData = $vStmt->fetch(\PDO::FETCH_ASSOC);

                if (!$vData) throw new \Exception("Vivienda no encontrada.");

                // 2. Calcular deuda actual
                $deudaStmt = $this->ejecutarConsulta(
                    "SELECT SUM(monto) as total, COUNT(*) as cantidad, 
                            GROUP_CONCAT(CONCAT(tipo_cobro, ' ', mes, '/', anio) SEPARATOR ', ') as resumen
                     FROM cobros 
                     WHERE vivienda_id = ? AND estado != 'Pagado'",
                    [$vivienda_id]
                );
                $deuda_info = $deudaStmt->fetch(\PDO::FETCH_ASSOC);
                $monto_deuda = $deuda_info['total'] ?? 0;
                $detalles_deuda = $deuda_info['resumen'] ?? 'Sin deudas pendientes';

                $pdo = $this->conectar();
                $pdo->beginTransaction();

                // 3. Crear solicitud ya aprobada (Baja Directa por Admin)
                $this->ejecutarConsulta(
                    "INSERT INTO solicitudes_vivienda (tipo, calle_id, vivienda_id, creado_por, revisado_por, estado, fecha_revision, monto_deuda, detalles_deuda)
                     VALUES ('Baja', ?, ?, ?, ?, 'Aprobado', CURRENT_TIMESTAMP, ?, ?)",
                    [$vData['calle_id'], $vivienda_id, $_SESSION['usuario_id'], $_SESSION['usuario_id'], $monto_deuda, $detalles_deuda]
                );

                // 4. Anular servicio
                $this->ejecutarConsulta("UPDATE viviendas SET estado_servicio = 'Anulado' WHERE id = ?", [$vivienda_id]);

                $pdo->commit();
                $mensaje_exito = "Servicio anulado directamente por Administración. Deuda registrada: S/ " . number_format($monto_deuda, 2);
            } catch (\Exception $e) {
                if(isset($pdo)) $pdo->rollBack();
                $mensaje_error = "Error al ejecutar baja: " . $e->getMessage();
            }
        }
    }


    // Inserta en la tabla de detalles según el rol
    private function insertarDetallesRol($pdo, $user_id, $rol_id) {
        $common_dni = $_POST['dni'] ?? null;
        $common_tel = $_POST['telefono'] ?? null;

        if ($rol_id == 5) { // Encargado de Barrio
            $pdo->prepare(
                "INSERT INTO detalles_encargado_barrio (usuario_id, barrio_id, dni, telefono, direccion)
                 VALUES (?, ?, ?, ?, ?)"
            )->execute([
                $user_id,
                (int)$_POST['barrio_id'],
                $common_dni,
                $common_tel,
                $_POST['direccion'] ?? null,
            ]);
        } elseif ($rol_id == 6) { // Encargado de Calle
            $pdo->prepare(
                "INSERT INTO detalles_encargado_calle (usuario_id, calle_id, dni, telefono)
                 VALUES (?, ?, ?, ?)"
            )->execute([
                $user_id,
                (int)$_POST['calle_id'],
                $common_dni,
                $common_tel,
            ]);
        } elseif ($rol_id == 2) { // Gestor
            $pdo->prepare(
                "INSERT INTO detalles_gestor (usuario_id, dni, telefono, area)
                 VALUES (?, ?, ?, ?)"
            )->execute([
                $user_id,
                $common_dni,
                $common_tel,
                $_POST['area'] ?? null,
            ]);
        } elseif ($rol_id == 3) { // Personal Obrero
            $pdo->prepare(
                "INSERT INTO detalles_personal_obrero (usuario_id, cargo, dni, telefono, turno)
                 VALUES (?, ?, ?, ?, ?)"
            )->execute([
                $user_id,
                $_POST['cargo'] ?? 'Recolector',
                $common_dni,
                $common_tel,
                $_POST['turno'] ?? 'Mañana',
            ]);
        }
    }
}




