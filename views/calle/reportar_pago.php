<?php
// views/calle/reportar_pago.php
$user = check_dashboard_access([6]);

// 1. Obtener la calle asignada
$calleStmt = $pdo->prepare("SELECT calle_id FROM detalles_encargado_calle WHERE usuario_id = ?");
$calleStmt->execute([$user['id']]);
$calle_id = $calleStmt->fetchColumn();

if (!$calle_id) {
    echo "<div class='alert error'>No tienes una calle asignada. Contacta al administrador.</div>";
    return;
}

// 2. Obtener todos los cobros pendientes de la calle
$sql = "SELECT c.*, v.propietario, v.numero_casa, v.direccion, v.estado_servicio
        FROM cobros c 
        JOIN viviendas v ON c.vivienda_id = v.id 
        WHERE v.calle_id = ? AND c.estado != 'Pagado'
        ORDER BY v.numero_casa ASC, c.fecha_vencimiento ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$calle_id]);
$cobros_pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Obtener cobros ya marcados como pagados pero NO enviados al Barrio (por este encargado)
$por_enviarStmt = $pdo->prepare("SELECT c.*, v.propietario 
                                 FROM cobros c 
                                 JOIN viviendas v ON c.vivienda_id = v.id 
                                 WHERE v.encargado_calle_id = ? AND c.estado = 'Pagado' AND c.recaudacion_id IS NULL");
$por_enviarStmt->execute([$user['id']]);
$cobros_por_enviar = $por_enviarStmt->fetchAll(PDO::FETCH_ASSOC);
$total_acumulado = array_sum(array_column($cobros_por_enviar, 'monto'));

$title = "Marcar Pagos - EcoCusco";
$header_title = "Reportar Cobros";
$header_subtitle = "Lista de viviendas con pagos pendientes en tu calle.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div style="display: grid; grid-template-columns: 1fr 300px; gap: 20px;">
        
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- TABLA DE COBROS PENDIENTES -->
            <div class="card">
                <h3 style="color: #10B981; margin-top:0;">🏠 Viviendas con Deuda</h3>
                <div style="overflow-x: auto;">
                    <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                                <th style="padding: 12px;">Vivienda</th>
                                <th style="padding: 12px;">Detalle / Fecha</th>
                                <th style="padding: 12px;">Monto</th>
                                <th style="padding: 12px;">Servicio</th>
                                <th style="padding: 12px; text-align: center;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($cobros_pendientes)): ?>
                                <tr><td colspan="5" style="text-align: center; color: #9CA3AF; padding: 40px;">No hay cobros pendientes en tu calle. ¡Todo al día!</td></tr>
                            <?php endif; ?>
                            <?php foreach($cobros_pendientes as $c): ?>
                                <tr style="border-bottom: 1px solid #F3F4F6;">
                                    <td style="padding: 12px;">
                                        <div style="font-weight: 700;"><?= htmlspecialchars($c['propietario']) ?></div>
                                        <div style="font-size: 11px; color: #6B7280;">Casa #<?= $c['numero_casa'] ?></div>
                                    </td>
                                    <td style="padding: 12px;">
                                        <span class="badge" style="background: <?= $c['tipo_cobro'] == 'Multa' ? '#FEE2E2' : '#E0F2FE' ?>; color: <?= $c['tipo_cobro'] == 'Multa' ? '#991B1B' : '#0369A1' ?>; font-size: 10px;">
                                            <?= $c['tipo_cobro'] ?>
                                        </span>
                                        <div style="font-size: 11px; color: #9CA3AF; margin-top:4px;"><?= $c['mes'] ?>/<?= $c['anio'] ?></div>
                                    </td>
                                    <td style="padding: 12px; font-weight: 800; color: #111827;">S/ <?= number_format($c['monto'], 2) ?></td>
                                    <td style="padding: 12px;">
                                        <?php
                                        $s_bg = ($c['estado_servicio'] == 'Activo') ? '#D1FAE5' : ($c['estado_servicio'] == 'Suspendido' ? '#FEF3C7' : '#F3F4F6');
                                        $s_color = ($c['estado_servicio'] == 'Activo') ? '#065F46' : ($c['estado_servicio'] == 'Suspendido' ? '#92400E' : '#4B5563');
                                        ?>
                                        <span class="badge" style="background: <?= $s_bg ?>; color: <?= $s_color ?>; font-size: 10px; border:none;"><?= $c['estado_servicio'] ?></span>
                                    </td>
                                    <td style="padding: 12px; text-align: center;">
                                        <form method="POST">
                                            <input type="hidden" name="form_type" value="procesar_pago">
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
        </div>

        <!-- SECCIÓN RESUMEN -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card" style="border-top: 4px solid #3B82F6; position: sticky; top: 20px;">
                <h3 style="color: #3B82F6; margin-top:0;">📊 Resumen Hoy</h3>
                <div style="text-align: center; padding: 15px 0;">
                    <div style="font-size: 12px; color: #6B7280; margin-bottom: 5px;">Recaudado por enviar:</div>
                    <div style="font-size: 32px; font-weight: 800; color: #3B82F6;">S/ <?= number_format($total_acumulado, 2) ?></div>
                    <div style="font-size: 11px; color: #9CA3AF; margin-top: 5px;"><?= count($cobros_por_enviar) ?> cobros realizados</div>
                </div>

                <?php if($total_acumulado > 0): ?>
                    <form method="POST">
                        <input type="hidden" name="form_type" value="enviar_recaudacion_barrio">
                        <button type="submit" class="btn-primary" style="width: 100%; background: #3B82F6; margin-top: 10px; font-size: 13px;">
                            Enviar al Barrio
                        </button>
                    </form>
                <?php else: ?>
                    <button disabled style="width: 100%; padding: 12px; background: #F3F4F6; color: #9CA3AF; border: none; border-radius: 8px; cursor: not-allowed; font-size: 13px;">
                        Nada por enviar
                    </button>
                <?php endif; ?>
                
                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #E5E7EB; font-size: 11px; color: #6B7280; line-height: 1.4;">
                    <strong>Nota:</strong> Al presionar "Enviar", notificas al encargado de barrio que tienes este dinero físicamente.
                </div>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
