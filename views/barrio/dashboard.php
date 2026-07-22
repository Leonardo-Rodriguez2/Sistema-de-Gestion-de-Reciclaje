<?php
// views/barrio/dashboard.php
global $pdo;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../app/helpers.php';

use app\models\mainModel;

if (empty($pdo)) {
    $pdo = (new mainModel())->conectar();
}

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

$vCountStmt = $pdo->prepare("SELECT COUNT(*) FROM viviendas WHERE barrio_id = ? AND estado_servicio='Activo'");
$vCountStmt->execute([$barrio_info['id']]);
$total_activas = $vCountStmt->fetchColumn();

$vSusStmt = $pdo->prepare("SELECT COUNT(*) FROM viviendas WHERE barrio_id = ? AND estado_servicio='Suspendido'");
$vSusStmt->execute([$barrio_info['id']]);
$total_suspendidas = $vSusStmt->fetchColumn();

$lotesPendStmt = $pdo->prepare("SELECT COUNT(*) FROM lotes_calle lc JOIN calles c ON lc.calle_id=c.id WHERE c.barrio_id=? AND lc.estado='Enviado'");
$lotesPendStmt->execute([$barrio_info['id']]);
$lotes_calle_pend = $lotesPendStmt->fetchColumn();

$lbStatus = $pdo->prepare("SELECT estado FROM lotes_barrio WHERE barrio_id=? AND mes=? AND anio=? ORDER BY id DESC LIMIT 1");
$mesActual = date('n');
$anioActual = date('Y');
$lbStatus->execute([$barrio_info['id'], $mesActual, $anioActual]);
$lote_barrio_estado = $lbStatus->fetchColumn() ?: '—';

