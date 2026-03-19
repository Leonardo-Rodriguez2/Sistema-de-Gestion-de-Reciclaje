<?php
session_start();
require_once 'data/conexion.php';
require_once 'data/dashboard_helper.php';

// Redirigir al login si no hay sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: /reciclaje/views/public/login.php");
    exit;
}

// Obtener datos del usuario logueado
$stmt = $pdo->prepare("SELECT u.id, u.nombre, u.apellido, u.rol_id, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: /reciclaje/views/public/login.php");
    exit;
}

// Lógica Compartida: Procesar Nueva Vivienda (Solo Admin o Gestor)
$mensaje_exito = null;
$mensaje_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'nuevo_vecino') {
    if ($user['rol_id'] == 1 || $user['rol_id'] == 2) {
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $email = trim($_POST['email']);
        $barrio_id = $_POST['barrio_id'];
        $direccion = trim($_POST['direccion']);
        $numero = trim($_POST['numero']);

        $password_hash = password_hash('123456', PASSWORD_BCRYPT);
        try {
            $pdo->beginTransaction();
            $stmtU = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password_hash, rol_id) VALUES (?, ?, ?, ?, 4)");
            $stmtU->execute([$nombre, $apellido, $email, $password_hash]);
            $nuevo_usuario_id = $pdo->lastInsertId();

            $stmtV = $pdo->prepare("INSERT INTO viviendas (usuario_id, barrio_id, direccion, numero_casa) VALUES (?, ?, ?, ?)");
            $stmtV->execute([$nuevo_usuario_id, $barrio_id, $direccion, $numero]);
            $pdo->commit();
            $mensaje_exito = "Familia y vivienda registrada correctamente.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensaje_error = "Error: El correo electrónico ya existe en el sistema.";
        }
    }
}

// Lógica: Procesar Pago (Solo Admin, Gestor o el Usuario Dueño)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'procesar_pago' && isset($_POST['cobro_id'])) {
    $cobro_id = $_POST['cobro_id'];
    
    // Si es Usuario normal, validar que el cobro es de su vivienda
    $puede_pagar = false;
    if ($user['rol_id'] == 1 || $user['rol_id'] == 2) {
        $puede_pagar = true; 
    } else if ($user['rol_id'] == 4) {
        // Verificar que el cobro le pertenece
        $vStmt = $pdo->prepare("SELECT id FROM viviendas WHERE usuario_id = ?");
        $vStmt->execute([$user['id']]);
        $vivienda_ids = $vStmt->fetchAll(PDO::FETCH_COLUMN);
        
        $cStmt = $pdo->prepare("SELECT vivienda_id FROM cobros WHERE id = ?");
        $cStmt->execute([$cobro_id]);
        $cobro = $cStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cobro && in_array($cobro['vivienda_id'], $vivienda_ids)) {
            $puede_pagar = true;
        }
    }

    if ($puede_pagar) {
        try {
            $pdo->beginTransaction();
            // Marcar cobro pagado
            $stmtPago = $pdo->prepare("UPDATE cobros SET estado = 'Pagado' WHERE id = ?");
            $stmtPago->execute([$cobro_id]);
            
            // Insertar registro de pago
            $cStmt = $pdo->prepare("SELECT monto FROM cobros WHERE id = ?");
            $cStmt->execute([$cobro_id]);
            $monto = $cStmt->fetchColumn();

            $pagoStmt = $pdo->prepare("INSERT INTO pagos (cobro_id, usuario_id, monto_pagado, metodo_pago) VALUES (?, ?, ?, 'Transferencia / Efectivo')");
            $pagoStmt->execute([$cobro_id, $user['id'], $monto]);
            
            $pdo->commit();
            $mensaje_exito = "Pago procesado exitosamente.";
        } catch(PDOException $e) {
            $pdo->rollBack();
            $mensaje_error = "Error al procesar el pago.";
        }
    }
}

// Lógica: Completar Reporte (Solo Recolector o Admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'completar_reporte' && isset($_POST['reporte_id'])) {
    if ($user['rol_id'] == 1 || $user['rol_id'] == 3) {
        $stmtR = $pdo->prepare("UPDATE reportes SET estado = 'Completado' WHERE id = ?");
        $stmtR->execute([$_POST['reporte_id']]);
        $mensaje_exito = "Punto de recolección marcado como completado.";
    }
}

// Lógica Admin: Gestión de Usuarios
if ($user['rol_id'] == 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agregar Usuario
    if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $email = trim($_POST['email']);
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : password_hash('123456', PASSWORD_BCRYPT);
        $rol_id = (int)$_POST['rol_id'];

        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password_hash, rol_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellido, $email, $password, $rol_id]);
            $mensaje_exito = "Usuario agregado correctamente.";
        } catch (PDOException $e) {
            $mensaje_error = "Error: El correo electrónico ya existe.";
        }
    }

    // Editar Usuario
    if (isset($_POST['action']) && $_POST['action'] === 'edit_user') {
        $id = (int)$_POST['user_id'];
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $email = trim($_POST['email']);
        $rol_id = (int)$_POST['rol_id'];

        try {
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, rol_id = ?, password_hash = ? WHERE id = ?");
                $stmt->execute([$nombre, $apellido, $email, $rol_id, $password, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, rol_id = ? WHERE id = ?");
                $stmt->execute([$nombre, $apellido, $email, $rol_id, $id]);
            }
            $mensaje_exito = "Usuario actualizado correctamente.";
        } catch (PDOException $e) {
            $mensaje_error = "Error al actualizar el usuario.";
        }
    }

    // Eliminar Usuario
    if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $id = (int)$_POST['user_id'];
        if ($id != $user['id']) { // Evitar eliminarse a sí mismo
            try {
                // Primero ver si tiene reportes, viviendas, etc. (En un sistema real usaríamos borrado lógico)
                // Aquí intentaremos el borrado físico si no hay dependencias fuertes
                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$id]);
                $mensaje_exito = "Usuario eliminado correctamente.";
            } catch (PDOException $e) {
                $mensaje_error = "No se puede eliminar el usuario porque tiene registros asociados (viviendas o reportes).";
            }
        } else {
            $mensaje_error = "No puedes eliminar tu propia cuenta de administrador.";
        }
    }
}


// INCLUSIÓN DINÁMICA DE LA VISTA (FRONT-CONTROLLER)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Enrutar según Rol y Página Solicitada
$view_path = '';

switch($user['rol_id']) {
    case 1: // ADMIN
        $view_path = 'views/admin_dashboard_view.php';
        break;
        
    case 2: // GESTOR
        $view_path = 'views/gestor_dashboard_view.php';
        break;
        
    case 3: // RECOLECTOR
        $view_path = 'views/recolector_dashboard_view.php';
        break;
        
    case 4: // USUARIO VECINO
        if ($page == 'reportar') {
            header("Location: /reciclaje/views/public/reportes.php"); 
            exit;
        }
        $view_path = 'views/usuario_dashboard_view.php';
        break;
        
    default:
        die("Rol desconocido");
}

// Cargar la vista
if (file_exists($view_path)) {
    require_once $view_path;
} else {
    die("Error: La vista no existe.");
}
?>

?>
