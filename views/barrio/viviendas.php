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

            <div class="form-group" style="width: 200px;">
                <label style="font-size: 11px; font-weight: 700; color: #6B7280; margin-bottom: 4px; display: block;">FILTRAR POR CALLE</label>
                <select name="calle_id" onchange="this.form.submit()" style="width: 100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 13px;">
                    <option value="">Todas las calles</option>
                    <?php foreach($calles as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $f_calle == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-primary" style="padding: 8px 15px;">Buscar</button>
                <a href="router.php?page=viviendas" class="btn-cancel" style="padding: 8px 15px; background: #F3F4F6; text-decoration: none; border-radius: 6px; font-size: 13px;">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">📋 Viviendas (<?= count($viviendas) ?>)</h3>
            <a href="router.php?page=registrar_vivienda" class="btn-primary" style="text-decoration: none;">+ Registrar Vivienda</a>
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 2px solid #F3F4F6; text-align: left;">
                        <th style="padding: 12px; color: #6B7280;">Calle</th>
                        <th style="padding: 12px; color: #6B7280;">Propietario / Familia</th>
                        <th style="padding: 12px; color: #6B7280;">Casa / Dirección</th>
                        <th style="padding: 12px; text-align: center; color: #6B7280;">Estado / Reg.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($viviendas)): ?>
                        <tr>
                            <td colspan="4" style="padding: 40px; text-align: center; color: #9CA3AF;">No se encontraron viviendas registradas.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach($viviendas as $v): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 12px;"><span style="background: #E5E7EB; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;"><?= htmlspecialchars($v['calle_nombre'] ?? 'Sin Calle') ?></span></td>
                            <td style="padding: 12px; font-weight: 700;"><?= htmlspecialchars($v['propietario']) ?></td>
                            <td style="padding: 12px;"><strong>#<?= htmlspecialchars($v['numero_casa']) ?></strong> - <?= htmlspecialchars($v['direccion']) ?></td>
                            <td style="padding: 12px; text-align: center; color: #4B5563;">
                                <div style="font-size: 11px;"><?= date('d/m/Y', strtotime($v['fecha_registro'])) ?></div>
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
