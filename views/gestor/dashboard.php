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
        <h3>💰 Recaudaciones por Verificar</h3>
        <p style="color: #6B7280; font-size: 13px;">Las recaudaciones enviadas por los encargados de barrio aparecerán aquí para tu validación.</p>
        <!-- Aquí iría la tabla de recaudaciones -->
        <div style="text-align: center; padding: 40px; color: #9CA3AF;">
            <div style="font-size: 40px; margin-bottom: 10px;">📊</div>
            Módulo de verificación en desarrollo.
        </div>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
