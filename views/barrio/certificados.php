<?php
// views/barrio/certificados.php — Facturas por calle recibidas del gestor
use app\models\mainModel;

if (empty($pdo)) $pdo = (new mainModel())->conectar();
$user = check_dashboard_access([5]);
(new \app\controllers\barrioController())->verificarDeudasBarrio($user['id']);

$barrio_id = $pdo->prepare("SELECT barrio_id FROM detalles_encargado_barrio WHERE usuario_id=?");
$barrio_id->execute([$user['id']]);
$barrio_id = $barrio_id->fetchColumn();
if (!$barrio_id) { echo "<div class='alert alert-error'>No tienes un barrio asignado.</div>"; return; }

$barrio = $pdo->prepare("SELECT * FROM barrios WHERE id=?");
$barrio->execute([$barrio_id]);
$barrio = $barrio->fetch(PDO::FETCH_ASSOC);
$meses_nombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

// Calles con certificado, agrupadas por lote_barrio (periodo)
$lotes = $pdo->prepare(
    "SELECT lc.*, c.nombre as calle_nombre,
            u.nombre as enc_nombre, u.apellido as enc_apellido,
            lb.estado as barrio_estado, lb.facturas_enviadas_barrio,
            lb.periodo_mes as lb_mes, lb.periodo_anio as lb_anio,
            rf.numero_recibo
     FROM lotes_calle lc
     JOIN calles c ON lc.calle_id = c.id
     LEFT JOIN usuarios u ON lc.encargado_calle_id = u.id
     LEFT JOIN lotes_barrio lb ON lc.lote_barrio_id = lb.id
     LEFT JOIN recibos_finiquito rf ON rf.lote_barrio_id = lc.lote_barrio_id
     WHERE lc.barrio_id = ? AND lc.certificado_generado = 1
     ORDER BY lb.facturas_enviadas_barrio DESC, lc.periodo_anio DESC, lc.periodo_mes DESC, c.nombre ASC"
);
$lotes->execute([$barrio_id]);
$lotes = $lotes->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por periodo
$por_periodo = [];
foreach ($lotes as $l) {
    $key = $l['periodo_anio'] . '-' . str_pad($l['periodo_mes'], 2, '0', STR_PAD_LEFT);
    $por_periodo[$key][] = $l;
}
krsort($por_periodo);

// Viviendas de todas las calles (una sola consulta)
$viviendasPorCalle = [];
if (!empty($lotes)) {
    $idsLoteCalle = array_column($lotes, 'id');
    $placeholders = implode(',', array_fill(0, count($idsLoteCalle), '?'));
    $vStmt = $pdo->prepare(
        "SELECT lc.id as lote_calle_id,
            v.id as vivienda_id, v.direccion, v.estado_servicio, v.propietario,
            cob.id as cobro_id, cob.monto, cob.estado as cobro_estado,
            cob.estado_verificacion, cob.referencia_pago, cob.fecha_emision
         FROM viviendas v
         JOIN lotes_calle lc ON lc.calle_id = v.calle_id AND lc.id IN ($placeholders)
         LEFT JOIN cobros cob ON cob.vivienda_id = v.id
            AND cob.mes = lc.periodo_mes AND cob.anio = lc.periodo_anio
            AND cob.lote_calle_id = lc.id
         WHERE v.estado_servicio != 'Anulado'
         ORDER BY lc.id, v.direccion ASC"
    );
    $vStmt->execute($idsLoteCalle);
    $viviendas = $vStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($viviendas as $vv) {
        $viviendasPorCalle[$vv['lote_calle_id']][] = $vv;
    }
}

