<?php
session_start();
require_once 'data/conexion.php';

// Verificar que la sesión exista
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar que el usuario tenga rol de Usuario/Vecino (rol_id = 4)
$stmt = $pdo->prepare("SELECT u.nombre, u.apellido, u.rol_id, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['rol_id'] != 4) {
    die("Acceso denegado. Este panel es exclusivo para usuarios residenciales.");
}

// Obtener las viviendas del usuario
$viviendasStmt = $pdo->prepare("SELECT v.id, v.direccion, v.numero_casa, b.nombre as barrio FROM viviendas v JOIN barrios b ON v.barrio_id = b.id WHERE v.usuario_id = ?");
$viviendasStmt->execute([$_SESSION['user_id']]);
$viviendas = $viviendasStmt->fetchAll(PDO::FETCH_ASSOC);

$vivienda_ids = array_column($viviendas, 'id');
$cobros_lista = [];

if (count($vivienda_ids) > 0) {
    // Preparar el query para obtener los cobros de SUS viviendas
    $in = str_repeat('?,', count($vivienda_ids) - 1) . '?';
    $cobrosStmt = $pdo->prepare("
        SELECT c.id, v.direccion, v.numero_casa, c.mes, c.anio, c.monto, c.estado, c.fecha_vencimiento 
        FROM cobros c
        JOIN viviendas v ON c.vivienda_id = v.id
        WHERE c.vivienda_id IN ($in)
        ORDER BY c.fecha_vencimiento DESC
    ");
    $cobrosStmt->execute($vivienda_ids);
    $cobros_lista = $cobrosStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Variables para el layout
$title = "Mi Panel Residencial - EcoCusco";
$header_title = "Mi Estado de Cuenta";
$header_subtitle = "Módulo Residencial";
$user_greeting = "Bienvenido(a)";

ob_start();
?>
    <!-- Stats -->
    <section class="grid">
      <div class="card">
        <h3>Viviendas Registradas</h3>
        <div class="value"><?php echo count($viviendas); ?></div>
      </div>
      <div class="card" style="border-top-color: #DC2626;">
        <h3 style="color: #DC2626;">Total Por Pagar (Atrasado)</h3>
        <div class="value" style="color: #DC2626;">S/ <?php 
          $deuda = 0;
          foreach($cobros_lista as $c) if($c['estado'] == 'Vencido') $deuda += $c['monto'];
          echo number_format($deuda, 2);
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

    <!-- Report Table -->
    <section class="table-container">
      <h3>Historial de Recibos y Mensualidades</h3>
      <table>
        <thead>
          <tr>
            <th>Propiedad</th>
            <th>Período Cobrado</th>
            <th>Vencimiento</th>
            <th>Monto</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($cobros_lista)): ?>
            <tr><td colspan="6" style="text-align:center;">No tienes propiedades registradas ni cobros pendientes.</td></tr>
          <?php else: ?>
            <?php foreach ($cobros_lista as $c): ?>
            <tr>
              <td style="font-weight: 500; color: #4B5563;"><?php echo htmlspecialchars($c['direccion'] . ' ' . $c['numero_casa']); ?></td>
              <td><?php echo htmlspecialchars(str_pad($c['mes'], 2, "0", STR_PAD_LEFT) . ' / ' . $c['anio']); ?></td>
              <td><?php echo date('d/m/Y', strtotime($c['fecha_vencimiento'])); ?></td>
              <td style="font-weight: 700; color: #374151;">S/ <?php echo number_format($c['monto'], 2); ?></td>
              <td>
                <span class="badge <?php echo strtolower($c['estado']); ?>"><?php echo htmlspecialchars($c['estado']); ?></span>
              </td>
              <td>
                  <?php if($c['estado'] != 'Pagado'): ?>
                    <form action="router.php?page=dashboard" method="POST" style="margin:0;">
                      <input type="hidden" name="form_type" value="procesar_pago">
                      <input type="hidden" name="cobro_id" value="<?php echo $c['id']; ?>">
                      <button type="submit" class="btn-action">Pagar Ahora</button>
                    </form>
                  <?php else: ?>
                    <span style="color: #059669; font-weight: 500; font-size: 14px;">✔ Cancelado</span>
                  <?php endif; ?>
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

