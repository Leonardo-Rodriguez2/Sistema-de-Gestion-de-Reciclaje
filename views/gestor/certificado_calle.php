<?php
// views/gestor/certificado_calle.php — Certificado de Verificación por Calle (dentro del dashboard)
global $pdo, $user;
use app\models\mainModel;

if (empty($pdo)) $pdo = (new mainModel())->conectar();

$lote_calle_id = (int)($_GET['lote_calle_id'] ?? 0);
if (!$lote_calle_id) { echo "<div class='alert alert-error'>ID de lote de calle requerido.</div>"; return; }

// Datos del lote de calle
$stmt = $pdo->prepare(
    "SELECT lc.*, c.nombre as calle_nombre, b.nombre as barrio_nombre, b.ciudad,
            ue.nombre as enc_nombre, ue.apellido as enc_apellido,
            (SELECT d.dni FROM detalles_encargado_calle d WHERE d.calle_id=c.id LIMIT 1) as enc_dni,
            ug.nombre as gestor_nombre, ug.apellido as gestor_apellido,
            lb.estado as barrio_estado, lb.gestor_id,
            rf.numero_recibo
     FROM lotes_calle lc
     JOIN calles c ON lc.calle_id = c.id
     JOIN barrios b ON lc.barrio_id = b.id
     LEFT JOIN usuarios ue ON lc.encargado_calle_id = ue.id
     LEFT JOIN lotes_barrio lb ON lc.lote_barrio_id = lb.id
     LEFT JOIN usuarios ug ON lb.gestor_id = ug.id
     LEFT JOIN recibos_finiquito rf ON rf.lote_barrio_id = lc.lote_barrio_id
     WHERE lc.id = ?"
);
$stmt->execute([$lote_calle_id]);
$lote = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lote) { echo "<div class='alert alert-error'>Certificado no encontrado.</div>"; return; }
if (!$lote['certificado_generado']) {
    echo "<div class='alert alert-warning'>Este lote aún no tiene certificado. Debe ser aprobado por el gestor primero.</div>";
    return;
}

// Viviendas del lote
$viviendasStmt = $pdo->prepare(
    "SELECT v.id, v.numero_casa as vivienda_nombre, v.direccion, v.estado_servicio,
            v.propietario,
            c.id as cobro_id, c.monto, c.estado as cobro_estado,
            c.estado_verificacion, c.referencia_pago, c.comprobante_calle,
            c.fecha_emision
     FROM viviendas v
     LEFT JOIN cobros c ON c.vivienda_id = v.id
        AND c.mes = ? AND c.anio = ? AND c.lote_calle_id = ?
     WHERE v.calle_id = ? AND v.estado_servicio != 'Anulado'
     ORDER BY v.direccion ASC"
);
$viviendasStmt->execute([$lote['periodo_mes'], $lote['periodo_anio'], $lote_calle_id, $lote['calle_id']]);
$viviendas = $viviendasStmt->fetchAll(PDO::FETCH_ASSOC);

$num_certificado = sprintf(
    'CERT-%04d%02d-%03d-%04d',
    $lote['periodo_anio'], $lote['periodo_mes'],
    $lote['calle_id'], rand(1000, 9999)
);

$meses_nombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

