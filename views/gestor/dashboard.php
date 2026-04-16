<?php
// views/gestor/dashboard.php
$user = check_dashboard_access([1, 2]);

// Obtener estadísticas de recaudación
$statsStmt = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM recaudaciones WHERE estado = 'Pendiente') as pendientes,
    (SELECT SUM(monto_total) FROM recaudaciones WHERE estado = 'Verificado') as total_recaudado,
    (SELECT COUNT(*) FROM viviendas) as total_viviendas
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$title = "Dashboard Gestor - EcoCusco";
$header_title = "Panel de Gestión de Pagos";
$header_subtitle = "Monitorea y verifica los pagos de todos los barrios.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <?php render_dashboard_stats([
        ['title' => 'Pendientes', 'value' => $stats['pendientes'], 'color' => '#F59E0B', 'icon' => '⏳'],
        ['title' => 'Viviendas', 'value' => $stats['total_viviendas'], 'color' => '#3B82F6', 'icon' => '🏠'],
        ['title' => 'Recaudado (S/)', 'value' => number_format($stats['total_recaudado'] ?? 0, 2), 'color' => '#10B981', 'icon' => '💰']
    ]); ?>

    <div class="card" style="margin-top: 20px;">
        <h3 style="margin-top:0;">⏳ Liquidaciones por Verificar</h3>
        <p style="color: #6B7280; font-size: 13px; margin-bottom: 20px;">Valida los montos reportados por los encargados de barrio antes de pasarlos al historial general.</p>

        <?php
        $pendientesStmt = $pdo->query("SELECT r.*, u.nombre as emisor_nombre, b.nombre as barrio_nombre
                                     FROM recaudaciones r
                                     JOIN usuarios u ON r.emisor_id = u.id
                                     JOIN barrios b ON r.barrio_id = b.id
                                     WHERE r.estado = 'Pendiente' AND r.tipo = 'Barrio'
                                     ORDER BY r.fecha_recaudacion ASC");
        $pendientes = $pendientesStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 2px solid #F3F4F6; text-align: left;">
                        <th style="padding: 10px;">Fecha Reporte</th>
                        <th style="padding: 10px;">Barrio</th>
                        <th style="padding: 10px;">Enviado por</th>
                        <th style="padding: 10px;">Monto</th>
                        <th style="padding: 10px; text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pendientes)): ?>
                        <tr><td colspan="5" style="padding: 40px; text-align: center; color: #9CA3AF;">No hay liquidaciones pendientes por verificar.</td></tr>
                    <?php endif; ?>
                    <?php foreach($pendientes as $p): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 10px;"><?= date('d/m/Y H:i', strtotime($p['fecha_recaudacion'])) ?></td>
                            <td style="padding: 10px; font-weight: 700;"><?= htmlspecialchars($p['barrio_nombre']) ?></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($p['emisor_nombre']) ?></td>
                            <td style="padding: 10px; font-weight: 800; color: #111827;">S/ <?= number_format($p['monto_total'], 2) ?></td>
                            <td style="padding: 10px; text-align: center;">
                                <form method="POST" onsubmit="return confirm('¿Confirmas que has recibido el dinero físico de este barrio?')">
                                    <input type="hidden" name="form_type" value="verificar_recaudacion">
                                    <input type="hidden" name="recaudacion_id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn-primary" style="background: #10B981; padding: 5px 12px; font-size: 11px;">
                                        ✅ Confirmar Recepción
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
