<?php
// gestor_dashboard_view.php
// Rediseñado con Componentes y Helper

$user = check_dashboard_access([1, 2]);

// 1. Obtener Recaudaciones Pendientes de Verificación
$recaudacionesStmt = $pdo->query("
    SELECT r.*, u.nombre as jefe_nombre, u.apellido as jefe_apellido, b.nombre as barrio_nombre 
    FROM recaudaciones r
    JOIN usuarios u ON r.jefe_id = u.id
    JOIN barrios b ON r.barrio_id = b.id
    WHERE r.estado = 'Pendiente'
    ORDER BY r.fecha_recaudacion DESC
");
$recaudaciones_pendientes = $recaudacionesStmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Obtener Historial de Recaudaciones Verificadas (últimas 5)
$verificadasStmt = $pdo->query("
    SELECT r.*, u.nombre as jefe_nombre, b.nombre as barrio_nombre 
    FROM recaudaciones r
    JOIN usuarios u ON r.jefe_id = u.id
    JOIN barrios b ON r.barrio_id = b.id
    WHERE r.estado = 'Verificado'
    ORDER BY r.fecha_recaudacion DESC LIMIT 5
");
$recaudaciones_verificadas = $verificadasStmt->fetchAll(PDO::FETCH_ASSOC);

$total_verificado = array_sum(array_column($recaudaciones_verificadas, 'monto_total'));

$title = "Gestor de Pagos - EcoCusco";
$header_title = "Verificación de Barrios";
$header_subtitle = "Revisa y confirma las recaudaciones enviadas por los Jefes.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Stats -->
    <?php render_dashboard_stats([
        ['title' => 'Pendientes de Verificar', 'value' => count($recaudaciones_pendientes), 'color' => '#92400E', 'icon' => '⏳'],
        ['title' => 'Total Recaudado', 'value' => 'S/ ' . number_format($total_verificado, 2), 'color' => '#065F46', 'icon' => '✅']
    ]); ?>

    <div class="grid" style="grid-template-columns: 2fr 1fr;">
        <!-- TABLA RECAUDACIONES PENDIENTES -->
        <div class="card">
            <h3 style="color: var(--secondary); font-size: 16px; border-bottom: 1px solid #F3F4F6; padding-bottom: 10px; margin-bottom: 15px;">📂 Recaudaciones por Verificar</h3>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Barrio / Comunidad</th>
                            <th>Jefe Responsable</th>
                            <th>Monto Total</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($recaudaciones_pendientes)): ?>
                            <tr><td colspan="4" style="text-align: center; padding: 30px; color: #9CA3AF;">No hay recaudaciones pendientes.</td></tr>
                        <?php endif; ?>
                        <?php foreach($recaudaciones_pendientes as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($r['barrio_nombre']) ?></strong><br>
                                    <small style="color: #6B7280;">Reportado: <?= date('d/m/Y H:i', strtotime($r['fecha_recaudacion'])) ?></small>
                                </td>
                                <td><?= htmlspecialchars($r['jefe_nombre'] . ' ' . $r['jefe_apellido']) ?></td>
                                <td style="font-weight: 700; color: #065F46;">S/ <?= number_format($r['monto_total'], 2) ?></td>
                                <td>
                                    <form method="POST" action="router.php?page=dashboard">
                                        <input type="hidden" name="form_type" value="verificar_recaudacion">
                                        <input type="hidden" name="recaudacion_id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="btn-primary" style="padding: 6px 12px; font-size: 11px;">Verificar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- HISTORIAL RECIENTE -->
        <div class="card">
            <h3 style="color: var(--secondary); font-size: 16px; border-bottom: 1px solid #F3F4F6; padding-bottom: 10px; margin-bottom: 15px;">📜 Últimas Verificadas</h3>
            <ul style="list-style: none; padding: 0;">
                <?php foreach($recaudaciones_verificadas as $rv): ?>
                    <li style="padding: 12px 0; border-bottom: 1px solid #F3F4F6;">
                        <div style="display: flex; justify-content: space-between;">
                            <strong><?= htmlspecialchars($rv['barrio_nombre']) ?></strong>
                            <span style="color: #065F46; font-weight: 700;">S/ <?= number_format($rv['monto_total'], 2) ?></span>
                        </div>
                        <small style="color: #6B7280;"><?= htmlspecialchars($rv['jefe_nombre']) ?> • <?= date('d/m', strtotime($rv['fecha_recaudacion'])) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>


