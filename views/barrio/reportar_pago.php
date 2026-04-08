<?php
// views/admin/reportar_pago.php
$user = check_dashboard_access([5]);

// 1. Cobros que el Jefe todavía no ha marcado como pagados (Pendientes de la vivienda)
$pendientesStmt = $pdo->prepare("SELECT c.*, v.propietario, v.direccion, v.numero_casa 
                                 FROM cobros c 
                                 JOIN viviendas v ON c.vivienda_id = v.id 
                                 WHERE v.encargado_calle_id = ? AND c.estado != 'Pagado'
                                 ORDER BY c.fecha_vencimiento ASC");
$pendientesStmt->execute([$user['id']]);
$cobros_pendientes = $pendientesStmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Cobros que el Jefe ya marcó como pagados pero NO ha enviado al Gestor aún
$por_enviarStmt = $pdo->prepare("SELECT c.*, v.propietario 
                                 FROM cobros c 
                                 JOIN viviendas v ON c.vivienda_id = v.id 
                                 WHERE v.encargado_calle_id = ? AND c.estado = 'Pagado' AND c.recaudacion_id IS NULL");
$por_enviarStmt->execute([$user['id']]);
$cobros_por_enviar = $por_enviarStmt->fetchAll(PDO::FETCH_ASSOC);
$total_acumulado = array_sum(array_column($cobros_por_enviar, 'monto'));

$title = "Reportar Pagos - Encargado de Barrio";
$header_title = "Gestión de Recaudación";
$header_subtitle = "Cobra a los vecinos y reporta el total al Gestor.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="grid">
        <!-- SECCIÓN 1: COBRAR A VECINOS -->
        <div class="card" style="grid-column: span 2;">
            <h3 style="color: #10B981;">🏠 Cobros Pendientes en mi Cuadra</h3>
            <p style="font-size: 14px; color: #6B7280;">Marca como 'Pagado' cuando recibas el dinero del vecino.</p>
            
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Vivienda</th>
                            <th>Mes/Año</th>
                            <th>Monto</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($cobros_pendientes)): ?>
                            <tr><td colspan="4" style="text-align: center; color: #9CA3AF; padding: 20px;">No hay cobros pendientes.</td></tr>
                        <?php endif; ?>
                        <?php foreach($cobros_pendientes as $c): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($c['propietario']) ?></strong><br>
                                    <small><?= htmlspecialchars($c['direccion']) ?></small>
                                </td>
                                <td><?= $c['mes'] ?>/<?= $c['anio'] ?></td>
                                <td style="font-weight: 700; color: #10B981;">S/ <?= number_format($c['monto'], 2) ?></td>
                                <td>
                                    <form method="POST" action="router.php?page=reportar_pago">
                                        <input type="hidden" name="form_type" value="jefe_marcar_pagado">
                                        <input type="hidden" name="cobro_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn-primary" style="padding: 6px 12px; font-size: 11px;">Marcar Pagado</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECCIÓN 2: ENVIAR TOTAL AL GESTOR -->
        <div class="card" style="border-top: 4px solid #F59E0B;">
            <h3 style="color: #F59E0B;">💰 Recaudación por Enviar</h3>
            <div style="text-align: center; padding: 20px 0;">
                <div style="font-size: 14px; color: #6B7280; margin-bottom: 10px;">Total Acumulado en Mano:</div>
                <div style="font-size: 42px; font-weight: 800; color: #F59E0B;">S/ <?= number_format($total_acumulado, 2) ?></div>
                <div style="font-size: 13px; color: #9CA3AF; margin-top: 5px;"><?= count($cobros_por_enviar) ?> viviendas pagadas</div>
            </div>

            <?php if($total_acumulado > 0): ?>
                <form method="POST" action="router.php?page=reportar_pago">
                    <input type="hidden" name="form_type" value="enviar_recaudacion_gestor">
                    <button type="submit" class="btn-primary" style="width: 100%; background: #F59E0B; margin-top: 10px;">
                        Reportar Total al Gestor
                    </button>
                </form>
            <?php else: ?>
                <button disabled style="width: 100%; padding: 12px; background: #E5E7EB; color: #9CA3AF; border: none; border-radius: 8px; cursor: not-allowed;">
                    Nada que reportar aún
                </button>
            <?php endif; ?>
        </div>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>

