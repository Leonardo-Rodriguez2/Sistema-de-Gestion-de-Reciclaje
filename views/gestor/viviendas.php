<?php
// views/gestor/viviendas.php
$user = check_dashboard_access([1, 2]); // Admin o Gestor

// Filtros
$f_barrio = (int)($_GET['barrio_id'] ?? 0);
$f_search = trim($_GET['search'] ?? '');

$sql = "SELECT v.*, b.nombre as barrio_nombre, c.nombre as calle_nombre
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
    <div class="card" style="margin-bottom: 20px; padding: 15px;">
        <form method="GET" action="router.php" style="display: flex; gap: 15px; align-items: flex-end;">
            <input type="hidden" name="page" value="viviendas">
            <div style="flex: 1;">
                <label style="font-size: 11px; font-weight: 700; color: #6B7280;">BUSCAR PROPIETARIO</label>
                <input type="text" name="search" value="<?= htmlspecialchars($f_search) ?>" placeholder="Nombre..." style="width: 100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 6px;">
            </div>
            <div style="width: 200px;">
                <label style="font-size: 11px; font-weight: 700; color: #6B7280;">BARRIO</label>
                <select name="barrio_id" onchange="this.form.submit()" style="width: 100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 6px;">
                    <option value="">Todos</option>
                    <?php foreach($barrios as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $f_barrio == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-primary">Filtrar</button>
        </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background: #F9FAFB; text-align: left;">
                    <th style="padding: 12px 20px;">Propietario</th>
                    <th style="padding: 12px;">Ubicación</th>
                    <th style="padding: 12px;">Barrio</th>
                    <th style="padding: 12px; text-align: center;">Estado Pago</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($viviendas as $v): ?>
                <tr style="border-bottom: 1px solid #F3F4F6;">
                    <td style="padding: 12px 20px; font-weight: 600;"><?= htmlspecialchars($v['propietario']) ?></td>
                    <td style="padding: 12px; color: #6B7280;"><?= htmlspecialchars($v['calle_nombre'] ?? 'Sin calle') ?> #<?= htmlspecialchars($v['numero_casa']) ?></td>
                    <td style="padding: 12px;"><?= htmlspecialchars($v['barrio_nombre']) ?></td>
                    <td style="padding: 12px; text-align: center;">
                        <span class="badge" style="background:#FEF3C7; color:#92400E;">Ver historial</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
