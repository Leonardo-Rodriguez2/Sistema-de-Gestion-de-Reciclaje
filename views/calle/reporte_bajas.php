<?php
// views/calle/reporte_bajas.php
$user = check_dashboard_access([6]);

// 1. Obtener la calle asignada
$calleStmt = $pdo->prepare("SELECT calle_id FROM detalles_encargado_calle WHERE usuario_id = ?");
$calleStmt->execute([$user['id']]);
$calle_id = $calleStmt->fetchColumn();

// 2. Obtener solicitudes de baja ya confirmadas (Estado 'Aprobado')
$sql = "SELECT s.*, v.propietario, v.numero_casa, v.direccion 
        FROM solicitudes_vivienda s
        JOIN viviendas v ON s.vivienda_id = v.id
        WHERE s.calle_id = ? AND s.tipo = 'Baja' AND s.estado = 'Aprobado' AND v.estado_servicio = 'Anulado'
        AND v.id NOT IN (SELECT vivienda_id FROM solicitudes_vivienda WHERE tipo = 'Renovacion' AND estado = 'Pendiente' AND vivienda_id IS NOT NULL)
        GROUP BY v.id
        ORDER BY s.fecha_revision DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$calle_id]);
$bajas_confirmadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Reporte de Bajas - EcoCusco";
$header_title = "Historial de Retiro";
$header_subtitle = "Registro de viviendas que ya no cuentan con el servicio.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">📉 Bajas Confirmadas</h3>
            <button onclick="window.print()" class="btn-primary" style="background: #F3F4F6; color: #374151; font-size: 11px;">🖨️ Imprimir Reporte</button>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                        <th style="padding: 12px;">Fecha Ejecución</th>
                        <th style="padding: 12px;">Vivienda</th>
                        <th style="padding: 12px; text-align: right;">Deuda al Retiro</th>
                        <th style="padding: 12px;">Estado Sistema</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($bajas_confirmadas)): ?>
                        <tr><td colspan="4" style="text-align: center; color: #9CA3AF; padding: 40px;">No hay registros de bajas confirmadas aún.</td></tr>
                    <?php endif; ?>
                    <?php foreach($bajas_confirmadas as $b): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 12px;">
                                <div style="font-weight: 600;"><?= date('d/m/Y', strtotime($b['fecha_revision'])) ?></div>
                                <div style="font-size: 10px; color: #9CA3AF;"><?= date('H:i', strtotime($b['fecha_revision'])) ?></div>
                            </td>
                            <td style="padding: 12px;">
                                <div style="font-weight: 700;"><?= htmlspecialchars($b['propietario']) ?></div>
                                <div style="font-size: 11px; color: #6B7280;">Solicitud #<?= $b['id'] ?></div>
                            </td>
                            <td style="padding: 12px; text-align: right;">
                                <div style="font-weight: 800; color: #991B1B;">S/ <?= number_format($b['monto_deuda'], 2) ?></div>
                                <div style="font-size: 9px; color: #6B7280;"><?= htmlspecialchars($b['detalles_deuda']) ?></div>
                            </td>
                            <td style="padding: 12px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span class="badge" style="background: #FEE2E2; color: #991B1B; border:none; font-size: 10px;">ANULADO</span>
                                    
                                    <form method="POST" action="router.php?page=reporte_bajas" onsubmit="return confirm('¿Solicitar la RENOVACIÓN del servicio para esta vivienda?')">
                                        <input type="hidden" name="sid" value="<?= htmlspecialchars($sid) ?>">
                                        <input type="hidden" name="form_type" value="solicitar_renovacion">
                                        <input type="hidden" name="vivienda_id" value="<?= $b['vivienda_id'] ?>">
                                        <button type="submit" class="btn-primary" style="background: #111827; color: white; border: none; padding: 4px 8px; font-size: 9px; cursor: pointer;">🔄 Renovación</button>
                                    </form>
                                </div>
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
        .card { border: none !important; box-shadow: none !important; }
    </style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
