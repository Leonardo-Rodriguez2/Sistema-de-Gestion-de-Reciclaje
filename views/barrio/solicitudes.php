<?php
// views/barrio/solicitudes.php
$user = check_dashboard_access([5]);

// 1. Obtener el barrio asignado
$barrioStmt = $pdo->prepare("SELECT barrio_id FROM detalles_encargado_barrio WHERE usuario_id = ?");
$barrioStmt->execute([$user['id']]);
$barrio_id = $barrioStmt->fetchColumn();

if (!$barrio_id) {
    die("No tienes un barrio asignado.");
}

// 2. Obtener todas las solicitudes (Pendientes, Aprobadas, Rechazadas)
$sql = "SELECT s.*, c.nombre as calle_nombre, u.nombre as solicitante_nombre 
        FROM solicitudes_vivienda s 
        JOIN calles c ON s.calle_id = c.id
        JOIN usuarios u ON s.creado_por = u.id
        WHERE c.barrio_id = ?
        ORDER BY s.fecha_creacion DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$barrio_id]);
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Solicitudes de Vivienda - EcoCusco";
$header_title = "Solicitudes de Registro";
$header_subtitle = "Gestiona las peticiones de alta y baja enviadas por los encargados de calle.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="card" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="border-bottom: 2px solid #F3F4F6; text-align: left;">
                        <th style="padding: 12px; color: #6B7280;">Fecha</th>
                        <th style="padding: 12px; color: #6B7280;">Tipo</th>
                        <th style="padding: 12px; color: #6B7280;">Propietario / Calle</th>
                        <th style="padding: 12px; color: #6B7280;">Solicitante</th>
                        <th style="padding: 12px; text-align: center; color: #6B7280;">Estado</th>
                        <th style="padding: 12px; text-align: right; color: #6B7280;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($solicitudes)): ?>
                        <tr>
                            <td colspan="6" style="padding: 40px; text-align: center; color: #9CA3AF;">No hay solicitudes registradas en tu barrio.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach($solicitudes as $s): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 12px; font-size: 12px; color: #6B7280;"><?= date('d/m/Y H:i', strtotime($s['fecha_creacion'])) ?></td>
                            <td style="padding: 12px;">
                                <span class="badge" style="background: <?= $s['tipo'] == 'Alta' ? '#D1FAE5' : '#FEE2E2' ?>; color: <?= $s['tipo'] == 'Alta' ? '#065F46' : '#991B1B' ?>;">
                                    <?= $s['tipo'] ?>
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                <strong><?= htmlspecialchars($s['propietario'] ?: 'Vivienda ID: '.$s['vivienda_id']) ?></strong><br>
                                <span style="font-size: 12px; color: #6B7280;"><?= htmlspecialchars($s['calle_nombre']) ?> - Casa <?= $s['numero_casa'] ?></span>
                            </td>
                            <td style="padding: 12px; color: #4B5563; font-size: 13px;"><?= htmlspecialchars($s['solicitante_nombre']) ?></td>
                            <td style="padding: 12px; text-align: center;">
                                <span class="badge" style="background: <?= $s['estado'] == 'Pendiente' ? '#FEF3C7' : ($s['estado'] == 'Aprobado' ? '#D1FAE5' : '#F3F4F6') ?>; color: <?= $s['estado'] == 'Pendiente' ? '#92400E' : ($s['estado'] == 'Aprobado' ? '#065F46' : '#4B5563') ?>;">
                                    <?= $s['estado'] ?>
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: right;">
                                <?php if($s['estado'] == 'Pendiente'): ?>
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        <a href="router.php?page=registrar_vivienda&solicitud_id=<?= $s['id'] ?>" class="btn-primary" style="padding: 6px 12px; font-size: 11px; text-decoration: none;">Registrar</a>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="form_type" value="procesar_solicitud">
                                            <input type="hidden" name="solicitud_id" value="<?= $s['id'] ?>">
                                            <input type="hidden" name="estado" value="Rechazado">
                                            <button type="submit" class="btn-cancel" style="padding: 6px 12px; font-size: 11px;" title="Rechazar">❌</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span style="font-size: 11px; color: #9CA3AF;">Procesada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .badge { padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .btn-primary { background: #111827; color: white; border: none; border-radius: 6px; cursor: pointer; }
        .btn-cancel { background: #F3F4F6; color: #4B5563; border: none; border-radius: 6px; cursor: pointer; }
    </style>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
