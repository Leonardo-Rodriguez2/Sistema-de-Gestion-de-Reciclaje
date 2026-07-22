<?php
global $pdo;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../app/helpers.php';

use app\models\mainModel;

if (empty($pdo)) {
    $pdo = (new mainModel())->conectar();
}

$user = check_dashboard_access([5]);
$page = 'seguimiento_pagos';

$barrioStmt = $pdo->prepare("SELECT b.id, b.nombre FROM detalles_encargado_barrio d JOIN barrios b ON d.barrio_id = b.id WHERE d.usuario_id = ?");
$barrioStmt->execute([$user['id']]);
$barrio = $barrioStmt->fetch(PDO::FETCH_ASSOC);
$barrio_id = $barrio['id'] ?? 0;

$sql = "SELECT v.id, v.propietario, v.numero_casa, v.direccion, v.estado_servicio, c.id as cobro_id, c.monto, c.estado, c.tipo_cobro, c.mes, c.anio, c.comprobante_calle, c.comprobante_barrio, c.observaciones, c.fecha_confirmacion_calle, c.fecha_confirmacion_barrio
        FROM viviendas v
        LEFT JOIN cobros c ON c.vivienda_id = v.id
        WHERE v.barrio_id = ?
        ORDER BY v.numero_casa, c.mes, c.anio";
$stmt = $pdo->prepare($sql);
$stmt->execute([$barrio_id]);
$casas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Seguimiento de Pagos - EcoCusco";
$header_title = "Seguimiento por Casa";
$header_subtitle = "Revisa qué casas pagaron, cuáles faltan y qué evidencia se recibió.";

ob_start();
?>
<div class="card">
  <h3 style="margin-top:0; color:#111827;">🏠 Estado de pagos por casa</h3>
  <div class="table-wrap">
    <table class="table-mini" style="width:100%; border-collapse:collapse;">
      <thead>
        <tr style="text-align:left; border-bottom:2px solid #F3F4F6;">
          <th style="padding:12px;">Casa</th>
          <th style="padding:12px;">Propietario</th>
          <th style="padding:12px;">Periodo</th>
          <th style="padding:12px;">Estado</th>
          <th style="padding:12px;">Comprobante</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($casas as $c): ?>
          <tr style="border-bottom:1px solid #F3F4F6;">
            <td style="padding:12px; font-weight:700;">#<?= htmlspecialchars($c['numero_casa'] ?? '') ?></td>
            <td style="padding:12px;"><?= htmlspecialchars($c['propietario']) ?></td>
            <td style="padding:12px;"><?= $c['mes'] ? $c['mes'] . '/' . $c['anio'] : 'Sin periodo' ?></td>
            <td style="padding:12px;">
              <?php $estado = $c['estado'] ?? 'Sin registro'; ?>
              <span class="badge" style="background: <?= $estado === 'Pagado' ? '#D1FAE5' : '#FEE2E2' ?>; color: <?= $estado === 'Pagado' ? '#065F46' : '#991B1B' ?>;">
                <?= htmlspecialchars($estado) ?>
              </span>
            </td>
            <td style="padding:12px;">
              <?php if (!empty($c['comprobante_barrio']) || !empty($c['comprobante_calle'])): ?>
                <?php $img = $c['comprobante_barrio'] ?: $c['comprobante_calle']; ?>
                <a href="#" data-url="<?= htmlspecialchars($img) ?>" onclick="return openComprobanteModal(this)" class="badge" style="background:#E0F2FE;color:#0369A1;text-decoration:none;">Ver evidencia</a>
              <?php else: ?>
                <span style="color:#9CA3AF;">Sin comprobante</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