$header_title = 'Facturas Recibidas';
$header_subtitle = 'Barrio: ' . htmlspecialchars($barrio['nombre']);
$extra_css = '
.factura-calle { border:1px solid #E5E7EB; border-radius:10px; margin-bottom:14px; overflow:hidden; }
.factura-calle-header { background:#F9FAFB; padding:12px 16px; border-bottom:1px solid #E5E7EB; display:flex; justify-content:space-between; align-items:center; cursor:pointer; }
.factura-calle-header h4 { margin:0; font-size:14px; font-weight:700; }
.factura-calle-body { padding:16px; display:none; }
.factura-calle-body.open { display:block; }
.invoice-table { width:100%; border-collapse:collapse; font-size:11px; margin-top:8px; }
.invoice-table th { padding:6px 8px; background:#065F46; color:#fff; text-align:left; font-size:9px; text-transform:uppercase; }
.invoice-table td { padding:5px 8px; border-bottom:1px solid #F3F4F6; }
.invoice-table tr.total-row { background:#ECFDF5; border-top:2px solid #065F46; }
.invoice-table tr.total-row td { font-weight:800; color:#065F46; }
.badge-enviado { display:inline-block; padding:3px 10px; border-radius:10px; font-size:10px; font-weight:700; }
.badge-enviado.si { background:#D1FAE5; color:#065F46; }
.badge-enviado.no { background:#FEF3C7; color:#92400E; }
.badge-enviado.pend { background:#E5E7EB; color:#6B7280; }
.btn { padding:7px 14px; border-radius:6px; font-size:11px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:5px; text-decoration:none; }
.btn-send-calle { background:#065F46; color:#fff; }
.btn-send-calle:hover { background:#047857; }
.btn-send-calle:disabled { background:#D1D5DB; cursor:not-allowed; }
.btn-view { background:#374151; color:#fff; }
.btn-view:hover { background:#111827; }
.btn-print-inline { background:#374151; color:#fff; padding:5px 10px; border-radius:6px; font-size:10px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px; }
.btn-print-inline:hover { background:#111827; }
.periodo-card { margin-bottom:24px; background:#fff; border:1px solid #E5E7EB; border-radius:12px; overflow:hidden; }
.periodo-header { display:flex; justify-content:space-between; align-items:center; padding:14px 20px; background:#F9FAFB; border-bottom:1px solid #E5E7EB; }
.periodo-header h3 { margin:0; font-size:16px; font-weight:700; color:#111827; }
.periodo-body { padding:16px 20px; }
.badge-count { background:#065F46; color:#fff; padding:3px 12px; border-radius:20px; font-size:11px; }
.empty-state { text-align:center; padding:60px 20px; background:#fff; border-radius:12px; }
.empty-icon { font-size:48px; margin-bottom:16px; }
.empty-state h3 { margin:0 0 8px; color:#374151; }
.empty-state p { color:#6B7280; font-size:14px; margin:0; }
';
ob_start();
?>
<div style="margin-bottom:24px;">
    <p style="font-size:13px; color:#6B7280; margin:0;">
        Facturas emitidas por el gestor para cada calle de tu barrio.
        Reenvía cada factura al encargado de calle correspondiente.
    </p>
</div>

<?php if (empty($por_periodo)): ?>
<div class="empty-state">
    <div class="empty-icon">📜</div>
    <h3>No hay facturas disponibles</h3>
    <p>Las facturas se generan cuando el gestor aprueba el lote de tu barrio y las envía.</p>
</div>
<?php else: ?>
    <?php foreach ($por_periodo as $periodo_key => $calles_lotes):
        [$anio, $mes] = explode('-', $periodo_key);
        $mes = (int)$mes;
        $total_enviadas = 0;
        $total_pendientes = 0;
        $total_espera = 0;
        foreach ($calles_lotes as $cl) {
            if (!$cl['facturas_enviadas_barrio']) $total_espera++;
            elseif ($cl['certificado_enviado_calle']) $total_enviadas++;
            else $total_pendientes++;
        }
    ?>
    <div class="periodo-card">
        <div class="periodo-header">
            <h3><?= $meses_nombres[$mes] ?> <?= $anio ?></h3>
            <div style="display:flex; align-items:center; gap:10px;">
                <?php if ($total_espera > 0): ?>
                    <span class="badge-enviado pend"><?= $total_espera ?> en espera</span>
                <?php endif; ?>
                <?php if ($total_pendientes > 0): ?>
                    <span class="badge-enviado no"><?= $total_pendientes ?> por enviar</span>
                <?php endif; ?>
                <?php if ($total_enviadas > 0): ?>
                    <span class="badge-enviado si"><?= $total_enviadas ?> enviadas</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="periodo-body">
            <?php foreach ($calles_lotes as $cl):
                $viviendas = $viviendasPorCalle[$cl['id']] ?? [];
                $total_monto_calle = 0;
                foreach ($viviendas as $v) $total_monto_calle += (float)($v['monto'] ?? 0);
                $puede_enviar = $cl['facturas_enviadas_barrio'] && !$cl['certificado_enviado_calle'];
                $num_factura_calle = sprintf('FCT-%04d%02d-%03d-%03d', $cl['periodo_anio'], $cl['periodo_mes'], $cl['calle_id'], $cl['id']);
            ?>
            <div class="factura-calle">
                <div class="factura-calle-header" onclick="toggleDetalle(<?= $cl['id'] ?>)">
                    <div>
                        <h4>📄 <?= htmlspecialchars($cl['calle_nombre']) ?>
                            <a href="router.php?page=certificado_calle&lote_calle_id=<?= $cl['id'] ?>" target="_blank" class="btn-print-inline" onclick="event.stopPropagation();" style="margin-left:8px;">🖨️ PDF</a>
                        </h4>
                        <div style="font-size:11px; color:#6B7280;">
                            👤 <?= htmlspecialchars(trim(($cl['enc_nombre']??'') . ' ' . ($cl['enc_apellido']??''))) ?> •
                            🏠 <?= $cl['casas_pagadas'] ?>/<?= $cl['total_casas'] ?> pag. •
                            💰 S/ <?= number_format($cl['monto_recolectado'] ?? 0, 2) ?>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <?php if ($cl['certificado_enviado_calle']): ?>
                            <span class="badge-enviado si">✅ Enviado a calle</span>
                        <?php elseif ($cl['facturas_enviadas_barrio']): ?>
                            <span class="badge-enviado no">⏳ Pendiente de envío</span>
                        <?php else: ?>
                            <span class="badge-enviado pend">⏳ Esperando gestor</span>
                        <?php endif; ?>
                        <span style="font-size:10px; color:#9CA3AF;">▼</span>
                    </div>
                </div>
                <div class="factura-calle-body" id="detalle-<?= $cl['id'] ?>">
                    <!-- Resumen stats -->
                    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:12px;">
                        <div style="text-align:center; padding:8px; background:#F9FAFB; border-radius:6px;">
                            <div style="font-size:18px; font-weight:800; color:#6B7280;"><?= $cl['total_casas'] ?></div>
                            <div style="font-size:9px; color:#6B7280; text-transform:uppercase;">Casas</div>
                        </div>
                        <div style="text-align:center; padding:8px; background:#F9FAFB; border-radius:6px;">
                            <div style="font-size:18px; font-weight:800; color:#059669;"><?= $cl['casas_pagadas'] ?></div>
                            <div style="font-size:9px; color:#6B7280; text-transform:uppercase;">Pagadas</div>
                        </div>
                        <div style="text-align:center; padding:8px; background:#F9FAFB; border-radius:6px;">
                            <div style="font-size:18px; font-weight:800; color:#DC2626;"><?= $cl['casas_morosas'] ?></div>
                            <div style="font-size:9px; color:#6B7280; text-transform:uppercase;">Morosas</div>
                        </div>
                        <div style="text-align:center; padding:8px; background:#F9FAFB; border-radius:6px;">
                            <div style="font-size:18px; font-weight:800; color:#065F46;">S/ <?= number_format($total_monto_calle, 2) ?></div>
                            <div style="font-size:9px; color:#6B7280; text-transform:uppercase;">Total</div>
                        </div>
                    </div>

                    <!-- Viviendas -->
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
                                <td>
                                    <?php if ($v['estado_servicio']==='Activo'): ?>
                                        <span style="padding:2px 6px; border-radius:8px; font-size:9px; font-weight:700; background:#D1FAE5; color:#065F46;">Activo</span>
                                    <?php elseif ($v['estado_servicio']==='Suspendido'): ?>
                                        <span style="padding:2px 6px; border-radius:8px; font-size:9px; font-weight:700; background:#FEF3C7; color:#92400E;">Suspendido</span>
                                    <?php else: ?><?= $v['estado_servicio'] ?><?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($v['cobro_estado']==='Pagado'): ?>
                                        <span style="color:#059669; font-weight:700;">✅ Pagado</span>
                                        <?php if ($v['fecha_emision']): ?><div style="font-size:9px; color:#9CA3AF;"><?= date('d/m/Y', strtotime($v['fecha_emision'])) ?></div><?php endif; ?>
                                    <?php elseif ($v['cobro_estado']==='Pendiente'): ?>
                                        <span style="color:#D97706; font-weight:700;">⏳ Pendiente</span>
                                    <?php else: ?><span style="color:#D97706;">—</span><?php endif; ?>
                                </td>
                                <td style="font-size:10px; color:#6B7280;"><?= htmlspecialchars($v['referencia_pago'] ?? '—') ?></td>
                                <td style="text-align:right; font-weight:700;"><?= $v['monto'] ? 'S/ '.number_format($v['monto'], 2) : '—' ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="6" style="padding:8px; text-align:right;">TOTAL — <?= htmlspecialchars($cl['calle_nombre']) ?></td>
                                <td style="padding:8px; text-align:right; font-size:13px;">S/ <?= number_format($total_monto_calle, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <?php else: ?>
                    <div style="padding:12px; text-align:center; color:#9CA3AF; font-size:11px;">Sin viviendas registradas.</div>
                    <?php endif; ?>

                    <!-- Acción enviar -->
                    <div style="margin-top:14px; padding-top:12px; border-top:1px solid #E5E7EB; display:flex; justify-content:space-between; align-items:center;">
                        <div style="font-size:11px; color:#6B7280;">
                            Factura N° <?= htmlspecialchars($num_factura_calle) ?>
                            <?php if ($cl['numero_recibo']): ?> • Recibo: <?= htmlspecialchars($cl['numero_recibo']) ?><?php endif; ?>
                        </div>
                        <?php if ($puede_enviar): ?>
                            <form method="POST" enctype="multipart/form-data" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                <input type="hidden" name="form_type" value="enviar_certificado_calle">
                                <input type="hidden" name="lote_calle_id" value="<?= $cl['id'] ?>">
                                <label style="font-size:11px; color:#6B7280; cursor:pointer; display:flex; align-items:center; gap:4px;">
                                    📎 Adjuntar factura propia
                                    <input type="file" name="factura_archivo" accept=".pdf,.jpg,.jpeg,.png" style="display:none;" onchange="this.previousElementSibling.textContent=this.files[0].name">
                                </label>
                                <button type="submit" class="btn btn-send-calle" onclick="return confirm('¿Enviar esta factura a <?= htmlspecialchars(trim(($cl['enc_nombre']??'') . ' ' . ($cl['enc_apellido']??''))) ?>?')">
                                    📨 Enviar
                                </button>
                            </form>
                        <?php elseif ($cl['certificado_enviado_calle']): ?>
                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                <span style="display:inline-flex; align-items:center; gap:5px; color:#065F46; font-weight:700; font-size:12px;">✅ Ya enviado al encargado de calle</span>
                                <?php if ($cl['factura_personalizada']): ?>
                                    <a href="<?= htmlspecialchars($cl['factura_personalizada']) ?>" target="_blank" class="btn btn-send-calle" style="font-size:10px; padding:4px 10px;">📎 Ver Documento</a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span style="color:#9CA3AF; font-size:11px;">El gestor aún no ha enviado las facturas al barrio</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
function toggleDetalle(id) {
    const body = document.getElementById('detalle-' + id);
    body.classList.toggle('open');
}
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
