<?php
// views/gestor/viviendas.php
global $pdo;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../app/helpers.php';

use app\models\mainModel;

if (empty($pdo)) {
    $pdo = (new mainModel())->conectar();
}

$user = check_dashboard_access([1, 2]); // Admin o Gestor
$page = 'viviendas';
$sid = $_GET['sid'] ?? '';

// Filtros
$f_barrio = (int)($_GET['barrio_id'] ?? 0);
$f_search = trim($_GET['search'] ?? '');

$estadoFiltro = $_GET['estado_pago'] ?? 'todos';

$mes_actual = date('n');
$anio_actual = date('Y');
$sql = "SELECT v.*, b.nombre as barrio_nombre, c.nombre as calle_nombre,
        (SELECT COUNT(*) FROM cobros co WHERE co.vivienda_id = v.id AND co.estado NOT IN ('Pagado','Anulado')) as deudas_pendientes,
        (SELECT COALESCE(SUM(co.monto),0) FROM cobros co WHERE co.vivienda_id = v.id AND co.estado NOT IN ('Pagado','Anulado')) as deuda_total,
        (SELECT co2.estado FROM cobros co2 WHERE co2.vivienda_id = v.id AND co2.mes = $mes_actual AND co2.anio = $anio_actual LIMIT 1) as pago_mes_estado
        FROM viviendas v 
        LEFT JOIN barrios b ON v.barrio_id = b.id 
        LEFT JOIN calles c ON v.calle_id = c.id
        WHERE 1=1";

$params = [];
if ($f_barrio > 0) {
    $sql .= " AND v.barrio_id = :barrio";
    $params[':barrio'] = $f_barrio;
}
if ($f_search !== '') {
    $sql .= " AND (v.propietario LIKE :search OR v.direccion LIKE :search)";
    $params[':search'] = "%$f_search%";
}
if ($estadoFiltro === 'pagado') {
    $sql .= " AND (SELECT COUNT(*) FROM cobros co WHERE co.vivienda_id = v.id AND co.estado = 'Pagado') > 0";
} elseif ($estadoFiltro === 'pendiente') {
    $sql .= " AND (SELECT COUNT(*) FROM cobros co WHERE co.vivienda_id = v.id AND co.estado != 'Pagado') > 0";
}

