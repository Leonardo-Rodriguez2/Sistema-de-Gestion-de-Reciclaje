<?php
// views/admin/dashboard.php
$user = check_dashboard_access([1]);

// Stats ampliadas
$statsStmt = $pdo->query("
    SELECT
        (SELECT COUNT(*) FROM usuarios) as total_usuarios,
        (SELECT COUNT(*) FROM viviendas) as total_viviendas,
        (SELECT COUNT(*) FROM viviendas WHERE estado_servicio='Activo') as activas,
        (SELECT COUNT(*) FROM viviendas WHERE estado_servicio='Suspendido') as suspendidas,
        (SELECT SUM(monto_total) FROM recaudaciones WHERE estado='Verificado') as total_ingresos,
        (SELECT COUNT(*) FROM recaudaciones WHERE estado='Pendiente') as reportes_pendientes,
        (SELECT COUNT(*) FROM barrios) as total_barrios,
        (SELECT COUNT(*) FROM calles) as total_calles,
        (SELECT COUNT(*) FROM lotes_barrio WHERE estado='Enviado') as lotes_pendientes,
        (SELECT COUNT(*) FROM solicitudes_vivienda WHERE estado='Pendiente') as solicitudes_pendientes
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$title = "Panel de Administración - EPSIC";
$header_title = "Dashboard Central";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Stats -->
    <?php render_dashboard_stats([
        ['title' => 'Usuarios',     'value' => $stats['total_usuarios'],    'color' => '#4B5563',  'icon' => '👥'],
        ['title' => 'Viviendas',    'value' => $stats['total_viviendas'],   'color' => '#1E40AF',  'icon' => '🏠'],
        ['title' => 'Activas',      'value' => $stats['activas'],           'color' => '#10B981',  'icon' => '✅'],
        ['title' => 'Suspendidas',  'value' => $stats['suspendidas'],       'color' => '#DC2626',  'icon' => '⛔'],
        ['title' => 'Barrios',      'value' => $stats['total_barrios'],     'color' => '#8B5CF6',  'icon' => '🏢'],
        ['title' => 'Calles',       'value' => $stats['total_calles'],      'color' => '#3B82F6',  'icon' => '🛣️'],
        ['title' => 'Lotes x Aprobar', 'value' => $stats['lotes_pendientes'],'color' => '#F59E0B','icon' => '⏳'],
        ['title' => 'Solicitudes',  'value' => $stats['solicitudes_pendientes'], 'color' => '#92400E','icon' => '📩'],
    ]); ?>

    <div class="grid">
        <div class="card" style="grid-column: span 2;">
            <h3>🚀 Accesos Rápidos</h3>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="router.php?page=usuarios" class="btn-primary" style="text-decoration: none; background: #4B5563;">Gestionar Personal</a>
                <a href="router.php?page=barrios" class="btn-primary" style="text-decoration: none;">Ver Barrios</a>
                <a href="router.php?page=viviendas" class="btn-primary" style="text-decoration: none; background: #6366F1;">Lista Viviendas</a>
                <a href="router.php?page=gestor_dashboard" class="btn-primary" style="text-decoration: none; background: #F59E0B;">Aprobar Lotes</a>
                <a href="router.php?page=gestor_recibos" class="btn-primary" style="text-decoration: none; background: #0369A1;">Ver Recibos</a>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
