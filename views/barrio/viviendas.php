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
        WHERE v.barrio_id = :barrio";

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
    <div class="card" style="margin-bottom: 20px; padding: 15px;">
        <form method="GET" action="router.php" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="page" value="viviendas">
            
            <div class="form-group" style="flex: 1; min-width: 250px;">
                <label style="font-size: 11px; font-weight: 700; color: #6B7280; margin-bottom: 4px; display: block;">BUSCAR PROPIETARIO / DIR</label>
                <input type="text" name="search" value="<?= htmlspecialchars($f_search) ?>" placeholder="Nombre, calle, número..." 
                       style="width: 100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 13px;">
            </div>

            <div class="form-group" style="width: 150px;">
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
            <a href="router.php?page=registrar_vivienda" class="btn-primary" style="text-decoration: none; padding: 10px 20px; background: #111827; border-radius: 8px; font-weight: 600; font-size: 13px;">+ Nueva Vivienda</a>
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0 8px; font-size: 14px;">
                <thead>
                    <tr style="text-align: left;">
                        <th style="padding: 12px; color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Ubicación / Calle</th>
                        <th style="padding: 12px; color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Propietario</th>
                        <th style="padding: 12px; color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Dirección / Referencia</th>
                        <th style="padding: 12px; text-align: center; color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Estado Pago</th>
                        <th style="padding: 12px; text-align: center; color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($viviendas)): ?>
                        <tr>
                            <td colspan="4" style="padding: 50px; text-align: center; color: #9CA3AF; background: #F9FAFB; border-radius: 12px;">
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
                                // Obtener último cobro del mes actual
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
                            <td style="padding: 15px; text-align: center; border-top-right-radius: 10px; border-bottom-right-radius: 10px; border: 1px solid #F3F4F6; border-left: none;">
                                <div style="font-size: 12px; font-weight: 600; color: #6B7280;"><?= date('d/m/Y', strtotime($v['fecha_registro'])) ?></div>
                                <div style="font-size: 10px; color: #9CA3AF;">Fecha Sistema</div>
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
