<?php
// views/barrio/reporte_bajas.php
$user = check_dashboard_access([5]);

// 1. Obtener viviendas anuladas y su deuda registrada en la solicitud aprobada
$sql = "SELECT v.id, v.propietario, v.numero_casa, v.direccion, c.nombre as calle_nombre,
               s.monto_deuda, s.detalles_deuda, s.fecha_revision as fecha_baja
        FROM viviendas v 
        JOIN calles c ON v.calle_id = c.id
        JOIN solicitudes_vivienda s ON v.id = s.vivienda_id
        JOIN detalles_encargado_barrio deb ON v.barrio_id = deb.barrio_id
        WHERE deb.usuario_id = ? AND v.estado_servicio = 'Anulado' AND s.tipo = 'Baja' AND s.estado = 'Aprobado'
        AND v.id NOT IN (SELECT vivienda_id FROM solicitudes_vivienda WHERE tipo = 'Renovacion' AND estado = 'Pendiente' AND vivienda_id IS NOT NULL)
        GROUP BY v.id
        ORDER BY s.fecha_revision DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user['id']]);
$bajas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Reporte de Bajas - EcoCusco";
$header_title = "Historial de Bajas";
$header_subtitle = "Viviendas que salieron del sistema y su estado de cuenta final.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="margin: 0;">📉 Registro de Retiros</h3>
            <button onclick="window.print()" class="btn-primary" style="background:#F3F4F6; color:#111827; font-size:11px;">🖨️ Imprimir Reporte</button>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                        <th style="padding: 12px;">Fecha Baja</th>
                        <th style="padding: 12px;">Vivienda / Calle</th>
                        <th style="padding: 12px; text-align: right;">Deuda Final</th>
                        <th style="padding: 12px;">Conceptos Pendientes</th>
                        <th style="padding: 12px; text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($bajas)): ?>
                        <tr><td colspan="4" style="text-align: center; color: #9CA3AF; padding: 40px;">No hay registros de bajas confirmadas en este barrio.</td></tr>
                    <?php endif; ?>
                    <?php foreach($bajas as $b): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 12px;">
                                <div style="font-weight: 700; color: #4B5563;"><?= date('d/m/Y', strtotime($b['fecha_baja'])) ?></div>
                                <div style="font-size: 10px; color: #9CA3AF;"><?= date('H:i', strtotime($b['fecha_baja'])) ?></div>
                            </td>
                            <td style="padding: 12px;">
                                <div style="font-weight: 700; color: #111827;"><?= htmlspecialchars($b['propietario']) ?></div>
                                <div style="font-size: 11px; color: #6B7280;"><?= htmlspecialchars($b['calle_nombre']) ?> | Casa #<?= htmlspecialchars($b['numero_casa']) ?></div>
                            </td>
                            <td style="padding: 12px; text-align: right;">
                                <div style="font-weight: 800; color: #991B1B; font-size: 15px;">S/ <?= number_format($b['monto_deuda'], 2) ?></div>
                            </td>
                            <td style="padding: 12px;">
                                <div style="font-size: 11px; color: #4B5563; line-height: 1.4; max-width: 200px;">
                                    <?= htmlspecialchars($b['detalles_deuda'] ?: 'Sin detalles registrados') ?>
                                </div>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <form method="POST" onsubmit="return confirm('¿RENOVAR SERVICIO DIRECTAMENTE? La vivienda volverá a estar activa.')">
                                    <input type="hidden" name="form_type" value="renovar_servicio_directo">
                                    <input type="hidden" name="vivienda_id" value="<?= $b['id'] ?>">
                                    <button type="submit" class="btn-primary" style="background:#111827; color:white; border:none; padding: 6px 12px; font-size:10px; cursor:pointer;">🔄 Renovar</button>
                                </form>
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
