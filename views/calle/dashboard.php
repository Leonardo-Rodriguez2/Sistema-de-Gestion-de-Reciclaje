<?php
// views/calle/dashboard.php
$user = check_dashboard_access([6]);

// Obtener datos de la calle asignada
$calleStmt = $pdo->prepare("SELECT c.id, c.nombre, c.barrio_id, b.nombre as barrio_nombre 
                           FROM detalles_encargado_calle d 
                           JOIN calles c ON d.calle_id = c.id
                           JOIN barrios b ON c.barrio_id = b.id
                           WHERE d.usuario_id = ?");
$calleStmt->execute([$user['id']]);
$calle_info = $calleStmt->fetch(PDO::FETCH_ASSOC);

if (!$calle_info) {
    die("No tienes una calle asignada. Contacta al administrador.");
}

// Obtener estadísticas rápidas
$vCountStmt = $pdo->prepare("SELECT COUNT(*) FROM viviendas WHERE calle_id = ?");
$vCountStmt->execute([$calle_info['id']]);
$total_viviendas = $vCountStmt->fetchColumn();

$mes = date('n'); $anio = date('Y');
$pCountStmt = $pdo->prepare("SELECT COUNT(*) FROM cobros c JOIN viviendas v ON c.vivienda_id = v.id WHERE v.calle_id = ? AND c.mes = ? AND c.anio = ? AND c.estado = 'Pendiente'");
$pCountStmt->execute([$calle_info['id'], $mes, $anio]);
$pendientes = $pCountStmt->fetchColumn();

$bCountStmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_vivienda WHERE calle_id = ? AND tipo = 'Baja' AND estado = 'Pendiente'");
$bCountStmt->execute([$calle_info['id']]);
$ordenes_baja = $bCountStmt->fetchColumn();

$title = "Dashboard - EcoCusco";
$header_title = "Panel de Calle: " . htmlspecialchars($calle_info['nombre']);
$header_subtitle = "Bienvenido al sistema de gestión de reciclaje.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Stats -->
    <?php render_dashboard_stats([
        ['title' => 'Viviendas', 'value' => $total_viviendas, 'color' => '#111827', 'icon' => '🏠'],
        ['title' => 'Pendientes', 'value' => $pendientes, 'color' => '#EF4444', 'icon' => '⏳'],
        ['title' => 'Órdenes Baja', 'value' => $ordenes_baja, 'color' => '#F59E0B', 'icon' => '📄']
    ]); ?>

    <h3 style="margin: 30px 0 15px; color: #374151;">Gestión de la Calle</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        
        <a href="router.php?page=viviendas" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 30px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#111827'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 40px; margin-bottom: 15px;">🏘️</div>
            <div style="font-weight: 700; color: #111827;">Mis Viviendas</div>
            <div style="font-size: 12px; color: #6B7280; text-align: center; margin-top: 5px;">Ver listado y estados de servicio</div>
        </a>

        <a href="router.php?page=reportar_pago" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 30px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#10B981'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 40px; margin-bottom: 15px;">💰</div>
            <div style="font-weight: 700; color: #111827;">Marcar Pagos</div>
            <div style="font-size: 12px; color: #6B7280; text-align: center; margin-top: 5px;">Registrar recaudación mensual</div>
        </a>

        <a href="router.php?page=registrar_vivienda" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 30px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#3B82F6'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 40px; margin-bottom: 15px;">➕</div>
            <div style="font-weight: 700; color: #111827;">Solicitar Alta</div>
            <div style="font-size: 12px; color: #6B7280; text-align: center; margin-top: 5px;">Registrar nueva vivienda</div>
        </a>

        <a href="router.php?page=ordenes_baja" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 30px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#EF4444'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 40px; margin-bottom: 15px;">📄</div>
            <div style="font-weight: 700; color: #111827;">Órdenes de Baja</div>
            <div style="font-size: 12px; color: #6B7280; text-align: center; margin-top: 5px;">Confirmar retiros de servicio</div>
        </a>

    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
