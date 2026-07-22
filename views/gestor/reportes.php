<?php
global $pdo;
require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../app/helpers.php';
use app\models\mainModel;
if (empty($pdo)) $pdo = (new mainModel())->conectar();

$user = check_dashboard_access([1, 2, 5]);
$page = 'reportes';

$tab = $_GET['tab'] ?? 'recaudacion';
$meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

$title = "Reportes - EPSIC";
$header_title = "Reportes y Estadísticas";
$header_subtitle = "Visualiza la recaudación, morosos y lotes cerrados por periodo.";

ob_start();
?>
<?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

<!-- Pestañas -->
<div style="display:flex; gap:6px; margin-bottom:20px; flex-wrap:wrap; border-bottom:2px solid #E5E7EB; padding-bottom:10px;">
  <a href="router.php?page=reportes&tab=recaudacion" class="btn-primary" style="text-decoration:none; background:<?= $tab==='recaudacion'?'#10B981':'#6B7280' ?>;">💰 Recaudación</a>
  <a href="router.php?page=reportes&tab=morosos" class="btn-primary" style="text-decoration:none; background:<?= $tab==='morosos'?'#DC2626':'#6B7280' ?>;">⛔ Morosos</a>
  <a href="router.php?page=reportes&tab=lotes" class="btn-primary" style="text-decoration:none; background:<?= $tab==='lotes'?'#3B82F6':'#6B7280' ?>;">📦 Lotes Cerrados</a>
  <a href="router.php?page=reportes&tab=general" class="btn-primary" style="text-decoration:none; background:<?= $tab==='general'?'#8B5CF6':'#6B7280' ?>;">📊 General</a>
</div>

<?php if ($tab === 'recaudacion'): ?>
<?php
$desde = $_GET['desde'] ?? date('Y-m', strtotime('-3 months'));
$hasta = $_GET['hasta'] ?? date('Y-m');

// Parse periodo
$d = explode('-', $desde); $dmes = (int)$d[1]; $danio = (int)$d[0];
$h = explode('-', $hasta); $hmes = (int)$h[1]; $hanio = (int)$h[0];

