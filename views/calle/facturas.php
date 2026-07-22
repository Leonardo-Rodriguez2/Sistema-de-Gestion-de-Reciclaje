<?php
// views/calle/facturas.php — Facturas recibidas del encargado de barrio
global $pdo;
require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../app/helpers.php';
use app\models\mainModel;

if (empty($pdo)) $pdo = (new mainModel())->conectar();
$user = check_dashboard_access([6]);

$calleStmt = $pdo->prepare("SELECT calle_id, dni FROM detalles_encargado_calle WHERE usuario_id = ?");
$calleStmt->execute([$user['id']]);
$calleData = $calleStmt->fetch(PDO::FETCH_ASSOC);
if (!$calleData) { echo "<div class='alert alert-error'>No tienes una calle asignada.</div>"; return; }

$calle_id = $calleData['calle_id'];

$calle = $pdo->prepare("SELECT * FROM calles WHERE id=?");
$calle->execute([$calle_id]);
$calle = $calle->fetch(PDO::FETCH_ASSOC);

$meses_nombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

// Facturas recibidas (enviadas por el barrio)
$facturas = $pdo->prepare(
    "SELECT lc.*, c.nombre as calle_nombre, b.nombre as barrio_nombre,
            lb.estado as barrio_estado, lb.facturas_enviadas_barrio,
            rf.numero_recibo
     FROM lotes_calle lc
     JOIN calles c ON lc.calle_id = c.id
     JOIN barrios b ON lc.barrio_id = b.id
     LEFT JOIN lotes_barrio lb ON lc.lote_barrio_id = lb.id
     LEFT JOIN recibos_finiquito rf ON rf.lote_barrio_id = lc.lote_barrio_id
     WHERE lc.calle_id = ? AND lc.certificado_enviado_calle = 1
     ORDER BY lc.periodo_anio DESC, lc.periodo_mes DESC"
);
$facturas->execute([$calle_id]);
$facturas = $facturas->fetchAll(PDO::FETCH_ASSOC);

// Viviendas de todas las facturas
$viviendasPorFactura = [];
if (!empty($facturas)) {
    $ids = array_column($facturas, 'id');
    $phs = implode(',', array_fill(0, count($ids), '?'));
    $vStmt = $pdo->prepare(
        "SELECT lc.id as lote_calle_id,
            v.id as vivienda_id, v.direccion, v.estado_servicio, v.propietario,
            cob.id as cobro_id, cob.monto, cob.estado as cobro_estado,
            cob.estado_verificacion, cob.referencia_pago, cob.fecha_emision
         FROM viviendas v
         JOIN lotes_calle lc ON lc.calle_id = v.calle_id AND lc.id IN ($phs)
         LEFT JOIN cobros cob ON cob.vivienda_id = v.id
            AND cob.mes = lc.periodo_mes AND cob.anio = lc.periodo_anio
            AND cob.lote_calle_id = lc.id
         WHERE v.estado_servicio != 'Anulado'
         ORDER BY lc.id, v.direccion ASC"
    );
    $vStmt->execute($ids);
    $viviendas = $vStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($viviendas as $vv) {
        $viviendasPorFactura[$vv['lote_calle_id']][] = $vv;
    }
}

