<?php
// views/gestor/dashboard.php — Panel de Aprobación Final de Lotes de Barrio
global $pdo;
require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../app/helpers.php';
use app\models\mainModel;
if (empty($pdo)) $pdo = (new mainModel())->conectar();

$user = check_dashboard_access([1, 2]);
$page = 'dashboard';

// Stats generales
$stats = $pdo->query("SELECT
    (SELECT COUNT(*) FROM lotes_barrio WHERE estado='Enviado') as lotes_pendientes,
    (SELECT COUNT(*) FROM lotes_barrio WHERE estado='Aprobado') as lotes_aprobados,
    (SELECT SUM(monto_total_recolectado) FROM lotes_barrio WHERE estado='Aprobado') as total_aprobado,
    (SELECT COUNT(*) FROM viviendas WHERE estado_servicio='Activo') as total_activas,
    (SELECT COUNT(*) FROM viviendas WHERE exento_cobro=1) as total_exentas,
    (SELECT COUNT(*) FROM viviendas WHERE estado_servicio='Suspendido') as total_suspendidas,
    (SELECT COALESCE(SUM(monto),0) FROM cobros WHERE estado NOT IN ('Pagado','Anulado')) as deuda_total,
    (SELECT COUNT(*) FROM cobros WHERE estado NOT IN ('Pagado','Anulado')) as cobros_pendientes,
    (SELECT COUNT(*) FROM recibos_finiquito) as total_recibos
")->fetch(PDO::FETCH_ASSOC);

// Morosidad por barrio
$morosidad_barrios = $pdo->query(
    "SELECT b.id, b.nombre,
        COUNT(DISTINCT v.id) as total_viviendas,
        COUNT(DISTINCT CASE WHEN v.exento_cobro=1 THEN v.id END) as exentas,
        (SELECT COALESCE(SUM(c.monto),0) FROM cobros c JOIN viviendas v2 ON c.vivienda_id=v2.id WHERE v2.barrio_id=b.id AND c.estado NOT IN ('Pagado','Anulado')) as deuda_barrio,
        (SELECT COUNT(DISTINCT c2.vivienda_id) FROM cobros c2 JOIN viviendas v3 ON c2.vivienda_id=v3.id WHERE v3.barrio_id=b.id AND c2.estado NOT IN ('Pagado','Anulado')) as morosos
     FROM barrios b
     LEFT JOIN viviendas v ON v.barrio_id=b.id
     GROUP BY b.id, b.nombre
     ORDER BY b.nombre"
)->fetchAll(PDO::FETCH_ASSOC);

// Lotes de Barrio pendientes de aprobación
$lotesEnviados = $pdo->query(
    "SELECT lb.*, b.nombre as barrio_nombre, u.nombre as encargado_nombre, u.apellido as encargado_apellido
     FROM lotes_barrio lb
     JOIN barrios b ON lb.barrio_id = b.id
     JOIN usuarios u ON lb.encargado_barrio_id = u.id
     WHERE lb.estado='Enviado'
     ORDER BY lb.fecha_envio ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// Historial reciente de lotes aprobados
$historial = $pdo->query(
    "SELECT lb.*, b.nombre as barrio_nombre, u.nombre as gestor_nombre,
            rf.numero_recibo, rf.fecha_emision as fecha_recibo
     FROM lotes_barrio lb
     JOIN barrios b ON lb.barrio_id = b.id
     LEFT JOIN usuarios u ON lb.gestor_id = u.id
     LEFT JOIN recibos_finiquito rf ON rf.lote_barrio_id = lb.id
     WHERE lb.estado='Aprobado'
     ORDER BY lb.fecha_aprobacion DESC
     LIMIT 20"
)->fetchAll(PDO::FETCH_ASSOC);

$meses_nombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

$title          = "Dashboard Gestor - EcoCusco";
$header_title   = "Panel de Gestión de Pagos";
$header_subtitle = "Revisa y aprueba los lotes de barrio para cerrar el ciclo de facturación.";

ob_start();
?>
<?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

<!-- KPIs -->
<?php render_dashboard_stats([
    ['title'=>'Lotes Pendientes',  'value'=>$stats['lotes_pendientes'],  'color'=>'#F59E0B','icon'=>'⏳'],
    ['title'=>'Lotes Aprobados',   'value'=>$stats['lotes_aprobados'],   'color'=>'#10B981','icon'=>'✅'],
    ['title'=>'Total Recaudado',   'value'=>'S/'.number_format($stats['total_aprobado']??0,2), 'color'=>'#3B82F6','icon'=>'💰'],
    ['title'=>'Casas Activas',     'value'=>$stats['total_activas'],     'color'=>'#8B5CF6','icon'=>'🏠'],
    ['title'=>'🛡️ Exoneradas',     'value'=>$stats['total_exentas'],     'color'=>'#7C3AED','icon'=>'🛡️'],
    ['title'=>'Deuda Total',       'value'=>'S/'.number_format($stats['deuda_total']??0,2), 'color'=>'#DC2626','icon'=>'💳'],
    ['title'=>'Morosos',           'value'=>$stats['cobros_pendientes'], 'color'=>'#EF4444','icon'=>'⚠️'],
    ['title'=>'Recibos Emitidos',  'value'=>$stats['total_recibos'],    'color'=>'#0369A1','icon'=>'📄'],
]); ?>

<!-- RESUMEN POR BARRIO & EXPORT -->
<div class="card" style="margin-top:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
        <h3 style="margin:0; display:flex; align-items:center; gap:10px;">📊 Resumen de Morosidad por Barrio</h3>
        <a href="router.php?page=exportar_pagos" target="_blank" style="background:#10B981; color:white; padding:8px 16px; border-radius:6px; text-decoration:none; font-size:13px; font-weight:600;">
            📥 Exportar Excel
        </a>
    </div>
    <div class="table-wrap">
        <table style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr style="background:#F9FAFB; text-align:left;">
                    <th style="padding:10px;">Barrio</th>
                    <th style="padding:10px; text-align:center;">Viviendas</th>
                    <th style="padding:10px; text-align:center;">🛡️ Exentas</th>
                    <th style="padding:10px; text-align:center;">Morosos</th>
                    <th style="padding:10px; text-align:right;">Deuda Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($morosidad_barrios as $mb): ?>
                <tr style="border-bottom:1px solid #F3F4F6;">
                    <td style="padding:10px; font-weight:600;"><?= htmlspecialchars($mb['nombre']) ?></td>
                    <td style="padding:10px; text-align:center;"><?= $mb['total_viviendas'] ?></td>
                    <td style="padding:10px; text-align:center; color:#7C3AED; font-weight:600;"><?= $mb['exentas'] ?></td>
                    <td style="padding:10px; text-align:center;">
                        <?php $pct = $mb['total_viviendas'] > 0 ? round(($mb['morosos']/$mb['total_viviendas'])*100) : 0; ?>
                        <span style="background:<?= $pct > 50 ? '#FEE2E2' : ($pct > 20 ? '#FEF3C7' : '#D1FAE5') ?>; color:<?= $pct > 50 ? '#991B1B' : ($pct > 20 ? '#92400E' : '#065F46') ?>; padding:2px 8px; border-radius:4px; font-weight:600;">
                            <?= $mb['morosos'] ?> (<?= $pct ?>%)
                        </span>
                    </td>
                    <td style="padding:10px; text-align:right; font-weight:700; color:#DC2626;">S/ <?= number_format($mb['deuda_barrio'],2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- LOTES PENDIENTES - LINK A PAGINA DEDICADA -->
<?php if($stats['lotes_pendientes'] > 0): ?>
<div class="card" style="margin-top:20px; border-left:4px solid #F59E0B; background:linear-gradient(135deg, #FFFBEB 0%, #FFFFFF 100%);">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <h3 style="margin:0; display:flex; align-items:center; gap:10px;">
                ⏳ Lotes Pendientes de Revisión
                <span style="background:#FEF3C7; color:#92400E; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700;">
                    <?= $stats['lotes_pendientes'] ?>
                </span>
            </h3>
            <p style="color:#6B7280; font-size:12px; margin:6px 0 0;">Hay lotes de barrio esperando tu aprobación</p>
        </div>
        <a href="router.php?page=revisar_lotes" style="background:#F59E0B; color:white; padding:10px 24px; border-radius:6px; text-decoration:none; font-size:13px; font-weight:700; display:flex; align-items:center; gap:6px; white-space:nowrap;">
            📋 Revisar Lotes →
        </a>
    </div>
</div>
<?php endif; ?>

<!-- HISTORIAL DE LOTES APROBADOS -->
<?php if(!empty($historial)): ?>
<div class="card" style="margin-top:20px;">
    <h3 style="margin-top:0;">📄 Historial de Lotes Aprobados</h3>
    <div class="table-wrap">
        <table style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr style="text-align:left; border-bottom:2px solid #F3F4F6;">
                    <th style="padding:10px;">Barrio</th>
                    <th style="padding:10px;">Periodo</th>
                    <th style="padding:10px;">Recolectado</th>
                    <th style="padding:10px;">Aprobado por</th>
                    <th style="padding:10px;">Recibo</th>
                    <th style="padding:10px;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($historial as $h): ?>
                <tr style="border-bottom:1px solid #F3F4F6;">
                    <td style="padding:10px; font-weight:700;"><?= htmlspecialchars($h['barrio_nombre']) ?></td>
                    <td style="padding:10px;"><?= $meses_nombres[$h['periodo_mes']] ?> <?= $h['periodo_anio'] ?></td>
                    <td style="padding:10px; font-weight:800; color:#059669;">S/ <?= number_format($h['monto_total_recolectado'],2) ?></td>
                    <td style="padding:10px; color:#6B7280;"><?= htmlspecialchars($h['gestor_nombre'] ?? '—') ?></td>
                    <td style="padding:10px;">
                        <?php if($h['numero_recibo']): ?>
                        <a href="router.php?page=ver_recibo_finiquito&lote_id=<?= $h['id'] ?>" target="_blank"
                           class="badge" style="background:#D1FAE5; color:#065F46; text-decoration:none; padding:5px 10px;">
                            📄 <?= htmlspecialchars($h['numero_recibo']) ?>
                        </a>
                        <?php else: ?>
                        <span style="color:#9CA3AF;">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px; color:#6B7280; font-size:12px;">
                        <?= $h['fecha_aprobacion'] ? date('d/m/Y', strtotime($h['fecha_aprobacion'])) : '—' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
function openComprobanteModal(el) {
    var url = el.getAttribute('data-url');
    var cont = document.getElementById('comprobante-content');
    if (url.match(/\.(jpe?g|png|gif|webp|bmp)(\?.*)?$/i)) {
        cont.innerHTML = '<img src="' + encodeURI(url) + '" style="max-width:100%; max-height:70vh; border-radius:8px;">';
    } else {
        cont.innerHTML = '<a href="' + encodeURI(url) + '" target="_blank" style="color:#2563EB; font-size:14px;">📄 Abrir PDF</a>';
    }
    document.getElementById('modal-comprobante').style.display = 'flex';
}
function closeComprobanteModal() {
    document.getElementById('modal-comprobante').style.display = 'none';
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
