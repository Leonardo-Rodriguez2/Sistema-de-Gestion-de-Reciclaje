<?php
// views/barrio/dashboard.php
$user = check_dashboard_access([5]);

// Obtener datos del barrio asignado
$barrioStmt = $pdo->prepare("SELECT b.* FROM detalles_encargado_barrio d JOIN barrios b ON d.barrio_id = b.id WHERE d.usuario_id = ?");
$barrioStmt->execute([$user['id']]);
$barrio_info = $barrioStmt->fetch(PDO::FETCH_ASSOC);

if (!$barrio_info) {
    die("No tienes un barrio asignado. Contacta al administrador.");
}

// Stats rápidas
$cCountStmt = $pdo->prepare("SELECT COUNT(*) FROM calles WHERE barrio_id = ?");
$cCountStmt->execute([$barrio_info['id']]);
$total_calles = $cCountStmt->fetchColumn();

$sCountStmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_vivienda s JOIN calles c ON s.calle_id = c.id WHERE c.barrio_id = ? AND s.estado = 'Pendiente'");
$sCountStmt->execute([$barrio_info['id']]);
$total_solicitudes = $sCountStmt->fetchColumn();

$rCountStmt = $pdo->prepare("SELECT COUNT(*) FROM recaudaciones WHERE receptor_id = ? AND estado = 'Pendiente'");
$rCountStmt->execute([$user['id']]);
$total_recaudaciones = $rCountStmt->fetchColumn();

$title = "Dashboard Barrio - EcoCusco";
$header_title = "Panel de Barrio: " . htmlspecialchars($barrio_info['nombre']);
$header_subtitle = "Gestión centralizada de calles, pagos y solicitudes.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Stats -->
    <?php render_dashboard_stats([
        ['title' => 'Mis Calles', 'value' => $total_calles, 'color' => '#3B82F6', 'icon' => '🛣️'],
        ['title' => 'Solicitudes', 'value' => $total_solicitudes, 'color' => '#F59E0B', 'icon' => '📩'],
        ['title' => 'Recaudaciones', 'value' => $total_recaudaciones, 'color' => '#10B981', 'icon' => '💰']
    ]); ?>

    <h3 style="margin: 30px 0 15px; color: #374151;">Módulos de Gestión</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
        
        <a href="router.php?page=viviendas" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 25px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#111827'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 35px; margin-bottom: 15px;">🏘️</div>
            <div style="font-weight: 700; color: #111827;">Control de Viviendas</div>
            <div style="font-size: 11px; color: #6B7280; text-align: center; margin-top: 5px;">Listado general del barrio</div>
        </a>

        <a href="router.php?page=reportar_pago" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 25px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#10B981'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 35px; margin-bottom: 15px;">💰</div>
            <div style="font-weight: 700; color: #111827;">Recaudación</div>
            <div style="font-size: 11px; color: #6B7280; text-align: center; margin-top: 5px;">Gestión de cobros y deudas</div>
        </a>

        <a href="router.php?page=solicitudes" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 25px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#F59E0B'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 35px; margin-bottom: 15px;">📩</div>
            <div style="font-weight: 700; color: #111827;">Solicitudes</div>
            <div style="font-size: 11px; color: #6B7280; text-align: center; margin-top: 5px;">Aprobar altas y renovaciones</div>
        </a>

        <a href="router.php?page=quitar_servicio" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 25px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#EF4444'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 35px; margin-bottom: 15px;">🔻</div>
            <div style="font-weight: 700; color: #111827;">Quitar Servicio</div>
            <div style="font-size: 11px; color: #6B7280; text-align: center; margin-top: 5px;">Emitir órdenes de suspensión</div>
        </a>

        <a href="router.php?page=calles" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 25px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#3B82F6'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 35px; margin-bottom: 15px;">🛣️</div>
            <div style="font-weight: 700; color: #111827;">Mis Calles</div>
            <div style="font-size: 11px; color: #6B7280; text-align: center; margin-top: 5px;">Gestión de infraestructura</div>
        </a>

    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
