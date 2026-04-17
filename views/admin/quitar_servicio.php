<?php
// views/admin/quitar_servicio.php
$user = check_dashboard_access([1]);

// 1. Obtener todas las viviendas activas/suspendidas del sistema
$sql = "SELECT v.id, v.propietario, v.numero_casa, v.direccion, v.estado_servicio, 
               c.nombre as calle_nombre, b.nombre as barrio_nombre,
               (SELECT SUM(monto) FROM cobros WHERE vivienda_id = v.id AND estado != 'Pagado') as deuda_total
        FROM viviendas v 
        JOIN calles c ON v.calle_id = c.id
        JOIN barrios b ON v.barrio_id = b.id
        WHERE v.estado_servicio != 'Anulado'
        ORDER BY b.nombre, c.nombre, v.numero_casa";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$viviendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Quitar Servicio (Admin) - EcoCusco";
$header_title = "Control Maestro de Servicios";
$header_subtitle = "Anula servicios de cualquier vivienda del sistema de forma directa y permanente.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="card">
        <h3 style="margin-top:0;">🛑 Anulación Directa por Administración</h3>
        <p style="font-size: 13px; color: #6B7280; margin-bottom: 20px;">
            Esta acción no requiere solicitud previa. El servicio se anulará de inmediato y se registrará la deuda actual para auditoría.
        </p>
        
        <div style="overflow-x: auto;">
            <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                        <th style="padding: 12px;">Barrio / Calle</th>
                        <th style="padding: 12px;">Vivienda / Propietario</th>
                        <th style="padding: 12px; text-align: center;">Deuda Acumulada</th>
                        <th style="padding: 12px; text-align: center;">Acción Administrativa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($viviendas)): ?>
                        <tr><td colspan="4" style="text-align: center; color: #9CA3AF; padding: 40px;">No hay viviendas activas en el sistema.</td></tr>
                    <?php endif; ?>
                    <?php foreach($viviendas as $v): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 12px;">
                                <div style="font-weight: 800; color: #065F46;"><?= htmlspecialchars($v['barrio_nombre']) ?></div>
                                <div style="font-size: 11px; color: #6B7280;"><?= htmlspecialchars($v['calle_nombre']) ?></div>
                            </td>
                            <td style="padding: 12px;">
                                <div style="font-weight: 700;"><?= htmlspecialchars($v['propietario']) ?></div>
                                <div style="font-size: 11px; color: #6B7280;">Casa #<?= htmlspecialchars($v['numero_casa']) ?></div>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <div style="font-weight: 800; color: #991B1B;">S/ <?= number_format($v['deuda_total'] ?: 0, 2) ?></div>
                                <div style="font-size: 10px; color: #6B7280;">Monto al retiro</div>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <form method="POST" onsubmit="return confirm('¿EJECUTAR BAJA ADMINISTRATIVA? Esta acción es instantánea y permanente.')">
                                    <input type="hidden" name="form_type" value="ordenar_baja">
                                    <input type="hidden" name="vivienda_id" value="<?= $v['id'] ?>">
                                    <button type="submit" class="btn-primary" style="background: #111827; color: white; border: none; padding: 8px 15px; font-size: 11px; font-weight: 800; border-radius: 4px;">ANULAR SERVICIO</button>
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
