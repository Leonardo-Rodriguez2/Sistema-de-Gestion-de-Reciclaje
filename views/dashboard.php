// views/dashboard.php
<?php
require_once '../data/estadisticas.php';
require_once '../data/conexion.php';
require_once '../includes/header.php';
?>

<div class="stats-grid">
  <?php foreach($estadisticas as $tipo=>$d):
    $cls = ($d['pct']!==null ? ($d['pct']>=0 ? 'positive':'negative') : '');
    $txt = ($d['pct']!==null ? (($d['pct']>=0 ? '+' : '').$d['pct'].'%') : '—');
  ?>
  <div class="stat-card">
    <h3><?= $tipo==='Total'?'Total Reciclado':$tipo ?></h3>
    <div class="stat-value"><?= number_format($d['actual'],2) ?> kg</div>
    <div class="stat-trend <?= $cls ?>"><?= $txt ?> vs <?= $mesAnteriorObj->format('M Y') ?></div>
  </div>
  <?php endforeach; ?>
</div>

<div class="charts-container">
  <div class="chart-card">
    <h2>Progreso Mensual (<?= $anioActual ?>)</h2>
    <canvas id="chartMensual"></canvas>
  </div>
  <div class="chart-card">
    <h2>Distribución por Material</h2>
    <canvas id="chartDistribucion"></canvas>
  </div>
</div>

<div class="activity-table" id="actividad">
  <div class="header">
    <h2>Actividad Reciente</h2>
    <button id="verMas" onclick="cargarMas()">Ver más</button>
  </div>
  <table>
    <thead><tr><th>Fecha</th><th>Material</th><th>Cantidad</th><th>Ubicación</th><th>Estado</th></tr></thead>
    <tbody id="tablaActividades">
      <?php foreach($reportes as $f): ?>
      <tr>
        <td><?= date('d/m/Y',strtotime($f['fecha_reporte'])) ?></td>
        <td><?= $f['tipo_residuo'] ?></td>
        <td><?= number_format($f['cantidad'],2) ?> kg</td>
        <td><?= $f['ubicacion_nombre'] ?></td>
        <td><span class="status <?= str_replace(' ','-',$f['estado']) ?>"><?= $f['estado'] ?></span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once '../includes/footer.php'; ?>
