<?php
// views/calle/reportar_pago.php — Módulo de Lote de Calle
global $pdo;
require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../app/helpers.php';
use app\models\mainModel;

if (empty($pdo)) {
    $pdo = (new mainModel())->conectar();
}

$user = check_dashboard_access([6]);
$page = 'reportar_pago';

// Periodo activo (por defecto mes actual)
$mes  = (int)($_GET['mes']  ?? date('n'));
$anio = (int)($_GET['anio'] ?? date('Y'));

// Array local para traducir los meses de forma nativa sin depender de helper externo
$mesesNombres = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

try {
    // Calle asignada — Usando alias limpio 'dc' para evitar conflictos
    $calleStmt = $pdo->prepare("SELECT dc.calle_id, c.nombre as calle_nombre 
                            FROM detalles_encargado_calle dc 
                            JOIN calles c ON c.id = dc.calle_id 
                            WHERE dc.usuario_id = ?");
    $calleStmt->execute([$user['id']]);
    $calleData = $calleStmt->fetch(PDO::FETCH_ASSOC);
    $calle_id = $calleData['calle_id'] ?? null;

    if (!$calle_id) {
        $title = "Error - EcoCusco";
        $header_title = "Sin asignación";
        $header_subtitle = "No se pudo cargar la información de la calle.";
        ob_start();
        ?>
        <div class="card" style="padding: 14px; border-left: 4px solid #DC2626; background: #FEF2F2; color: #991B1B;">
            <h3 style="margin-top:0;">⚠️ No tienes una calle asignada</h3>
            <p style="font-size:14px; margin-bottom:0;">
                Tu usuario (ID: <strong><?= htmlspecialchars($user['id']) ?></strong>) no figura registrado en la tabla <code>detalles_encargado_calle</code>.
            </p>
        </div>
        <?php
        $content = ob_get_clean();
        include __DIR__ . '/../layouts/dashboard_layout.php';
        return;
    }

    // Lote del mes actual
    $loteStmt = $pdo->prepare("SELECT * FROM lotes_calle WHERE calle_id=? AND anio=?");
    $loteStmt->execute([$calle_id, $anio]);
    $lote = $loteStmt->fetch(PDO::FETCH_ASSOC);
    $lote_estado = $lote['estado'] ?? 'No Creado';

    // Contar viviendas activas (excluye bajas aprobadas)
    $activasStmt = $pdo->prepare("SELECT COUNT(*) FROM viviendas WHERE calle_id=? AND estado_servicio='Activo' AND id NOT IN (SELECT vivienda_id FROM solicitudes_vivienda WHERE tipo='Baja' AND estado='Aprobado')");
    $activasStmt->execute([$calle_id]);
    $total_activas = (int)$activasStmt->fetchColumn();

    // Contar exentas
    $exentasStmt = $pdo->prepare("SELECT COUNT(*) FROM viviendas WHERE calle_id=? AND estado_servicio='Activo' AND exento_cobro=1");
    $exentasStmt->execute([$calle_id]);
    $exentas_cant = (int)$exentasStmt->fetchColumn();

    // Casas que deben pagar (activas - exentas)
    $casas_a_cobrar = $total_activas - $exentas_cant;

    // Obtener cobros — CORREGIDO: Se cambiaron c.mes_recaudacion y c.anio_recaudacion por c.mes y c.anio
    $cobrosStmt = $pdo->prepare("SELECT c.*, v.propietario, v.numero_casa 
                                 FROM cobros c 
                                 JOIN viviendas v ON c.vivienda_id = v.id 
                                 WHERE v.calle_id = ? AND c.mes = ? AND c.anio = ? AND c.estado = 'Pagado'");
    $cobrosStmt->execute([$calle_id, $mes, $anio]);
    $cobros = $cobrosStmt->fetchAll(PDO::FETCH_ASSOC);

    $total_recaudado = 0;
    foreach ($cobros as $c) {
        $total_recaudado += $c['monto'];
    }

    $casas_pagadas = count($cobros);
    $casas_morosas = $casas_a_cobrar - $casas_pagadas;

} catch (\Exception $e) {
    // Si la base de datos lanza un error, lo pintamos limpiamente en vez de romper el servidor
    $title = "Error de Base de Datos";
    $header_title = "Error Interno";
    $header_subtitle = "Ocurrió un problema al procesar las consultas SQL.";
    ob_start();
    echo "<div class='card' style='color:#DC2626; background:#FEF2F2; padding:20px; border-left:4px solid #DC2626;'>";
    echo "<h3>💥 Error detectado en la Base de Datos:</h3>";
    echo "<p><code>" . htmlspecialchars($e->getMessage()) . "</code></p>";
    echo "</div>";
    $content = ob_get_clean();
    include __DIR__ . '/../layouts/dashboard_layout.php';
    return;
}

$title = "Reportar Lote - EcoCusco";
$header_title = "Reportar Pagos del Mes";
$header_subtitle = "Administra el envío y estado del lote de recaudación mensual de tu calle.";

ob_start();
?>

<?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; align-items: start;">
    
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h3 style="margin:0;">💰 Pagos Recaudados en Calle: <?= htmlspecialchars($calleData['calle_nombre'] ?? 'Sin Nombre') ?></h3>
            
            <form method="GET" action="router.php" style="display:flex; gap:5px;">
                <input type="hidden" name="page" value="reportar_pago">
                <input type="hidden" name="sid" value="<?= htmlspecialchars($_SESSION['active_sid'] ?? 'main') ?>">
                <select name="mes" style="padding: 5px; border-radius:4px; border:1px solid #D1D5DB;">
                    <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m===$mes?'selected':'' ?>><?= $mesesNombres[$m] ?></option>
                    <?php endfor; ?>
                </select>
                <select name="anio" style="padding: 5px; border-radius:4px; border:1px solid #D1D5DB;">
                    <option value="<?= date('Y') ?>" selected><?= date('Y') ?></option>
                </select>
                <button type="submit" style="padding:5px 10px; background:#3B82F6; color:white; border:none; border-radius:4px; cursor:pointer;">Ir</button>
            </form>
        </div>

        <p style="font-size: 13px; color:#6B7280; margin-bottom: 10px;">
            A continuación se muestran los pagos que has marcado como 'Pagado' en la sección de viviendas cuyo dinero pertenece a la recaudación de este periodo.
        </p>

        <div class="table-wrap">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #F9FAFB; border-bottom: 1px solid #E5E7EB; text-align: left;">
                        <th style="padding: 12px;">Vivienda / Propietario</th>
                        <th style="padding: 12px;">Comprobante / Referencia</th>
                        <th style="padding: 12px; text-align: center;">Monto</th>
                        <th style="padding: 12px; text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cobros)): ?>
                        <tr>
                            <td colspan="4" style="padding: 14px; text-align: center; color: #9CA3AF;">
                                No has registrado cobros efectivos para el periodo seleccionado de <?= $mesesNombres[$mes] ?>/<?= $anio ?>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cobros as $c): ?>
                            <tr style="border-bottom: 1px solid #E5E7EB;">
                                <td style="padding: 12px;">
                                    <div style="font-weight: 600; color: #111827;">Nro. <?= htmlspecialchars($c['numero_casa']) ?></div>
                                    <div style="font-size: 11px; color: #6B7280;"><?= htmlspecialchars($c['propietario']) ?></div>
                                </td>
                                <td style="padding: 12px;">
                                    <div style="display:flex; gap:8px; align-items:center;">
                                        <?php if (!empty($c['comprobante_calle'])): ?>
                                            <?php if (preg_match('/\.(jpe?g|png|gif|webp|bmp)(\?.*)?$/i', $c['comprobante_calle'])): ?>
                                                <img src="<?= htmlspecialchars($c['comprobante_calle']) ?>" alt="Comp" style="width:48px;height:48px;border-radius:6px;object-fit:cover;border:1px solid #E5E7EB;cursor:pointer;" onclick="openComprobanteModal(this)" data-url="<?= htmlspecialchars($c['comprobante_calle']) ?>">
                                            <?php else: ?>
                                                <a href="#" data-url="<?= htmlspecialchars($c['comprobante_calle']) ?>" onclick="return openComprobanteModal(this)" style="background:#E0F2FE;color:#0369A1;padding:4px 10px;border-radius:4px;text-decoration:none;font-size:11px;">📎 Ver PDF</a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color:#9CA3AF;">—</span>
                                        <?php endif; ?>
                                        <span style="font-size:13px;color:#111827;font-weight:700;background:#F9FAFB;padding:4px 8px;border-radius:4px;"><?= htmlspecialchars($c['referencia_pago'] ?? '—') ?></span>
                                    </div>
                                </td>
                                <td style="padding: 12px; text-align: center; font-weight: 700; color: #10B981;">
                                    S/ <?= number_format($c['monto'], 2) ?>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <button onclick="openDetalleCobro(<?= $c['id'] ?>, '<?= htmlspecialchars($c['numero_casa'], ENT_QUOTES) ?>', '<?= htmlspecialchars($c['propietario'], ENT_QUOTES) ?>', '<?= htmlspecialchars($c['referencia_pago'] ?? '', ENT_QUOTES) ?>', '<?= number_format($c['monto'], 2) ?>', '<?= htmlspecialchars($c['comprobante_calle'] ?? '', ENT_QUOTES) ?>')"
                                            style="background:#F3F4F6; color:#374151; border:1px solid #D1D5DB; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:11px;">🔍 Detalle</button>
                                    <?php if (!in_array($lote_estado, ['Enviado', 'Aprobado'])): ?>
                                        <button onclick="openEditarCobro(<?= $c['id'] ?>, <?= $c['vivienda_id'] ?>, '<?= htmlspecialchars($c['numero_casa'], ENT_QUOTES) ?>', '<?= htmlspecialchars($c['propietario'], ENT_QUOTES) ?>', '<?= htmlspecialchars($c['referencia_pago'] ?? '', ENT_QUOTES) ?>')" 
                                                style="background:transparent; color:#6B7280; border:1px solid #D1D5DB; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:11px; margin-left:4px;">✏️ Editar</button>
                                    <?php else: ?>
                                        <span style="color:#9CA3AF; font-size:11px; margin-left:4px;">🔒 Enviado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="background: #FAFAFA; border: 1px solid #E5E7EB;">
        <h4 style="margin-top: 0; margin-bottom: 10px; color: #374151;">📦 Control de Lote Mensual</h4>
        <div style="font-size: 12px; color:#6B7280; margin-bottom: 10px;">
            Periodo: <strong><?= $mesesNombres[$mes] ?> / <?= $anio ?></strong>
        </div>

        <div style="margin-bottom: 14px; padding: 12px; border-radius: 8px; background: white; border:1px solid #E5E7EB;">
            <div style="font-size: 11px; color:#9CA3AF; font-weight: 600; text-transform: uppercase;">Estado del Reporte</div>
            <div style="font-size: 16px; font-weight: 700; margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                <?php if ($lote_estado === 'No Creado' || $lote_estado === 'Abierto'): ?>
                    <span style="color: #D97706;">🟢 Abierto / Recaudando</span>
                <?php elseif ($lote_estado === 'Enviado'): ?>
                    <span style="color: #2563EB;">📤 Enviado al Barrio</span>
                <?php elseif ($lote_estado === 'Aprobado'): ?>
                    <span style="color: #16A34A;">✅ Aprobado</span>
                <?php elseif ($lote_estado === 'Rechazado'): ?>
                    <span style="color: #DC2626;">❌ Rechazado (Corregir)</span>
                <?php endif; ?>
            </div>
            <?php if($lote_estado === 'Rechazado' && !empty($lote['observaciones'])): ?>
                <div style="margin-top: 8px; padding: 8px; background: #FEF2F2; border-left: 3px solid #EF4444; font-size: 11px; color: #991B1B;">
                    <strong>Motivo:</strong> <?= htmlspecialchars($lote['observaciones']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 14px;">
            <div style="font-size: 11px; color:#6B7280;">Total Neto Recaudado:</div>
            <div style="font-size: 20px; font-weight: 800; color: #111827; margin-top: 2px;">
                S/ <?= number_format($total_recaudado, 2) ?>
            </div>
            <div style="font-size: 11px; color:#9CA3AF; margin-top:2px;">Total transacciones: <?= count($cobros) ?></div>
        </div>

        <!-- Resumen de casas -->
        <div style="margin-bottom: 14px; padding: 12px; background: white; border-radius: 8px; border:1px solid #E5E7EB;">
            <div style="font-size: 11px; color:#9CA3AF; font-weight: 600; text-transform: uppercase; margin-bottom:8px;">Resumen de viviendas</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                <div style="background:#F9FAFB; padding:8px; border-radius:6px; text-align:center;">
                    <div style="font-size:18px; font-weight:800; color:#374151;"><?= $total_activas ?></div>
                    <div style="font-size:10px; color:#6B7280;">🏠 Casas activas</div>
                </div>
                <div style="background:#F5F3FF; padding:8px; border-radius:6px; text-align:center;">
                    <div style="font-size:18px; font-weight:800; color:#6D28D9;"><?= $exentas_cant ?></div>
                    <div style="font-size:10px; color:#6D28D9;">🛡️ Exoneradas</div>
                </div>
                <div style="background:#D1FAE5; padding:8px; border-radius:6px; text-align:center;">
                    <div style="font-size:18px; font-weight:800; color:#065F46;"><?= $casas_pagadas ?></div>
                    <div style="font-size:10px; color:#065F46;">✅ Pagadas</div>
                </div>
                <div style="background:#FEE2E2; padding:8px; border-radius:6px; text-align:center;">
                    <div style="font-size:18px; font-weight:800; color:#991B1B;"><?= max(0,$casas_morosas) ?></div>
                    <div style="font-size:10px; color:#991B1B;">⏳ Sin pagar</div>
                </div>
            </div>
            <!-- Barra de progreso -->
            <div style="margin-top:10px;">
                <div style="display:flex; justify-content:space-between; font-size:10px; color:#6B7280; margin-bottom:4px;">
                    <span>Progreso de cobro</span>
                    <span style="font-weight:700;"><?= $casas_a_cobrar > 0 ? round(($casas_pagadas / $casas_a_cobrar) * 100) : 0 ?>%</span>
                </div>
                <div style="background:#E5E7EB; height:8px; border-radius:4px; overflow:hidden;">
                    <div style="width:<?= $casas_a_cobrar > 0 ? round(($casas_pagadas / $casas_a_cobrar) * 100) : 0 ?>%; height:100%; background:<?= $casas_pagadas == $casas_a_cobrar ? '#10B981' : '#F59E0B' ?>; border-radius:4px; transition:width .5s;"></div>
                </div>
            </div>
            <div style="margin-top:6px; font-size:10px; color:#6B7280; text-align:center;">
                <?= $casas_pagadas ?> de <?= $casas_a_cobrar ?> casas por cobrar (excluye <?= $exentas_cant ?> exoneradas)
            </div>
        </div>

        <!-- Casas sin pagar -->
        <?php
        $pendientesStmt = $pdo->prepare(
            "SELECT v.id, v.numero_casa, v.propietario, v.direccion
             FROM viviendas v
             WHERE v.calle_id = ? 
             AND v.estado_servicio = 'Activo' 
             AND v.exento_cobro = 0
             AND v.id NOT IN (SELECT vivienda_id FROM solicitudes_vivienda WHERE tipo='Baja' AND estado='Aprobado')
             AND v.id NOT IN (SELECT vivienda_id FROM cobros WHERE mes = ? AND anio = ? AND estado = 'Pagado')
             ORDER BY CAST(v.numero_casa AS UNSIGNED) ASC, v.numero_casa ASC"
        );
        $pendientesStmt->execute([$calle_id, $mes, $anio]);
        $pendientes = $pendientesStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php if (!empty($pendientes)): ?>
        <div style="margin-bottom: 14px; padding: 12px; background: white; border-radius: 8px; border:1px solid #FECACA; border-left:3px solid #EF4444;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <div style="font-size: 11px; color:#991B1B; font-weight: 600; text-transform: uppercase;">Casas sin pagar — <?= count($pendientes) ?></div>
                <a href="router.php?page=viviendas&sid=<?= htmlspecialchars($_SESSION['active_sid'] ?? 'main') ?>" style="font-size:10px; color:#3B82F6; text-decoration:none; font-weight:600;">Ir a Marcar Pagos →</a>
            </div>
            <div style="max-height:200px; overflow-y:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:11px;">
                    <thead>
                        <tr style="background:#FEF2F2; text-align:left;">
                            <th style="padding:6px 8px;">#</th>
                            <th style="padding:6px 8px;">Propietario</th>
                            <th style="padding:6px 8px;">Dirección</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendientes as $p): ?>
                        <tr style="border-bottom:1px solid #FEE2E2;">
                            <td style="padding:5px 8px; font-weight:700; color:#DC2626;"><?= htmlspecialchars($p['numero_casa']) ?></td>
                            <td style="padding:5px 8px;"><?= htmlspecialchars($p['propietario']) ?></td>
                            <td style="padding:5px 8px; color:#6B7280;"><?= htmlspecialchars($p['direccion'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 10px;">
            <?php if ($lote_estado === 'No Creado' || $lote_estado === 'Abierto' || $lote_estado === 'Rechazado'): ?>
                <?php if ($total_activas > $exentas_cant): ?>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="sid" value="<?= htmlspecialchars($_SESSION['active_sid'] ?? 'main') ?>">
                        <input type="hidden" name="form_type" value="enviar_lote_calle">
                        <input type="hidden" name="mes" value="<?= $mes ?>">
                        <input type="hidden" name="anio" value="<?= $anio ?>">
                        
                        <div style="margin-bottom: 10px; padding: 10px; background: #EFF6FF; border-radius: 6px; border: 1px solid #BFDBFE;">
                            <label style="font-size: 11px; font-weight: 700; color: #1E40AF; display: block; margin-bottom: 4px;">
                                🔢 Referencia del Lote (número de voucher o transacción del lote):
                            </label>
                            <input type="text" name="referencia_lote" placeholder="Ej: Voucher Lote #00123" required
                                   style="width: 100%; padding: 8px; border: 1px solid #93C5FD; border-radius: 4px; font-size: 12px; box-sizing: border-box;">
                            <small style="font-size: 10px; color: #1D4ED8;">Número de referencia que el Encargado de Barrio usará para verificar el pago</small>
                        </div>

                        <div style="margin-bottom: 8px; padding: 10px; background: #FEF3C7; border-radius: 6px; border: 1px dashed #F59E0B;">
                            <label style="font-size: 11px; font-weight: 700; color: #92400E; display: block; margin-bottom: 4px;">
                                📷 Comprobante Global del Lote (captura de transferencia):
                            </label>
                            <input type="file" name="comprobante_lote" accept="image/*,.pdf" style="font-size: 11px; width: 100%;">
                            <small style="font-size: 10px; color: #B45309;">Sube una imagen del voucher o captura de pantalla del pago</small>
                        </div>
                        
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; font-weight: 700; background: #10B981; border: none; border-radius: 8px; cursor: pointer; display: inline-block; text-align: center;" onclick="return confirm('¿Estás seguro de enviar el lote al Encargado de Barrio? Se incluirán las <?= count($pendientes) ?> viviendas pendientes.')">
                            📤 <?= $lote_estado === 'Rechazado' ? 'Reenviar Lote Corregido' : 'Enviar Lote al Barrio' ?>
                        </button>
                    </form>
                <?php else: ?>
                    <button disabled style="width:100%; padding:12px; background:#F3F4F6; color:#9CA3AF; border:none; border-radius:8px; cursor:not-allowed;">
                        Sin viviendas por reportar
                    </button>
                <?php endif; ?>
            <?php elseif ($lote_estado === 'Aprobado'): ?>
                <div style="text-align:center; padding:15px; background:#D1FAE5; border-radius:8px; font-size:13px; color:#065F46;">
                    ✅ <strong>Lote aprobado por el Barrio</strong><br>
                    <small>Monto aprobado: S/ <?= number_format($lote['monto_recolectado'], 2) ?></small>
                </div>
            <?php else: ?>
                <div style="text-align:center; padding:15px; background:#EFF6FF; border-radius:8px; font-size:13px; color:#1E40AF;">
                    📤 Lote enviado — esperando revisión del Barrio
                </div>
            <?php endif; ?>

            <div style="margin-top:15px; padding-top:12px; border-top:1px dashed #E5E7EB; font-size:11px; color:#6B7280; line-height:1.4;">
                💡 <strong>Nota:</strong> Al enviar el lote, se congelará la edición de cobros de este periodo y pasará a revisión del encargado general de tu barrio.
            </div>
        </div>
    </div>
</div>

<div id="modal-detalle-cobro" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;" onclick="if(event.target===this)closeDetalleCobro()">
    <div style="background:white; border-radius:16px; padding:28px; width:90%; max-width:480px; box-shadow:0 20px 60px rgba(0,0,0,0.3); position:relative; animation:modalIn .25s ease;">
        <button onclick="closeDetalleCobro()" style="position:absolute; top:12px; right:16px; background:none; border:none; font-size:22px; cursor:pointer; color:#9CA3AF;">✕</button>
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:40px; margin-bottom:8px;">🔍</div>
            <h3 style="margin:0; font-size:18px;">Detalle del Pago</h3>
            <p style="color:#6B7280; font-size:13px; margin:4px 0 0;">Revisa la información antes de enviar</p>
        </div>
        <div id="detalle-vivienda" style="background:#F3F4F6; border-radius:10px; padding:14px; margin-bottom:18px; display:flex; gap:12px; align-items:center;">
            <div style="background:#10B981; color:white; width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:16px; flex-shrink:0;" id="detalle-casa-num">#</div>
            <div>
                <div style="font-weight:700; font-size:14px;" id="detalle-propietario">—</div>
                <div style="font-size:11px; color:#6B7280;">Vivienda</div>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:18px;">
            <div style="background:#F9FAFB; padding:12px; border-radius:8px; text-align:center;">
                <div style="font-size:10px; color:#6B7280; text-transform:uppercase; font-weight:600;">Referencia</div>
                <div style="font-size:14px; font-weight:700; color:#111827; margin-top:4px;" id="detalle-referencia">—</div>
            </div>
            <div style="background:#F9FAFB; padding:12px; border-radius:8px; text-align:center;">
                <div style="font-size:10px; color:#6B7280; text-transform:uppercase; font-weight:600;">Monto</div>
                <div style="font-size:18px; font-weight:800; color:#059669; margin-top:4px;" id="detalle-monto">—</div>
            </div>
        </div>
        <div style="margin-bottom:10px;">
            <div style="font-size:11px; font-weight:700; color:#374151; margin-bottom:6px;">COMPROBANTE</div>
            <div id="detalle-comprobante" style="background:#F9FAFB; border-radius:8px; padding:16px; text-align:center; min-height:100px; display:flex; align-items:center; justify-content:center; border:1px solid #E5E7EB;">
                <span style="color:#9CA3AF;">Sin comprobante</span>
            </div>
        </div>
    </div>
</div>

<div id="modal-editar-cobro" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;" onclick="if(event.target===this)closeEditarCobro()">
    <div style="background:white; border-radius:16px; padding:28px; width:90%; max-width:440px; box-shadow:0 20px 60px rgba(0,0,0,0.3); position:relative; animation:modalIn .25s ease;">
        <button onclick="closeEditarCobro()" style="position:absolute; top:12px; right:16px; background:none; border:none; font-size:22px; cursor:pointer; color:#9CA3AF;">✕</button>
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:40px; margin-bottom:8px;">✏️</div>
            <h3 style="margin:0; font-size:18px;">Editar Datos del Cobro</h3>
            <p style="color:#6B7280; font-size:13px; margin:4px 0 0;">Corrige la referencia o el comprobante</p>
        </div>
        <form method="POST" enctype="multipart/form-data" id="form-editar-cobro">
            <input type="hidden" name="action" value="editar_cobro">
            <input type="hidden" name="cobro_id" id="edit-cobro-id">
            <div style="background:#F3F4F6; border-radius:10px; padding:14px; margin-bottom:18px; display:flex; gap:12px; align-items:center;">
                <div style="background:#F59E0B; color:white; width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:16px; flex-shrink:0;" id="edit-casa-num">#</div>
                <div>
                    <div style="font-weight:700; font-size:14px;" id="edit-propietario">—</div>
                    <div style="font-size:11px; color:#6B7280;">Vivienda</div>
                </div>
            </div>
            <div style="margin-bottom:14px;">
                <label style="font-size:12px; font-weight:700; color:#374151; display:block; margin-bottom:5px;">REFERENCIA DE PAGO</label>
                <input type="text" name="referencia_pago" id="edit-referencia" required placeholder="Ej: Voucher #12345" 
                       style="width:100%; padding:12px; border:2px solid #E5E7EB; border-radius:10px; font-size:14px; box-sizing:border-box;">
            </div>
            <div style="margin-bottom:20px;">
                <label style="font-size:12px; font-weight:700; color:#374151; display:block; margin-bottom:5px;">NUEVO COMPROBANTE (opcional)</label>
                <div style="border:2px dashed #D1D5DB; border-radius:10px; padding:20px; text-align:center; cursor:pointer; background:#F9FAFB; transition:.2s;"
                     onclick="document.getElementById('edit-file-input').click()" id="edit-upload-area">
                    <div style="font-size:36px; margin-bottom:6px;">📷</div>
                    <div style="font-size:13px; color:#6B7280;">Haz clic para cambiar la imagen</div>
                    <div style="font-size:11px; color:#9CA3AF; margin-top:2px;">PNG, JPG o PDF · Deja vacío para mantener actual</div>
                </div>
                <input type="file" name="comprobante_pago" id="edit-file-input" accept="image/*,.pdf" style="display:none;" onchange="document.getElementById('edit-upload-area').innerHTML='<div style=font-size:24px;margin-bottom:4px;>✅</div><div style=font-size:13px;color:#059669;font-weight:600;>Archivo seleccionado</div><div style=font-size:11px;color:#6B7280;>' + this.files[0].name + '</div>'">
            </div>
            <button type="submit" style="width:100%; background:#F59E0B; color:white; border:none; padding:14px; border-radius:10px; font-size:15px; font-weight:700; cursor:pointer;">
                💾 Guardar Cambios
            </button>
        </form>
    </div>
</div>

<style>
@keyframes modalIn { from { opacity:0; transform:scale(.95); } to { opacity:1; transform:scale(1); } }
</style>

<script>
function openDetalleCobro(id, casa, prop, ref, monto, comprobante) {
    document.getElementById('detalle-casa-num').textContent = '#' + casa;
    document.getElementById('detalle-propietario').textContent = prop;
    document.getElementById('detalle-referencia').textContent = ref || '—';
    document.getElementById('detalle-monto').textContent = 'S/ ' + monto;
    var compDiv = document.getElementById('detalle-comprobante');
    if (comprobante) {
        var isImage = comprobante.match(/\.(jpe?g|png|gif|webp|bmp)(\?.*)?$/i);
        if (isImage) {
            compDiv.innerHTML = '<img src="' + encodeURI(comprobante) + '" alt="Comprobante" style="max-width:100%; max-height:240px; border-radius:6px; object-fit:contain;">';
        } else {
            compDiv.innerHTML = '<a href="' + encodeURI(comprobante) + '" target="_blank" style="background:#E0F2FE; color:#0369A1; padding:10px 20px; border-radius:6px; text-decoration:none; font-weight:600;">📄 Ver PDF</a>';
        }
    } else {
        compDiv.innerHTML = '<span style="color:#9CA3AF;">Sin comprobante</span>';
    }
    document.getElementById('modal-detalle-cobro').style.display = 'flex';
}
function closeDetalleCobro() {
    document.getElementById('modal-detalle-cobro').style.display = 'none';
}

function openEditarCobro(id, vivienda_id, casa, prop, ref) {
    document.getElementById('edit-cobro-id').value = id;
    document.getElementById('edit-casa-num').textContent = '#' + casa;
    document.getElementById('edit-propietario').textContent = prop;
    document.getElementById('edit-referencia').value = ref !== '' ? ref : '';
    document.getElementById('edit-upload-area').innerHTML = '<div style="font-size:36px;margin-bottom:6px;">📷</div><div style="font-size:13px;color:#6B7280;">Haz clic para cambiar la imagen</div><div style="font-size:11px;color:#9CA3AF;margin-top:2px;">PNG, JPG o PDF · Deja vacío para mantener actual</div>';
    document.getElementById('edit-file-input').value = '';
    document.getElementById('modal-editar-cobro').style.display = 'flex';
}

function closeEditarCobro() {
    document.getElementById('modal-editar-cobro').style.display = 'none';
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>