<?php
session_start();
require_once 'data/conexion.php';

// Verificar que la sesión exista
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar que el usuario tenga rol de Gestor de Pagos (rol_id = 2) o superior (Admin = 1)
$stmt = $pdo->prepare("SELECT u.nombre, u.apellido, u.rol_id, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || ($user['rol_id'] != 1 && $user['rol_id'] != 2)) {
    die("Acceso denegado. Se requiere nivel de Gestor de Pagos.");
}

// Procesar formulario de nueva vivienda y vecino
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] == 'nuevo_vecino') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $barrio_id = $_POST['barrio_id'];
    $direccion = trim($_POST['direccion']);
    $numero = trim($_POST['numero']);

    // Crear al usuario con clave default "123456" y rol 4
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
        $mensaje_error = "Error al registrar: Puede que el correo ya exista.";
    }
}

// Obtener barrios para el formulario
$barriosStmt = $pdo->query("SELECT * FROM barrios ORDER BY nombre ASC");
$barrios = $barriosStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener cobros pendientes y vencidos
$cobrosStmt = $pdo->query("
    SELECT c.id, v.direccion, v.numero_casa, b.nombre as barrio, u.nombre, u.apellido, c.mes, c.anio, c.monto, c.estado, c.fecha_vencimiento 
    FROM cobros c
    JOIN viviendas v ON c.vivienda_id = v.id
    JOIN barrios b ON v.barrio_id = b.id
    JOIN usuarios u ON v.usuario_id = u.id
    WHERE c.estado IN ('Pendiente', 'Vencido')
    ORDER BY c.fecha_vencimiento ASC
");
$cobros_lista = $cobrosStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener últimos pagos
$pagosStmt = $pdo->query("
    SELECT p.id, p.monto_pagado, p.fecha_pago, p.metodo_pago, u.nombre, u.apellido, v.direccion
    FROM pagos p
    JOIN cobros c ON p.cobro_id = c.id
    JOIN viviendas v ON c.vivienda_id = v.id
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.fecha_pago DESC LIMIT 5
");
$pagos_lista = $pagosStmt->fetchAll(PDO::FETCH_ASSOC);

// Variables para el layout
$title = "Gestor de Pagos - EcoCusco";
$primary_color = "#D97706";
$secondary_color = "#B45309";
$bg_color = "#FFFBEB";
$header_title = "Gestión de Pagos y Recolección";
$header_subtitle = "Módulo de Finanzas";
$user_greeting = "Hola";

ob_start();
?>
    <!-- Stats -->
    <section class="grid">
      <div class="card">
        <h3>Cobros Pendientes</h3>
        <div class="value"><?php echo count($cobros_lista); ?></div>
      </div>
      <div class="card">
        <h3>Últimos Ingresos</h3>
        <div class="value">S/ <?php 
          $total = 0;
          foreach($pagos_lista as $p) $total += $p['monto_pagado'];
          echo number_format($total, 2);
        ?></div>
      </div>
    </section>

    <!-- Alertas -->
    <?php if(isset($mensaje_exito)): ?>
      <div style="background: #D1FAE5; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">✓ <?php echo $mensaje_exito; ?></div>
    <?php endif; ?>
    <?php if(isset($mensaje_error)): ?>
      <div style="background: #FEE2E2; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px;">✕ <?php echo $mensaje_error; ?></div>
    <?php endif; ?>

    <!-- Formulario de Registro Nuevo Vecino -->
    <section class="table-container" id="registrar_vecino" style="background: #FEF3C7; border: 1px solid #FDE68A;">
      <h3 style="color: #B45309;"><i class="fas fa-home"></i> Afiliar Nuevo Vecino y Vivienda</h3>
      <p style="color: #92400E; font-size: 14px; margin-bottom: 20px;">Use este formulario exclusivo para inscribir una nueva casa al servicio. El sistema creará la cuenta con la contraseña temporal: <b>123456</b></p>
      
      <form action="router.php?page=dashboard" method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <input type="hidden" name="form_type" value="nuevo_vecino">
        
        <div>
          <label style="display:block; font-size: 14px; font-weight: 500; margin-bottom: 5px;">Nombre del Responsable</label>
          <input type="text" name="nombre" required style="width: 90%; padding: 8px; border: 1px solid #D1D5DB; border-radius: 6px;">
        </div>
        <div>
          <label style="display:block; font-size: 14px; font-weight: 500; margin-bottom: 5px;">Apellido</label>
          <input type="text" name="apellido" required style="width: 90%; padding: 8px; border: 1px solid #D1D5DB; border-radius: 6px;">
        </div>
        <div>
          <label style="display:block; font-size: 14px; font-weight: 500; margin-bottom: 5px;">Correo Electrónico (Login)</label>
          <input type="email" name="email" required style="width: 90%; padding: 8px; border: 1px solid #D1D5DB; border-radius: 6px;">
        </div>
        <div>
          <label style="display:block; font-size: 14px; font-weight: 500; margin-bottom: 5px;">Barrio / Zona</label>
          <select name="barrio_id" required style="width: 95%; padding: 8px; border: 1px solid #D1D5DB; border-radius: 6px; background: white;">
            <?php foreach($barrios as $b): ?>
              <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['nombre']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="display:block; font-size: 14px; font-weight: 500; margin-bottom: 5px;">Dirección de la Casa</label>
          <input type="text" name="direccion" placeholder="Ej. Av. Los Pinos" required style="width: 90%; padding: 8px; border: 1px solid #D1D5DB; border-radius: 6px;">
        </div>
        <div>
          <label style="display:block; font-size: 14px; font-weight: 500; margin-bottom: 5px;">Número de Lote/Casa</label>
          <input type="text" name="numero" placeholder="Ej. L-12 o 404" required style="width: 90%; padding: 8px; border: 1px solid #D1D5DB; border-radius: 6px;">
        </div>
        
        <div style="grid-column: span 2; margin-top: 10px;">
          <button type="submit" class="btn-action" style="background: #B45309;">Registrar Vivienda en el Sistema</button>
        </div>
      </form>
    </section>

    <!-- Users Table -->
    <section class="table-container">
      <h3>Viviendas con Deuda Activa</h3>
      <table>
        <thead>
          <tr>
            <th>Propietario / Vecino</th>
            <th>Vivienda (Barrio)</th>
            <th>Período</th>
            <th>Monto</th>
            <th>Estado</th>
            <th>Vencimiento</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($cobros_lista)): ?>
            <tr><td colspan="7" style="text-align:center;">No hay viviendas con deuda.</td></tr>
          <?php else: ?>
            <?php foreach ($cobros_lista as $c): ?>
            <tr>
              <td style="font-weight: 500;"><?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido']); ?></td>
              <td><?php echo htmlspecialchars($c['direccion'] . ' ' . $c['numero_casa']); ?> <br><small style="color: #6B7280;"><?php echo htmlspecialchars($c['barrio']); ?></small></td>
              <td><?php echo htmlspecialchars(str_pad($c['mes'], 2, "0", STR_PAD_LEFT) . '/' . $c['anio']); ?></td>
              <td style="font-weight: 700; color: #374151;">S/ <?php echo number_format($c['monto'], 2); ?></td>
              <td>
                <span class="badge <?php echo strtolower($c['estado']); ?>"><?php echo htmlspecialchars($c['estado']); ?></span>
              </td>
              <td><?php echo date('d/m/Y', strtotime($c['fecha_vencimiento'])); ?></td>
              <td>
                <form action="router.php?page=dashboard" method="POST" style="margin:0;">
                  <input type="hidden" name="form_type" value="procesar_pago">
                  <input type="hidden" name="cobro_id" value="<?php echo $c['id']; ?>">
                  <button type="submit" class="btn-action">Marcar Pagado</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/dashboard_layout.php';
?>

