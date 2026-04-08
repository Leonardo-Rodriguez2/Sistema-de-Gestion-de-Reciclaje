<?php
// views/barrio/dashboard.php
$user = check_dashboard_access([1, 5]); // Admin o Encargado de Barrio

// Obtener datos del barrio asignado
$barrioStmt = $pdo->prepare("SELECT b.* FROM detalles_encargado_barrio d JOIN barrios b ON d.barrio_id = b.id WHERE d.usuario_id = ?");
$barrioStmt->execute([$user['id']]);
$barrio_info = $barrioStmt->fetch(PDO::FETCH_ASSOC);

if (!$barrio_info) {
    die("No tienes un barrio asignado. Contacta al administrador.");
}

// Obtener calles de su barrio
$callesStmt = $pdo->prepare("SELECT c.*, (SELECT COUNT(*) FROM viviendas WHERE calle_id = c.id) as total_viviendas 
                            FROM calles c WHERE c.barrio_id = ?");
$callesStmt->execute([$barrio_info['id']]);
$calles = $callesStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener solicitudes pendientes de sus calles
$solicitudesStmt = $pdo->prepare("SELECT s.*, c.nombre as calle_nombre, u.nombre as solicitante_nombre 
                                FROM solicitudes_vivienda s 
                                JOIN calles c ON s.calle_id = c.id
                                JOIN usuarios u ON s.creado_por = u.id
                                WHERE c.barrio_id = ? AND s.estado = 'Pendiente'
                                ORDER BY s.fecha_creacion DESC");
$solicitudesStmt->execute([$barrio_info['id']]);
$solicitudes = $solicitudesStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener recaudaciones de calles pendientes por verificar
$recaudacionesStmt = $pdo->prepare("SELECT r.*, c.nombre as calle_nombre, u.nombre as emisor_nombre 
                                   FROM recaudaciones r 
                                   JOIN calles c ON r.calle_id = c.id
                                   JOIN usuarios u ON r.emisor_id = u.id
                                   WHERE r.receptor_id = ? AND r.estado = 'Pendiente' AND r.tipo = 'Calle'");
$recaudacionesStmt->execute([$user['id']]);
$recaudaciones_pendientes = $recaudacionesStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Gestión de Barrio - EcoCusco";
$header_title = "Barrio: " . htmlspecialchars($barrio_info['nombre']);
$header_subtitle = "Revisa las calles, solicitudes y pagos de tu zona.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Stats -->
    <?php render_dashboard_stats([
        ['title' => 'Calles', 'value' => count($calles), 'color' => '#3B82F6', 'icon' => '🛣️'],
        ['title' => 'Solicitudes', 'value' => count($solicitudes), 'color' => '#F59E0B', 'icon' => '📩'],
        ['title' => 'Recaudaciones', 'value' => count($recaudaciones_pendientes), 'color' => '#10B981', 'icon' => '💰']
    ]); ?>

    <style>
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px; }
        .card-title { font-weight: 700; font-size: 16px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; color: var(--secondary); border-bottom: 1px solid #F3F4F6; padding-bottom: 10px; }
        .table-mini { width: 100%; border-collapse: collapse; font-size: 13px; }
        .table-mini th { text-align: left; padding: 10px 8px; border-bottom: 1px solid #E5E7EB; color: #6B7280; font-size: 11px; text-transform: uppercase; }
        .table-mini td { padding: 10px 8px; border-bottom: 1px solid #F9FAFB; }
        .btn-action { padding: 5px 8px; border-radius: 4px; font-size: 11px; border: none; cursor: pointer; }
        .btn-approve { background: #D1FAE5; color: #065F46; }
        .btn-reject { background: #FEE2E2; color: #991B1B; }
        @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="dashboard-grid">
        <!-- Solicitudes de Vivienda -->
        <div class="card">
            <div class="card-title"><span>📩 Solicitudes de Alta/Baja</span></div>
            <div style="max-height: 400px; overflow-y: auto;">
                <table class="table-mini">
                    <thead>
                        <tr><th>Tipo</th><th>Calle</th><th>Detalles</th><th>Acción</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($solicitudes)): ?>
                            <tr><td colspan="4" style="text-align:center; padding:20px; color:#9CA3AF;">No hay solicitudes pendientes.</td></tr>
                        <?php endif; ?>
                        <?php foreach($solicitudes as $s): ?>
                            <tr>
                                <td><span class="badge" style="background:<?= $s['tipo']=='Alta'?'#D1FAE5':'#FEE2E2' ?>; color:<?= $s['tipo']=='Alta'?'#065F46':'#991B1B' ?>;"><?= $s['tipo'] ?></span></td>
                                <td><?= htmlspecialchars($s['calle_nombre']) ?></td>
                                <td style="font-size:11px;">
                                    <?php if($s['tipo'] == 'Alta'): ?>
                                        <strong><?= htmlspecialchars($s['propietario']) ?></strong> (Casa <?= $s['numero_casa'] ?>)
                                    <?php else: ?>
                                        Vivienda ID: <?= $s['vivienda_id'] ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="form_type" value="procesar_solicitud">
                                        <input type="hidden" name="solicitud_id" value="<?= $s['id'] ?>">
                                        <input type="hidden" name="estado" value="Aprobado">
                                        <button type="submit" class="btn-action btn-approve" title="Aprobar">✔️</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="form_type" value="procesar_solicitud">
                                        <input type="hidden" name="solicitud_id" value="<?= $s['id'] ?>">
                                        <input type="hidden" name="estado" value="Rechazado">
                                        <button type="submit" class="btn-action btn-reject" title="Rechazar">❌</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recaudaciones de Calles -->
        <div class="card">
            <div class="card-title"><span>💰 Recaudaciones de Calles</span></div>
            <div style="max-height: 300px; overflow-y: auto;">
                <table class="table-mini">
                    <thead>
                        <tr><th>Calle</th><th>Monto</th><th>Encargado</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recaudaciones_pendientes)): ?>
                            <tr><td colspan="3" style="text-align:center; padding:20px; color:#9CA3AF;">Sin recaudaciones pendientes.</td></tr>
                        <?php endif; ?>
                        <?php foreach($recaudaciones_pendientes as $r): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['calle_nombre']) ?></strong></td>
                                <td style="font-weight:700; color:#065F46;">S/ <?= number_format($r['monto_total'], 2) ?></td>
                                <td><?= htmlspecialchars($r['emisor_nombre']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($recaudaciones_pendientes)): ?>
                <div style="margin-top:15px; padding-top:15px; border-top:1px solid #F3F4F6;">
                    <form method="POST" onsubmit="return confirm('¿Confirmas el envío de la recaudación total al gestor de pagos?')">
                        <input type="hidden" name="form_type" value="enviar_recaudacion_gestor">
                        <button type="submit" class="btn-primary" style="width:100%;">Consolidar y Enviar al Gestor</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gestión de Calles -->
    <div class="card" style="margin-top:20px;">
        <div class="card-title">🛣️ Calles en mi Barrio</div>
        <table class="table-mini">
            <thead>
                <tr><th>Nombre de Calle</th><th>Total Viviendas</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach($calles as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                        <td><?= $c['total_viviendas'] ?> viviendas</td>
                        <td>
                            <a href="router.php?page=viviendas&calle_id=<?= $c['id'] ?>" class="badge" style="background:#E0F2FE; color:#0369A1; text-decoration:none;">Ver Viviendas</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
