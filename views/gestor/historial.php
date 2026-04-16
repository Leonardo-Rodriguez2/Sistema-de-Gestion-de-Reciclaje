<?php
// views/gestor/historial.php
$user = check_dashboard_access([1, 2]);
$page = 'historial';
$sid = $_GET['sid'] ?? '';

// 1. Filtros para Liquidaciones (Recaudaciones)
$f_barrio = (int)($_GET['f_barrio'] ?? 0);
$f_inicio = $_GET['f_inicio'] ?? '';
$f_fin = $_GET['f_fin'] ?? '';

$sql = "SELECT r.*, u.nombre as emisor_nombre, b.nombre as barrio_nombre
        FROM recaudaciones r
        JOIN usuarios u ON r.emisor_id = u.id
        JOIN barrios b ON r.barrio_id = b.id
        WHERE 1=1";

$params = [];
if ($f_barrio > 0) {
    $sql .= " AND r.barrio_id = :barrio";
    $params[':barrio'] = $f_barrio;
}
if ($f_inicio) {
    $sql .= " AND r.fecha_recaudacion >= :inicio";
    $params[':inicio'] = $f_inicio . " 00:00:00";
}
if ($f_fin) {
    $sql .= " AND r.fecha_recaudacion <= :fin";
    $params[':fin'] = $f_fin . " 23:59:59";
}

$sql .= " ORDER BY r.fecha_recaudacion DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$recaudaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Cargar barrios para el filtro
$barrios = $pdo->query("SELECT id, nombre FROM barrios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

$title = "Historial Financiero - EcoCusco";
$header_title = "Historial General";
$header_subtitle = "Revisión y filtrado de todas las operaciones financieras.";

ob_start();
?>
    <div class='card' style='margin-bottom: 20px; padding: 15px; border-top: 4px solid #3B82F6;'>
        <form method='GET' action='router.php' style='display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;'>
            <input type='hidden' name='page' value='historial'>
            <input type='hidden' name='sid' value='<?php echo htmlspecialchars($sid); ?>'>
            <div style='flex: 1; min-width: 150px;'>
                <label style='font-size: 11px; font-weight: 700; color: #6B7280; display: block; margin-bottom: 5px;'>BARRIO</label>
                <select name='f_barrio' style='width:100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 6px;'>
                    <option value=''>Todos los barrios</option>
                    <?php foreach($barrios as $b): ?>
                        <option value='<?php echo $b['id']; ?>' <?php echo ($f_barrio == $b['id'] ? 'selected' : ''); ?>><?php echo htmlspecialchars($b['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style='width: 150px;'>
                <label style='font-size: 11px; font-weight: 700; color: #6B7280; display: block; margin-bottom: 5px;'>DESDE</label>
                <input type='date' name='f_inicio' value='<?php echo htmlspecialchars($f_inicio); ?>' style='width:100%; padding: 7px; border: 1px solid #E5E7EB; border-radius: 6px;'>
            </div>
            <div style='width: 150px;'>
                <label style='font-size: 11px; font-weight: 700; color: #6B7280; display: block; margin-bottom: 5px;'>HASTA</label>
                <input type='date' name='f_fin' value='<?php echo htmlspecialchars($f_fin); ?>' style='width:100%; padding: 7px; border: 1px solid #E5E7EB; border-radius: 6px;'>
            </div>
            <div style='display: flex; gap: 8px;'>
                <button type='submit' class='btn-primary'>Filtrar</button>
                <a href='router.php?page=historial&sid=<?php echo $sid; ?>' class='badge' style='background:#F3F4F6; color:#4B5563; text-decoration:none; display:flex; align-items:center; padding: 10px;'>Limpiar</a>
            </div>
        </form>
    </div>
    <div class='card' style='background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
        <h3 style='margin-top: 0; display: flex; align-items: center; gap: 10px;'><span>💵</span> Liquidaciones de Barrios</h3>
        <div style='overflow-x: auto;'>
            <table style='width: 100%; border-collapse: collapse; font-size: 13px;'>
                <thead>
                    <tr style='border-bottom: 2px solid #F3F4F6; text-align: left;'>
                        <th style='padding: 12px;'>Fecha / Hora</th>
                        <th style='padding: 12px;'>Barrio</th>
                        <th style='padding: 12px;'>Responsable</th>
                        <th style='padding: 12px;'>Total Recaudado</th>
                        <th style='padding: 12px;'>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recaudaciones as $r): ?>
                    <tr style='border-bottom: 1px solid #F3F4F6;'>
                        <td style='padding: 12px;'><?php echo date('d/m/Y H:i', strtotime($r['fecha_recaudacion'])); ?></td>
                        <td style='padding: 12px;'><strong><?php echo htmlspecialchars($r['barrio_nombre']); ?></strong></td>
                        <td style='padding: 12px;'><?php echo htmlspecialchars($r['emisor_nombre']); ?></td>
                        <td style='padding: 12px; font-weight: 800; color: #059669;'>S/ <?php echo number_format($r['monto_total'], 2); ?></td>
                        <td style='padding: 12px;'><span class='badge' style='background:#D1FAE5; color:#065F46;'><?php echo $r['estado']; ?></span></td>
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