$stmt = $pdo->prepare("
  SELECT c.mes, c.anio, b.nombre as barrio, SUM(c.monto) as total, COUNT(c.id) as transacciones
  FROM cobros c
  JOIN viviendas v ON c.vivienda_id = v.id
  JOIN barrios b ON v.barrio_id = b.id
  WHERE c.estado = 'Pagado'
    AND (c.anio > ? OR (c.anio = ? AND c.mes >= ?))
    AND (c.anio < ? OR (c.anio = ? AND c.mes <= ?))
  GROUP BY c.anio, c.mes, v.barrio_id
  ORDER BY c.anio DESC, c.mes DESC, b.nombre
");
$stmt->execute([$danio, $danio, $dmes, $hanio, $hanio, $hmes]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$gran_total = 0;
foreach ($rows as $r) $gran_total += $r['total'];
?>
<div class="card">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
    <h3 style="margin:0;">💰 Recaudación por Periodo</h3>
    <form method="GET" action="router.php" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
      <input type="hidden" name="page" value="reportes">
      <input type="hidden" name="tab" value="recaudacion">
      <label style="font-size:12px; color:#6B7280;">Desde:</label>
      <input type="month" name="desde" value="<?= $desde ?>" style="padding:5px 8px; border:1px solid #D1D5DB; border-radius:6px; font-size:12px;">
      <label style="font-size:12px; color:#6B7280;">Hasta:</label>
      <input type="month" name="hasta" value="<?= $hasta ?>" style="padding:5px 8px; border:1px solid #D1D5DB; border-radius:6px; font-size:12px;">
      <button type="submit" class="btn-primary" style="padding:5px 12px; font-size:12px;">Filtrar</button>
    </form>
  </div>
  <div style="font-size:24px; font-weight:800; color:#059669; margin-bottom:15px;">
    Total recaudado: S/ <?= number_format($gran_total, 2) ?>
  </div>
  <?php if (empty($rows)): ?>
    <div style="text-align:center; padding:30px; color:#9CA3AF;">Sin datos para el periodo seleccionado.</div>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Periodo</th><th>Barrio</th><th>Transacciones</th><th>Total</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td style="padding:10px;"><?= $meses[$r['mes']] ?> <?= $r['anio'] ?></td>
          <td style="padding:10px;"><?= htmlspecialchars($r['barrio']) ?></td>
          <td style="padding:10px;"><?= $r['transacciones'] ?></td>
          <td style="padding:10px; font-weight:700; color:#059669;">S/ <?= number_format($r['total'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($tab === 'morosos'): ?>
<?php
$barrio_id_m = (int)($_GET['barrio_id'] ?? 0);

$barrios = $pdo->query("SELECT id, nombre FROM barrios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT v.id, v.numero_casa, v.propietario, v.direccion, b.nombre as barrio, c.nombre as calle,
               SUM(cb.monto) as deuda_total, COUNT(cb.id) as meses_deuda
        FROM viviendas v
        JOIN barrios b ON v.barrio_id = b.id
        JOIN calles c ON v.calle_id = c.id
        LEFT JOIN cobros cb ON cb.vivienda_id = v.id AND cb.estado != 'Pagado'
        WHERE v.estado_servicio = 'Activo'";
$params = [];
if ($barrio_id_m) {
  $sql .= " AND v.barrio_id = ?";
  $params[] = $barrio_id_m;
}
$sql .= " GROUP BY v.id HAVING deuda_total > 0 ORDER BY deuda_total DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$morosos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card">
  <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
    <h3 style="margin:0;">⛔ Casas Morosas</h3>
    <form method="GET" action="router.php" style="display:flex; gap:8px; align-items:center;">
      <input type="hidden" name="page" value="reportes">
      <input type="hidden" name="tab" value="morosos">
      <select name="barrio_id" style="padding:5px 8px; border:1px solid #D1D5DB; border-radius:6px; font-size:12px;">
        <option value="0">Todos los barrios</option>
        <?php foreach ($barrios as $b): ?>
          <option value="<?= $b['id'] ?>" <?= $barrio_id_m==$b['id']?'selected':'' ?>><?= htmlspecialchars($b['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-primary" style="padding:5px 12px; font-size:12px;">Filtrar</button>
    </form>
  </div>
  <?php if (empty($morosos)): ?>
    <div style="text-align:center; padding:30px; color:#9CA3AF;">No hay casas morosas<?= $barrio_id_m?' en este barrio':'' ?>.</div>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Casa</th><th>Propietario</th><th>Barrio</th><th>Calle</th><th>Meses Deuda</th><th>Deuda Total</th></tr>
      </thead>
      <tbody>
        <?php foreach ($morosos as $m): ?>
        <tr>
          <td style="padding:10px; font-weight:700;">#<?= htmlspecialchars($m['numero_casa']) ?></td>
          <td style="padding:10px;"><?= htmlspecialchars($m['propietario']) ?></td>
          <td style="padding:10px;"><?= htmlspecialchars($m['barrio']) ?></td>
          <td style="padding:10px;"><?= htmlspecialchars($m['calle']) ?></td>
          <td style="padding:10px;"><?= $m['meses_deuda'] ?></td>
          <td style="padding:10px; font-weight:700; color:#DC2626;">S/ <?= number_format($m['deuda_total'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($tab === 'lotes'): ?>
<?php
$lotes = $pdo->query("
  SELECT lb.*, b.nombre as barrio_nombre, u.nombre as encargado, rf.numero_recibo
  FROM lotes_barrio lb
  JOIN barrios b ON lb.barrio_id = b.id
  LEFT JOIN usuarios u ON lb.encargado_barrio_id = u.id
  LEFT JOIN recibos_finiquito rf ON rf.lote_barrio_id = lb.id
  ORDER BY lb.fecha_envio DESC
  LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card">
  <h3 style="margin-top:0;">📦 Lotes de Barrio Cerrados</h3>
  <?php if (empty($lotes)): ?>
    <div style="text-align:center; padding:30px; color:#9CA3AF;">No hay lotes registrados.</div>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Barrio</th><th>Periodo</th><th>Estado</th><th>Esperado</th><th>Recolectado</th><th>Recibo</th><th>Envío</th></tr>
      </thead>
      <tbody>
        <?php foreach ($lotes as $l):
          $ec = ['Enviado'=>'#F59E0B','Aprobado'=>'#10B981','Rechazado'=>'#DC2626'];
        ?>
        <tr>
          <td style="padding:10px; font-weight:700;"><?= htmlspecialchars($l['barrio_nombre']) ?></td>
          <td style="padding:10px;"><?= $meses[$l['periodo_mes']] ?> <?= $l['periodo_anio'] ?></td>
          <td style="padding:10px;"><span class="badge" style="background:<?= ($ec[$l['estado']]??'#6B7280') ?>22; color:<?= ($ec[$l['estado']]??'#6B7280') ?>;"><?= $l['estado'] ?></span></td>
          <td style="padding:10px;">S/ <?= number_format($l['monto_total_esperado'],2) ?></td>
          <td style="padding:10px; font-weight:700; color:#059669;">S/ <?= number_format($l['monto_total_recolectado'],2) ?></td>
          <td style="padding:10px;"><?= $l['numero_recibo'] ? htmlspecialchars($l['numero_recibo']) : '—' ?></td>
          <td style="padding:10px; font-size:12px; color:#6B7280;"><?= $l['fecha_envio'] ? date('d/m/Y', strtotime($l['fecha_envio'])) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($tab === 'general'): ?>
<?php
$g = $pdo->query("
  SELECT
    (SELECT COUNT(*) FROM viviendas WHERE estado_servicio='Activo') as activas,
    (SELECT COUNT(*) FROM viviendas WHERE estado_servicio='Suspendido') as suspendidas,
    (SELECT COUNT(*) FROM viviendas WHERE estado_servicio='Anulado') as anuladas,
    (SELECT COUNT(*) FROM barrios) as barrios,
    (SELECT COUNT(*) FROM calles) as calles,
    (SELECT COUNT(*) FROM usuarios) as usuarios,
    (SELECT SUM(monto_total_recolectado) FROM lotes_barrio WHERE estado='Aprobado') as total_aprobado,
    (SELECT COUNT(*) FROM lotes_barrio WHERE estado='Aprobado') as lotes_aprobados,
    (SELECT SUM(monto) FROM cobros WHERE estado='Pagado' AND YEAR(fecha_emision)=YEAR(CURDATE())) as anual
")->fetch(PDO::FETCH_ASSOC);
?>
<div class="grid" style="margin-bottom:20px;">
  <div class="card" style="text-align:center;"><div style="font-size:11px;color:#6B7280;">Casas Activas</div><div style="font-size:28px;font-weight:800;color:#10B981;"><?= $g['activas'] ?></div></div>
  <div class="card" style="text-align:center;"><div style="font-size:11px;color:#6B7280;">Suspendidas</div><div style="font-size:28px;font-weight:800;color:#F59E0B;"><?= $g['suspendidas'] ?></div></div>
  <div class="card" style="text-align:center;"><div style="font-size:11px;color:#6B7280;">Anuladas</div><div style="font-size:28px;font-weight:800;color:#DC2626;"><?= $g['anuladas'] ?></div></div>
  <div class="card" style="text-align:center;"><div style="font-size:11px;color:#6B7280;">Barrios</div><div style="font-size:28px;font-weight:800;color:#8B5CF6;"><?= $g['barrios'] ?></div></div>
  <div class="card" style="text-align:center;"><div style="font-size:11px;color:#6B7280;">Calles</div><div style="font-size:28px;font-weight:800;color:#3B82F6;"><?= $g['calles'] ?></div></div>
  <div class="card" style="text-align:center;"><div style="font-size:11px;color:#6B7280;">Usuarios</div><div style="font-size:28px;font-weight:800;color:#4B5563;"><?= $g['usuarios'] ?></div></div>
  <div class="card" style="text-align:center;"><div style="font-size:11px;color:#6B7280;">Lotes Aprobados</div><div style="font-size:28px;font-weight:800;color:#059669;"><?= $g['lotes_aprobados'] ?></div></div>
  <div class="card" style="text-align:center;"><div style="font-size:11px;color:#6B7280;">Total Aprobado</div><div style="font-size:28px;font-weight:800;color:#059669;">S/ <?= number_format($g['total_aprobado']??0,0) ?></div></div>
  <div class="card" style="text-align:center; grid-column: span 2;"><div style="font-size:11px;color:#6B7280;">Recaudación Anual (este año)</div><div style="font-size:28px;font-weight:800;color:#059669;">S/ <?= number_format($g['anual']??0,2) ?></div></div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
