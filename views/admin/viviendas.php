<?php
// views/admin/viviendas.php
$user = check_dashboard_access([1]);

// 1. Obtener filtros
$f_barrio = (int)($_GET['barrio_id'] ?? 0);
$f_calle = (int)($_GET['calle_id'] ?? 0);
$f_search = trim($_GET['search'] ?? '');

// 2. Preparar consulta con filtros
$sql = "SELECT v.*, b.nombre as barrio_nombre, c.nombre as calle_nombre, 
               u.nombre as enc_nombre, u.apellido as enc_apellido
        FROM viviendas v 
        LEFT JOIN barrios b ON v.barrio_id = b.id 
        LEFT JOIN calles c ON v.calle_id = c.id
        LEFT JOIN usuarios u ON v.encargado_calle_id = u.id
        WHERE 1=1";

$params = [];
if ($f_barrio > 0) {
    $sql .= " AND v.barrio_id = :barrio";
    $params[':barrio'] = $f_barrio;
}
if ($f_calle > 0) {
    $sql .= " AND v.calle_id = :calle";
    $params[':calle'] = $f_calle;
}
if ($f_search !== '') {
    $sql .= " AND (v.propietario LIKE :search OR v.direccion LIKE :search OR v.numero_casa LIKE :search)";
    $params[':search'] = "%$f_search%";
}

$sql .= " ORDER BY v.fecha_registro DESC";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$viviendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Obtener barrios y calles para los selectores del filtro
$barrios = $pdo->query("SELECT id, nombre FROM barrios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$calles = [];
if ($f_barrio > 0) {
    $cStmt = $pdo->prepare("SELECT id, nombre FROM calles WHERE barrio_id = ? ORDER BY nombre");
    $cStmt->execute([$f_barrio]);
    $calles = $cStmt->fetchAll(PDO::FETCH_ASSOC);
}

$title = "Viviendas - EcoCusco";
$header_title = "Gestión de Viviendas";
$header_subtitle = "Monitoreo y administración de predios registrados.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Barra de Filtros -->
    <div class="card" style="margin-bottom: 20px; padding: 15px;">
        <form method="GET" action="router.php" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="page" value="viviendas">
            
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label style="font-size: 11px; font-weight: 700; color: #6B7280; margin-bottom: 4px; display: block;">BUSCAR PROPIETARIO / DIR</label>
                <input type="text" name="search" value="<?= htmlspecialchars($f_search) ?>" placeholder="Nombre, calle, número..." 
                       style="width: 100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 13px;">
            </div>

            <div class="form-group" style="width: 180px;">
                <label style="font-size: 11px; font-weight: 700; color: #6B7280; margin-bottom: 4px; display: block;">BARRIO</label>
                <select name="barrio_id" onchange="this.form.submit()" style="width: 100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 13px;">
                    <option value="">Todos</option>
                    <?php foreach($barrios as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $f_barrio == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="width: 180px;">
                <label style="font-size: 11px; font-weight: 700; color: #6B7280; margin-bottom: 4px; display: block;">CALLE</label>
                <select name="calle_id" onchange="this.form.submit()" <?= empty($calles) ? 'disabled' : '' ?> style="width: 100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 13px;">
                    <option value="">Todas</option>
                    <?php foreach($calles as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $f_calle == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-primary" style="padding: 8px 15px;">Aplicar</button>
                <a href="router.php?page=viviendas" class="btn-cancel" style="padding: 8px 15px; background: #F3F4F6; text-decoration: none; border-radius: 6px; font-size: 13px;">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Resultados -->
    <div class="card" style="background: white; padding: 0; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden;">
        <div style="padding: 20px; border-bottom: 1px solid #F3F4F6; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 16px;">📋 Viviendas Registradas (<?= count($viviendas) ?>)</h3>
            <a href="router.php?page=registrar_vivienda" class="btn-primary" style="text-decoration: none; padding: 8px 16px;">+ Registrar Nueva</a>
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #F9FAFB; text-align: left;">
                        <th style="padding: 12px 20px; color: #6B7280; font-weight: 600;">Propietario / Familia</th>
                        <th style="padding: 12px; color: #6B7280; font-weight: 600;">Dirección / Nro Casa</th>
                        <th style="padding: 12px; color: #6B7280; font-weight: 600;">Zona</th>
                        <th style="padding: 12px; color: #6B7280; font-weight: 600;">Encargado Responsable</th>
                        <th style="padding: 12px 20px; color: #6B7280; font-weight: 600; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($viviendas)): ?>
                        <tr>
                            <td colspan="5" style="padding: 40px; text-align: center; color: #9CA3AF;">No se encontraron viviendas con los filtros aplicados.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach($viviendas as $v): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6; transition: 0.2s;" onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='white'">
                            <td style="padding: 15px 20px;">
                                <div style="font-weight: 700; color: #111827;"><?= htmlspecialchars($v['propietario']) ?></div>
                                <div style="font-size: 11px; color: #6B7280;">ID: #<?= $v['id'] ?></div>
                            </td>
                            <td style="padding: 12px;">
                                <div><?= htmlspecialchars($v['direccion']) ?></div>
                                <?php if($v['numero_casa']): ?>
                                    <span style="font-size: 11px; color: #0369A1; background: #E0F2FE; padding: 2px 6px; border-radius: 4px;">Nro: <?= htmlspecialchars($v['numero_casa']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <div style="font-weight: 600; color: #374151;"><?= htmlspecialchars($v['barrio_nombre']) ?></div>
                                <div style="font-size: 11px; color: #6B7280;"><?= htmlspecialchars($v['calle_nombre'] ?? 'Sin calle' ) ?></div>
                            </td>
                            <td style="padding: 12px;">
                                <?php if($v['enc_nombre']): ?>
                                    <div style="font-weight: 500;"><?= htmlspecialchars($v['enc_nombre'] . ' ' . $v['enc_apellido']) ?></div>
                                    <div style="font-size: 11px; color: #6B7280;">Encargado de Calle</div>
                                <?php else: ?>
                                    <span style="color: #9CA3AF; font-style: italic;">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px 20px; text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <a href="router.php?page=vivienda_ver&id=<?= $v['id'] ?>" title="Ver detalles" style="text-decoration: none;">👁️</a>
                                    <a href="router.php?page=vivienda_editar&id=<?= $v['id'] ?>" title="Editar" style="text-decoration: none;">✏️</a>
                                </div>
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
