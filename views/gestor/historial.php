<?php
// views/gestor/historial.php
$user = check_dashboard_access([1, 2]);

// Obtener todas las recaudaciones enviadas por los encargados de barrio
$stmt = $pdo->query("SELECT r.*, u.nombre as emisor_nombre, b.nombre as barrio_nombre
                    FROM recaudaciones r
                    JOIN usuarios u ON r.emisor_id = u.id
                    JOIN barrios b ON r.barrio_id = b.id
                    ORDER BY r.fecha DESC");
$recaudaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Historial de Recaudaciones - EcoCusco";
$header_title = "Historial General";
$header_subtitle = "Revisión de todos los fondos liquidados por barrio.";

ob_start();
?>
    <div class="card" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0;">💵 Liquidaciones Recibidas</h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 2px solid #F3F4F6; text-align: left;">
                        <th style="padding: 12px;">Fecha</th>
                        <th style="padding: 12px;">Barrio</th>
                        <th style="padding: 12px;">Emisor (Encargado)</th>
                        <th style="padding: 12px;">Monto Total</th>
                        <th style="padding: 12px;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recaudaciones)): ?>
                        <tr><td colspan="5" style="padding: 20px; text-align: center; color: #9CA3AF;">No hay liquidaciones registradas aún.</td></tr>
                    <?php endif; ?>
                    <?php foreach($recaudaciones as $r): ?>
                    <tr style="border-bottom: 1px solid #F3F4F6;">
                        <td style="padding: 12px;"><?= date('d/m/Y H:i', strtotime($r['fecha'])) ?></td>
                        <td style="padding: 12px; font-weight: 600;"><?= htmlspecialchars($r['barrio_nombre']) ?></td>
                        <td style="padding: 12px;"><?= htmlspecialchars($r['emisor_nombre']) ?></td>
                        <td style="padding: 12px; font-weight: 700; color: #059669;">S/. <?= number_format($r['monto_total'], 2) ?></td>
                        <td style="padding: 12px;">
                            <span class="badge" style="background: #D1FAE5; color: #065F46; border:none;"><?= htmlspecialchars($r['estado']) ?></span>
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
