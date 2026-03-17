<?php
session_start();
require_once 'data/conexion.php';

// Verificar que la sesión exista
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar que el usuario tenga rol de Recolector (rol_id = 3) o superior
$stmt = $pdo->prepare("SELECT u.nombre, u.apellido, u.rol_id, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || ($user['rol_id'] != 1 && $user['rol_id'] != 3)) {
    die("Acceso denegado. Se requiere nivel de Recolector.");
}

// Obtener reportes activos (En proceso o Pendiente de asignar)
$reportesStmt = $pdo->query("
    SELECT r.id, r.ubicacion_nombre, r.tipo_residuo, r.descripcion, r.cantidad, r.estado, r.fecha_reporte, u.nombre, u.apellido 
    FROM reportes r
    JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.estado IN ('Pendiente', 'En proceso')
    ORDER BY r.fecha_reporte ASC
");
$reportes_lista = $reportesStmt->fetchAll(PDO::FETCH_ASSOC);

// Variables para el layout
$title = "Recolector - EcoCusco";
$primary_color = "#4F46E5";
$secondary_color = "#4338CA";
$bg_color = "#EEF2FF";
$header_title = "Mis Rutas y Pendientes";
$header_subtitle = "Módulo Logístico y de Recolección";
$user_greeting = "Hola";

ob_start();
?>
    <!-- Stats -->
    <section class="grid">
      <div class="card">
        <h3>Reportes por Atender</h3>
        <div class="value"><?php echo count($reportes_lista); ?></div>
      </div>
      <div class="card">
        <h3>Material Recolectado Hoy</h3>
        <div class="value">0.0 kg</div>
      </div>
    </section>

    <!-- Alertas -->
    <?php if(isset($mensaje_exito)): ?>
      <div style="background: #DBEAFE; color: #1E40AF; padding: 15px; border-radius: 8px; margin-bottom: 20px;">✓ <?php echo $mensaje_exito; ?></div>
    <?php endif; ?>
    <?php if(isset($mensaje_error)): ?>
      <div style="background: #FEE2E2; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px;">✕ <?php echo $mensaje_error; ?></div>
    <?php endif; ?>

    <!-- Report Table -->
    <section class="table-container">
      <h3>Lista de Puntos de Recolección</h3>
      <table>
        <thead>
          <tr>
            <th>Ubicación</th>
            <th>Tipo de Residuo</th>
            <th>Cantidad</th>
            <th>Reportado Por</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($reportes_lista)): ?>
            <tr><td colspan="6" style="text-align:center;">No hay reportes pendientes para recolectar hoy. ¡Buen trabajo!</td></tr>
          <?php else: ?>
            <?php foreach ($reportes_lista as $r): ?>
            <tr>
              <td style="font-weight: 500;"><?php echo htmlspecialchars($r['ubicacion_nombre']); ?> <br> <small style="color:#6B7280;font-weight:400;"><?php echo htmlspecialchars($r['descripcion']); ?></small></td>
              <td><?php echo htmlspecialchars($r['tipo_residuo']); ?></td>
              <td style="font-weight: 700; color: #374151;"><?php echo number_format($r['cantidad'], 2); ?> kg</td>
              <td><?php echo htmlspecialchars($r['nombre'] . ' ' . $r['apellido']); ?><br><small><?php echo date('d/m/Y H:i', strtotime($r['fecha_reporte'])); ?></small></td>
              <td>
                <span class="badge <?php echo str_replace(' ', '-', strtolower($r['estado'])); ?>"><?php echo htmlspecialchars($r['estado']); ?></span>
              </td>
              <td>
                <form action="router.php?page=dashboard" method="POST" style="margin:0;">
                  <input type="hidden" name="form_type" value="completar_reporte">
                  <input type="hidden" name="reporte_id" value="<?php echo $r['id']; ?>">
                  <button type="submit" class="btn-action">Marcar Recogido</button>
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