$title = 'Certificado — ' . htmlspecialchars($lote['calle_nombre']);
$extra_css = '
@media print {
  body { background:#fff !important; }
  .sidebar, .header, .notif-wrap, .menu-toggle, .sidebar-overlay, .no-print { display:none !important; }
  .main { margin-left:0 !important; padding:0 !important; }
  .cert { box-shadow:none !important; border-radius:0 !important; }
  .cert .header { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
  .marco-verde { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
  table.viviendas th { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
}
';
ob_start();
?>
<div class="no-print" style="text-align:center; margin-bottom:20px;">
    <button class="btn-primary" onclick="window.print()">🖨️ Descargar PDF / Imprimir</button>
    <a href="javascript:history.back()" style="margin-left:15px; color:#6B7280; font-size:13px;">← Volver</a>
</div>

<div class="cert" style="max-width:850px; margin:0 auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,0.12); font-family:'Poppins',sans-serif;">
    <!-- HEADER -->
    <div class="header" style="background:linear-gradient(135deg,#065F46 0%,#047857 50%,#111827 100%); color:#fff; padding:35px 40px; position:relative;">
        <div style="font-size:11px; opacity:0.7; margin-bottom:8px;">SISTEMA DE GESTIÓN DE RECICLAJE — EPSIC / EcoCusco</div>
        <h1 style="margin:0; font-size:26px; font-weight:800; letter-spacing:1px;">CERTIFICADO DE VERIFICACIÓN</h1>
        <div style="font-size:12px; opacity:0.8; margin-top:6px;">Documento que certifica la correcta recaudación del servicio de reciclaje</div>
        <div class="numero" style="position:absolute; right:30px; top:30px; text-align:right;">
            <div style="font-size:10px; opacity:0.6;">N° CERTIFICADO</div>
            <div style="font-size:16px; font-weight:700;"><?= htmlspecialchars($num_certificado) ?></div>
        </div>
        <div style="position:absolute; right:30px; top:70px; width:70px; height:70px; border:3px solid rgba(255,255,255,0.3); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px;">✅</div>
    </div>

    <div style="padding:35px 40px;">
        <!-- DATOS GENERALES -->
        <div style="margin-bottom:28px;">
            <h3 style="font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:1px; margin:0 0 12px 0; border-bottom:2px solid #065F46; padding-bottom:6px;">Datos del Periodo y Ubicación</h3>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                <div style="background:#F9FAFB; padding:12px 14px; border-radius:8px;">
                    <label style="font-size:10px; color:#9CA3AF; font-weight:600; display:block; margin-bottom:3px; text-transform:uppercase;">Barrio</label>
                    <span style="font-size:14px; font-weight:700; color:#111827;"><?= htmlspecialchars($lote['barrio_nombre']) ?></span>
                </div>
                <div style="background:#F9FAFB; padding:12px 14px; border-radius:8px;">
                    <label style="font-size:10px; color:#9CA3AF; font-weight:600; display:block; margin-bottom:3px; text-transform:uppercase;">Calle</label>
                    <span style="font-size:14px; font-weight:700; color:#111827;"><?= htmlspecialchars($lote['calle_nombre']) ?></span>
                </div>
                <div style="background:#F9FAFB; padding:12px 14px; border-radius:8px;">
                    <label style="font-size:10px; color:#9CA3AF; font-weight:600; display:block; margin-bottom:3px; text-transform:uppercase;">Ciudad</label>
                    <span style="font-size:14px; font-weight:700; color:#111827;"><?= htmlspecialchars($lote['ciudad'] ?? 'Cusco') ?></span>
                </div>
                <div style="background:#F9FAFB; padding:12px 14px; border-radius:8px;">
                    <label style="font-size:10px; color:#9CA3AF; font-weight:600; display:block; margin-bottom:3px; text-transform:uppercase;">Periodo de Facturación</label>
                    <span style="font-size:14px; font-weight:700; color:#111827;"><?= $meses_nombres[$lote['periodo_mes']] ?> <?= $lote['periodo_anio'] ?></span>
                </div>
                <div style="background:#F9FAFB; padding:12px 14px; border-radius:8px;">
                    <label style="font-size:10px; color:#9CA3AF; font-weight:600; display:block; margin-bottom:3px; text-transform:uppercase;">Fecha de Emisión</label>
                    <span style="font-size:14px; font-weight:700; color:#111827;"><?= $lote['fecha_certificado'] ? date('d/m/Y H:i', strtotime($lote['fecha_certificado'])) : date('d/m/Y H:i') ?></span>
                </div>
                <div style="background:#F9FAFB; padding:12px 14px; border-radius:8px;">
                    <label style="font-size:10px; color:#9CA3AF; font-weight:600; display:block; margin-bottom:3px; text-transform:uppercase;">Recibo de Finiquito</label>
                    <span style="font-size:14px; font-weight:700; color:#111827;"><?= htmlspecialchars($lote['numero_recibo'] ?? '—') ?></span>
                </div>
            </div>
        </div>

        <!-- RESPONSABLES -->
        <div style="margin-bottom:28px;">
            <h3 style="font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:1px; margin:0 0 12px 0; border-bottom:2px solid #065F46; padding-bottom:6px;">Responsables</h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div style="background:#F9FAFB; padding:12px 14px; border-radius:8px;">
                    <label style="font-size:10px; color:#9CA3AF; font-weight:600; display:block; margin-bottom:3px; text-transform:uppercase;">Encargado de Calle</label>
                    <span style="font-size:14px; font-weight:700; color:#111827;"><?= htmlspecialchars(trim(($lote['enc_nombre'] ?? '') . ' ' . ($lote['enc_apellido'] ?? ''))) ?></span>
                    <div style="font-size:11px; color:#9CA3AF;">DNI: <?= htmlspecialchars($lote['enc_dni'] ?? '—') ?></div>
                </div>
                <div style="background:#F9FAFB; padding:12px 14px; border-radius:8px;">
                    <label style="font-size:10px; color:#9CA3AF; font-weight:600; display:block; margin-bottom:3px; text-transform:uppercase;">Verificado por (Gestor/Admin)</label>
                    <span style="font-size:14px; font-weight:700; color:#111827;"><?= htmlspecialchars(trim(($lote['gestor_nombre'] ?? '') . ' ' . ($lote['gestor_apellido'] ?? ''))) ?></span>
                </div>
            </div>
        </div>

        <!-- RESUMEN DE RECAUDACIÓN -->
        <div style="margin-bottom:28px;">
            <h3 style="font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:1px; margin:0 0 12px 0; border-bottom:2px solid #065F46; padding-bottom:6px;">Resumen de Recaudación</h3>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin:16px 0;">
                <div style="text-align:center; padding:16px 8px; border-radius:10px; background:#F9FAFB; border:1px solid #E5E7EB;">
                    <div style="font-size:28px; font-weight:800; color:#6B7280;"><?= $lote['total_casas'] ?></div>
                    <div style="font-size:10px; color:#6B7280; margin-top:4px; text-transform:uppercase;">Casas Activas</div>
                </div>
                <div style="text-align:center; padding:16px 8px; border-radius:10px; background:#F9FAFB; border:1px solid #E5E7EB;">
                    <div style="font-size:28px; font-weight:800; color:#059669;"><?= $lote['casas_pagadas'] ?></div>
                    <div style="font-size:10px; color:#6B7280; margin-top:4px; text-transform:uppercase;">Pagadas</div>
                </div>
                <div style="text-align:center; padding:16px 8px; border-radius:10px; background:#F9FAFB; border:1px solid #E5E7EB;">
                    <div style="font-size:28px; font-weight:800; color:<?= $lote['casas_morosas']>0?'#DC2626':'#9CA3AF' ?>;"><?= $lote['casas_morosas'] ?></div>
                    <div style="font-size:10px; color:#6B7280; margin-top:4px; text-transform:uppercase;">Morosas</div>
                </div>
                <div style="text-align:center; padding:16px 8px; border-radius:10px; background:#F9FAFB; border:1px solid #E5E7EB;">
                    <div style="font-size:28px; font-weight:800; color:#065F46;">S/ <?= number_format($lote['monto_recolectado'], 2) ?></div>
                    <div style="font-size:10px; color:#6B7280; margin-top:4px; text-transform:uppercase;">Recolectado</div>
                </div>
            </div>
            <div style="border:2px solid #6EE7B7; border-radius:10px; padding:16px; background:#ECFDF5; margin:16px 0; text-align:center;">
                <div style="font-size:12px; color:#065F46;">MONTO ESPERADO</div>
                <div style="font-size:24px; font-weight:800; color:#065F46;">S/ <?= number_format($lote['monto_esperado'], 2) ?></div>
                <div style="font-size:12px; color:#065F46; margin-top:4px;">
                    Diferencia: S/ <?= number_format($lote['monto_esperado'] - $lote['monto_recolectado'], 2) ?>
                    <?php if ($lote['alerta_deuda']): ?>
                        <span style="color:#D97706;"> ⚠️ Alerta de deuda</span>
                    <?php else: ?>
                        <span style="color:#059669;"> ✅ Sin novedad</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- DETALLE POR VIVIENDA -->
        <div style="margin-bottom:28px;">
            <h3 style="font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:1px; margin:0 0 12px 0; border-bottom:2px solid #065F46; padding-bottom:6px;">Detalle de Viviendas (<?= count($viviendas) ?> registros)</h3>
            <div class="table-wrap">
            <table class="viviendas" style="width:100%; border-collapse:collapse; font-size:11px; margin-top:10px;">
                <thead>
                    <tr>
                        <th style="padding:8px 10px; background:#065F46; color:#fff; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:0.5px;">#</th>
                        <th style="padding:8px 10px; background:#065F46; color:#fff; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:0.5px;">Dirección</th>
                        <th style="padding:8px 10px; background:#065F46; color:#fff; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:0.5px;">Propietario</th>
                        <th style="padding:8px 10px; background:#065F46; color:#fff; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:0.5px;">Servicio</th>
                        <th style="padding:8px 10px; background:#065F46; color:#fff; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:0.5px;">Estado Pago</th>
                        <th style="padding:8px 10px; background:#065F46; color:#fff; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:0.5px;">Verificación</th>
                        <th style="padding:8px 10px; background:#065F46; color:#fff; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:0.5px;">Referencia</th>
                        <th style="padding:8px 10px; background:#065F46; color:#fff; text-align:right; font-size:10px; text-transform:uppercase; letter-spacing:0.5px;">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total_monto = 0; $i = 0; foreach ($viviendas as $v): $i++; ?>
                    <tr style="<?= $v['cobro_estado']==='Pagado'?'background:#ECFDF5;':'' ?>">
                        <td style="padding:7px 10px; border-bottom:1px solid #F3F4F6;"><?= $i ?></td>
                        <td style="padding:7px 10px; border-bottom:1px solid #F3F4F6;"><strong><?= htmlspecialchars($v['direccion']) ?></strong></td>
                        <td style="padding:7px 10px; border-bottom:1px solid #F3F4F6;"><?= htmlspecialchars($v['propietario'] ?? '—') ?></td>
                        <td style="padding:7px 10px; border-bottom:1px solid #F3F4F6;">
                            <?php if ($v['estado_servicio']==='Activo'): ?>
                                <span style="display:inline-block; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; background:#D1FAE5; color:#065F46;">Activo</span>
                            <?php elseif ($v['estado_servicio']==='Suspendido'): ?>
                                <span style="display:inline-block; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; background:#FEF3C7; color:#92400E;">Suspendido</span>
                            <?php else: ?>
                                <span><?= $v['estado_servicio'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:7px 10px; border-bottom:1px solid #F3F4F6;">
                            <?php if ($v['cobro_estado']==='Pagado'): ?>
                                <span style="color:#059669; font-weight:700;">✅ Pagado</span>
                                <?php if ($v['fecha_emision']): ?>
                                    <div style="font-size:9px; color:#9CA3AF;"><?= date('d/m/Y', strtotime($v['fecha_emision'])) ?></div>
                                <?php endif; ?>
                            <?php elseif ($v['cobro_estado']==='Pendiente'): ?>
                                <span style="color:#D97706; font-weight:700;">⏳ Pendiente</span>
                            <?php else: ?>
                                <span style="color:#D97706; font-weight:700;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:7px 10px; border-bottom:1px solid #F3F4F6;">
                            <?php if ($v['estado_verificacion']==='Verificado'): ?>
                                <span style="color:#059669;">✅ Verif.</span>
                            <?php elseif ($v['estado_verificacion']==='Rechazado'): ?>
                                <span style="color:#DC2626;">❌ Rechaz.</span>
                            <?php else: ?>
                                <span style="color:#9CA3AF;">⏳ Pend.</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:7px 10px; border-bottom:1px solid #F3F4F6; font-size:10px; color:#6B7280;"><?= htmlspecialchars($v['referencia_pago'] ?? '—') ?></td>
                        <td style="padding:7px 10px; border-bottom:1px solid #F3F4F6; text-align:right; font-weight:700;">
                            <?php if ($v['monto']): ?>S/ <?= number_format($v['monto'], 2) ?><?php endif; ?>
                        </td>
                    </tr>
                    <?php $total_monto += (float)($v['monto'] ?? 0); endforeach; ?>
                    <tr style="border-top:2px solid #065F46; background:#ECFDF5;">
                        <td colspan="7" style="padding:10px; font-weight:800; text-align:right;">TOTAL RECAUDADO</td>
                        <td style="padding:10px; font-weight:800; color:#065F46; font-size:15px; text-align:right;">S/ <?= number_format($total_monto, 2) ?></td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <!-- DECLARACIÓN -->
        <div style="margin-bottom:28px;">
            <h3 style="font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:1px; margin:0 0 12px 0; border-bottom:2px solid #065F46; padding-bottom:6px;">Declaración de Verificación</h3>
            <div style="background:#F9FAFB; padding:16px 20px; border-radius:10px; border-left:4px solid #065F46; font-size:12px; color:#4B5563; line-height:1.8;">
                El gestor de pagos, en virtud de las facultades conferidas por el Sistema de Gestión de Reciclaje
                EPSIC / EcoCusco, <strong>CERTIFICA</strong> que la recaudación del servicio de reciclaje
                correspondiente a la calle <strong><?= htmlspecialchars($lote['calle_nombre']) ?></strong> del barrio
                <strong><?= htmlspecialchars($lote['barrio_nombre']) ?></strong>, durante el periodo de
                <strong><?= $meses_nombres[$lote['periodo_mes']] ?> de <?= $lote['periodo_anio'] ?></strong>,
                ha sido debidamente verificada y aprobada, encontrándose conforme a los registros del sistema.
                <br><br>
                Se deja constancia que <strong><?= $lote['casas_pagadas'] ?> de <?= $lote['total_casas'] ?></strong>
                viviendas cumplieron con el pago oportuno, y
                <?php if ($lote['casas_morosas'] > 0): ?>
                    <strong><?= $lote['casas_morosas'] ?></strong> viviendas fueron registradas como morosas,
                    procediéndose a la suspensión temporal del servicio según el reglamento interno.
                <?php else: ?>
                    todas las viviendas cumplieron con el pago en el periodo establecido.
                <?php endif; ?>
            </div>
        </div>

        <!-- FIRMAS -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:40px; margin-top:50px;">
            <div style="text-align:center; padding-top:40px; border-top:2px solid #E5E7EB;">
                <div style="font-size:11px; color:#6B7280;">Encargado de Calle</div>
                <div style="font-size:13px; font-weight:700; margin-top:4px;"><?= htmlspecialchars(trim(($lote['enc_nombre'] ?? '') . ' ' . ($lote['enc_apellido'] ?? ''))) ?></div>
                <div style="font-size:10px; color:#9CA3AF;">DNI: <?= htmlspecialchars($lote['enc_dni'] ?? '—') ?></div>
            </div>
            <div style="text-align:center; padding-top:40px; border-top:2px solid #E5E7EB;">
                <div style="font-size:11px; color:#6B7280;">Gestor / Administrador</div>
                <div style="font-size:13px; font-weight:700; margin-top:4px;"><?= htmlspecialchars(trim(($lote['gestor_nombre'] ?? '') . ' ' . ($lote['gestor_apellido'] ?? ''))) ?></div>
            </div>
        </div>
    </div>

    <div style="background:#F9FAFB; padding:20px 40px; text-align:center; font-size:10px; color:#9CA3AF; border-top:1px solid #E5E7EB;">
        Este documento es el certificado oficial de verificación de recaudación por calle.<br>
        Generado automáticamente por el Sistema EcoCusco — Emitido el <?= date('d/m/Y H:i') ?>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