$title = "Dashboard Barrio - EcoCusco";
$header_title = "Panel de Barrio: " . htmlspecialchars($barrio_info['nombre']);
$header_subtitle = "Gestión centralizada de calles, pagos y solicitudes.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Stats -->
    <?php render_dashboard_stats([
        ['title' => 'Mis Calles', 'value' => $total_calles, 'color' => '#3B82F6', 'icon' => '🛣️'],
        ['title' => 'Casas Activas', 'value' => $total_activas, 'color' => '#10B981', 'icon' => '🏠'],
        ['title' => 'Suspendidas', 'value' => $total_suspendidas, 'color' => '#DC2626', 'icon' => '⛔'],
        ['title' => 'Solicitudes', 'value' => $total_solicitudes, 'color' => '#F59E0B', 'icon' => '📩'],
        ['title' => 'Lotes Calle Pend.', 'value' => $lotes_calle_pend, 'color' => '#F59E0B', 'icon' => '⏳'],
        ['title' => 'Lote Barrio', 'value' => $lote_barrio_estado, 'color' => '#8B5CF6', 'icon' => '🏘️'],
    ]); ?>

    <!-- Resumen del periodo actual -->
    <?php
    $loteBarrioInfo = $pdo->prepare("SELECT lb.*, rf.numero_recibo FROM lotes_barrio lb LEFT JOIN recibos_finiquito rf ON rf.lote_barrio_id=lb.id WHERE lb.barrio_id=? AND lb.mes=? AND lb.anio=? ORDER BY lb.id DESC LIMIT 1");
    $loteBarrioInfo->execute([$barrio_info['id'], $mesActual, $anioActual]);
    $lbi = $loteBarrioInfo->fetch(PDO::FETCH_ASSOC);
    if ($lbi): ?>
    <div class="card" style="margin-bottom:20px; border-left:4px solid <?= $lbi['estado']==='Aprobado'?'#10B981':($lbi['estado']==='Enviado'?'#F59E0B':'#6B7280') ?>;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div>
                <strong style="font-size:14px;">📊 Periodo Actual: <?= date('F Y', mktime(0,0,0,$mesActual,1,$anioActual)) ?></strong>
                <div style="font-size:12px; color:#6B7280; margin-top:4px;">
                    Estado lote barrio: <strong style="color:<?= $lbi['estado']==='Aprobado'?'#059669':($lbi['estado']==='Enviado'?'#D97706':'#6B7280') ?>"><?= $lbi['estado'] ?></strong>
                    <?php if($lbi['numero_recibo']): ?> • Recibo: <?= htmlspecialchars($lbi['numero_recibo']) ?><?php endif; ?>
                </div>
            </div>
            <a href="router.php?page=reportar_pago" class="btn-primary" style="background:#F59E0B;">
                Ir a Verificación de Pagos →
            </a>
        </div>
    </div>
    <?php endif; ?>

    <h3 style="margin: 30px 0 15px; color: #374151;">Módulos de Gestión</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
        
        <a href="router.php?page=viviendas" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 25px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#111827'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 11px; color: #9CA3AF; margin-bottom: 5px;">PASO 1</div>
            <div style="font-size: 35px; margin-bottom: 15px;">🏘️</div>
            <div style="font-weight: 700; color: #111827;">Control de Viviendas</div>
            <div style="font-size: 11px; color: #6B7280; text-align: center; margin-top: 5px;">Revisa el listado general del barrio</div>
        </a>

        <a href="router.php?page=reportar_pago" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 25px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#10B981'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 11px; color: #9CA3AF; margin-bottom: 5px;">PASO 2</div>
            <div style="font-size: 35px; margin-bottom: 15px;">💰</div>
            <div style="font-weight: 700; color: #111827;">Verificación de Pagos</div>
            <div style="font-size: 11px; color: #6B7280; text-align: center; margin-top: 5px;">Marca casas que pagaron y revisa comprobantes</div>
        </a>

        <a href="router.php?page=seguimiento_pagos" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 25px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#3B82F6'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 11px; color: #9CA3AF; margin-bottom: 5px;">PASO 3</div>
            <div style="font-size: 35px; margin-bottom: 15px;">📋</div>
            <div style="font-weight: 700; color: #111827;">Seguimiento por Casa</div>
            <div style="font-size: 11px; color: #6B7280; text-align: center; margin-top: 5px;">Ver estado de cada vivienda y evidencias</div>
        </a>

        <a href="router.php?page=solicitudes" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 25px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#F59E0B'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 11px; color: #9CA3AF; margin-bottom: 5px;">PASO 4</div>
            <div style="font-size: 35px; margin-bottom: 15px;">📩</div>
            <div style="font-weight: 700; color: #111827;">Solicitudes</div>
            <div style="font-size: 11px; color: #6B7280; text-align: center; margin-top: 5px;">Aprueba altas y renovaciones pendientes</div>
        </a>

        <a href="router.php?page=quitar_servicio" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 25px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#EF4444'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 11px; color: #9CA3AF; margin-bottom: 5px;">OPCIONAL</div>
            <div style="font-size: 35px; margin-bottom: 15px;">🔻</div>
            <div style="font-weight: 700; color: #111827;">Quitar Servicio</div>
            <div style="font-size: 11px; color: #6B7280; text-align: center; margin-top: 5px;">Emitir órdenes de suspensión</div>
        </a>

        <a href="router.php?page=calles" class="card" style="text-decoration: none; display: flex; flex-direction: column; align-items: center; padding: 25px; transition: 0.3s; border: 2px solid transparent;" onmouseover="this.style.borderColor='#3B82F6'" onmouseout="this.style.borderColor='transparent'">
            <div style="font-size: 11px; color: #9CA3AF; margin-bottom: 5px;">OPCIONAL</div>
            <div style="font-size: 35px; margin-bottom: 15px;">🛣️</div>
            <div style="font-weight: 700; color: #111827;">Mis Calles</div>
            <div style="font-size: 11px; color: #6B7280; text-align: center; margin-top: 5px;">Gestión de infraestructura</div>
        </a>

    </div>

    <!-- Flujo de trabajo -->
    <div style="margin-top: 30px; padding: 20px; background: #F9FAFB; border-radius: 3px; border: 1px solid #E5E7EB;">
        <h4 style="margin: 0 0 12px; color: #374151;">📋 Flujo de Trabajo Mensual</h4>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; font-size: 13px; color: #6B7280;">
            <span style="background: #EFF6FF; padding: 6px 12px; border-radius: 3px; color: #1E40AF; font-weight: 600;">1. Verificar viviendas</span>
            <span>→</span>
            <span style="background: #ECFDF5; padding: 6px 12px; border-radius: 3px; color: #065F46; font-weight: 600;">2. Verificar pagos</span>
            <span>→</span>
            <span style="background: #FEF3C7; padding: 6px 12px; border-radius: 3px; color: #92400E; font-weight: 600;">3. Aprobar solicitudes</span>
            <span>→</span>
            <span style="background: #EDE9FE; padding: 6px 12px; border-radius: 3px; color: #5B21B6; font-weight: 600;">4. Enviar lote</span>
        </div>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
