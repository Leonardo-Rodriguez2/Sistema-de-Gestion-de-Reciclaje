<?php
// recolector_dashboard_view.php
// Rediseñado con Componentes y Helper

$user = check_dashboard_access([1, 3]);

// Obtener reportes activos
$reportesStmt = $pdo->query("
    SELECT r.id, r.ubicacion_nombre, r.tipo_residuo, r.descripcion, r.cantidad, r.estado, r.fecha_reporte, u.nombre, u.apellido 
    FROM reportes r
    JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.estado IN ('Pendiente', 'En proceso')
    ORDER BY r.fecha_reporte ASC
");
$reportes_lista = $reportesStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Recolector - EcoCusco";
$primary_color = "#4F46E5";
$secondary_color = "#4338CA";
$bg_color = "#EEF2FF";
$header_title = "Rutas de Recolección";
$header_subtitle = "Módulo Logístico";
$user_greeting = "Hola";

ob_start();
?>
    <!-- Feedback Messages -->
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Stats -->
    <?php render_dashboard_stats([
        ['title' => 'Reportes Pendientes', 'value' => count($reportes_lista), 'color' => '#4F46E5', 'icon' => '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 7m0 10V7m0 0L9 4"></path></svg>'],
        ['title' => 'Recolectado Hoy', 'value' => '0.0 kg', 'color' => '#10B981', 'icon' => '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>']
    ]); ?>

    <!-- Report Table -->
    <section class="table-container">
      <h3>Lista de Puntos de Recolección</h3>
      <table>
        <thead>
          <tr>
            <th>Ubicación</th>
            <th>Residuo</th>
            <th>Cantidad</th>
            <th>Reportado Por</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($reportes_lista)): ?>
            <tr><td colspan="6" style="text-align:center;">No hay reportes pendientes para recolectar hoy.</td></tr>
          <?php else: ?>
            <?php foreach ($reportes_lista as $r): ?>
            <tr>
              <td style="font-weight: 600;">
                <?php echo htmlspecialchars($r['ubicacion_nombre']); ?>
                <br><small style="color:#6B7280; font-weight:400;"><?php echo htmlspecialchars($r['descripcion']); ?></small>
              </td>
              <td><?php echo htmlspecialchars($r['tipo_residuo']); ?></td>
              <td style="font-weight: 700; color: #111827;"><?php echo number_format($r['cantidad'], 2); ?> kg</td>
              <td>
                <?php echo htmlspecialchars($r['nombre'] . ' ' . $r['apellido']); ?>
                <br><small><?php echo date('d/m/Y H:i', strtotime($r['fecha_reporte'])); ?></small>
              </td>
              <td><span class="badge <?php echo str_replace(' ', '-', strtolower($r['estado'])); ?>"><?php echo htmlspecialchars($r['estado']); ?></span></td>
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

<style>
    .table-container { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 12px; border-bottom: 1px solid #E5E7EB; color: #6B7280; font-size: 12px; text-transform: uppercase; }
    td { padding: 15px 12px; border-bottom: 1px solid #F3F4F6; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/dashboard_layout.php';
?>

