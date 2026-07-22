<?php
// views/gestor/revisar_lotes.php — Revisión y Aprobación de Lotes de Barrio
global $pdo;
require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../app/helpers.php';
use app\models\mainModel;
if (empty($pdo)) $pdo = (new mainModel())->conectar();

$user = check_dashboard_access([1, 2]);
$page = 'revisar_lotes';

$meses_nombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

// Lotes pendientes de aprobación (Enviados)
$lotesPendientes = $pdo->query(
    "SELECT lb.*, b.nombre as barrio_nombre, 
            u.nombre as encargado_nombre, u.apellido as encargado_apellido,
            (SELECT COUNT(*) FROM lotes_calle lc WHERE lc.lote_barrio_id = lb.id) as total_calles_lote,
            (SELECT COALESCE(SUM(lc2.monto_recolectado),0) FROM lotes_calle lc2 WHERE lc2.lote_barrio_id = lb.id) as real_recolectado
     FROM lotes_barrio lb
     JOIN barrios b ON lb.barrio_id = b.id
     JOIN usuarios u ON lb.encargado_barrio_id = u.id
     WHERE lb.estado = 'Enviado'
     ORDER BY lb.fecha_envio DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// Lotes ya revisados (historial reciente)
$historial = $pdo->query(
    "SELECT lb.*, b.nombre as barrio_nombre, u.nombre as gestor_nombre, u.apellido as gestor_apellido,
            rf.numero_recibo
     FROM lotes_barrio lb
     JOIN barrios b ON lb.barrio_id = b.id
     LEFT JOIN usuarios u ON lb.gestor_id = u.id
     LEFT JOIN recibos_finiquito rf ON rf.lote_barrio_id = lb.id
     WHERE lb.estado IN ('Aprobado','Rechazado')
     ORDER BY COALESCE(lb.fecha_aprobacion, lb.fecha_envio) DESC
     LIMIT 30"
)->fetchAll(PDO::FETCH_ASSOC);

$title = "Revisar Lotes - EcoCusco";
$header_title = "Revisión de Lotes de Barrio";
$header_subtitle = "Revisa, compara y aprueba los lotes enviados por los encargados de barrio.";

ob_start();
?>
<?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

<?php if(empty($lotesPendientes)): ?>
<div class="card" style="text-align:center; padding:50px 20px;">
    <div style="font-size:48px; margin-bottom:16px;">✅</div>
    <h3 style="color:#065F46; margin:0 0 8px;">No hay lotes pendientes</h3>
    <p style="color:#6B7280; font-size:13px; margin:0;">Todos los lotes de barrio han sido revisados</p>
</div>
<?php else: ?>

<div style="margin-bottom:12px; display:flex; align-items:center; gap:10px;">
    <span style="background:#FEF3C7; color:#92400E; padding:4px 12px; border-radius:20px; font-size:13px; font-weight:700;">
        <?= count($lotesPendientes) ?> lote<?= count($lotesPendientes)>1?'s':'' ?> pendiente<?= count($lotesPendientes)>1?'s':'' ?>
    </span>
    <span style="color:#6B7280; font-size:12px;">Haz clic en un lote para ver el desglose completo</span>
</div>

<?php foreach($lotesPendientes as $lb):
    $dif = $lb['monto_total_recolectado'] - $lb['monto_total_esperado'];
    $pct = $lb['monto_total_esperado'] > 0 ? round(($lb['monto_total_recolectado'] / $lb['monto_total_esperado']) * 100) : 0;
?>
<div class="card" style="border-left:4px solid <?= $lb['alerta_deuda']?'#DC2626':'#10B981' ?>; margin-bottom:20px;">

    <!-- HEADER DEL LOTE -->
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
        <div>
            <h3 style="margin:0; font-size:18px; color:#111827;">🏘️ <?= htmlspecialchars($lb['barrio_nombre']) ?></h3>
            <div style="font-size:12px; color:#6B7280; margin-top:4px;">
                <?= $meses_nombres[$lb['periodo_mes']] ?> <?= $lb['periodo_anio'] ?> •
                Encargado: <?= htmlspecialchars($lb['encargado_nombre'] . ' ' . $lb['encargado_apellido']) ?>
                <?php if($lb['fecha_envio']): ?> • Recibido: <?= date('d/m/Y H:i', strtotime($lb['fecha_envio'])) ?><?php endif; ?>
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            <?php if($lb['alerta_deuda']): ?>
            <span style="background:#FEE2E2; color:#991B1B; padding:6px 14px; border-radius:20px; font-size:11px; font-weight:700;">⚠️ ALERTA</span>
            <?php else: ?>
            <span style="background:#D1FAE5; color:#065F46; padding:6px 14px; border-radius:20px; font-size:11px; font-weight:700;">✅ OK</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- COMPARACIÓN DE MONTOS -->
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
        <div style="background:#F9FAFB; padding:14px; border-radius:8px; text-align:center; border:1px solid #E5E7EB;">
            <div style="font-size:10px; color:#6B7280; text-transform:uppercase; font-weight:600; margin-bottom:4px;">Esperado</div>
            <div style="font-size:22px; font-weight:800; color:#111827;">S/ <?= number_format($lb['monto_total_esperado'],2) ?></div>
            <div style="font-size:10px; color:#9CA3AF; margin-top:2px;">de <?= $lb['total_calles'] ?? 0 ?> calles</div>
        </div>
        <div style="background:<?= $dif<0?'#FEF2F2':'#F0FDF4' ?>; padding:14px; border-radius:8px; text-align:center; border:1px solid <?= $dif<0?'#FECACA':'#BBF7D0' ?>;">
            <div style="font-size:10px; color:#6B7280; text-transform:uppercase; font-weight:600; margin-bottom:4px;">Recolectado</div>
            <div style="font-size:22px; font-weight:800; color:<?= $dif<0?'#DC2626':'#059669' ?>;">S/ <?= number_format($lb['monto_total_recolectado'],2) ?></div>
            <div style="font-size:10px; color:#9CA3AF; margin-top:2px;"><?= $lb['calles_completas'] ?? 0 ?> calles completas</div>
        </div>
        <div style="background:<?= $dif<0?'#FEF3C7':'#EFF6FF' ?>; padding:14px; border-radius:8px; text-align:center; border:1px solid <?= $dif<0?'#FDE68A':'#BFDBFE' ?>;">
            <div style="font-size:10px; color:#6B7280; text-transform:uppercase; font-weight:600; margin-bottom:4px;">Diferencia</div>
            <div style="font-size:22px; font-weight:800; color:<?= $dif<0?'#D97706':'#1D4ED8' ?>;">
                <?= $dif >= 0 ? '+' : '' ?>S/ <?= number_format(abs($dif),2) ?>
            </div>
            <div style="font-size:10px; color:<?= $dif<0?'#D97706':'#1D4ED8' ?>; margin-top:2px;"><?= $dif>=0?'Sobrante':'Faltante' ?></div>
        </div>
        <div style="background:#F9FAFB; padding:14px; border-radius:8px; text-align:center; border:1px solid #E5E7EB;">
            <div style="font-size:10px; color:#6B7280; text-transform:uppercase; font-weight:600; margin-bottom:4px;">Cumplimiento</div>
            <div style="font-size:28px; font-weight:800; color:<?= $pct>=100?'#059669':($pct>=70?'#D97706':'#DC2626') ?>;"><?= $pct ?>%</div>
            <div style="background:#E5E7EB; height:4px; border-radius:2px; margin-top:6px; overflow:hidden;">
                <div style="width:<?= min(100,$pct) ?>%; height:100%; background:<?= $pct>=100?'#10B981':($pct>=70?'#F59E0B':'#EF4444') ?>;"></div>
            </div>
        </div>
    </div>

    <!-- COMPROBANTE GLOBAL DEL BARRIO -->
    <?php if(!empty($lb['comprobante_lote'])): ?>
    <div style="margin-bottom:14px; padding:10px 14px; background:#EFF6FF; border-radius:6px; border-left:3px solid #3B82F6; display:flex; align-items:center; gap:10px;">
        <span style="font-size:12px; color:#1E40AF; font-weight:600;">📎 Comprobante del Barrio:</span>
        <a href="#" data-url="<?= htmlspecialchars($lb['comprobante_lote']) ?>" onclick="return openComprobanteModal(this)" 
           style="background:#3B82F6; color:white; padding:4px 12px; border-radius:4px; text-decoration:none; font-size:11px; font-weight:600;">
            📷 Ver Comprobante
        </a>
    </div>
    <?php endif; ?>

    <?php if(!empty($lb['comprobante_barrio'])): ?>
    <div style="margin-bottom:14px; padding:10px 14px; background:#F0FDF4; border-radius:6px; border-left:3px solid #10B981; display:flex; align-items:center; gap:10px;">
        <span style="font-size:12px; color:#065F46; font-weight:600;">📎 Comprobante de Pago del Barrio:</span>
        <a href="#" data-url="<?= htmlspecialchars($lb['comprobante_barrio']) ?>" onclick="return openComprobanteModal(this)" 
           style="background:#10B981; color:white; padding:4px 12px; border-radius:4px; text-decoration:none; font-size:11px; font-weight:600;">
            📷 Ver Captura
        </a>
    </div>
    <?php endif; ?>

    <?php if(!empty($lb['observaciones_barrio'])): ?>
    <div style="margin-bottom:14px; padding:10px 14px; background:#F9FAFB; border-radius:6px; border:1px solid #E5E7EB;">
        <span style="font-size:11px; color:#6B7280; font-weight:600;">💬 Nota del Encargado:</span>
        <p style="margin:4px 0 0; font-size:12px; color:#374151;"><?= htmlspecialchars($lb['observaciones_barrio']) ?></p>
    </div>
    <?php endif; ?>

    <!-- DESGLOSE POR CALLES -->
    <div style="margin-bottom:16px;">
        <button onclick="toggleLote(<?= $lb['id'] ?>)" style="background:#374151; color:white; border:none; padding:8px 16px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px;">
            📋 Ver Desglose por Calles
        </button>

        <div id="lote-<?= $lb['id'] ?>" style="display:none; margin-top:14px;">
            <?php
            $callesStmt = $pdo->prepare(
                "SELECT lc.*, c.nombre as calle_nombre, u.nombre as enc_nombre, u.apellido as enc_apellido,
                        (SELECT COUNT(*) FROM viviendas v WHERE v.calle_id = lc.calle_id AND v.estado_servicio = 'Activo' 
                         AND v.id NOT IN (SELECT vivienda_id FROM solicitudes_vivienda WHERE tipo='Baja' AND estado='Aprobado')) as total_viviendas,
                        (SELECT COUNT(*) FROM viviendas v WHERE v.calle_id = lc.calle_id AND v.estado_servicio = 'Activo' AND v.exento_cobro = 1
                         AND v.id NOT IN (SELECT vivienda_id FROM solicitudes_vivienda WHERE tipo='Baja' AND estado='Aprobado')) as viviendas_exentas
                 FROM lotes_calle lc 
                 JOIN calles c ON lc.calle_id = c.id 
                 JOIN usuarios u ON lc.encargado_calle_id = u.id
                 WHERE lc.lote_barrio_id = ?
                 ORDER BY c.nombre"
            );
            $callesStmt->execute([$lb['id']]);
            $callesLote = $callesStmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php foreach($callesLote as $cl):
                $cobrables = ($cl['total_viviendas'] ?? 0) - ($cl['viviendas_exentas'] ?? 0);
                $sinPagar = max(0, $cobrables - $cl['casas_pagadas']);
                $pctCalle = $cobrables > 0 ? round(($cl['casas_pagadas'] / $cobrables) * 100) : 0;
            ?>
            <div style="border:1px solid #E5E7EB; border-radius:8px; padding:14px; margin-bottom:10px; background:white;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                    <div>
                        <strong style="font-size:14px; color:#111827;">🛣️ <?= htmlspecialchars($cl['calle_nombre']) ?></strong>
                        <div style="font-size:11px; color:#6B7280;">Encargado: <?= htmlspecialchars($cl['enc_nombre'] . ' ' . $cl['enc_apellido']) ?></div>
                    </div>
                    <div style="display:flex; gap:16px; align-items:center; font-size:12px;">
                        <span title="Pagadas"><strong style="color:#059669;"><?= $cl['casas_pagadas'] ?></strong> pagadas</span>
                        <span title="Sin pagar" style="color:#DC2626;"><strong><?= $sinPagar ?></strong> sin pagar</span>
                        <span title="Exoneradas" style="color:#6D28D9;"><strong><?= $cl['viviendas_exentas'] ?? 0 ?></strong> exentas</span>
                        <span style="font-weight:700; color:#111827;">S/ <?= number_format($cl['monto_recolectado'],2) ?></span>
                    </div>
                </div>

                <!-- Barra de progreso -->
                <div style="margin-top:8px;">
                    <div style="display:flex; justify-content:space-between; font-size:10px; color:#6B7280; margin-bottom:3px;">
                        <span><?= $cl['casas_pagadas'] ?> de <?= $cobrables ?> cobrables</span>
                        <span style="font-weight:700;"><?= $pctCalle ?>%</span>
                    </div>
                    <div style="background:#E5E7EB; height:6px; border-radius:3px; overflow:hidden;">
                        <div style="width:<?= $pctCalle ?>%; height:100%; background:<?= $pctCalle>=100?'#10B981':($pctCalle>=70?'#F59E0B':'#EF4444') ?>;"></div>
                    </div>
                </div>

                <!-- Comprobante de la calle -->
                <?php if(!empty($cl['comprobante_lote'])): ?>
                <div style="margin-top:8px;">
                    <a href="#" data-url="<?= htmlspecialchars($cl['comprobante_lote']) ?>" onclick="return openComprobanteModal(this)" 
                       style="font-size:11px; color:#92400E; text-decoration:none; background:#FEF3C7; padding:3px 8px; border-radius:4px;">
                        📎 Comprobante de Calle
                    </a>
                </div>
                <?php endif; ?>

                <!-- Referencia del lote -->
                <?php if(!empty($cl['referencia_lote'])): ?>
                <div style="margin-top:6px; font-size:11px;">
                    <span style="color:#6B7280;">Referencia:</span>
                    <strong style="background:#F3F4F6; padding:2px 6px; border-radius:3px;"><?= htmlspecialchars($cl['referencia_lote']) ?></strong>
                </div>
                <?php endif; ?>

                <!-- Ver viviendas de esta calle -->
                <button onclick="toggleCasas(<?= $cl['id'] ?>)" style="margin-top:8px; background:white; color:#374151; border:1px solid #D1D5DB; padding:5px 12px; border-radius:4px; font-size:11px; cursor:pointer; font-weight:600;">
                    👁️ Ver Viviendas (<?= $cl['total_viviendas'] ?? 0 ?>)
                </button>

                <div id="casas-<?= $cl['id'] ?>" style="display:none; margin-top:10px;">
                    <?php
                    $casasStmt = $pdo->prepare(
                        "SELECT v.numero_casa, v.propietario, v.exento_cobro, v.estado_servicio,
                                c.monto, c.estado, c.referencia_pago, c.comprobante_calle
                         FROM viviendas v
                         LEFT JOIN cobros c ON c.vivienda_id = v.id AND c.mes = ? AND c.anio = ? AND c.estado != 'Anulado'
                         WHERE v.calle_id = ?
                         AND v.id NOT IN (SELECT vivienda_id FROM solicitudes_vivienda WHERE tipo='Baja' AND estado='Aprobado')
                         ORDER BY CAST(v.numero_casa AS UNSIGNED), v.numero_casa"
                    );
                    $casasStmt->execute([$lb['periodo_mes'], $lb['periodo_anio'], $cl['calle_id']]);
                    $casasData = $casasStmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table style="width:100%; border-collapse:collapse; font-size:11px;">
                        <thead>
                            <tr style="background:#F9FAFB; text-align:left;">
                                <th style="padding:6px 8px;"># Casa</th>
                                <th style="padding:6px 8px;">Propietario</th>
                                <th style="padding:6px 8px; text-align:center;">Estado</th>
                                <th style="padding:6px 8px;">Referencia</th>
                                <th style="padding:6px 8px; text-align:center;">Comprobante</th>
                                <th style="padding:6px 8px; text-align:right;">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($casasData as $cs):
                                $pagado = $cs['estado'] === 'Pagado';
                                $exenta = $cs['exento_cobro'] == 1;
                                $suspendida = $cs['estado_servicio'] === 'Suspendido';
                            ?>
                            <tr style="border-bottom:1px solid #F3F4F6; background:<?= $suspendida?'#F9FAFB':($exenta?'#F5F3FF':($pagado?'#F0FDF4':'#FEF2F2')) ?>; opacity:<?= $suspendida?'0.5':'1' ?>;">
                                <td style="padding:5px 8px; font-weight:600;">#<?= htmlspecialchars($cs['numero_casa']) ?></td>
                                <td style="padding:5px 8px;"><?= htmlspecialchars($cs['propietario']) ?></td>
                                <td style="padding:5px 8px; text-align:center;">
                                    <?php if($suspendida): ?>
                                        <span style="color:#6B7280; font-size:10px;">Suspendida</span>
                                    <?php elseif($exenta): ?>
                                        <span style="background:#EDE9FE; color:#5B21B6; padding:1px 6px; border-radius:8px; font-size:9px; font-weight:600;">Exenta</span>
                                    <?php elseif($pagado): ?>
                                        <span style="background:#D1FAE5; color:#065F46; padding:1px 6px; border-radius:8px; font-size:9px; font-weight:600;">✅ Pagado</span>
                                    <?php else: ?>
                                        <span style="background:#FEE2E2; color:#991B1B; padding:1px 6px; border-radius:8px; font-size:9px; font-weight:600;">⏳ Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:5px 8px; font-weight:600; font-size:10px;"><?= htmlspecialchars($cs['referencia_pago'] ?? '—') ?></td>
                                <td style="padding:5px 8px; text-align:center;">
                                    <?php if(!empty($cs['comprobante_calle']) && preg_match('/\.(jpe?g|png|gif|webp|bmp)(\?.*)?$/i', $cs['comprobante_calle'])): ?>
                                        <img src="<?= htmlspecialchars($cs['comprobante_calle']) ?>" style="width:28px; height:28px; border-radius:3px; object-fit:cover; cursor:pointer; border:1px solid #E5E7EB;" onclick="openComprobanteModal(this)" data-url="<?= htmlspecialchars($cs['comprobante_calle']) ?>">
                                    <?php elseif(!empty($cs['comprobante_calle'])): ?>
                                        <a href="#" data-url="<?= htmlspecialchars($cs['comprobante_calle']) ?>" onclick="return openComprobanteModal(this)" style="color:#2563EB; font-size:10px;">📎</a>
                                    <?php else: ?>
                                        <span style="color:#D1D5DB;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:5px 8px; text-align:right; font-weight:700; color:<?= $pagado?'#059669':($exenta?'#6D28D9':($suspendida?'#9CA3AF':'#DC2626')) ?>;">
                                    <?php if($suspendida): ?>—<?php elseif($exenta): ?>Exenta<?php else: ?>S/ <?= number_format($cs['monto'] ?? 0,2) ?><?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ACCIONES -->
    <div style="display:flex; gap:12px; flex-wrap:wrap; padding-top:12px; border-top:1px solid #E5E7EB;">
        <form method="POST" onsubmit="return confirm('¿APROBAR el lote de <?= htmlspecialchars($lb['barrio_nombre']) ?>? Se emitirá el recibo de finiquito.')">
            <input type="hidden" name="form_type" value="aprobar_lote_barrio">
            <input type="hidden" name="lote_id" value="<?= $lb['id'] ?>">
            <button type="submit" style="background:#10B981; color:white; border:none; padding:10px 24px; border-radius:6px; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px;">
                ✅ Aprobar y Emitir Recibo
            </button>
        </form>

        <button onclick="toggleRechazo(<?= $lb['id'] ?>)" style="background:white; color:#DC2626; border:2px solid #DC2626; padding:10px 24px; border-radius:6px; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px;">
            ❌ Rechazar
        </button>
    </div>

    <div id="rechazo-<?= $lb['id'] ?>" style="display:none; margin-top:12px;">
        <form method="POST" style="display:flex; gap:8px; flex-wrap:wrap;">
            <input type="hidden" name="form_type" value="rechazar_lote_barrio">
            <input type="hidden" name="lote_id" value="<?= $lb['id'] ?>">
            <input type="text" name="motivo_rechazo" placeholder="Motivo del rechazo (obligatorio)" required
                style="flex:1; min-width:250px; padding:8px 12px; border:2px solid #FCA5A5; border-radius:6px; font-size:12px;">
            <button type="submit" style="background:#DC2626; color:white; border:none; padding:8px 20px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                Confirmar Rechazo
            </button>
        </form>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- HISTORIAL -->
<?php if(!empty($historial)): ?>
<div class="card" style="margin-top:20px;">
    <h3 style="margin-top:0; font-size:15px;">📄 Historial de Lotes Revisados</h3>
    <div class="table-wrap">
        <table style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr style="background:#F9FAFB; text-align:left;">
                    <th style="padding:10px;">Barrio</th>
                    <th style="padding:10px;">Periodo</th>
                    <th style="padding:10px;">Recolectado</th>
                    <th style="padding:10px;">Estado</th>
                    <th style="padding:10px;">Recibo</th>
                    <th style="padding:10px;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($historial as $h): ?>
                <tr style="border-bottom:1px solid #F3F4F6;">
                    <td style="padding:10px; font-weight:600;"><?= htmlspecialchars($h['barrio_nombre']) ?></td>
                    <td style="padding:10px;"><?= $meses_nombres[$h['periodo_mes']] ?> <?= $h['periodo_anio'] ?></td>
                    <td style="padding:10px; font-weight:700; color:#059669;">S/ <?= number_format($h['monto_total_recolectado'],2) ?></td>
                    <td style="padding:10px;">
                        <?php if($h['estado']==='Aprobado'): ?>
                            <span style="background:#D1FAE5; color:#065F46; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:600;">✅ Aprobado</span>
                        <?php else: ?>
                            <span style="background:#FEE2E2; color:#991B1B; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:600;">❌ Rechazado</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px;">
                        <?php if($h['numero_recibo']): ?>
                            <span style="background:#EFF6FF; color:#1E40AF; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:600;">📄 <?= htmlspecialchars($h['numero_recibo']) ?></span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td style="padding:10px; color:#6B7280; font-size:11px;">
                        <?= $h['fecha_aprobacion'] ? date('d/m/Y H:i', strtotime($h['fecha_aprobacion'])) : '—' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- MODAL COMPROBANTE -->
<div id="modal-comprobante" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000; justify-content:center; align-items:center;" onclick="if(event.target===this)closeComprobanteModal()">
    <div style="background:white; border-radius:12px; padding:20px; max-width:90%; max-height:90vh; position:relative;">
        <button onclick="closeComprobanteModal()" style="position:absolute; top:8px; right:12px; background:none; border:none; font-size:20px; cursor:pointer; color:#6B7280;">✕</button>
        <div id="comprobante-content" style="text-align:center;"></div>
    </div>
</div>

<style>
@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
</style>

<script>
function toggleLote(id) {
    var el = document.getElementById('lote-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function toggleCasas(id) {
    var el = document.getElementById('casas-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function toggleRechazo(id) {
    var el = document.getElementById('rechazo-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
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
