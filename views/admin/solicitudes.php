<?php
// views/admin/solicitudes.php
$user = check_dashboard_access([1]);
global $pdo;

// Get all pending requests
$stmt = $pdo->prepare(
    "SELECT s.*, c.nombre as calle_nombre, u.nombre as solicitante_nombre 
     FROM solicitudes_vivienda s 
     JOIN calles c ON s.calle_id = c.id
     JOIN usuarios u ON s.creado_por = u.id
     WHERE s.estado = 'Pendiente'
     ORDER BY s.fecha_creacion DESC"
);
$stmt->execute();
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Solicitudes del Sistema";
$header_title = "Solicitudes Pendientes";
$header_subtitle = "Gestiona todas las solicitudes de altas, bajas y renovaciones.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="card">
        <div class="table-wrap">
            <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                        <th style="padding: 12px;">Fecha</th>
                        <th style="padding: 12px;">Tipo</th>
                        <th style="padding: 12px;">Calle</th>
                        <th style="padding: 12px;">Solicitante</th>
                        <th style="padding: 12px;">Vivienda</th>
                        <th style="padding: 12px;">Deuda</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($solicitudes)): ?>
                        <tr><td colspan="6" style="text-align:center;color:#9CA3AF;padding:40px;">No hay solicitudes pendientes.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($solicitudes as $s): ?>
                        <tr style="border-bottom:1px solid #F3F4F6;">
                            <td style="padding:12px;"><?= date('d/m/Y H:i', strtotime($s['fecha_creacion'])) ?></td>
                            <td style="padding:12px;">
                                <span style="background:<?= $s['tipo']=='Alta'?'#D1FAE5':($s['tipo']=='Baja'?'#FEE2E2':'#E0F2FE') ?>;color:<?= $s['tipo']=='Alta'?'#065F46':($s['tipo']=='Baja'?'#991B1B':'#0369A1') ?>;padding:3px 8px;font-size:10px;font-weight:700;"><?= strtoupper($s['tipo']) ?></span>
                            </td>
                            <td style="padding:12px;"><?= htmlspecialchars($s['calle_nombre']) ?></td>
                            <td style="padding:12px;"><?= htmlspecialchars($s['solicitante_nombre']) ?></td>
                            <td style="padding:12px;"><?= htmlspecialchars($s['propietario'] ?? $s['vivienda_id']) ?></td>
                            <td style="padding:12px;font-weight:700;color:#991B1B;">S/ <?= number_format($s['monto_deuda'] ?? 0, 2) ?></td>
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
