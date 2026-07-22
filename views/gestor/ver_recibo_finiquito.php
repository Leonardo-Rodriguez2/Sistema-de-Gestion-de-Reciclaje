<?php
// views/gestor/ver_recibo_finiquito.php — Recibo de Finiquito (dentro del dashboard)
global $pdo, $user;
use app\models\mainModel;

if (empty($pdo)) $pdo = (new mainModel())->conectar();

$lote_id = (int)($_GET['lote_id'] ?? 0);
if (!$lote_id) { echo "<div class='alert alert-error'>ID de lote requerido.</div>"; return; }

// Datos del lote y recibo
$stmt = $pdo->prepare(
    "SELECT lb.*, b.nombre as barrio_nombre, b.ciudad,
            rf.numero_recibo, rf.fecha_emision as fecha_recibo, rf.monto_aprobado,
            ug.nombre as gestor_nombre, ug.apellido as gestor_apellido,
            ub.nombre as encargado_nombre, ub.apellido as encargado_apellido
     FROM lotes_barrio lb
     JOIN barrios b ON lb.barrio_id = b.id
     LEFT JOIN recibos_finiquito rf ON rf.lote_barrio_id = lb.id
     LEFT JOIN usuarios ug ON lb.gestor_id = ug.id
     LEFT JOIN usuarios ub ON lb.encargado_barrio_id = ub.id
     WHERE lb.id = ?"
);
$stmt->execute([$lote_id]);
$recibo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recibo) { echo "<div class='alert alert-error'>Lote no encontrado.</div>"; return; }

// Calles incluidas
$callesStmt = $pdo->prepare(
    "SELECT lc.*, c.nombre as calle_nombre, u.nombre as enc_n, u.apellido as enc_ap
     FROM lotes_calle lc JOIN calles c ON lc.calle_id=c.id JOIN usuarios u ON lc.encargado_calle_id=u.id
     WHERE lc.lote_barrio_id=?"
);
$callesStmt->execute([$lote_id]);
$calles = $callesStmt->fetchAll(PDO::FETCH_ASSOC);

$meses_nombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

