<?php
// views/gestor/barrios.php — Facturación por Barrio (facturas inline por calle)
use app\models\mainModel;

if (empty($pdo)) $pdo = (new mainModel())->conectar();

$user = check_dashboard_access([1, 2]);
$mes_actual = date('n');
$anio_actual = date('Y');
$meses_nombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

// ========== LOTES PENDIENTES ==========
$pendientes = $pdo->query("
    SELECT lb.*, b.nombre as barrio_nombre,
        (SELECT CONCAT(u.nombre,' ',u.apellido) FROM detalles_encargado_barrio d JOIN usuarios u ON u.id=d.usuario_id WHERE d.barrio_id=b.id LIMIT 1) as encargado,
        (SELECT COUNT(*) FROM lotes_calle WHERE lote_barrio_id=lb.id) as total_calles_lote,
        (SELECT COALESCE(SUM(monto_recolectado),0) FROM lotes_calle WHERE lote_barrio_id=lb.id) as suma_callesis
    FROM lotes_barrio lb
    JOIN barrios b ON lb.barrio_id=b.id
    WHERE lb.estado='Enviado'
    ORDER BY lb.fecha_envio DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ========== LOTES FACTURADOS ==========
$facturados = $pdo->query("
    SELECT lb.*, b.nombre as barrio_nombre,
        rf.numero_recibo, rf.fecha_emision as fecha_recibo, rf.monto_aprobado,
        (SELECT CONCAT(u.nombre,' ',u.apellido) FROM detalles_encargado_barrio d JOIN usuarios u ON u.id=d.usuario_id WHERE d.barrio_id=b.id LIMIT 1) as encargado,
        (SELECT COUNT(*) FROM lotes_calle WHERE lote_barrio_id=lb.id AND certificado_generado=1) as certificados_generados,
        (SELECT COUNT(*) FROM lotes_calle WHERE lote_barrio_id=lb.id) as total_calles_lote
    FROM lotes_barrio lb
    JOIN barrios b ON lb.barrio_id=b.id
    LEFT JOIN recibos_finiquito rf ON rf.lote_barrio_id=lb.id
    WHERE lb.estado='Aprobado'
    ORDER BY lb.fecha_aprobacion DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

// ========== CALLES + VIVIENDAS por barrio facturado ==========
$datosFacturados = [];
foreach ($facturados as $fb) {
    // Calles de este barrio
    $st = $pdo->prepare("
        SELECT lc.id as lote_calle_id, lc.calle_id, c.nombre as calle_nombre,
            lc.total_casas, lc.casas_pagadas, lc.casas_morosas,
            lc.monto_recolectado, lc.monto_esperado, lc.certificado_generado,
            lc.periodo_mes, lc.periodo_anio, lc.fecha_certificado,
            (SELECT CONCAT(u.nombre,' ',u.apellido) FROM detalles_encargado_calle d JOIN usuarios u ON u.id=d.usuario_id WHERE d.calle_id=c.id LIMIT 1) as encargado_calle,
            (SELECT d.dni FROM detalles_encargado_calle d WHERE d.calle_id=c.id LIMIT 1) as encargado_dni
        FROM lotes_calle lc
        JOIN calles c ON lc.calle_id=c.id
        WHERE lc.lote_barrio_id=?
        ORDER BY c.nombre
    ");
    $st->execute([$fb['id']]);
    $calles = $st->fetchAll(PDO::FETCH_ASSOC);

    // Viviendas de todas las calles de este barrio (una sola consulta)
    $vStmt = $pdo->prepare("
        SELECT lc.id as lote_calle_id,
            v.id as vivienda_id, v.numero_casa as vivienda_nombre, v.direccion,
            v.estado_servicio, v.propietario,
            cob.id as cobro_id, cob.monto, cob.estado as cobro_estado,
            cob.estado_verificacion, cob.referencia_pago, cob.comprobante_calle,
            cob.fecha_emision
        FROM viviendas v
        JOIN lotes_calle lc ON lc.calle_id = v.calle_id AND lc.lote_barrio_id = ?
        LEFT JOIN cobros cob ON cob.vivienda_id = v.id
            AND cob.mes = lc.periodo_mes AND cob.anio = lc.periodo_anio
            AND cob.lote_calle_id = lc.id
        WHERE v.estado_servicio != 'Anulado' AND v.calle_id IN (
            SELECT calle_id FROM lotes_calle WHERE lote_barrio_id = ?
        )
        ORDER BY lc.id, v.direccion ASC
    ");
    $vStmt->execute([$fb['id'], $fb['id']]);
    $viviendas = $vStmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar viviendas por lote_calle_id
    $viviendasPorCalle = [];
    foreach ($viviendas as $vv) {
        $viviendasPorCalle[$vv['lote_calle_id']][] = $vv;
    }

    // Combinar
    foreach ($calles as &$calle) {
        $calle['viviendas'] = $viviendasPorCalle[$calle['lote_calle_id']] ?? [];
    }
    unset($calle);
    $datosFacturados[$fb['id']] = $calles;
}

$title = 'Facturación — EcoCusco';
$header_title = '📄 Facturación por Barrio';
$header_subtitle = 'Aprueba lotes, genera facturas por calle y envía todo al encargado de barrio';

$extra_css = '
.lote-card { background:#fff; border:1px solid #E5E7EB; border-radius:12px; margin-bottom:16px; overflow:hidden; }
.lote-card.pendiente { border-left:4px solid #F59E0B; }
.lote-card.facturado { border-left:4px solid #10B981; }
.lote-header { display:flex; justify-content:space-between; align-items:center; padding:16px 20px; background:#F9FAFB; border-bottom:1px solid #E5E7EB; }
.lote-header h3 { margin:0; font-size:16px; font-weight:700; }
.lote-body { padding:16px 20px; }
.lote-stats { display:flex; gap:16px; flex-wrap:wrap; margin:10px 0; }
.lote-stat { text-align:center; padding:10px 16px; background:#F9FAFB; border-radius:8px; min-width:100px; }
.lote-stat .num { font-size:18px; font-weight:800; }
.lote-stat .lbl { font-size:10px; color:#6B7280; text-transform:uppercase; margin-top:2px; }
.badge-estado { display:inline-block; padding:4px 12px; border-radius:12px; font-size:11px; font-weight:700; }
.badge-Enviado { background:#FEF3C7; color:#92400E; }
.badge-Aprobado { background:#D1FAE5; color:#065F46; }
.btn { padding:8px 16px; border-radius:6px; font-size:12px; font-weight:600; border:none; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:5px; }
.btn-approve { background:#10B981; color:#fff; }
.btn-approve:hover { background:#059669; }
.btn-reject { background:#DC2626; color:#fff; }
.btn-reject:hover { background:#B91C1C; }
.btn-print { background:#374151; color:#fff; }
.btn-print:hover { background:#111827; }
.btn-send { background:#065F46; color:#fff; }
.btn-send:hover { background:#047857; }
.alert-info { background:#EFF6FF; border:1px solid #DBEAFE; border-radius:8px; padding:40px; text-align:center; color:#1E40AF; }
.alert-info h3 { margin:0 0 6px; }
.alert-info p { margin:0; font-size:13px; color:#6B7280; }
.form-inline { display:inline; }
.form-inline input[type=text] { padding:8px 12px; border:1px solid #FCA5A5; border-radius:6px; min-width:200px; }
.toggle-section { cursor:pointer; color:#6B7280; font-size:12px; }
.toggle-section:hover { color:#111827; }
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
.firma-area { display:grid; grid-template-columns:1fr 1fr; gap:30px; margin-top:24px; padding-top:20px; border-top:1px solid #E5E7EB; }
.firma-area > div { text-align:center; padding-top:30px; border-top:2px solid #E5E7EB; }
';
ob_start();
render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null);
?>

<!-- SECCIÓN 1: LOTES PENDIENTES -->
<h2 style="font-size:18px; font-weight:700; color:#111827; margin:0 0 16px;">⏳ Lotes Pendientes de Facturación</h2>

<?php if (empty($pendientes)): ?>
    <div class="alert-info" style="margin-bottom:32px;">
        <div style="font-size:40px; margin-bottom:10px;">✅</div>
        <h3>No hay lotes pendientes</h3>
        <p>Todos los barrios han sido facturados. Los nuevos lotes aparecerán aquí cuando los encargados de barrio los envíen.</p>
    </div>
<?php else: ?>
    <?php foreach ($pendientes as $lb):
        $dif = (float)($lb['monto_total_esperado']??0) - (float)($lb['monto_total_recolectado']??0);
    ?>
    <div class="lote-card pendiente">
        <div class="lote-header">
            <div>
                <h3>🏙️ <?= htmlspecialchars($lb['barrio_nombre']) ?></h3>
                <div style="font-size:12px; color:#6B7280;">
                    👤 <?= htmlspecialchars($lb['encargado'] ?? 'Sin encargado') ?> •
                    📅 Enviado: <?= $lb['fecha_envio'] ? date('d/m/Y H:i', strtotime($lb['fecha_envio'])) : '—' ?> •
                    🏷️ <?= $meses_nombres[$lb['periodo_mes']] ?> <?= $lb['periodo_anio'] ?>
                </div>
            </div>
            <span class="badge-estado badge-Enviado">⏳ Pendiente</span>
        </div>
        <div class="lote-body">
            <div class="lote-stats">
                <div class="lote-stat"><div class="num" style="color:#6B7280;"><?= $lb['total_calles_lote'] ?></div><div class="lbl">Calles</div></div>
                <div class="lote-stat"><div class="num" style="color:#059669;">S/ <?= number_format($lb['monto_total_recolectado']??0, 2) ?></div><div class="lbl">Recolectado</div></div>
                <div class="lote-stat"><div class="num" style="color:#D97706;">S/ <?= number_format($lb['monto_total_esperado']??0, 2) ?></div><div class="lbl">Esperado</div></div>
                <div class="lote-stat"><div class="num" style="color:<?= $dif>0?'#DC2626':'#059669' ?>;"><?php if ($dif > 0): ?>-<?php endif; ?>S/ <?= number_format($dif, 2) ?></div><div class="lbl">Diferencia</div></div>
            </div>
            <?php if (!empty($lb['observaciones_barrio'])): ?>
            <div style="background:#FFFBEB; padding:8px 12px; border-radius:6px; font-size:12px; color:#92400E; margin:8px 0;">💬 <?= htmlspecialchars($lb['observaciones_barrio']) ?></div>
            <?php endif; ?>
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; border-top:1px solid #E5E7EB; padding-top:12px;">
                <form method="POST" onsubmit="return confirm('¿Aprobar lote de <?= htmlspecialchars($lb['barrio_nombre']) ?>? Se emitirá una FACTURA por cada calle.')">
                    <input type="hidden" name="form_type" value="aprobar_lote_barrio">
                    <input type="hidden" name="lote_id" value="<?= $lb['id'] ?>">
                    <button type="submit" class="btn btn-approve">✅ Aprobar y Emitir Facturas</button>
                </form>
                <button onclick="toggleRechazo(<?= $lb['id'] ?>)" class="btn btn-reject">❌ Rechazar</button>
                <div id="rechazo-<?= $lb['id'] ?>" style="display:none; width:100%;">
                    <form method="POST" style="display:flex; gap:8px; align-items:center; margin-top:8px;">
                        <input type="hidden" name="form_type" value="rechazar_lote_barrio">
                        <input type="hidden" name="lote_id" value="<?= $lb['id'] ?>">
                        <input type="text" name="motivo_rechazo" placeholder="Escribe el motivo del rechazo..." required style="flex:1; padding:8px 12px; border:1px solid #FCA5A5; border-radius:6px;">
                        <button type="submit" class="btn btn-reject">Confirmar Rechazo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- SECCIÓN 2: FACTURAS EMITIDAS (por calle, inline) -->
<h2 style="font-size:18px; font-weight:700; color:#111827; margin:32px 0 16px;">📄 Facturas Emitidas</h2>

<?php if (empty($facturados)): ?>
    <div class="alert-info">
        <div style="font-size:40px; margin-bottom:10px;">📄</div>
        <h3>No hay facturas emitidas</h3>
        <p>Las facturas por calle se generan automáticamente al aprobar un lote de barrio.</p>
    </div>
<?php else: ?>
    <?php foreach ($facturados as $fb):
        $calles = $datosFacturados[$fb['id']] ?? [];
    ?>
    <div class="lote-card facturado">
        <div class="lote-header" style="cursor:pointer;" onclick="toggleCalles(<?= $fb['id'] ?>)">
            <div>
                <h3>🏙️ <?= htmlspecialchars($fb['barrio_nombre']) ?></h3>
                <div style="font-size:12px; color:#6B7280;">
                    👤 <?= htmlspecialchars($fb['encargado'] ?? '—') ?> •
                    🏷️ <?= $meses_nombres[$fb['periodo_mes']] ?> <?= $fb['periodo_anio'] ?> •
                    📅 <?= $fb['fecha_recibo'] ? date('d/m/Y', strtotime($fb['fecha_recibo'])) : date('d/m/Y', strtotime($fb['fecha_aprobacion'])) ?>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <span class="badge-estado badge-Aprobado"><?= $fb['certificados_generados'] ?>/<?= $fb['total_calles_lote'] ?> fact.</span>
                <span class="toggle-section" id="toggleIcon<?= $fb['id'] ?>">▼</span>
            </div>
        </div>
        <div class="lote-body">
            <!-- Enviar al Barrio -->
            <div style="margin:10px 0 14px;">
                <?php if (!empty($fb['facturas_enviadas_barrio'])): ?>
                    <span style="display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:#D1FAE5; color:#065F46; border-radius:8px; font-size:12px; font-weight:700;">
                        ✅ Facturas enviadas al Encargado de Barrio
                    </span>
                <?php else: ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="form_type" value="enviar_facturas_barrio">
                        <input type="hidden" name="lote_id" value="<?= $fb['id'] ?>">
                        <button type="submit" class="btn btn-send" onclick="return confirm('¿Enviar todas las facturas al encargado de barrio <?= htmlspecialchars($fb['encargado'] ?? '') ?>?')">
                            📨 Enviar Facturas al Barrio
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Facturas por calle (inline) -->
            <div id="calles-<?= $fb['id'] ?>" style="display:none;">
                <?php foreach ($calles as $c):
                    $viviendas = $c['viviendas'] ?? [];
                    $total_monto = 0;
                    foreach ($viviendas as $v) $total_monto += (float)($v['monto'] ?? 0);
                    $num_factura_calle = sprintf('FCT-%04d%02d-%03d-%03d', $c['periodo_anio'], $c['periodo_mes'], $c['calle_id'], $c['lote_calle_id']);
                ?>
                <div class="factura-calle" id="factura-calle-<?= $c['lote_calle_id'] ?>">
                    <div class="factura-calle-header" onclick="toggleFacturaCalle(<?= $c['lote_calle_id'] ?>)">
                        <div>
                            <h4>📄 <?= htmlspecialchars($c['calle_nombre']) ?></h4>
                            <div style="font-size:11px; color:#6B7280;">
                                👤 <?= htmlspecialchars($c['encargado_calle'] ?? '—') ?> •
                                🏠 <?= $c['casas_pagadas'] ?>/<?= $c['total_casas'] ?> pag. •
                                💰 S/ <?= number_format($c['monto_recolectado'] ?? 0, 2) ?>
                                <span style="display:inline-block; margin-left:8px; font-size:9px; color:#9CA3AF;"><?= $num_factura_calle ?></span>
                            </div>
                        </div>
                        <span style="font-size:11px; color:#6B7280;">▼ detalle</span>
                    </div>
                    <div class="factura-calle-body" id="factura-body-<?= $c['lote_calle_id'] ?>">
                        <!-- Resumen -->
                        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:14px;">
                            <div style="text-align:center; padding:8px; background:#F9FAFB; border-radius:6px;">
                                <div style="font-size:20px; font-weight:800; color:#6B7280;"><?= $c['total_casas'] ?></div>
                                <div style="font-size:9px; color:#6B7280; text-transform:uppercase;">Casas</div>
                            </div>
                            <div style="text-align:center; padding:8px; background:#F9FAFB; border-radius:6px;">
                                <div style="font-size:20px; font-weight:800; color:#059669;"><?= $c['casas_pagadas'] ?></div>
                                <div style="font-size:9px; color:#6B7280; text-transform:uppercase;">Pagadas</div>
                            </div>
                            <div style="text-align:center; padding:8px; background:#F9FAFB; border-radius:6px;">
                                <div style="font-size:20px; font-weight:800; color:#DC2626;"><?= $c['casas_morosas'] ?></div>
                                <div style="font-size:9px; color:#6B7280; text-transform:uppercase;">Morosas</div>
                            </div>
                            <div style="text-align:center; padding:8px; background:#F9FAFB; border-radius:6px;">
                                <div style="font-size:20px; font-weight:800; color:#065F46;">S/ <?= number_format($total_monto, 2) ?></div>
                                <div style="font-size:9px; color:#6B7280; text-transform:uppercase;">Total</div>
                            </div>
                        </div>

                        <!-- Tabla de viviendas -->
                        <?php if (count($viviendas) > 0): ?>
                        <div class="table-wrap">
                        <table class="invoice-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Dirección</th>
                                    <th>Propietario</th>
                                    <th>Servicio</th>
                                    <th>Estado Pago</th>
                                    <th>Referencia</th>
                                    <th style="text-align:right;">Monto</th>
                                </tr>
                            </thead>
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
                                        <?php else: ?>
                                            <?= $v['estado_servicio'] ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($v['cobro_estado']==='Pagado'): ?>
                                            <span style="color:#059669; font-weight:700;">✅ Pagado</span>
                                            <?php if ($v['fecha_emision']): ?><div style="font-size:9px; color:#9CA3AF;"><?= date('d/m/Y', strtotime($v['fecha_emision'])) ?></div><?php endif; ?>
                                        <?php elseif ($v['cobro_estado']==='Pendiente'): ?>
                                            <span style="color:#D97706; font-weight:700;">⏳ Pendiente</span>
                                        <?php else: ?>
                                            <span style="color:#D97706; font-weight:700;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:10px; color:#6B7280;"><?= htmlspecialchars($v['referencia_pago'] ?? '—') ?></td>
                                    <td style="text-align:right; font-weight:700;"><?= $v['monto'] ? 'S/ '.number_format($v['monto'], 2) : '—' ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td colspan="6" style="padding:8px; text-align:right;">TOTAL RECAUDADO — <?= htmlspecialchars($c['calle_nombre']) ?></td>
                                    <td style="padding:8px; text-align:right; font-size:14px;">S/ <?= number_format($total_monto, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                        <?php else: ?>
                        <div style="padding:12px; text-align:center; color:#9CA3AF; font-size:12px;">No hay viviendas registradas para esta calle.</div>
                        <?php endif; ?>

                        <!-- Firmas -->
                        <div class="firma-area">
                            <div>
                                <div style="font-size:11px; color:#6B7280;">Encargado de Calle</div>
                                <div style="font-size:13px; font-weight:700; margin-top:4px;"><?= htmlspecialchars($c['encargado_calle'] ?? '—') ?></div>
                                <div style="font-size:10px; color:#9CA3AF;">DNI: <?= htmlspecialchars($c['encargado_dni'] ?? '—') ?></div>
                            </div>
                            <div>
                                <div style="font-size:11px; color:#6B7280;">Gestor / Administrador</div>
                                <div style="font-size:13px; font-weight:700; margin-top:4px;">
                                    <?= htmlspecialchars($_SESSION['nombre'] ?? '') . ' ' . htmlspecialchars($_SESSION['apellido'] ?? '') ?>
                                </div>
                                <div style="font-size:10px; color:#9CA3AF;">Factura N° <?= htmlspecialchars($fb['numero_recibo'] ?? '—') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div style="margin-top:12px; font-size:12px; color:#6B7280; text-align:center;">
                    ✅ Estas <?= count($calles) ?> facturas por calle están disponibles para el encargado de barrio en su panel <strong>"Certificados"</strong>.
                    El encargado de barrio las reenvía a cada encargado de calle.
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
function toggleRechazo(id) {
    const div = document.getElementById('rechazo-' + id);
    div.style.display = div.style.display === 'none' ? 'block' : 'none';
}
function toggleCalles(id) {
    const wrap = document.getElementById('calles-' + id);
    const icon = document.getElementById('toggleIcon' + id);
    wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
    if (icon) icon.classList.toggle('open');
}
function toggleFacturaCalle(id) {
    const body = document.getElementById('factura-body-' + id);
    body.classList.toggle('open');
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>