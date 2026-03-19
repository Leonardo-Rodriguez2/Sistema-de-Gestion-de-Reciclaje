<?php
// usuario_dashboard_view.php
// Rediseñado con Componentes y Helper

$user = check_dashboard_access([4]);

// Obtener las viviendas del usuario
$viviendasStmt = $pdo->prepare("SELECT v.id, v.direccion, v.numero_casa, b.nombre as barrio FROM viviendas v JOIN barrios b ON v.barrio_id = b.id WHERE v.usuario_id = ?");
$viviendasStmt->execute([$user['id']]);
$viviendas = $viviendasStmt->fetchAll(PDO::FETCH_ASSOC);

$vivienda_ids = array_column($viviendas, 'id');
$cobros_lista = [];
$deuda_total = 0;

if (count($vivienda_ids) > 0) {
    $in = str_repeat('?,', count($vivienda_ids) - 1) . '?';
    $cobrosStmt = $pdo->prepare("
        SELECT c.id, v.direccion, v.numero_casa, c.mes, c.anio, c.monto, c.estado, c.fecha_vencimiento 
        FROM cobros c JOIN viviendas v ON c.vivienda_id = v.id
        WHERE c.vivienda_id IN ($in) ORDER BY c.fecha_vencimiento DESC
    ");
    $cobrosStmt->execute($vivienda_ids);
    $cobros_lista = $cobrosStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($cobros_lista as $c) if($c['estado'] == 'Vencido') $deuda_total += $c['monto'];
}

$title = "Mi Panel Residencial - EcoCusco";
$header_title = "Mi Estado de Cuenta";
$header_subtitle = "Módulo Residencial";
$user_greeting = "Bienvenido(a)";

ob_start();
?>
    <!-- Feedback Messages -->
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Stats -->
    <?php render_dashboard_stats([
        ['title' => 'Viviendas', 'value' => count($viviendas), 'color' => '#10B981', 'icon' => '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>'],
        ['title' => 'Total Pendiente', 'value' => 'S/ ' . number_format($deuda_total, 2), 'color' => '#DC2626', 'icon' => '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>']
    ]); ?>

    <!-- Recibos Table -->
    <section class="table-container">
      <h3>Historial de Recibos y Mensualidades</h3>
      <table>
        <thead>
          <tr>
            <th>Propiedad</th>
            <th>Período</th>
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
              <td style="font-weight: 600;"><?php echo htmlspecialchars($c['direccion'] . ' ' . $c['numero_casa']); ?></td>
              <td><?php echo htmlspecialchars(str_pad($c['mes'], 2, "0", STR_PAD_LEFT) . ' / ' . $c['anio']); ?></td>
              <td><?php echo date('d/m/Y', strtotime($c['fecha_vencimiento'])); ?></td>
              <td style="font-weight: 700; color: #111827;">S/ <?php echo number_format($c['monto'], 2); ?></td>
              <td><span class="badge <?php echo strtolower($c['estado']); ?>"><?php echo htmlspecialchars($c['estado']); ?></span></td>
              <td>
                  <?php if($c['estado'] != 'Pagado'): ?>
                    <form action="router.php?page=dashboard" method="POST" style="margin:0;">
                      <input type="hidden" name="form_type" value="procesar_pago">
                      <input type="hidden" name="cobro_id" value="<?php echo $c['id']; ?>">
                      <button type="submit" class="btn-action">Pagar Ahora</button>
                    </form>
                  <?php else: ?>
                    <span style="color: #059669; font-weight: 600; font-size: 14px;">✔ Cancelado</span>
                  <?php endif; ?>
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

