<?php
// views/barrio/reportar_pago.php — Módulo de Verificación de Lotes de Calle
global $pdo;
require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../app/helpers.php';
use app\models\mainModel;

if (empty($pdo)) $pdo = (new mainModel())->conectar();

$user = check_dashboard_access([5]);
$page = 'reportar_pago';

// Periodo
$mes  = (int)($_GET['mes']  ?? date('n'));
$anio = (int)($_GET['anio'] ?? date('Y'));

// 1. Obtener Barrio (Protegido)
$barrio_id = 0;
$barrio = ['nombre' => 'Sin asignar'];
try {
    $barrioStmt = $pdo->prepare("SELECT b.* FROM detalles_encargado_barrio d JOIN barrios b ON d.barrio_id = b.id WHERE d.usuario_id = ?");
    $barrioStmt->execute([$user['id']]);
    if ($res = $barrioStmt->fetch(PDO::FETCH_ASSOC)) {
        $barrio = $res;
        $barrio_id = $barrio['id'];
    }
} catch (\Throwable $e) {}

// 2. Lote de Barrio del periodo (Protegido)
$loteBarrio = false;
try {
    $loteBarrioStmt = $pdo->prepare("SELECT lb.*, u.nombre as encargado_nombre FROM lotes_barrio lb LEFT JOIN usuarios u ON lb.encargado_barrio_id = u.id WHERE lb.barrio_id=? AND lb.mes=? AND lb.anio=?");
    $loteBarrioStmt->execute([$barrio_id, $mes, $anio]);
    $loteBarrio = $loteBarrioStmt->fetch(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {}

// 3. Lotes de Calle del periodo en el barrio (Protegido)
$lotes_calle = [];
try {
    $lotesCalle = $pdo->prepare(
        "SELECT lc.*, c.nombre as calle_nombre, u.nombre as encargado_nombre, u.apellido as encargado_apellido,
                (SELECT COUNT(*) FROM viviendas v WHERE v.calle_id = lc.calle_id AND v.estado_servicio = 'Activo' 
                 AND v.id NOT IN (SELECT vivienda_id FROM solicitudes_vivienda WHERE tipo='Baja' AND estado='Aprobado')) as total_viviendas,
                (SELECT COUNT(*) FROM viviendas v WHERE v.calle_id = lc.calle_id AND v.estado_servicio = 'Activo' AND v.exento_cobro = 1
                 AND v.id NOT IN (SELECT vivienda_id FROM solicitudes_vivienda WHERE tipo='Baja' AND estado='Aprobado')) as viviendas_exentas
         FROM lotes_calle lc
         JOIN calles c ON lc.calle_id = c.id
         JOIN usuarios u ON lc.encargado_calle_id = u.id
         WHERE lc.barrio_id=? AND lc.mes=? AND lc.anio=?
         ORDER BY lc.fecha_envio DESC, lc.id DESC"
    );
    $lotesCalle->execute([$barrio_id, $mes, $anio]);
    $lotes_calle = $lotesCalle->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {}

// 4. Config tarifa (Protegido)
$config = ['cuota_mensual' => 0.00, 'multa_renovacion' => 0.00];
try {
    $cuotaStmt = $pdo->prepare("SELECT cuota_mensual, multa_renovacion FROM configuraciones_barrio WHERE barrio_id=?");
    $cuotaStmt->execute([$barrio_id]);
    if ($cfg = $cuotaStmt->fetch(PDO::FETCH_ASSOC)) {
        $config = $cfg;
    }
} catch (\Throwable $e) {}

// 5. Totals del barrio (Protegido)
$viviendas_activas = 0;
$casas_pagadas_total = 0;
$casas_morosas_total = 0;
$monto_reportado_total = 0;
$monto_esperado_lotes = 0;
$exentas_count = 0;
try {
    $vActStmt = $pdo->prepare("SELECT COUNT(*) FROM viviendas WHERE barrio_id = ? AND estado_servicio = 'Activo'");
    $vActStmt->execute([$barrio_id]);
    $viviendas_activas = (int)$vActStmt->fetchColumn();

    $exentasStmt = $pdo->prepare("SELECT COUNT(*) FROM viviendas WHERE barrio_id = ? AND estado_servicio = 'Activo' AND exento_cobro = 1");
    $exentasStmt->execute([$barrio_id]);
    $exentas_count = (int)$exentasStmt->fetchColumn();

    $sumStmt = $pdo->prepare(
        "SELECT COALESCE(SUM(casas_pagadas),0), COALESCE(SUM(casas_morosas),0),
                COALESCE(SUM(monto_recolectado),0), COALESCE(SUM(monto_esperado),0)
         FROM lotes_calle WHERE barrio_id = ? AND mes = ? AND anio = ?"
    );
    $sumStmt->execute([$barrio_id, $mes, $anio]);
    $row = $sumStmt->fetch(PDO::FETCH_NUM);
    $casas_pagadas_total = (int)$row[0];
    $casas_morosas_total = (int)$row[1];
    $monto_reportado_total = (float)$row[2];
    $monto_esperado_lotes = (float)$row[3];
} catch (\Throwable $e) {}

$casas_sin_reportar = $viviendas_activas - $exentas_count - $casas_pagadas_total - $casas_morosas_total;
$total_esperado_barrio = ($viviendas_activas - $exentas_count) * ($config['cuota_mensual'] ?: 0);
$diferencia_total = $total_esperado_barrio - $monto_reportado_total;

$meses_nombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

$title          = "Lotes de Calle - EcoCusco";
$header_title   = "Verificación de Pagos";
$header_subtitle = "Barrio: " . htmlspecialchars($barrio['nombre']) . " — {$meses_nombres[$mes]} {$anio}";

$estado_color = ['Borrador'=>'#6B7280','Enviado'=>'#3B82F6','Aprobado'=>'#10B981','Rechazado'=>'#DC2626'];

ob_start();
?>
<?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

<div class="card" style="margin-bottom:18px; padding:10px 14px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
    <form method="GET" action="router.php" style="display:flex; gap:8px; align-items:center;">
        <input type="hidden" name="page" value="reportar_pago">
        <label style="font-size:11px; font-weight:600; color:#6B7280;">PERIODO:</label>
        <select name="mes" style="padding:5px 8px; border:1px solid #D1D5DB; border-radius:4px; font-size:12px;">
            <?php for($m=1;$m<=12;$m++): ?>
                <option value="<?= $m ?>" <?= $m==$mes?'selected':'' ?>><?= $meses_nombres[$m] ?></option>
            <?php endfor; ?>
        </select>
        <select name="anio" style="padding:5px 8px; border:1px solid #D1D5DB; border-radius:4px; font-size:12px;">
            <?php for($a=date('Y')-1;$a<=date('Y')+1;$a++): ?>
                <option value="<?= $a ?>" <?= $a==$anio?'selected':'' ?>><?= $a ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" style="background:#374151; color:white; border:none; padding:5px 12px; border-radius:4px; font-size:12px; cursor:pointer;">Ver</button>
    </form>
    <form method="POST" style="display:flex; gap:6px; align-items:center; margin-left:auto;">
        <input type="hidden" name="form_type" value="actualizar_configuracion_barrio">
        <label style="font-size:11px; color:#6B7280;">Cuota S/</label>
        <input type="number" step="0.01" name="cuota_mensual" value="<?= $config['cuota_mensual'] ?? 0 ?>"
            style="width:70px; padding:4px 6px; border:1px solid #D1D5DB; border-radius:4px; font-size:12px;">
        <label style="font-size:11px; color:#6B7280;">Multa S/</label>
        <input type="number" step="0.01" name="multa_renovacion" value="<?= $config['multa_renovacion'] ?? 0 ?>"
            style="width:70px; padding:4px 6px; border:1px solid #D1D5DB; border-radius:4px; font-size:12px;">
        <button type="submit" style="background:#6B7280; color:white; border:none; padding:4px 10px; border-radius:4px; font-size:11px; cursor:pointer;">Guardar</button>
    </form>
</div>

<div class="card" style="margin-bottom:18px; border-left:3px solid #6B7280; background:#fff;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px;">
        <div>
            <strong style="font-size:14px;">Resumen del Barrio — <?= $meses_nombres[$mes] ?> <?= $anio ?></strong>
            <div style="font-size:12px; color:#6B7280; margin-top:3px;">
                Cuota: S/ <?= number_format($config['cuota_mensual'] ?: 0, 2) ?>
                <?php if($viviendas_activas > 0): ?>
                • Esperado: <strong>S/ <?= number_format($total_esperado_barrio, 2) ?></strong>
                (<?= $viviendas_activas ?> casas × S/ <?= number_format($config['cuota_mensual'] ?: 0, 2) ?>)
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(100px, 1fr)); gap:8px; margin-top:10px; font-size:12px; text-align:center;">
        <div style="background:#F9FAFB; padding:8px; border-radius:6px;">
            <div style="font-size:20px; font-weight:700; color:#111827;"><?= $viviendas_activas ?></div>
            <div style="color:#6B7280;">Activas</div>
        </div>
        <div style="background:#F9FAFB; padding:8px; border-radius:6px;">
            <div style="font-size:20px; font-weight:700; color:#6D28D9;"><?= $exentas_count ?></div>
            <div style="color:#6B7280;">Exoneradas</div>
        </div>
        <div style="background:#F9FAFB; padding:8px; border-radius:6px;">
            <div style="font-size:20px; font-weight:700; color:#059669;"><?= $casas_pagadas_total ?></div>
            <div style="color:#6B7280;">Pagadas</div>
        </div>
        <div style="background:#F9FAFB; padding:8px; border-radius:6px;">
            <div style="font-size:20px; font-weight:700;"><?= max(0, $casas_sin_reportar) ?></div>
            <div style="color:#6B7280;">Sin pagar</div>
        </div>
    </div>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:8px; margin-top:8px; font-size:12px; text-align:center;">
        <div style="padding:6px; border-radius:4px; background:#F9FAFB;">
            <span style="color:#6B7280;">Reportado:</span>
            <strong style="margin-left:4px;">S/ <?= number_format($monto_reportado_total, 2) ?></strong>
        </div>
        <div style="padding:6px; border-radius:4px; background:#F9FAFB;">
            <span style="color:#6B7280;">Esperado:</span>
            <strong style="margin-left:4px;">S/ <?= number_format($total_esperado_barrio, 2) ?></strong>
        </div>
        <div style="padding:6px; border-radius:4px; background:#F9FAFB;">
            <span style="color:#6B7280;">Diferencia:</span>
            <strong style="margin-left:4px; color:<?= $diferencia_total > 0 ? '#DC2626' : '#059669' ?>;">
                S/ <?= number_format(abs($diferencia_total), 2) ?>
            </strong>
        </div>
    </div>
</div>

<?php if($loteBarrio): ?>
<div class="card" style="margin-bottom:18px; font-size:12px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <div>
            <strong>Lote de Barrio — <?= $loteBarrio['estado'] ?></strong>
            <div style="color:#6B7280; margin-top:2px;">
                <?= $loteBarrio['calles_completas'] ?>/<?= $loteBarrio['total_calles'] ?> calles •
                Esperado: S/ <?= number_format($loteBarrio['monto_total_esperado'],2) ?> •
                Recolectado: S/ <?= number_format($loteBarrio['monto_total_recolectado'],2) ?>
            </div>
        </div>
    </div>
    <?php if($loteBarrio['estado']==='Rechazado' && $loteBarrio['observaciones_gestor']): ?>
    <div style="margin-top:6px; font-size:11px; color:#DC2626;">
        Rechazado por Gestor: <?= htmlspecialchars($loteBarrio['observaciones_gestor']) ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<style>
  @media (max-width: 768px) {
    .reportar-pago-grid { grid-template-columns: 1fr !important; }
  }
</style>
<div class="reportar-pago-grid" style="display:grid; grid-template-columns:1fr 320px; gap:20px;">
    <div style="display:flex; flex-direction:column; gap:20px;">

        <div class="card">
            <h3 style="margin-top:0; color:#111827;">📋 Lotes de Calle — <?= $meses_nombres[$mes] ?> <?= $anio ?></h3>

            <?php if(empty($lotes_calle)): ?>
            <div style="text-align:center; padding:40px; color:#9CA3AF;">
                No hay lotes de calle enviados por los Encargados de Calle para este periodo.
            </div>
            <?php else: ?>

            <?php 
            $numLotesBloqueado = $loteBarrio && $loteBarrio['estado'] === 'Enviado';
            foreach($lotes_calle as $lc): 
                $lc_color = $estado_color[$lc['estado']] ?? '#6B7280';
                $porcentaje = $lc['total_casas'] > 0 ? round($lc['casas_pagadas']/$lc['total_casas']*100) : 0;
            ?>
            <div style="border:1px solid #E5E7EB; border-radius:8px; padding:16px; margin-bottom:14px; background:#fff;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px;">
                    <div>
                        <div style="font-size:15px; font-weight:700; color:#111827;">
                            <?= htmlspecialchars($lc['calle_nombre']) ?>
                        </div>
                        <div style="font-size:11px; color:#6B7280; margin-top:2px;">
                            Encargado: <?= htmlspecialchars($lc['encargado_nombre'] . ' ' . $lc['encargado_apellido']) ?>
                            <?php if($lc['fecha_envio']): ?> • <?= date('d/m/Y H:i', strtotime($lc['fecha_envio'])) ?><?php endif; ?>
                        </div>
                    </div>
                    <span style="font-size:11px; color:#6B7280;"><?= $lc['estado'] ?></span>
                </div>

                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin:12px 0; font-size:12px; text-align:center;">
                    <div style="background:#F9FAFB; padding:8px; border-radius:6px;">
                        <div style="font-size:18px; font-weight:700;"><?= $lc['total_viviendas'] ?? $lc['total_casas'] ?></div>
                        <div style="color:#6B7280; font-size:11px;">🏠 Total viviendas</div>
                    </div>
                    <div style="background:#F5F3FF; padding:8px; border-radius:6px;">
                        <div style="font-size:18px; font-weight:700; color:#6D28D9;"><?= $lc['viviendas_exentas'] ?? 0 ?></div>
                        <div style="color:#6D28D9; font-size:11px;">🛡️ Exoneradas</div>
                    </div>
                    <div style="background:#D1FAE5; padding:8px; border-radius:6px;">
                        <div style="font-size:18px; font-weight:700; color:#065F46;"><?= $lc['casas_pagadas'] ?></div>
                        <div style="color:#065F46; font-size:11px;">✅ Pagaron</div>
                    </div>
                    <div style="background:#FEE2E2; padding:8px; border-radius:6px;">
                        <?php 
                        $cobrables = ($lc['total_viviendas'] ?? $lc['total_casas']) - ($lc['viviendas_exentas'] ?? 0);
                        $pendientes = $cobrables - $lc['casas_pagadas'];
                        ?>
                        <div style="font-size:18px; font-weight:700; color:#991B1B;"><?= max(0, $pendientes) ?></div>
                        <div style="color:#991B1B; font-size:11px;">⏳ Sin pagar</div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; font-size:12px; margin-bottom:12px; padding:10px; background:#F9FAFB; border-radius:6px;">
                    <div>
                        <div style="color:#6B7280; font-size:10px; text-transform:uppercase; font-weight:600;">Monto esperado</div>
                        <div style="font-weight:700; color:#111827; margin-top:2px;">S/ <?= number_format($lc['monto_esperado'],2) ?></div>
                    </div>
                    <div>
                        <div style="color:#6B7280; font-size:10px; text-transform:uppercase; font-weight:600;">Recolectado</div>
                        <div style="font-weight:700; color:<?= $lc['monto_recolectado'] < $lc['monto_esperado']?'#DC2626':'#059669' ?>; margin-top:2px;">S/ <?= number_format($lc['monto_recolectado'],2) ?></div>
                    </div>
                    <div>
                        <div style="color:#6B7280; font-size:10px; text-transform:uppercase; font-weight:600;">Diferencia</div>
                        <div style="font-weight:700; color:<?= $lc['monto_recolectado'] < $lc['monto_esperado']?'#DC2626':'#059669' ?>; margin-top:2px;">
                            <?= $lc['monto_recolectado'] >= $lc['monto_esperado'] ? '✅ Completo' : 'S/ '.number_format($lc['monto_esperado']-$lc['monto_recolectado'],2).' faltante' ?>
                        </div>
                    </div>
                </div>

                <?php if(!empty($lc['comprobante_lote'])): ?>
                <div style="margin-bottom:10px; font-size:12px;">
                    <span style="color:#6B7280;">Comprobante del lote:</span>
                    <a href="#" data-url="<?= htmlspecialchars($lc['comprobante_lote']) ?>" onclick="return openComprobanteModal(this)" style="color:#2563EB; text-decoration:none; font-weight:600;">Ver captura</a>
                </div>
                <?php endif; ?>

                <?php if(!empty($lc['referencia_lote'])): ?>
                <div style="margin-bottom:10px; font-size:12px;">
                    <span style="color:#6B7280;">Referencia del lote:</span>
                    <strong style="color:#111827; background:#F3F4F6; padding:3px 8px; border-radius:4px;"><?= htmlspecialchars($lc['referencia_lote']) ?></strong>
                </div>
                <?php endif; ?>

                <?php if(!empty($lc['observaciones_calle'])): ?>
                <div style="background:#F9FAFB; padding:6px 10px; border-radius:4px; font-size:11px; color:#374151; margin-bottom:10px;">
                    <?= htmlspecialchars($lc['observaciones_calle']) ?>
                </div>
                <?php endif; ?>

                <?php if($lc['estado'] === 'Enviado'): ?>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button onclick="toggleDetalle(<?= $lc['id'] ?>)" style="background:#374151; color:white; border:none; padding:6px 14px; border-radius:6px; font-size:12px; cursor:pointer;">🔍 Ver Viviendas</button>
                    <form method="POST" action="router.php" onsubmit="return confirm('¿Aprobar el lote de <?= htmlspecialchars($lc['calle_nombre']) ?>?')">
                        <input type="hidden" name="form_type" value="aprobar_lote_calle">
                        <input type="hidden" name="lote_id" value="<?= $lc['id'] ?>">
                        <button type="submit" style="background:#059669; color:white; border:none; padding:6px 14px; border-radius:6px; font-size:12px; cursor:pointer;">✅ Aprobar</button>
                    </form>
                    <button onclick="toggleRechazo(<?= $lc['id'] ?>)" style="background:#DC2626; color:white; border:none; padding:6px 14px; border-radius:6px; font-size:12px; cursor:pointer;">❌ Rechazar</button>
                </div>

                <div id="rechazo-<?= $lc['id'] ?>" style="display:none; margin-top:10px;">
                    <form method="POST" action="router.php" style="display:flex; gap:8px; flex-wrap:wrap;">
                        <input type="hidden" name="form_type" value="rechazar_lote_calle">
                        <input type="hidden" name="lote_id" value="<?= $lc['id'] ?>">
                        <input type="text" name="motivo_rechazo" placeholder="Motivo del rechazo" required
                            style="flex:1; min-width:200px; padding:7px 10px; border:1px solid #D1D5DB; border-radius:6px; font-size:12px;">
                        <button type="submit" style="background:#DC2626; color:white; border:none; padding:6px 14px; border-radius:6px; font-size:12px; cursor:pointer;">Confirmar Rechazo</button>
                    </form>
                </div>

                <div id="detalle-<?= $lc['id'] ?>" style="display:none; margin-top:12px;">
                    <?php
                    $detalles_lc = [];
                    try {
                        $detStmt = $pdo->prepare(
                            "SELECT v.id as vivienda_id, v.numero_casa, v.propietario, v.direccion, v.exento_cobro, v.estado_servicio,
                                    c.id as cobro_id, c.monto, c.estado, c.referencia_pago, c.comprobante_calle, c.fecha_emision
                             FROM viviendas v
                             LEFT JOIN cobros c ON c.vivienda_id = v.id AND c.mes = ? AND c.anio = ? AND c.estado != 'Anulado'
                             WHERE v.calle_id = ?
                             AND v.id NOT IN (SELECT vivienda_id FROM solicitudes_vivienda WHERE tipo='Baja' AND estado='Aprobado')
                             ORDER BY CAST(v.numero_casa AS UNSIGNED), v.numero_casa"
                        );
                        $detStmt->execute([$lc['mes'], $lc['anio'], $lc['calle_id']]);
                        $detalles_lc = $detStmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (\Throwable $e) {}
                    ?>
                    <div style="border:1px solid #E5E7EB; border-radius:6px; overflow:hidden;">
                        <table style="width:100%; border-collapse:collapse; font-size:11px;">
                            <thead style="background:#F9FAFB;">
                                <tr>
                                    <th style="padding:8px; text-align:left;">Casa</th>
                                    <th style="padding:8px; text-align:left;">Propietario</th>
                                    <th style="padding:8px; text-align:left;">Dirección</th>
                                    <th style="padding:8px; text-align:center;">Estado</th>
                                    <th style="padding:8px;">Referencia</th>
                                    <th style="padding:8px;">Comprobante</th>
                                    <th style="padding:8px; text-align:right;">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($detalles_lc as $dl): ?>
                                <?php
                                    $pagado = $dl['estado'] === 'Pagado';
                                    $exenta = $dl['exento_cobro'] == 1;
                                    $suspendida = $dl['estado_servicio'] === 'Suspendido';
                                    $rowBg = $suspendida ? '#F9FAFB' : ($exenta ? '#F5F3FF' : ($pagado ? '#F0FDF4' : '#FEF2F2'));
                                ?>
                                <tr style="border-top:1px solid #F3F4F6; background:<?= $rowBg ?>; opacity:<?= $suspendida?'0.6':'1' ?>;">
                                    <td style="padding:8px; font-weight:600; color:#111827;">#<?= htmlspecialchars($dl['numero_casa']) ?></td>
                                    <td style="padding:8px;"><?= htmlspecialchars($dl['propietario']) ?></td>
                                    <td style="padding:8px; color:#6B7280; font-size:10px;"><?= htmlspecialchars($dl['direccion'] ?? '—') ?></td>
                                    <td style="padding:8px; text-align:center;">
                                        <?php if($suspendida): ?>
                                            <span style="background:#F3F4F6; color:#6B7280; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:600;">⛔ Suspendida</span>
                                        <?php elseif($exenta): ?>
                                            <span style="background:#EDE9FE; color:#5B21B6; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:600;">🛡️ Exonerada</span>
                                        <?php elseif($pagado): ?>
                                            <span style="background:#D1FAE5; color:#065F46; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:600;">✅ Pagado</span>
                                        <?php else: ?>
                                            <span style="background:#FEE2E2; color:#991B1B; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:600;">⏳ Pendiente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:8px; font-weight:600; font-size:11px;"><?= htmlspecialchars($dl['referencia_pago'] ?? '—') ?></td>
                                    <td style="padding:8px; text-align:center;">
                                        <?php if(!empty($dl['comprobante_calle']) && preg_match('/\.(jpe?g|png|gif|webp|bmp)(\?.*)?$/i', $dl['comprobante_calle'])): ?>
                                            <img src="<?= htmlspecialchars($dl['comprobante_calle']) ?>" alt="Comp" style="width:36px; height:36px; border-radius:4px; object-fit:cover; cursor:pointer; border:1px solid #E5E7EB;" onclick="openComprobanteModal(this)" data-url="<?= htmlspecialchars($dl['comprobante_calle']) ?>">
                                        <?php elseif(!empty($dl['comprobante_calle'])): ?>
                                            <a href="#" data-url="<?= htmlspecialchars($dl['comprobante_calle']) ?>" onclick="return openComprobanteModal(this)" style="color:#2563EB; text-decoration:none;">📎 PDF</a>
                                        <?php else: ?>
                                            <span style="color:#9CA3AF;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:8px; text-align:right; font-weight:700; color:<?= $pagado?'#059669':($exenta?'#6D28D9':($suspendida?'#9CA3AF':'#DC2626')) ?>;">
                                        <?php if($suspendida): ?>
                                            <span style="color:#9CA3AF;">—</span>
                                        <?php elseif($exenta): ?>
                                            <span style="color:#9CA3AF;">Exenta</span>
                                        <?php else: ?>
                                            S/ <?= number_format($dl['monto'] ?? 0,2) ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($detalles_lc)): ?>
                                <tr><td colspan="7" style="padding:12px; text-align:center; color:#9CA3AF;">Sin viviendas registradas en esta calle</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php elseif($lc['estado'] === 'Aprobado'): ?>
                <div style="font-size:12px; color:#374151; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                    <span>✅ Aprobado — pendiente de envío al Gestor</span>
                    <?php if(!$numLotesBloqueado): ?>
                    <span style="font-size:11px; color:#6B7280;">Usa el panel lateral para enviar</span>
                    <?php endif; ?>
                </div>
                <?php elseif($lc['estado'] === 'Rechazado'): ?>
                <div style="font-size:12px; color:#DC2626;">
                    ❌ Rechazado — <?= htmlspecialchars($lc['observaciones_barrio'] ?? 'Sin motivo') ?>
                </div>
                <?php else: ?>
                <div style="font-size:12px; color:#6B7280;">
                    ✏️ En borrador — el encargado de calle aún no ha enviado el lote.
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card" style="border-top:3px solid #6B7280; position:sticky; top:20px;">
            <h3 style="margin-top:0; font-size:15px; color:#111827;">Lote de Barrio</h3>

            <?php
            // CORRECCIÓN PARA COMPATIBILIDAD ABSOLUTA (Reemplaza al array_filter / array_column)
            $lotesAprobados = [];
            $totalEsp = 0;
            $totalRec = 0;

            if (is_array($lotes_calle)) {
                foreach ($lotes_calle as $l) {
                    if ($l['estado'] === 'Aprobado' && empty($l['lote_barrio_id'])) {
                        $lotesAprobados[] = $l;
                        $totalEsp += (float)$l['monto_esperado'];
                        $totalRec += (float)$l['monto_recolectado'];
                    }
                }
            }
            
            $cantidadAprobados = count($lotesAprobados);
            ?>

            <div style="margin-bottom:12px; font-size:13px;">
                <div style="display:flex; justify-content:space-between; padding:3px 0;">
                    <span style="color:#6B7280;">Lotes aprobados:</span>
                    <strong style="color:#059669;"><?= $cantidadAprobados ?></strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding:3px 0;">
                    <span style="color:#6B7280;">Total calles:</span>
                    <strong><?= count($lotes_calle) ?></strong>
                </div>
                <hr style="border:none; border-top:1px solid #E5E7EB; margin:4px 0;">
                <div style="display:flex; justify-content:space-between; padding:3px 0;">
                    <span style="color:#6B7280;">Esperado:</span>
                    <strong>S/ <?= number_format($totalEsp,2) ?></strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding:3px 0;">
                    <span style="color:#6B7280;">Recolectado:</span>
                    <strong style="color:#059669;">S/ <?= number_format($totalRec,2) ?></strong>
                </div>
                <?php if($totalRec < $totalEsp): ?>
                <div style="font-size:12px; color:#DC2626; padding:3px 0;">
                    Diferencia: S/ <?= number_format($totalEsp - $totalRec,2) ?>
                </div>
                <?php endif; ?>
            </div>

            <?php if($loteBarrio && !empty($loteBarrio['comprobante_lote'])): ?>
            <div style="margin-bottom:12px; font-size:12px;">
                <span style="color:#6B7280;">Comprobante enviado al Gestor:</span><br>
                <a href="#" data-url="<?= htmlspecialchars($loteBarrio['comprobante_lote']) ?>" onclick="return openComprobanteModal(this)" style="color:#2563EB; text-decoration:none; font-weight:600;">Ver captura</a>
            </div>
            <?php endif; ?>

            <?php if(!$numLotesBloqueado): ?>
            <?php if($cantidadAprobados > 0): ?>
            <form method="POST" action="router.php" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:8px;">
                <input type="hidden" name="form_type" value="enviar_lote_barrio">
                <input type="hidden" name="periodo_mes" value="<?= $mes ?>">
                <input type="hidden" name="periodo_anio" value="<?= $anio ?>">
                <div style="padding:10px; background:#F9FAFB; border-radius:6px; border:1px dashed #D1D5DB;">
                    <label style="font-size:11px; color:#374151; font-weight:600; display:block; margin-bottom:4px;">
                        Comprobante de pago (captura de transferencia):
                    </label>
                    <input type="file" name="comprobante_lote_barrio" accept="image/*,.pdf" style="font-size:11px; width:100%;">
                </div>
                <textarea name="observaciones_lote_barrio" placeholder="Observaciones" rows="2"
                    style="padding:6px; border:1px solid #D1D5DB; border-radius:4px; font-size:12px;"></textarea>
                <button type="submit" style="background:#374151; color:white; border:none; padding:10px; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer;"
                    onclick="return confirm('¿Enviar el lote de barrio al Gestor?')">
                    <?= ($loteBarrio && $loteBarrio['estado']==='Rechazado') ? 'Reenviar al Gestor' : 'Enviar al Gestor' ?>
                </button>
            </form>
            <?php else: ?>
            <button disabled style="width:100%; padding:10px; background:#F3F4F6; color:#9CA3AF; border:none; border-radius:6px; font-size:12px;">
                Aprueba lotes de calle primero
            </button>
            <?php endif; ?>
            <?php elseif($loteBarrio && $loteBarrio['estado']==='Aprobado'): ?>
            <div style="font-size:12px; color:#059669;">✅ Lote aprobado por el Gestor</div>
            <?php else: ?>
            <div style="font-size:12px; color:#6B7280;">📤 Enviado al Gestor — esperando aprobación</div>
            <?php endif; ?>

            <div style="margin-top:10px; font-size:10px; color:#9CA3AF;">
                Solo se consolidan lotes de calle en estado Aprobado.
            </div>
        </div>
    </div>
</div>

<script>
function toggleDetalle(id) {
    const el = document.getElementById('detalle-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function toggleRechazo(id) {
    const el = document.getElementById('rechazo-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>