$title = 'Recibo de Finiquito — ' . ($recibo['numero_recibo'] ?? '');
$extra_css = '
@media print {
  body { background:#fff !important; }
  .sidebar, .header, .notif-wrap, .menu-toggle, .sidebar-overlay, .no-print { display:none !important; }
  .main { margin-left:0 !important; padding:0 !important; }
  .recibo { box-shadow:none !important; border-radius:0 !important; }
}
';
ob_start();
?>
<div class="no-print" style="text-align:center; margin-bottom:20px;">
    <button class="btn-primary" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
    <a href="router.php?page=dashboard" style="margin-left:15px; color:#6B7280; font-size:13px;">← Volver</a>
</div>

<div class="recibo" style="max-width:750px; margin:0 auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,0.12);">
    <!-- HEADER -->
    <div style="background:linear-gradient(135deg,#111827 0%,#1F2937 60%,#065F46 100%); color:#fff; padding:35px 40px; position:relative;">
        <div style="position:absolute; right:30px; top:30px; width:80px; height:80px; border:3px solid rgba(16,185,129,0.7); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:28px;">✅</div>
        <div style="font-size:11px; opacity:0.7; margin-bottom:8px;">SISTEMA DE GESTIÓN DE RECICLAJE — EPSIC / EcoCusco</div>
        <h1 style="margin:0; font-size:28px; font-weight:800; letter-spacing:1px;">RECIBO DE FINIQUITO</h1>
        <div style="font-size:13px; opacity:0.8; margin-top:4px;">N° <?= htmlspecialchars($recibo['numero_recibo'] ?? 'SIN NÚMERO') ?></div>
    </div>

    <div style="padding:35px 40px;">
        <!-- DATOS PRINCIPALES -->
        <div style="margin-bottom:24px;">
            <h3 style="font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:1px; margin:0 0 12px 0; border-bottom:1px solid #E5E7EB; padding-bottom:6px;">Información del Periodo</h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div style="background:#F9FAFB; padding:12px 14px; border-radius:8px;">
                    <label style="font-size:10px; color:#9CA3AF; font-weight:600; display:block; margin-bottom:3px; text-transform:uppercase;">Barrio</label>
                    <span style="font-size:14px; font-weight:700; color:#111827;"><?= htmlspecialchars($recibo['barrio_nombre']) ?></span>
                </div>
                <div style="background:#F9FAFB; padding:12px 14px; border-radius:8px;">
                    <label style="font-size:10px; color:#9CA3AF; font-weight:600; display:block; margin-bottom:3px; text-transform:uppercase;">Ciudad</label>
                    <span style="font-size:14px; font-weight:700; color:#111827;"><?= htmlspecialchars($recibo['ciudad'] ?? 'Cusco') ?></span>
                </div>
                <div style="background:#F9FAFB; padding:12px 14px; border-radius:8px;">
                    <label style="font-size:10px; color:#9CA3AF; font-weight:600; display:block; margin-bottom:3px; text-transform:uppercase;">Periodo de Facturación</label>
                    <span style="font-size:14px; font-weight:700; color:#111827;"><?= $meses_nombres[$recibo['periodo_mes']] ?> <?= $recibo['periodo_anio'] ?></span>
                </div>
                <div style="background:#F9FAFB; padding:12px 14px; border-radius:8px;">
                    <label style="font-size:10px; color:#9CA3AF; font-weight:600; display:block; margin-bottom:3px; text-transform:uppercase;">Fecha de Emisión</label>
                    <span style="font-size:14px; font-weight:700; color:#111827;"><?= $recibo['fecha_recibo'] ? date('d/m/Y H:i', strtotime($recibo['fecha_recibo'])) : date('d/m/Y') ?></span>
                </div>
            </div>
        </div>

        <!-- RESPONSABLES -->
        <div style="margin-bottom:24px;">
            <h3 style="font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:1px; margin:0 0 12px 0; border-bottom:1px solid #E5E7EB; padding-bottom:6px;">Responsables</h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div style="background:#F9FAFB; padding:12px 14px; border-radius:8px;">
                    <label style="font-size:10px; color:#9CA3AF; font-weight:600; display:block; margin-bottom:3px; text-transform:uppercase;">Encargado de Barrio</label>
                    <span style="font-size:14px; font-weight:700; color:#111827;"><?= htmlspecialchars($recibo['encargado_nombre'] . ' ' . $recibo['encargado_apellido']) ?></span>
                </div>
                <div style="background:#F9FAFB; padding:12px 14px; border-radius:8px;">
                    <label style="font-size:10px; color:#9CA3AF; font-weight:600; display:block; margin-bottom:3px; text-transform:uppercase;">Aprobado por (Gestor/Admin)</label>
                    <span style="font-size:14px; font-weight:700; color:#111827;"><?= htmlspecialchars(($recibo['gestor_nombre'] ?? '') . ' ' . ($recibo['gestor_apellido'] ?? '')) ?></span>
                </div>
            </div>
        </div>

        <!-- MONTO TOTAL -->
        <div style="text-align:center; padding:28px; background:linear-gradient(135deg,#ECFDF5,#D1FAE5); border-radius:10px; border:2px solid #6EE7B7; margin:20px 0;">
            <div style="font-size:13px; color:#065F46; margin-bottom:6px;">MONTO TOTAL APROBADO Y RECIBIDO</div>
            <div style="font-size:42px; font-weight:800; color:#065F46;">S/ <?= number_format($recibo['monto_aprobado'] ?? $recibo['monto_total_recolectado'], 2) ?></div>
            <div style="font-size:12px; color:#065F46; margin-top:8px; opacity:0.8;">
                Esperado: S/ <?= number_format($recibo['monto_total_esperado'],2) ?> •
                <?php $dif = $recibo['monto_total_esperado'] - ($recibo['monto_aprobado'] ?? $recibo['monto_total_recolectado']); ?>
                <?php if($dif > 0): ?>
                <span style="color:#D97706;">Diferencia no cobrada: S/ <?= number_format($dif,2) ?></span>
                <?php else: ?>
                <span>Cobro completo ✅</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- COMPROBANTE GLOBAL (captura de transferencia del barrio) -->
        <?php if(!empty($recibo['comprobante_lote'])): ?>
        <div style="margin-bottom:24px; padding:16px; background:#E0F2FE; border-radius:10px; border-left:4px solid #0369A1;">
            <h3 style="font-size:11px; font-weight:700; color:#0369A1; text-transform:uppercase; letter-spacing:1px; margin:0 0 8px 0;">📎 Comprobante Global del Barrio (captura de transferencia)</h3>
            <a href="#" data-url="<?= htmlspecialchars($recibo['comprobante_lote']) ?>" onclick="return openComprobanteModal(this)"
               style="display:inline-block; padding:8px 16px; background:#0369A1; color:white; border-radius:6px; text-decoration:none; font-size:12px; font-weight:600;">
                📷 Ver Imagen de Transferencia
            </a>
        </div>
        <?php endif; ?>

        <!-- DETALLE POR CALLES -->
        <div style="margin-bottom:24px;">
            <h3 style="font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:1px; margin:0 0 12px 0; border-bottom:1px solid #E5E7EB; padding-bottom:6px;">Detalle por Calles (<?= count($calles) ?> calles incluidas)</h3>
            <div class="table-wrap">
            <table style="width:100%; border-collapse:collapse; font-size:12px;">
                <thead>
                    <tr>
                        <th style="padding:8px 10px; background:#F3F4F6; text-align:left; font-size:10px; color:#6B7280; text-transform:uppercase;">Calle</th>
                        <th style="padding:8px 10px; background:#F3F4F6; text-align:left; font-size:10px; color:#6B7280; text-transform:uppercase;">Encargado</th>
                        <th style="padding:8px 10px; background:#F3F4F6; text-align:left; font-size:10px; color:#6B7280; text-transform:uppercase;">Casas</th>
                        <th style="padding:8px 10px; background:#F3F4F6; text-align:left; font-size:10px; color:#6B7280; text-transform:uppercase;">Pagadas</th>
                        <th style="padding:8px 10px; background:#F3F4F6; text-align:left; font-size:10px; color:#6B7280; text-transform:uppercase;">Morosas</th>
                        <th style="padding:8px 10px; background:#F3F4F6; text-align:left; font-size:10px; color:#6B7280; text-transform:uppercase;">Recolectado</th>
                        <th style="padding:8px 10px; background:#F3F4F6; text-align:left; font-size:10px; color:#6B7280; text-transform:uppercase;">Certificado</th>
                        <th style="padding:8px 10px; background:#F3F4F6; text-align:left; font-size:10px; color:#6B7280; text-transform:uppercase;">Comprobante</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($calles as $c): ?>
                    <?php
                        $casasStmt = $pdo->prepare(
                            "SELECT c.monto, c.comprobante_calle, c.referencia_pago, v.numero_casa, v.propietario
                             FROM cobros c JOIN viviendas v ON c.vivienda_id = v.id
                             WHERE c.lote_calle_id = ? AND c.estado = 'Pagado'
                             ORDER BY v.numero_casa"
                        );
                        $casasStmt->execute([$c['id']]);
                        $casas_pagadas_list = $casasStmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <tr style="<?= $c['casas_morosas']>0?'background:#FFFBEB;':'' ?>">
                        <td style="padding:8px 10px; border-bottom:1px solid #F9FAFB;"><strong><?= htmlspecialchars($c['calle_nombre']) ?></strong></td>
                        <td style="padding:8px 10px; border-bottom:1px solid #F9FAFB;"><?= htmlspecialchars($c['enc_n'] . ' ' . $c['enc_ap']) ?></td>
                        <td style="padding:8px 10px; border-bottom:1px solid #F9FAFB; text-align:center;"><?= $c['total_casas'] ?></td>
                        <td style="padding:8px 10px; border-bottom:1px solid #F9FAFB; text-align:center; color:#059669; font-weight:700;"><?= $c['casas_pagadas'] ?></td>
                        <td style="padding:8px 10px; border-bottom:1px solid #F9FAFB; text-align:center; color:<?= $c['casas_morosas']>0?'#DC2626':'#9CA3AF' ?>; font-weight:700;"><?= $c['casas_morosas'] ?></td>
                        <td style="padding:8px 10px; border-bottom:1px solid #F9FAFB; font-weight:800; color:#059669;">S/ <?= number_format($c['monto_recolectado'],2) ?></td>
                        <td style="padding:8px 10px; border-bottom:1px solid #F9FAFB; text-align:center;">
                            <?php if ($c['certificado_generado']): ?>
                                <a href="router.php?page=certificado_calle&lote_calle_id=<?= $c['id'] ?>" target="_blank" style="display:inline-block; padding:4px 10px; background:#065F46; color:#fff; border-radius:6px; font-size:11px; font-weight:600; text-decoration:none;">📄 Ver</a>
                            <?php else: ?>
                                <span style="color:#9CA3AF; font-size:11px;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:8px 10px; border-bottom:1px solid #F9FAFB; text-align:center;">
                            <?php if(!empty($c['comprobante_lote'])): ?>
                                <a href="#" data-url="<?= htmlspecialchars($c['comprobante_lote']) ?>" onclick="return openComprobanteModal(this)"
                                   style="color:#0369A1; text-decoration:none; font-size:12px;">📎 Ver</a>
                            <?php else: ?>
                                <span style="color:#9CA3AF; font-size:11px;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if(!empty($casas_pagadas_list)): ?>
                    <tr style="background:#F9FAFB;">
                        <td colspan="8" style="padding:4px 10px 10px 30px;">
                            <button onclick="toggleCasasRecibo(<?= $c['id'] ?>)" style="background:none; border:1px solid #E5E7EB; border-radius:4px; padding:3px 8px; font-size:10px; cursor:pointer; color:#6B7280;">
                                👁️ Ver <?= count($casas_pagadas_list) ?> casa(s) pagada(s)
                            </button>
                            <div id="casas-recibo-<?= $c['id'] ?>" style="display:none; margin-top:8px;">
                                <table style="width:100%; border-collapse:collapse; font-size:11px; background:white; border:1px solid #E5E7EB; border-radius:4px; overflow:hidden;">
                                    <thead style="background:#F3F4F6;">
                                        <tr>
                                            <th style="padding:4px 6px;">Casa</th>
                                            <th style="padding:4px 6px;">Propietario</th>
                                            <th style="padding:4px 6px;">Monto</th>
                                            <th style="padding:4px 6px;">Referencia</th>
                                            <th style="padding:4px 6px;">Comprobante</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($casas_pagadas_list as $cs): ?>
                                        <tr style="border-top:1px solid #F3F4F6;">
                                            <td style="padding:4px 6px;">#<?= htmlspecialchars($cs['numero_casa']) ?></td>
                                            <td style="padding:4px 6px;"><?= htmlspecialchars($cs['propietario']) ?></td>
                                            <td style="padding:4px 6px; color:#059669; font-weight:700;">S/ <?= number_format($cs['monto'],2) ?></td>
                                            <td style="padding:4px 6px; text-align:center;">
                                                <?php if(!empty($cs['comprobante_calle'])): ?>
                                                    <a href="#" data-url="<?= htmlspecialchars($cs['comprobante_calle']) ?>" onclick="return openComprobanteModal(this)"
                                                       style="color:#0369A1; text-decoration:none;">📎 Ver</a>
                                                <?php else: ?>
                                                    <span style="color:#9CA3AF;">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <tr style="border-top:2px solid #E5E7EB; background:#F3F4F6;">
                        <td colspan="7" style="padding:10px; font-weight:700;">TOTAL</td>
                        <td style="padding:10px; font-weight:800; color:#065F46; font-size:15px;">S/ <?= number_format(array_sum(array_column($calles,'monto_recolectado')),2) ?></td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <!-- FIRMA -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:40px; margin-top:40px;">
            <div style="text-align:center; padding-top:40px; border-top:1px solid #E5E7EB;">
                <div style="font-size:12px; color:#6B7280;">Encargado de Barrio</div>
                <div style="font-size:13px; font-weight:700; margin-top:4px;">
                    <?= htmlspecialchars($recibo['encargado_nombre'] . ' ' . $recibo['encargado_apellido']) ?>
                </div>
            </div>
            <div style="text-align:center; padding-top:40px; border-top:1px solid #E5E7EB;">
                <div style="font-size:12px; color:#6B7280;">Gestor / Administrador</div>
                <div style="font-size:13px; font-weight:700; margin-top:4px;">
                    <?= htmlspecialchars(($recibo['gestor_nombre'] ?? '') . ' ' . ($recibo['gestor_apellido'] ?? '')) ?>
                </div>
            </div>
        </div>
    </div>

    <div style="background:#F9FAFB; padding:20px 40px; text-align:center; font-size:11px; color:#9CA3AF; border-top:1px solid #E5E7EB;">
        Este documento es el comprobante oficial de finiquito del ciclo de facturación.<br>
        Generado automáticamente por el Sistema EcoCusco — <?= date('d/m/Y H:i') ?>
    </div>
</div>
<script>
function toggleCasasRecibo(id) {
    const el = document.getElementById('casas-recibo-' + id);
    if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
