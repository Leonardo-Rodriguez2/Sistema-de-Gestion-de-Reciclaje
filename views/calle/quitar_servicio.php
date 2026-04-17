<?php
// views/calle/quitar_servicio.php
$user = check_dashboard_access([6]);

// 1. Obtener la calle asignada
$calleStmt = $pdo->prepare("SELECT calle_id FROM detalles_encargado_calle WHERE usuario_id = ?");
$calleStmt->execute([$user['id']]);
$calle_id = $calleStmt->fetchColumn();

// 2. Obtener viviendas activas de esa calle
$sql = "SELECT v.id, v.propietario, v.numero_casa, v.direccion, v.estado_servicio,
               (SELECT SUM(monto) FROM cobros WHERE vivienda_id = v.id AND estado != 'Pagado') as deuda_total,
               (SELECT COUNT(*) FROM cobros WHERE vivienda_id = v.id AND estado != 'Pagado') as meses_pendientes
        FROM viviendas v 
        WHERE v.calle_id = ? AND v.estado_servicio != 'Anulado'
        ORDER BY v.numero_casa ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$calle_id]);
$viviendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Solicitar Baja - EcoCusco";
$header_title = "Retiro de Servicio";
$header_subtitle = "Envia una solicitud al barrio para anular el servicio de una vivienda.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="card">
        <h3 style="margin-top:0;">🛡️ Gestión de Retiros</h3>
        <p style="font-size: 13px; color: #6B7280; margin-bottom: 20px;">
            Selecciona la vivienda que deseas dar de baja. Se enviará el monto de deuda acumulada para aprobación.
        </p>
        
        <div style="overflow-x: auto;">
            <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                        <th style="padding: 12px;">Casa</th>
                        <th style="padding: 12px;">Propietario</th>
                        <th style="padding: 12px; text-align: center;">Deuda / Pendientes</th>
                        <th style="padding: 12px; text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($viviendas)): ?>
                        <tr><td colspan="4" style="text-align: center; color: #9CA3AF; padding: 40px;">No hay viviendas activas en tu calle.</td></tr>
                    <?php endif; ?>
                    <?php foreach($viviendas as $v): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 12px;">
                                <div style="font-weight: 800; color: #111827;">#<?= htmlspecialchars($v['numero_casa']) ?></div>
                                <div style="font-size: 11px; color: #6B7280;">ID: <?= $v['id'] ?></div>
                            </td>
                            <td style="padding: 12px;">
                                <div style="font-weight: 600;"><?= htmlspecialchars($v['propietario']) ?></div>
                                <div style="font-size: 10px; color: #9CA3AF;"><?= htmlspecialchars($v['direccion']) ?></div>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <div style="font-weight: 800; color: #991B1B;">S/ <?= number_format($v['deuda_total'] ?: 0, 2) ?></div>
                                <div style="font-size: 10px; color: #6B7280;"><?= $v['meses_pendientes'] ?> conceptos</div>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <form method="POST" onsubmit="return confirm('¿Solicitar la baja de esta vivienda?')">
                                    <input type="hidden" name="form_type" value="solicitar_baja">
                                    <input type="hidden" name="vivienda_id" value="<?= $v['id'] ?>">
                                    <button type="submit" class="btn-primary" style="background: white; color: #EF4444; border: 1px solid #EF4444; padding: 6px 12px; font-size: 11px; font-weight: 700;">Solicitar Baja</button>
                                </form>
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
