<?php
// views/admin/monitor_pagos.php
$user = check_dashboard_access([1]);

// 1. Obtener resumen por barrio del mes actual
$mes = date('n'); $anio = date('Y');

$sql = "SELECT b.id, b.nombre,
          (SELECT COUNT(*) FROM viviendas WHERE barrio_id = b.id) as total_casas,
          (SELECT COUNT(*) FROM cobros c JOIN viviendas v ON c.vivienda_id = v.id 
           WHERE v.barrio_id = b.id AND c.mes = :mes AND c.anio = :anio AND c.estado = 'Pagado' AND c.tipo_cobro = 'Servicio') as casas_pagadas,
          (SELECT SUM(monto) FROM cobros c JOIN viviendas v ON c.vivienda_id = v.id 
           WHERE v.barrio_id = b.id AND c.mes = :mes AND c.anio = :anio AND c.estado = 'Pagado') as total_recaudado
        FROM barrios b";

$stmt = $pdo->prepare($sql);
$stmt->execute([':mes' => $mes, ':anio' => $anio]);
$resumen_barrios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Monitor de Pagos - EPSIC";
$header_title = "Fiscalización de Ingresos";
$header_subtitle = "Seguimiento en tiempo real del cumplimiento por barrio.";

ob_start();
?>
    <div class="card" style="margin-bottom: 25px;">
        <h3 style="margin-top:0;">🌐 Estado General del Mes (<?= date('F Y') ?>)</h3>
        <div style="overflow-x: auto;">
            <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                        <th style="padding: 15px;">Barrio</th>
                        <th style="padding: 15px; text-align: center;">Cumplimiento</th>
                        <th style="padding: 15px; text-align: center;">Casas Pagadas</th>
                        <th style="padding: 15px; text-align: center;">Total Recaudado</th>
                        <th style="padding: 15px; text-align: center;">Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($resumen_barrios as $b): 
                        $porc = ($b['total_casas'] > 0) ? ($b['casas_pagadas'] / $b['total_casas']) * 100 : 0;
                        $color = ($porc >= 80) ? '#10B981' : (($porc >= 50) ? '#F59E0B' : '#EF4444');
                    ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 15px; font-weight: 700;"><?= htmlspecialchars($b['nombre']) ?></td>
                            <td style="padding: 15px; text-align: center;">
                                <div style="display: flex; align-items: center; gap: 10px; justify-content: center;">
                                    <div style="width: 100px; height: 8px; background: #F3F4F6; border-radius: 10px; overflow: hidden;">
                                        <div style="width: <?= $porc ?>%; height: 100%; background: <?= $color ?>;"></div>
                                    </div>
                                    <span style="font-weight: 800; color: <?= $color ?>;"><?= round($porc) ?>%</span>
                                </div>
                            </td>
                            <td style="padding: 15px; text-align: center; font-weight: 600;">
                                <?= $b['casas_pagadas'] ?> / <?= $b['total_casas'] ?>
                            </td>
                            <td style="padding: 15px; text-align: center; font-weight: 800; color: #059669;">
                                S/ <?= number_format($b['total_recaudado'] ?? 0, 2) ?>
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <a href="router.php?page=monitor_pagos&barrio_id=<?= $b['id'] ?>#detalle" class="badge" style="background: #E0F2FE; color: #0369A1; text-decoration: none;">Ver Morosos</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php 
    $barrio_id = (int)($_GET['barrio_id'] ?? 0);
    if ($barrio_id > 0): 
        $bNameStmt = $pdo->prepare("SELECT nombre FROM barrios WHERE id = ?");
        $bNameStmt->execute([$barrio_id]);
        $bName = $bNameStmt->fetchColumn();

        // Obtener casas de este barrio que NO han pagado el mes actual
        $sqlMorosos = "SELECT v.*, c.nombre as calle_nombre 
                       FROM viviendas v 
                       LEFT JOIN calles c ON v.calle_id = c.id
                       WHERE v.barrio_id = ? 
                       AND v.id NOT IN (SELECT vivienda_id FROM cobros WHERE mes = ? AND anio = ? AND estado = 'Pagado' AND tipo_cobro = 'Servicio')
                       ORDER BY c.nombre, v.numero_casa";
        $stmtM = $pdo->prepare($sqlMorosos);
        $stmtM->execute([$barrio_id, $mes, $anio]);
        $morosos = $stmtM->fetchAll(PDO::FETCH_ASSOC);
    ?>
        <div id="detalle" class="card" style="border-top: 4px solid #EF4444;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; color: #EF4444;">🚨 Viviendas Pendientes: <?= htmlspecialchars($bName) ?></h3>
                <span class="badge" style="background: #FEE2E2; color: #991B1B; font-weight: 800;"><?= count($morosos) ?> casas sin pago</span>
            </div>
            <div style="overflow-x: auto;">
                <table class="table-mini" style="width: 100%;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                            <th>Calle</th>
                            <th>Propietario / Casa</th>
                            <th>Dirección</th>
                            <th style="text-align: center;">Estado Servicio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($morosos as $m): ?>
                            <tr style="border-bottom: 1px solid #F3F4F6;">
                                <td style="padding: 10px; font-weight: 600;"><?= htmlspecialchars($m['calle_nombre'] ?? 'S/N') ?></td>
                                <td style="padding: 10px;">
                                    <strong><?= htmlspecialchars($m['propietario']) ?></strong><br>
                                    <small>Casa #<?= htmlspecialchars($m['numero_casa']) ?></small>
                                </td>
                                <td style="padding: 10px; color: #6B7280; font-size: 11px;"><?= htmlspecialchars($m['direccion']) ?></td>
                                <td style="padding: 10px; text-align: center;">
                                    <?php $isSusp = ($m['estado_servicio'] == 'Suspendido'); ?>
                                    <span class="badge" style="background: <?= $isSusp ? '#FEE2E2' : '#FEF3C7' ?>; color: <?= $isSusp ? '#991B1B' : '#92400E' ?>;">
                                        <?= $m['estado_servicio'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($morosos)): ?>
                            <tr><td colspan="4" style="text-align: center; padding: 20px; color: #10B981; font-weight: 700;">¡Todo el barrio está al día! 👏</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
