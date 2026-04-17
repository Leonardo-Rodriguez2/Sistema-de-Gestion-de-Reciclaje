<?php
// views/admin/reporte_bajas.php
$user = check_dashboard_access([1]);

// 1. Obtener todas las viviendas anuladas del sistema
$sql = "SELECT v.id, v.propietario, v.numero_casa, c.nombre as calle_nombre, b.nombre as barrio_nombre,
               s.monto_deuda, s.detalles_deuda, s.fecha_revision as fecha_baja, u.nombre as ejecutor_nombre
        FROM viviendas v 
        JOIN calles c ON v.calle_id = c.id
        JOIN barrios b ON v.barrio_id = b.id
        JOIN solicitudes_vivienda s ON v.id = s.vivienda_id
        LEFT JOIN usuarios u ON s.revisado_por = u.id
        WHERE v.estado_servicio = 'Anulado' AND s.tipo = 'Baja' AND s.estado = 'Aprobado'
        ORDER BY s.fecha_revision DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$bajas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Reporte Global de Bajas - EcoCusco";
$header_title = "Auditoría de Retiros";
$header_subtitle = "Historial completo de servicios anulados y deudas pendientes en todo el sistema.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="margin: 0;">📊 Reporte Consolidado</h3>
            <button onclick="window.print()" class="btn-primary" style="background:#F3F4F6; color:#111827; font-size:11px;">🖨️ Exportar PDF</button>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                        <th style="padding: 12px;">Fecha / Barrio</th>
                        <th style="padding: 12px;">Vivienda / Calle</th>
                        <th style="padding: 12px; text-align: right;">Deuda Final</th>
                        <th style="padding: 12px;">Autorizado por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($bajas)): ?>
                        <tr><td colspan="4" style="text-align: center; color: #9CA3AF; padding: 40px;">No se registran bajas en el sistema.</td></tr>
                    <?php endif; ?>
                    <?php foreach($bajas as $b): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 12px;">
                                <div style="font-weight: 700; color: #065F46;"><?= htmlspecialchars($b['barrio_nombre']) ?></div>
                                <div style="font-size: 11px; color: #9CA3AF;"><?= date('d/m/Y', strtotime($b['fecha_baja'])) ?></div>
                            </td>
                            <td style="padding: 12px;">
                                <div style="font-weight: 700; color: #111827;"><?= htmlspecialchars($b['propietario']) ?></div>
                                <div style="font-size: 11px; color: #6B7280;"><?= htmlspecialchars($b['calle_nombre']) ?> | Casa #<?= htmlspecialchars($b['numero_casa']) ?></div>
                            </td>
                            <td style="padding: 12px; text-align: right;">
                                <div style="font-weight: 800; color: #991B1B;">S/ <?= number_format($b['monto_deuda'], 2) ?></div>
                                <div style="font-size: 10px; color: #6B7280; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 200px;"><?= htmlspecialchars($b['detalles_deuda']) ?></div>
                            </td>
                            <td style="padding: 12px;">
                                <span class="badge" style="background: #F3F4F6; color: #374151;"><?= htmlspecialchars($b['ejecutor_nombre'] ?: 'N/A') ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <style media="print">
        .sidebar, .header, .btn-primary { display: none !important; }
        .main { margin: 0 !important; padding: 0 !important; }
    </style>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