$header_title = 'Mis Facturas';
$header_subtitle = 'Calle: ' . htmlspecialchars($calle['nombre']);
$extra_css = '
.factura-card { border:1px solid #E5E7EB; border-radius:10px; margin-bottom:16px; overflow:hidden; }
.factura-header { background:#F9FAFB; padding:14px 18px; border-bottom:1px solid #E5E7EB; display:flex; justify-content:space-between; align-items:center; cursor:pointer; }
.factura-header h3 { margin:0; font-size:15px; font-weight:700; }
.factura-body { padding:16px; display:none; }
.factura-body.open { display:block; }
.invoice-table { width:100%; border-collapse:collapse; font-size:11px; margin-top:8px; }
.invoice-table th { padding:6px 8px; background:#065F46; color:#fff; text-align:left; font-size:9px; text-transform:uppercase; }
.invoice-table td { padding:5px 8px; border-bottom:1px solid #F3F4F6; }
.invoice-table tr.total-row { background:#ECFDF5; border-top:2px solid #065F46; }
.invoice-table tr.total-row td { font-weight:800; color:#065F46; }
.btn-print { display:inline-block; padding:6px 14px; background:#374151; color:#fff; border-radius:6px; font-size:11px; font-weight:600; text-decoration:none; }
.btn-print:hover { background:#111827; }
.btn-print-inline { background:#374151; color:#fff; padding:4px 10px; border-radius:5px; font-size:10px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px; margin-left:8px; }
.btn-print-inline:hover { background:#111827; }
.empty-state { text-align:center; padding:60px 20px; background:#fff; border-radius:12px; }
.empty-icon { font-size:48px; margin-bottom:16px; }
.empty-state h3 { margin:0 0 8px; color:#374151; }
.empty-state p { color:#6B7280; font-size:14px; margin:0; }
';
ob_start();
?>

<div style="margin-bottom:20px;">
    <p style="font-size:13px; color:#6B7280; margin:0;">
        Facturas que el encargado de barrio ha enviado para tu calle.
        Haz clic en cada una para ver el detalle de viviendas.
    </p>
</div>

<?php if (empty($facturas)): ?>
<div class="empty-state">
    <div class="empty-icon">📄</div>
    <h3>No tienes facturas recibidas</h3>
    <p>Cuando el encargado de barrio te envíe las facturas, aparecerán aquí.</p>
</div>
<?php else: ?>
    <?php foreach ($facturas as $f):
        $viviendas = $viviendasPorFactura[$f['id']] ?? [];
        $total_monto = 0;
        foreach ($viviendas as $v) $total_monto += (float)($v['monto'] ?? 0);
        $num_factura = sprintf('FCT-%04d%02d-%03d-%03d', $f['periodo_anio'], $f['periodo_mes'], $f['calle_id'], $f['id']);
    ?>
    <div class="factura-card">
        <div class="factura-header" onclick="toggleFactura(<?= $f['id'] ?>)">
            <div>
                <h3>📄 <?= $meses_nombres[$f['periodo_mes']] ?> <?= $f['periodo_anio'] ?>
                    <a href="router.php?page=certificado_calle&lote_calle_id=<?= $f['id'] ?>" target="_blank" class="btn-print-inline" onclick="event.stopPropagation();">🖨️ PDF</a>
                </h3>
                <div style="font-size:11px; color:#6B7280;">
                    🏙️ <?= htmlspecialchars($f['barrio_nombre']) ?> •
                    🏠 <?= $f['casas_pagadas'] ?>/<?= $f['total_casas'] ?> pag. •
                    💰 S/ <?= number_format($f['monto_recolectado'] ?? 0, 2) ?>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <?php if ($f['factura_personalizada']): ?>
                    <span style="font-size:10px; color:#065F46; font-weight:600;">📎 Con documento</span>
                <?php endif; ?>
                <span style="font-size:10px; color:#9CA3AF;">▼</span>
            </div>
        </div>
        <div class="factura-body" id="detalle-<?= $f['id'] ?>">
            <!-- Resumen -->
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:12px;">
                <div style="text-align:center; padding:8px; background:#F9FAFB; border-radius:6px;">
                    <div style="font-size:18px; font-weight:800; color:#6B7280;"><?= $f['total_casas'] ?></div>
                    <div style="font-size:9px; color:#6B7280; text-transform:uppercase;">Casas</div>
                </div>
                <div style="text-align:center; padding:8px; background:#F9FAFB; border-radius:6px;">
                    <div style="font-size:18px; font-weight:800; color:#059669;"><?= $f['casas_pagadas'] ?></div>
                    <div style="font-size:9px; color:#6B7280; text-transform:uppercase;">Pagadas</div>
                </div>
                <div style="text-align:center; padding:8px; background:#F9FAFB; border-radius:6px;">
                    <div style="font-size:18px; font-weight:800; color:#DC2626;"><?= $f['casas_morosas'] ?></div>
                    <div style="font-size:9px; color:#6B7280; text-transform:uppercase;">Morosas</div>
                </div>
                <div style="text-align:center; padding:8px; background:#F9FAFB; border-radius:6px;">
                    <div style="font-size:18px; font-weight:800; color:#065F46;">S/ <?= number_format($total_monto, 2) ?></div>
                    <div style="font-size:9px; color:#6B7280; text-transform:uppercase;">Total</div>
                </div>
            </div>

            <!-- Tabla viviendas -->
            <?php if (count($viviendas) > 0): ?>
            <div class="table-wrap">
            <table class="invoice-table">
                <thead><tr>
                    <th>#</th><th>Dirección</th><th>Propietario</th><th>Servicio</th>
                    <th>Estado Pago</th><th>Referencia</th><th style="text-align:right;">Monto</th>
                </tr></thead>
                <tbody>
                    <?php $i = 0; foreach ($viviendas as $v): $i++; ?>
                    <tr style="<?= $v['cobro_estado']==='Pagado'?'background:#ECFDF5;':'' ?>">
                        <td><?= $i ?></td>
                        <td><strong><?= htmlspecialchars($v['direccion']) ?></strong></td>
                        <td><?= htmlspecialchars($v['propietario'] ?? '—') ?></td>
                        <td><?php if ($v['estado_servicio']==='Activo'): ?><span style="padding:2px 6px; border-radius:8px; font-size:9px; font-weight:700; background:#D1FAE5; color:#065F46;">Activo</span><?php elseif ($v['estado_servicio']==='Suspendido'): ?><span style="padding:2px 6px; border-radius:8px; font-size:9px; font-weight:700; background:#FEF3C7; color:#92400E;">Suspendido</span><?php else: ?><?= $v['estado_servicio'] ?><?php endif; ?></td>
                        <td><?php if ($v['cobro_estado']==='Pagado'): ?><span style="color:#059669; font-weight:700;">✅ Pagado</span><?php if ($v['fecha_emision']): ?><div style="font-size:9px; color:#9CA3AF;"><?= date('d/m/Y', strtotime($v['fecha_emision'])) ?></div><?php endif; ?><?php elseif ($v['cobro_estado']==='Pendiente'): ?><span style="color:#D97706; font-weight:700;">⏳ Pendiente</span><?php else: ?><span style="color:#D97706;">—</span><?php endif; ?></td>
                        <td style="font-size:10px; color:#6B7280;"><?= htmlspecialchars($v['referencia_pago'] ?? '—') ?></td>
                        <td style="text-align:right; font-weight:700;"><?= $v['monto'] ? 'S/ '.number_format($v['monto'], 2) : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="6" style="padding:8px; text-align:right;">TOTAL</td>
                        <td style="padding:8px; text-align:right; font-size:13px;">S/ <?= number_format($total_monto, 2) ?></td>
                    </tr>
                </tbody>
            </table>
            </div>
            <?php else: ?>
            <div style="padding:12px; text-align:center; color:#9CA3AF; font-size:11px;">Sin viviendas registradas.</div>
            <?php endif; ?>

            <!-- Documento adjunto -->
            <div style="margin-top:14px; padding-top:12px; border-top:1px solid #E5E7EB; display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:11px; color:#6B7280;">Factura N° <?= htmlspecialchars($num_factura) ?></div>
                <div style="display:flex; gap:8px;">
                    <?php if ($f['factura_personalizada']): ?>
                        <a href="<?= htmlspecialchars($f['factura_personalizada']) ?>" target="_blank" class="btn-print">📎 Ver Documento Adjunto</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
function toggleFactura(id) {
    const body = document.getElementById('detalle-' + id);
    body.classList.toggle('open');
}
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
