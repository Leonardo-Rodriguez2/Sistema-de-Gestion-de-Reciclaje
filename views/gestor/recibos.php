<?php
// views/gestor/recibos.php
$user = check_dashboard_access([1, 2]);
$page = 'recibos';
$sid = $_GET['sid'] ?? '';
$search = $_GET['search'] ?? '';

// Obtener cobros pagados para generar recibos
$sql = "SELECT c.*, v.propietario, v.numero_casa, b.nombre as barrio_nombre 
        FROM cobros c 
        JOIN viviendas v ON c.vivienda_id = v.id 
        JOIN barrios b ON v.barrio_id = b.id
        WHERE c.estado = 'Pagado'";

if ($search) {
    $sql .= " AND (v.propietario LIKE :search OR v.numero_casa LIKE :search OR b.nombre LIKE :search)";
}
$sql .= " ORDER BY c.id DESC LIMIT 50";

$stmt = $pdo->prepare($sql);
if ($search) {
    $stmt->execute([':search' => "%$search%"]);
} else {
    $stmt->execute();
}
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Centro de Recibos - EcoCusco";
$header_title = "Gestión de Comprobantes";
$header_subtitle = "Emisión de recibos y reportes para los ciudadanos.";

ob_start();
?>
    <div style="display: grid; grid-template-columns: 350px 1fr; gap: 25px; align-items: start;">
        
        <!-- LATERAL: REPORTES MENSAL -->
        <div class="card" style="border-top: 4px solid #10B981;">
            <div style="text-align: center; padding: 10px;">
                <div style="font-size: 40px; margin-bottom: 10px;">📊</div>
                <h3 style="margin-bottom: 10px;">Reportes de Recaudación</h3>
                <p style="color: #6B7280; font-size: 13px; margin-bottom: 20px;">Genera un consolidado de todos los barrios del mes actual para control interno.</p>
                <a href="router.php?page=monitor_pagos" class="btn-primary" style="display: block; text-decoration: none; margin-bottom: 10px;">Ver Monitor Global</a>
                <button class="btn-primary" style="background:#4B5563; width: 100%;" onclick="window.print()">Imprimir Vista Actual</button>
            </div>
        </div>

        <!-- PRINCIPAL: BUSCAR Y GENERAR RECIBO -->
        <div class="card">
            <h3 style="margin-top: 0; color: #111827;">🧾 Generar Recibo de Vivienda</h3>
            <p style="color: #6B7280; font-size: 14px;">Busca por nombre del propietario, número de casa o barrio.</p>
            
            <form method="GET" action="router.php" style="margin-bottom: 25px; display: flex; gap: 10px;">
                <input type="hidden" name="page" value="recibos">
                <input type="hidden" name="sid" value="<?= htmlspecialchars($sid) ?>">
                <input type="text" name="search" placeholder="Ej: Juan Perez o Barrio Sol..." value="<?= htmlspecialchars($search) ?>" 
                       style="flex: 1; padding: 10px; border: 1px solid #E5E7EB; border-radius: 8px;">
                <button type="submit" class="btn-primary">Buscar</button>
            </form>

            <div style="overflow-x: auto;">
                <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                            <th style="padding: 12px;">Propietario</th>
                            <th style="padding: 12px;">Barrio</th>
                            <th style="padding: 12px;">Periodo</th>
                            <th style="padding: 12px; text-align: center;">Recibo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pagos)): ?>
                            <tr><td colspan="4" style="text-align: center; padding: 30px; color: #9CA3AF;">No se encontraron pagos realizados con ese criterio.</td></tr>
                        <?php endif; ?>
                        <?php foreach($pagos as $p): ?>
                            <tr style="border-bottom: 1px solid #F3F4F6;">
                                <td style="padding: 12px;">
                                    <div style="font-weight: 700;"><?= htmlspecialchars($p['propietario']) ?></div>
                                    <div style="font-size: 11px; color: #6B7280;">Casa #<?= htmlspecialchars($p['numero_casa']) ?></div>
                                </td>
                                <td style="padding: 12px; font-size: 13px;"><?= htmlspecialchars($p['barrio_nombre']) ?></td>
                                <td style="padding: 12px; font-size: 13px; font-weight: 600;"><?= $p['mes'] ?>/<?= $p['anio'] ?></td>
                                <td style="padding: 12px; text-align: center;">
                                    <a href="views/gestor/ver_recibo.php?cobro_id=<?= $p['id'] ?>" target="_blank" class="badge" style="background: #D1FAE5; color: #065F46; text-decoration: none; padding: 8px 12px;">
                                        📄 Ver / Imprimir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
