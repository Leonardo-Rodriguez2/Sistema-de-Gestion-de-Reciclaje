<?php
// views/calle/historial_vivienda.php — Historial completo de pagos por vivienda
global $pdo;
require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../app/helpers.php';
use app\models\mainModel;

if (empty($pdo)) $pdo = (new mainModel())->conectar();

$user = check_dashboard_access([6]);

$calleStmt = $pdo->prepare("SELECT calle_id FROM detalles_encargado_calle WHERE usuario_id = ?");
$calleStmt->execute([$user['id']]);
$calle_id = $calleStmt->fetchColumn();

if (!$calle_id) {
    die("<div class='alert alert-error'>No tienes una calle asignada.</div>");
}

$viviendasStmt = $pdo->prepare("SELECT id, numero_casa, propietario FROM viviendas WHERE calle_id = ? ORDER BY numero_casa");
$viviendasStmt->execute([$calle_id]);
$viviendas = $viviendasStmt->fetchAll(PDO::FETCH_ASSOC);

$vivienda_id = (int)($_GET['vivienda_id'] ?? 0);
$historial = [];
$vivienda_sel = null;
if ($vivienda_id > 0) {
    $vStmt = $pdo->prepare("SELECT * FROM viviendas WHERE id = ? AND calle_id = ?");
    $vStmt->execute([$vivienda_id, $calle_id]);
    $vivienda_sel = $vStmt->fetch(PDO::FETCH_ASSOC);
    if ($vivienda_sel) {
        $hStmt = $pdo->prepare("SELECT c.* FROM cobros c WHERE c.vivienda_id = ? ORDER BY c.anio DESC, c.mes DESC");
        $hStmt->execute([$vivienda_id]);
        $historial = $hStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$meses_nombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

$title = "Historial de Pagos - EcoCusco";
$header_title = "Historial de Pagos por Vivienda";
$header_subtitle = "Revisa el historial completo de pagos de cada vivienda de tu calle.";

ob_start();
?>
<div class="card" style="margin-bottom:20px;">
    <form method="GET" action="router.php" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
        <input type="hidden" name="page" value="historial_vivienda">
        <div style="flex:1; min-width:250px;">
            <label style="font-size:11px; font-weight:700; color:#6B7280; display:block; margin-bottom:4px;">SELECCIONA UNA VIVIENDA</label>
            <select name="vivienda_id" style="width:100%; padding:10px; border:1px solid #E5E7EB; border-radius:6px; font-size:14px;">
                <option value="">— Selecciona —</option>
                <?php foreach($viviendas as $v): ?>
                    <option value="<?= $v['id'] ?>" <?= $v['id'] == $vivienda_id ? 'selected' : '' ?>>
                        Casa #<?= htmlspecialchars($v['numero_casa']) ?> — <?= htmlspecialchars($v['propietario']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn-primary" style="padding:10px 20px;">Ver Historial</button>
    </form>
</div>

<?php if ($vivienda_sel): ?>
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
        <div>
            <h3 style="margin:0;">🏠 Casa #<?= htmlspecialchars($vivienda_sel['numero_casa']) ?></h3>
            <div style="color:#6B7280; font-size:13px;">
                <?= htmlspecialchars($vivienda_sel['propietario']) ?> — <?= htmlspecialchars($vivienda_sel['direccion']) ?>
                <?php if ($vivienda_sel['exento_cobro']): ?>
                    <span style="background:#EDE9FE; color:#6D28D9; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:700; margin-left:8px;">🛡️ EXENTA</span>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $deudaStmt = $pdo->prepare("SELECT COALESCE(SUM(monto),0) FROM cobros WHERE vivienda_id = ? AND estado NOT IN ('Pagado','Anulado')");
        $deudaStmt->execute([$vivienda_id]);
        $deuda_total = (float)$deudaStmt->fetchColumn();
        ?>
        <div style="text-align:right;">
            <div style="font-size:11px; color:#6B7280;">Deuda Acumulada</div>
            <div style="font-size:22px; font-weight:800; color:<?= $deuda_total > 0 ? '#DC2626' : '#059669' ?>;">
                S/ <?= number_format($deuda_total, 2) ?>
            </div>
        </div>
    </div>

    <?php if (empty($historial)): ?>
        <div style="text-align:center; padding:40px; color:#9CA3AF;">
            No hay pagos registrados para esta vivienda.
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F9FAFB; border-bottom:2px solid #E5E7EB; text-align:left;">
                        <th style="padding:10px 14px;">Periodo</th>
                        <th style="padding:10px;">Monto</th>
                        <th style="padding:10px;">Estado</th>
                        <th style="padding:10px;">Referencia</th>
                        <th style="padding:10px;">Comprobante</th>
                        <th style="padding:10px;">Fecha Emisión</th>
                        <th style="padding:10px;">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($historial as $h): ?>
                    <tr style="border-bottom:1px solid #F3F4F6;">
                        <td style="padding:10px 14px; font-weight:600;">
                            <?= $meses_nombres[$h['mes']] ?> <?= $h['anio'] ?>
                        </td>
                        <td style="padding:10px; font-weight:700; color:#059669;">S/ <?= number_format($h['monto'],2) ?></td>
                        <td style="padding:10px;">
                            <span style="background:<?= $h['estado']=='Pagado'?'#D1FAE5':($h['estado']=='Anulado'?'#FEE2E2':'#FEF3C7') ?>; color:<?= $h['estado']=='Pagado'?'#065F46':($h['estado']=='Anulado'?'#991B1B':'#92400E') ?>; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:600;">
                                <?= $h['estado'] == 'Pagado' ? '✓ Pagado' : ($h['estado'] == 'Anulado' ? '✗ Anulado' : '⏳ Pendiente') ?>
                            </span>
                        </td>
                        <td style="padding:10px; font-size:12px; color:#6B7280;"><?= htmlspecialchars($h['referencia_pago'] ?? '—') ?></td>
                        <td style="padding:10px;">
                            <?php if (!empty($h['comprobante_calle'])): ?>
                                <a href="#" data-url="<?= htmlspecialchars($h['comprobante_calle']) ?>" onclick="return openComprobanteModal(this)"
                                   style="background:#E0F2FE; color:#0369A1; padding:3px 8px; border-radius:4px; text-decoration:none; font-size:11px;">📎 Ver</a>
                            <?php else: ?>
                                <span style="color:#9CA3AF;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:10px; font-size:12px; color:#6B7280;"><?= $h['fecha_emision'] ? date('d/m/Y H:i', strtotime($h['fecha_emision'])) : '—' ?></td>
                        <td style="padding:10px; font-size:11px; color:#6B7280; max-width:200px;"><?= htmlspecialchars($h['observaciones'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php elseif ($vivienda_id > 0): ?>
    <div class="alert alert-error">Vivienda no encontrada o no pertenece a tu calle.</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