$sql .= " ORDER BY b.nombre, c.nombre";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$viviendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$barrios = $pdo->query("SELECT id, nombre FROM barrios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

$title = "Estado de Viviendas - EcoCusco";
$header_title = "Consulta de Viviendas";
$header_subtitle = "Revisión técnica de predios para control de pagos.";

ob_start();
?>
    <div class="card" style="margin-bottom: 14px; padding: 15px;">
        <form method="GET" action="router.php" style="display: flex; gap: 15px; align-items: flex-end;">
            <input type="hidden" name="page" value="viviendas">
            <input type="hidden" name="sid" value="<?= htmlspecialchars($sid) ?>">
            <div style="flex: 1;">
                <label style="font-size: 11px; font-weight: 700; color: #6B7280;">BUSCAR PROPIETARIO</label>
                <input type="text" name="search" value="<?= htmlspecialchars($f_search) ?>" placeholder="Nombre..." style="width: 100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 6px;">
            </div>
            <div style="width: 200px;">
                <label style="font-size: 11px; font-weight: 700; color: #6B7280;">BARRIO</label>
                <select name="barrio_id" style="width: 100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 6px;">
                    <option value="">Todos</option>
                    <?php foreach($barrios as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $f_barrio == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="width: 180px;">
                <label style="font-size: 11px; font-weight: 700; color: #6B7280;">ESTADO PAGO</label>
                <select name="estado_pago" style="width: 100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 6px;">
                    <option value="todos" <?= $estadoFiltro === 'todos' ? 'selected' : '' ?>>Todos</option>
                    <option value="pagado" <?= $estadoFiltro === 'pagado' ? 'selected' : '' ?>>Con pagos</option>
                    <option value="pendiente" <?= $estadoFiltro === 'pendiente' ? 'selected' : '' ?>>Con deuda</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Filtrar</button>
        </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrap">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background: #F9FAFB; text-align: left; color:#374151;">
                    <th style="padding: 10px 14px;">Propietario</th>
                    <th style="padding: 10px;">Ubicación</th>
                    <th style="padding: 10px;">Barrio</th>
                    <th style="padding: 10px; text-align: center;">Estado</th>
                    <th style="padding: 10px; text-align: center;">Deuda</th>
                    <th style="padding: 10px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($viviendas as $v): ?>
                <tr style="border-bottom: 1px solid #F3F4F6; <?= $v['exento_cobro'] ? 'background:#F5F3FF;' : '' ?>">
                    <td style="padding: 10px 14px; font-weight: 600;">
                        <?= htmlspecialchars($v['propietario']) ?>
                        <?php if ($v['exento_cobro']): ?>
                            <span style="background:#EDE9FE; color:#6D28D9; padding:1px 6px; border-radius:4px; font-size:9px; font-weight:700; margin-left:4px;">EXENTA</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 10px; color: #6B7280;"><?= htmlspecialchars($v['calle_nombre'] ?? 'Sin calle') ?> #<?= htmlspecialchars($v['numero_casa']) ?></td>
                    <td style="padding: 10px;"><?= htmlspecialchars($v['barrio_nombre']) ?></td>
                    <td style="padding: 10px; text-align: center;">
                        <?php
                        if ($v['exento_cobro']):
                            $bg = '#EDE9FE'; $color = '#6D28D9'; $text = '🛡️ Exenta';
                        elseif ($v['pago_mes_estado'] === 'Pagado'):
                            $bg = '#D1FAE5'; $color = '#065F46'; $text = '✓ Pagado';
                        else:
                            $bg = '#FEE2E2'; $color = '#991B1B'; $text = '⏳ Pendiente';
                        endif;
                        ?>
                        <span class="badge" style="background: <?= $bg ?>; color: <?= $color ?>; border:none;"><?= $text ?></span>
                    </td>
                    <td style="padding: 10px; text-align: center; font-weight:700;">
                        <?php if ((float)$v['deuda_total'] > 0 && !$v['exento_cobro']): ?>
                            <span style="color:#DC2626;">S/ <?= number_format((float)$v['deuda_total'],2) ?></span>
                        <?php else: ?>
                            <span style="color:#9CA3AF;">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <button onclick="toggleHistorial(<?= $v['id'] ?>)" style="background:transparent; color:#6B7280; border:1px solid #E5E7EB; padding:4px 8px; border-radius:4px; font-size:10px; cursor:pointer;">📋 Historial</button>
                        <div id="historial-<?= $v['id'] ?>" style="display:none; margin-top:8px; background:#F9FAFB; border-radius:6px; padding:10px; border:1px solid #E5E7EB; text-align:left; position:absolute; z-index:10; box-shadow:0 4px 12px rgba(0,0,0,0.1); width:280px;">
                            <?php
                            $histStmt = $pdo->prepare("SELECT c.* FROM cobros c WHERE c.vivienda_id = ? ORDER BY c.anio DESC, c.mes DESC LIMIT 12");
                            $histStmt->execute([$v['id']]);
                            $historial = $histStmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <div style="font-weight:700; font-size:11px; margin-bottom:6px;">📋 Historial de Pagos</div>
                            <?php if(empty($historial)): ?>
                                <div style="font-size:10px; color:#9CA3AF;">Sin movimientos registrados.</div>
                            <?php else: ?>
                                <table style="width:100%; border-collapse:collapse; font-size:10px;">
                                    <thead><tr style="border-bottom:1px solid #E5E7EB;">
                                        <th style="padding:3px; text-align:left;">Periodo</th>
                                        <th style="padding:3px;">Monto</th>
                                        <th style="padding:3px;">Estado</th>
                                    </tr></thead>
                                    <tbody>
                                    <?php foreach($historial as $h): ?>
                                        <tr style="border-bottom:1px solid #F3F4F6;">
                                            <td style="padding:3px;"><?= $h['mes'] ?>/<?= $h['anio'] ?></td>
                                            <td style="padding:3px; font-weight:600;">S/ <?= number_format($h['monto'],2) ?></td>
                                            <td style="padding:3px;">
                                                <span style="background:<?= $h['estado']=='Pagado'?'#D1FAE5':'#FEE2E2' ?>; color:<?= $h['estado']=='Pagado'?'#065F46':'#991B1B' ?>; padding:1px 5px; border-radius:3px; font-size:9px;">
                                                    <?= $h['estado'] == 'Pagado' ? '✓ Pagado' : ($h['estado'] == 'Anulado' ? '✗ Anulado' : '⏳ Pendiente') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                            <button onclick="document.getElementById('historial-<?= $v['id'] ?>').style.display='none'" style="margin-top:6px; background:#F3F4F6; border:none; padding:3px 8px; border-radius:4px; font-size:9px; cursor:pointer; width:100%;">Cerrar</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($viviendas)): ?>
                <tr><td colspan="6" style="padding:30px; text-align:center; color:#9CA3AF;">No se encontraron viviendas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

<script>
function toggleHistorial(id) {
    var div = document.getElementById('historial-' + id);
    if (div.style.display === 'none') {
        document.querySelectorAll('[id^="historial-"]').forEach(function(el) { el.style.display = 'none'; });
        div.style.display = 'block';
    } else {
        div.style.display = 'none';
    }
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="historial-"]') && !e.target.closest('button[onclick*="toggleHistorial"]')) {
        document.querySelectorAll('[id^="historial-"]').forEach(function(el) { el.style.display = 'none'; });
    }
});
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
