<?php
// views/barrio/solicitudes.php
$user = check_dashboard_access([5]);

// 1. Obtener solicitudes de las calles de su barrio
$sql = "SELECT s.*, c.nombre as calle_nombre, u.nombre as solicitante_nombre 
        FROM solicitudes_vivienda s 
        JOIN calles c ON s.calle_id = c.id
        JOIN usuarios u ON s.creado_por = u.id
        JOIN detalles_encargado_barrio deb ON c.barrio_id = deb.barrio_id
        WHERE deb.usuario_id = ? AND s.estado = 'Pendiente' AND s.tipo = 'Alta'
        ORDER BY s.fecha_creacion DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user['id']]);
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Bandeja de Solicitudes - EcoCusco";
$header_title = "Solicitudes Pendientes";
$header_subtitle = "Revisa y aprueba altas, bajas o renovaciones de servicio.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="card">
        <div style="overflow-x: auto;">
            <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                        <th style="padding: 12px;">Fecha / Tipo</th>
                        <th style="padding: 12px;">Vivienda / Propietario</th>
                        <th style="padding: 12px;">Deuda al Retiro</th>
                        <th style="padding: 12px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($solicitudes)): ?>
                        <tr><td colspan="4" style="text-align: center; color: #9CA3AF; padding: 40px;">No tienes solicitudes pendientes por procesar.</td></tr>
                    <?php endif; ?>
                    <?php foreach($solicitudes as $s): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 12px;">
                                <div style="font-size: 11px; color: #6B7280;"><?= date('d/m/Y', strtotime($s['fecha_creacion'])) ?></div>
                                <span class="badge" style="background: <?= $s['tipo'] == 'Alta' ? '#D1FAE5' : ($s['tipo'] == 'Baja' ? '#FEE2E2' : '#E0F2FE') ?>; color: <?= $s['tipo'] == 'Alta' ? '#065F46' : ($s['tipo'] == 'Baja' ? '#991B1B' : '#0369A1') ?>; border: none; font-size: 10px; font-weight: 700;">
                                    <?= strtoupper($s['tipo']) ?>
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                <?php if($s['tipo'] == 'Alta'): ?>
                                    <div style="font-weight: 700;"><?= htmlspecialchars($s['propietario']) ?></div>
                                    <div style="font-size: 11px; color: #6B7280;">Calle: <?= htmlspecialchars($s['calle_nombre']) ?> | Casa: <?= htmlspecialchars($s['numero_casa']) ?></div>
                                <?php else: ?>
                                    <?php 
                                        $vStmt = $pdo->prepare("SELECT propietario, numero_casa FROM viviendas WHERE id = ?");
                                        $vStmt->execute([$s['vivienda_id']]);
                                        $v = $vStmt->fetch();
                                    ?>
                                    <div style="font-weight: 700;"><?= htmlspecialchars($v['propietario'] ?? 'N/A') ?></div>
                                    <div style="font-size: 11px; color: #6B7280;">ID Casa: #<?= $s['vivienda_id'] ?> | Calle: <?= htmlspecialchars($s['calle_nombre']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php if($s['tipo'] == 'Baja'): ?>
                                    <div style="font-weight: 800; color: #991B1B;">S/ <?= number_format($s['monto_deuda'], 2) ?></div>
                                    <div style="font-size: 10px; color: #6B7280; max-width: 200px;"><?= htmlspecialchars($s['detalles_deuda']) ?></div>
                                <?php else: ?>
                                    <span style="color: #9CA3AF; font-size: 12px;">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <form method="POST" onsubmit="return confirm('¿Aprobar esta solicitud?')">
                                        <input type="hidden" name="form_type" value="procesar_solicitud">
                                        <input type="hidden" name="solicitud_id" value="<?= $s['id'] ?>">
                                        <input type="hidden" name="estado" value="Aprobado">
                                        <button type="submit" class="badge" style="background: #111827; color: white; border:none; cursor:pointer;">Aprobar</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('¿Rechazar esta solicitud?')">
                                        <input type="hidden" name="form_type" value="procesar_solicitud">
                                        <input type="hidden" name="solicitud_id" value="<?= $s['id'] ?>">
                                        <input type="hidden" name="estado" value="Rechazado">
                                        <button type="submit" class="badge" style="background: #F3F4F6; color: #374151; border:none; cursor:pointer;">Rechazar</button>
                                    </form>
                                </div>
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
