<?php
// views/barrio/viviendas.php
$user = check_dashboard_access([5]);

// 1. Obtener el barrio asignado
$barrioStmt = $pdo->prepare("SELECT barrio_id FROM detalles_encargado_barrio WHERE usuario_id = ?");
$barrioStmt->execute([$user['id']]);
$barrio_id = $barrioStmt->fetchColumn();

// 2. Obtener filtros
$f_calle = (int)($_GET['calle_id'] ?? 0);
$f_search = trim($_GET['search'] ?? '');
$f_estado = $_GET['estado'] ?? '';

// 3. Preparar consulta con filtros
$sql = "SELECT v.*, b.nombre as barrio_nombre, c.nombre as calle_nombre 
        FROM viviendas v 
        JOIN barrios b ON v.barrio_id = b.id 
        LEFT JOIN calles c ON v.calle_id = c.id
        WHERE v.barrio_id = :barrio
        AND v.id NOT IN (SELECT sv.vivienda_id FROM solicitudes_vivienda sv WHERE sv.tipo = 'Baja' AND sv.estado = 'Pendiente' AND sv.vivienda_id IS NOT NULL)";

$params = [':barrio' => $barrio_id];
if ($f_calle > 0) {
    $sql .= " AND v.calle_id = :calle";
    $params[':calle'] = $f_calle;
}
if ($f_search !== '') {
    $sql .= " AND (v.propietario LIKE :search OR v.direccion LIKE :search OR v.numero_casa LIKE :search)";
    $params[':search'] = "%$f_search%";
}

// Filtro de estado de pago (complejo porque depende de la tabla cobros)
if ($f_estado !== '') {
    $mes = date('n'); $anio = date('Y');
    if ($f_estado === 'Pagado') {
        $sql .= " AND v.id IN (SELECT vivienda_id FROM cobros WHERE mes = $mes AND anio = $anio AND estado = 'Pagado')";
    } elseif ($f_estado === 'Pendiente') {
        $sql .= " AND v.id IN (SELECT vivienda_id FROM cobros WHERE mes = $mes AND anio = $anio AND estado != 'Pagado')";
    } elseif ($f_estado === 'Sin Cobro') {
        $sql .= " AND v.id NOT IN (SELECT vivienda_id FROM cobros WHERE mes = $mes AND anio = $anio)";
    }
}

