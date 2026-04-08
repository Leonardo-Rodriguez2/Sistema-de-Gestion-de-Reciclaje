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

// Obtener calles de su barrio con estadísticas de pago (mes actual)
$mes_actual = date('n');
$anio_actual = date('Y');

$callesStmt = $pdo->prepare("SELECT c.*, 
                            (SELECT COUNT(*) FROM viviendas WHERE calle_id = c.id) as total_viviendas,
                            (SELECT COUNT(DISTINCT v.id) 
                             FROM viviendas v 
                             JOIN cobros co ON v.id = co.vivienda_id 
                             WHERE v.calle_id = c.id 
                               AND co.mes = ? 
                               AND co.anio = ? 
                               AND co.estado = 'Pagado') as pagados
                            FROM calles c WHERE c.barrio_id = ?");
$callesStmt->execute([$mes_actual, $anio_actual, $barrio_info['id']]);
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

    <!-- Quick Actions -->
    <div style="display: flex; gap: 15px; margin-top: 20px;">
        <a href="router.php?page=registrar_vivienda" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px; background: #111827; color: white; text-decoration: none; border-radius: 12px; font-weight: 700; transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.1);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
            <span style="font-size: 20px;">🏠</span> Registrar Vivienda
        </a>
        <a href="router.php?page=solicitudes" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px; background: white; color: #111827; text-decoration: none; border-radius: 12px; font-weight: 700; border: 2px solid #111827; transition: 0.3s;" onmouseover="this.style.background='#F3F4F6'" onmouseout="this.style.background='white'">
            <span style="font-size: 20px;">📩</span> Ver Solicitudes
        </a>
        <a href="router.php?page=calles" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px; background: white; color: #111827; text-decoration: none; border-radius: 12px; font-weight: 700; border: 2px solid #E5E7EB; transition: 0.3s;" onmouseover="this.style.background='#F3F4F6'" onmouseout="this.style.background='white'">
            <span style="font-size: 20px;">🛣️</span> Lista de Calles
        </a>
    </div>

    <style>
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px; }
        .card-title { font-weight: 700; font-size: 16px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; color: var(--secondary); border-bottom: 1px solid #F3F4F6; padding-bottom: 10px; }
        .table-mini { width: 100%; border-collapse: collapse; font-size: 13px; }
        .table-mini th { text-align: left; padding: 10px 8px; border-bottom: 1px solid #E5E7EB; color: #6B7280; font-size: 11px; text-transform: uppercase; }
        .table-mini td { padding: 10px 8px; border-bottom: 1px solid #F9FAFB; }
        .btn-action { padding: 5px 8px; border-radius: 4px; font-size: 11px; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-approve { background: #D1FAE5; color: #065F46; }
        .btn-reject { background: #FEE2E2; color: #991B1B; }
        .progress-bar { width: 100%; height: 6px; background: #F3F4F6; border-radius: 10px; overflow: hidden; margin-top: 5px; }
        .progress-fill { height: 100%; background: #10B981; border-radius: 10px; }
        @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="dashboard-grid">
        <!-- Solicitudes de Vivienda -->
        <div class="card">
            <div class="card-title"><span>📩 Solicitudes de Registro</span></div>
            <div style="max-height: 400px; overflow-y: auto;">
                <table class="table-mini">
                    <thead>
                        <tr><th>Información</th><th>Calle</th><th>Acción</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($solicitudes)): ?>
                            <tr><td colspan="3" style="text-align:center; padding:20px; color:#9CA3AF;">No hay solicitudes pendientes.</td></tr>
                        <?php endif; ?>
                        <?php foreach($solicitudes as $s): ?>
                            <tr>
                                <td style="font-size:11px;">
                                    <strong><?= htmlspecialchars($s['propietario']) ?></strong><br>
                                    <span style="color:#6B7280;">N° <?= $s['numero_casa'] ?></span>
                                </td>
                                <td><?= htmlspecialchars($s['calle_nombre']) ?></td>
                                <td>
                                    <a href="router.php?page=registrar_vivienda&solicitud_id=<?= $s['id'] ?>" class="btn-action btn-approve" title="Verificar y Registrar">Registrar</a>
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
            <div class="card-title"><span>💰 Recaudaciones Pendientes</span></div>
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

    <!-- Gestión de Calles con Estadísticas -->
    <div class="card" style="margin-top:20px;">
        <div class="card-title">🛣️ Estadísticas de Pago por Calle (<?= date('F Y') ?>)</div>
        <table class="table-mini">
            <thead>
                <tr><th>Calle</th><th>Progreso de Pago</th><th>Viviendas</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach($calles as $c): 
                    $porcentaje = ($c['total_viviendas'] > 0) ? ($c['pagados'] / $c['total_viviendas']) * 100 : 0;
                ?>
                    <tr>
                        <td style="width: 200px;"><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="progress-bar" style="flex-grow: 1;">
                                    <div class="progress-fill" style="width: <?= $porcentaje ?>%;"></div>
                                </div>
                                <span style="font-weight: 700; color: #374151; min-width: 40px;"><?= round($porcentaje) ?>%</span>
                            </div>
                        </td>
                        <td><?= $c['pagados'] ?> / <?= $c['total_viviendas'] ?> pagado</td>
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
