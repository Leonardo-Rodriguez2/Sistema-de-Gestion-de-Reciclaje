<?php
// views/admin/dashboard.php
$user = check_dashboard_access([1]);

// Stats Avanzadas
$statsStmt = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM usuarios) as total_usuarios,
        (SELECT COUNT(*) FROM viviendas) as total_viviendas,
        (SELECT SUM(monto_total) FROM recaudaciones WHERE estado = 'Verificado') as total_ingresos,
        (SELECT COUNT(*) FROM recaudaciones WHERE estado = 'Pendiente') as reportes_pendientes
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$title = "Panel de Administración - EPSIC";
$header_title = "Dashboard Central";

ob_start();
?>
    <!-- Stats -->
    <?php render_dashboard_stats([
        ['title' => 'Usuarios', 'value' => $stats['total_usuarios'], 'color' => '#4B5563', 'icon' => '👥'],
        ['title' => 'Viviendas', 'value' => $stats['total_viviendas'], 'color' => '#1E40AF', 'icon' => '🏠'],
        ['title' => 'Ingresos Totales', 'value' => 'S/ ' . number_format($stats['total_ingresos'] ?? 0, 2), 'color' => '#065F46', 'icon' => '💰'],
        ['title' => 'Reportes x Verificar', 'value' => $stats['reportes_pendientes'], 'color' => '#92400E', 'icon' => '⏳']
    ]); ?>

    <div class="grid">
        <div class="card" style="grid-column: span 2;">
            <h3>🚀 Accesos Rápidos</h3>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="router.php?page=usuarios" class="btn-primary" style="text-decoration: none; background: #4B5563;">Gestionar Personal</a>
                <a href="router.php?page=barrios" class="btn-primary" style="text-decoration: none;">Ver Barrios</a>
                <a href="router.php?page=viviendas" class="btn-primary" style="text-decoration: none; background: #6366F1;">Lista Viviendas</a>
            </div>
        </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
