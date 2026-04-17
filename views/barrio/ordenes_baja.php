<?php
// views/barrio/ordenes_baja.php
$user = check_dashboard_access([5]);

// 1. Obtener solicitudes de BAJA únicamente
$sql = "SELECT s.*, c.nombre as calle_nombre, u.nombre as solicitante_nombre 
        FROM solicitudes_vivienda s 
        JOIN calles c ON s.calle_id = c.id
        JOIN usuarios u ON s.creado_por = u.id
        JOIN detalles_encargado_barrio deb ON c.barrio_id = deb.barrio_id
        WHERE deb.usuario_id = ? AND s.tipo = 'Baja' AND s.estado = 'Pendiente'
        ORDER BY s.fecha_creacion DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user['id']]);
$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Órdenes de Baja - EcoCusco";
$header_title = "Ejecución de Bajas";
$header_subtitle = "Confirma el retiro del servicio para las viviendas solicitadas por los encargados de calle.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="card" style="border-left: 4px solid #EF4444;">
        <h3 style="margin-top:0;">🛑 Solicitudes de Baja Pendientes</h3>
        <p style="font-size: 13px; color: #6B7280; margin-bottom: 20px;">
            Al confirmar, el servicio se anulará en el sistema y se registrará la deuda final.
        </p>
        
        <div style="overflow-x: auto;">
            <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                        <th style="padding: 12px;">Fecha / Calle</th>
                        <th style="padding: 12px;">Propietario / Vivienda</th>
                        <th style="padding: 12px; text-align: right;">Deuda Pendiente</th>
                        <th style="padding: 12px; text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($ordenes)): ?>
                        <tr><td colspan="4" style="text-align: center; color: #9CA3AF; padding: 40px;">No hay órdenes de baja pendientes.</td></tr>
                    <?php endif; ?>
                    <?php foreach($ordenes as $o): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 12px;">
                                <div style="font-weight: 700; color: #111827;"><?= htmlspecialchars($o['calle_nombre']) ?></div>
                                <div style="font-size: 11px; color: #6B7280;"><?= date('d/m/Y H:i', strtotime($o['fecha_creacion'])) ?></div>
                            </td>
                            <td style="padding: 12px;">
                                <?php 
                                    $vStmt = $pdo->prepare("SELECT propietario, numero_casa FROM viviendas WHERE id = ?");
                                    $vStmt->execute([$o['vivienda_id']]);
                                    $v = $vStmt->fetch();
                                ?>
                                <div style="font-weight: 700;"><?= htmlspecialchars($v['propietario'] ?? 'N/A') ?></div>
                                <div style="font-size: 11px; color: #6B7280;">Casa #<?= htmlspecialchars($v['numero_casa'] ?? '#') ?> (ID: <?= $o['vivienda_id'] ?>)</div>
                            </td>
                            <td style="padding: 12px; text-align: right;">
                                <div style="font-weight: 800; color: #991B1B;">S/ <?= number_format($o['monto_deuda'], 2) ?></div>
                                <div style="font-size: 9px; color: #6B7280;"><?= htmlspecialchars($o['detalles_deuda']) ?></div>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <form method="POST" onsubmit="return confirm('¿CONFIRMAR BAJA DE SERVICIO? El usuario ya no contará con el servicio de recolección.')">
                                    <input type="hidden" name="form_type" value="procesar_solicitud">
                                    <input type="hidden" name="solicitud_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="estado" value="Aprobado">
                                    <button type="submit" class="btn-primary" style="background: #111827; color: white; border: none; padding: 8px 15px; font-size: 11px; font-weight: 700;">Realizar Baja</button>
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