$sql .= " ORDER BY c.nombre, v.numero_casa";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$viviendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Obtener calles del barrio para el filtro
$callesStmt = $pdo->prepare("SELECT id, nombre FROM calles WHERE barrio_id = ? ORDER BY nombre");
$callesStmt->execute([$barrio_id]);
$calles = $callesStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Viviendas del Barrio - EcoCusco";
$header_title = "Gestión del Barrio";
$header_subtitle = "Revisión completa de todas las viviendas y su estado.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Barra de Filtros -->
    <style>@media(max-width:600px){.bv-filter{min-width:100%!important;width:100%!important}}</style>
    <div class="card" style="margin-bottom: 20px; padding: 15px;">
        <form method="GET" action="router.php" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="page" value="viviendas">
            
            <div class="form-group bv-filter" style="flex: 1; min-width: 250px;">
                <label style="font-size: 11px; font-weight: 700; color: #6B7280; margin-bottom: 4px; display: block;">BUSCAR PROPIETARIO / DIR</label>
                <input type="text" name="search" value="<?= htmlspecialchars($f_search) ?>" placeholder="Nombre, calle, número..." 
                       style="width: 100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 13px;">
            </div>

            <div class="form-group bv-filter" style="width: 150px;">
                <label style="font-size: 11px; font-weight: 700; color: #6B7280; margin-bottom: 4px; display: block;">ESTADO PAGO</label>
                <select name="estado" onchange="this.form.submit()" style="width: 100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 13px;">
                    <option value="">Todos</option>
                    <option value="Pagado" <?= $f_estado == 'Pagado' ? 'selected' : '' ?>>Pagado</option>
                    <option value="Pendiente" <?= $f_estado == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="Sin Cobro" <?= $f_estado == 'Sin Cobro' ? 'selected' : '' ?>>Sin Cobro</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-primary" style="padding: 8px 15px;">Buscar</button>
                <a href="router.php?page=viviendas" class="btn-cancel" style="padding: 8px 15px; background: #F3F4F6; text-decoration: none; border-radius: 6px; font-size: 13px;">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">🏡</span> 
                Listado de Viviendas (<?= count($viviendas) ?>)
            </h3>
        </div>
        
        <style>
     @media (max-width: 768px) {
       .viviendas-table { font-size: 12px !important; display: none !important; }
       .viviendas-card { display: block !important; }
       .viviendas-desktop-table { display: none !important; }
       .viviendas-list { display: flex; flex-direction: column; gap: 8px !important; }
       .viviendas-card { background: white; border-radius: 8px; padding: 12px; border: 1px solid #E5E7EB; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
       .viviendas-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; }
       .viviendas-propietario { font-weight: 700; color: #111827; font-size: 13px; margin-bottom: 2px; }
       .viviendas-meta { font-size: 10px; color: #6B7280; margin-top: 2px; }
       .viviendas-info-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px; }
       .viviendas-info-label { color: #9CA3AF; font-size: 10px; }
       .viviendas-info-value { color: #374151; font-weight: 600; font-size: 11px; }
       .viviendas-status-section { text-align: center; margin-top: 6px; padding-top: 6px; border-top: 1px solid #F3F4F6; }
       .viviendas-status-label { font-size: 9px; color: #6B7280; }
       .viviendas-status-badge { font-size: 10px; padding: 3px 6px !important; }
     }
   </style>

<div class="viviendas-list">
            <?php foreach($viviendas as $v): ?>
            <?php
              $mes = date('n');
              $anio = date('Y');
              $cobroStmt = $pdo->prepare("SELECT estado FROM cobros WHERE vivienda_id = ? AND mes = ? AND anio = ? LIMIT 1");
              $cobroStmt->execute([$v['id'], $mes, $anio]);
              $estado = $cobroStmt->fetchColumn() ?: 'Sin Cobro';
              $deudaStmt = $pdo->prepare("SELECT COALESCE(SUM(monto),0) FROM cobros WHERE vivienda_id = ? AND estado NOT IN ('Pagado','Anulado')");
              $deudaStmt->execute([$v['id']]);
              $deuda = (float)$deudaStmt->fetchColumn();
            ?>
            <div class="viviendas-card" style="<?= $v['exento_cobro'] ? 'border-left:3px solid #8B5CF6;' : '' ?>">
              <div class="viviendas-card-header">
                <div class="viviendas-propietario">
                  🏠 Casa #<?= htmlspecialchars($v['numero_casa']) ?: '-' ?>
                  <?php if ($v['exento_cobro']): ?>
                    <span style="background:#EDE9FE; color:#6D28D9; padding:1px 6px; border-radius:4px; font-size:9px; font-weight:700; margin-left:4px; display:inline-block;">EXENTA</span>
                  <?php endif; ?>
                </div>
                <span class="viviendas-badge" style="background: #F3F4F6; color: #374151; font-size: 10px;"><?= htmlspecialchars($v['calle_nombre'] ?? 'S/N') ?></span>
              </div>
              <div class="viviendas-meta">ID: #<?= $v['id'] ?> • <?= date('d/m/Y', strtotime($v['fecha_registro'])) ?></div>
              <div class="viviendas-info-row">
                <div><div class="viviendas-info-label">Propietario</div><div class="viviendas-info-value"><?= htmlspecialchars($v['propietario']) ?></div></div>
                <div><div class="viviendas-info-label">Dirección</div><div class="viviendas-info-value"><?= htmlspecialchars($v['direccion']) ?></div></div>
                <div><div class="viviendas-info-label">Estado</div><div class="viviendas-info-value"><span class="viviendas-badge" style="background: <?= $v['estado_servicio'] == 'Activo' ? '#D1FAE5' : ($v['estado_servicio'] == 'Anulado' ? '#FEE2E2' : '#FEF3C7') ?>; color: <?= $v['estado_servicio'] == 'Activo' ? '#065F46' : ($v['estado_servicio'] == 'Anulado' ? '#991B1B' : '#92400E') ?>;"><?= $v['estado_servicio'] ?></span></div></div>
                <div><div class="viviendas-info-label">Pago</div><div class="viviendas-info-value"><span class="viviendas-badge" style="background: <?= $estado == 'Pagado' ? '#DEF7EC' : ($estado == 'Pendiente' || $estado == 'Vencido' ? '#FDE8E8' : '#F3F4F6') ?>; color: <?= $estado == 'Pagado' ? '#03543F' : ($estado == 'Pendiente' || $estado == 'Vencido' ? '#9B1C1C' : '#6B7280') ?>;"><?= $estado ?></span></div></div>
              </div>
              <div style="display:flex; gap:8px; align-items:center; padding:4px 0;">
                <?php if ($deuda > 0 && !$v['exento_cobro']): ?>
                  <span style="color:#DC2626; font-weight:700; font-size:12px;">Deuda: S/ <?= number_format($deuda,2) ?></span>
                <?php endif; ?>
                <button onclick="toggleHistorial(<?= $v['id'] ?>)" style="margin-left:auto; background:transparent; color:#6B7280; border:1px solid #E5E7EB; padding:3px 8px; border-radius:4px; font-size:10px; cursor:pointer;">📋 Historial</button>
              </div>
              <div id="historial-m-<?= $v['id'] ?>" style="display:none; margin-top:6px; background:#F9FAFB; border-radius:6px; padding:10px; border:1px solid #E5E7EB; font-size:11px;">
                <?php
                $histStmt = $pdo->prepare("SELECT c.* FROM cobros c WHERE c.vivienda_id = ? ORDER BY c.anio DESC, c.mes DESC LIMIT 12");
                $histStmt->execute([$v['id']]);
                $historial = $histStmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div style="font-weight:700; margin-bottom:6px;">📋 Historial de Pagos</div>
                <?php if(empty($historial)): ?>
                  <div style="color:#9CA3AF;">Sin movimientos registrados.</div>
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
                <button onclick="document.getElementById('historial-m-<?= $v['id'] ?>').style.display='none'" style="margin-top:6px; background:#F3F4F6; border:none; padding:3px 8px; border-radius:4px; font-size:9px; cursor:pointer; width:100%;">Cerrar</button>
              </div>
              <div class="viviendas-status-section">
                <div class="viviendas-status-label">Servicio Registrado</div>
                <div class="viviendas-status-badge" style="background: #F9FAFB; color: #6B7280; font-size: 9px; padding: 2px 6px !important;"><?= date('d/m/Y', strtotime($v['fecha_registro'])) ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="viviendas-desktop-table" style="display: none;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0 8px; font-size: 14px;">
              <thead>
                <tr style="text-align: left;">
                    <th style="padding: 12px; color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Ubicación / Calle</th>
                    <th style="padding: 12px; color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Propietario</th>
                    <th style="padding: 12px; color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Dirección / Referencia</th>
                    <th style="padding: 12px; text-align: center; color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Estado Pago</th>
                    <th style="padding: 12px; text-align: center; color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Deuda</th>
                    <th style="padding: 12px; text-align: center; color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Registro</th>
                    <th style="padding: 12px; text-align: center; color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Acciones</th>
                </tr>
              </thead>
               <tbody>
                   <?php if (empty($viviendas)): ?>
                       <tr>
                           <td colspan="7" style="padding: 50px; text-align: center; color: #9CA3AF; background: #F9FAFB; border-radius: 12px;">
                               <div style="font-size: 40px; margin-bottom: 10px;">🏠</div>
                               No se encontraron viviendas registradas en esta zona.
                           </td>
                       </tr>
                   <?php endif; ?>
                   <?php foreach($viviendas as $v): ?>
                       <tr style="background: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: 0.3s;">
                           <td style="padding: 15px; border-top-left-radius: 10px; border-bottom-left-radius: 10px; border: 1px solid #F3F4F6; border-right: none;">
                               <span style="background: #F3F4F6; color: #374151; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;">
                                   <?= htmlspecialchars($v['calle_nombre'] ?? 'S/N') ?>
                               </span>
                           </td>
                           <td style="padding: 15px; border: 1px solid #F3F4F6; border-left: none; border-right: none;">
        <div style="font-weight: 800; color: #111827; display: flex; align-items: center; gap: 8px;">
                                    <?= htmlspecialchars($v['propietario']) ?>
                                    <?php if ($v['exento_cobro']): ?>
                                        <span style="background:#EDE9FE; color:#6D28D9; padding:1px 6px; border-radius:4px; font-size:9px; font-weight:700;">EXENTA</span>
                                    <?php endif; ?>
                                    <?php if ($v['estado_servicio'] == 'Suspendido'): ?>
                                        <span style="font-size: 9px; background: #FEF3C7; color: #92400E; padding: 2px 6px; border-radius: 4px; font-weight: 700;">SERVICIO SUSPENDIDO</span>
                                    <?php endif; ?>
                                </div>
                               <div style="font-size: 11px; color: #6B7280;">ID: #<?= $v['id'] ?></div>
                           </td>
                           <td style="padding: 15px; border: 1px solid #F3F4F6; border-left: none; border-right: none;">
                               <div style="font-weight: 700; color: #4B5563;">Casa <?= htmlspecialchars($v['numero_casa'] ?: '-') ?></div>
                               <div style="font-size: 12px; color: #9CA3AF; font-style: italic;"><?= htmlspecialchars($v['direccion']) ?></div>
                           </td>
                            <td style="padding: 15px; text-align: center; border: 1px solid #F3F4F6; border-left: none; border-right: none;">
                                <?php
                                  $mes = date('n');
                                  $anio = date('Y');
                                  $cobroStmt = $pdo->prepare("SELECT estado FROM cobros WHERE vivienda_id = ? AND mes = ? AND anio = ? LIMIT 1");
                                  $cobroStmt->execute([$v['id'], $mes, $anio]);
                                  $estado = $cobroStmt->fetchColumn() ?: 'Sin Cobro';
                                  $bg = '#F3F4F6'; $color = '#6B7280';
                                  if ($estado == 'Pagado') { $bg = '#DEF7EC'; $color = '#03543F'; }
                                  elseif ($estado == 'Pendiente' || $estado == 'Vencido') { $bg = '#FDE8E8'; $color = '#9B1C1C'; }
                                ?>
                                <span class="badge" style="background: <?= $bg ?>; color: <?= $color ?>; border:none; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;"><?= $estado ?></span>
                            </td>
                            <td style="padding: 15px; text-align: center; border: 1px solid #F3F4F6; border-left: none; border-right: none;">
                                <?php
                                $deudaStmt = $pdo->prepare("SELECT COALESCE(SUM(monto),0) FROM cobros WHERE vivienda_id = ? AND estado NOT IN ('Pagado','Anulado')");
                                $deudaStmt->execute([$v['id']]);
                                $deuda = (float)$deudaStmt->fetchColumn();
                                ?>
                                <?php if ($deuda > 0 && !$v['exento_cobro']): ?>
                                    <span style="color:#DC2626; font-weight:700; font-size:13px;">S/ <?= number_format($deuda,2) ?></span>
                                <?php else: ?>
                                    <span style="color:#9CA3AF; font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px; text-align: center; border: 1px solid #F3F4F6; border-left: none; border-right: none;">
                                <div style="font-size: 12px; font-weight: 600; color: #6B7280;"><?= date('d/m/Y', strtotime($v['fecha_registro'])) ?></div>
                                <div style="font-size: 10px; color: #9CA3AF;">Fecha Sistema</div>
                            </td>
                            <td style="padding: 15px; text-align: center; border-top-right-radius: 10px; border-bottom-right-radius: 10px; border: 1px solid #F3F4F6; border-left: none;">
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
               </tbody>
            </table>
         </div>
    </div>
<script>
function toggleHistorial(id) {
    var divs = document.querySelectorAll('[id="historial-' + id + '"], [id="historial-m-' + id + '"]');
    divs.forEach(function(div) {
        if (div.style.display === 'none') {
            document.querySelectorAll('[id^="historial-"]').forEach(function(el) { el.style.display = 'none'; });
            div.style.display = 'block';
        } else {
            div.style.display = 'none';
        }
    });
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
