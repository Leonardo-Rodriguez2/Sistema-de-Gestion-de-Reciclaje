<?php
// views/barrio/reportar_pago.php
$user = check_dashboard_access([5]);

// 1. Obtener datos del barrio
$barrioStmt = $pdo->prepare("SELECT b.* FROM detalles_encargado_barrio d JOIN barrios b ON d.barrio_id = b.id WHERE d.usuario_id = ?");
$barrioStmt->execute([$user['id']]);
$barrio_info = $barrioStmt->fetch(PDO::FETCH_ASSOC);
$barrio_id = $barrio_info['id'] ?? 0;

// 2. Cobros pendientes del barrio completo
$pendientesStmt = $pdo->prepare("SELECT c.*, v.propietario, v.direccion, v.numero_casa 
                                 FROM cobros c 
                                 JOIN viviendas v ON c.vivienda_id = v.id 
                                 WHERE v.barrio_id = ? AND c.estado != 'Pagado'
                                 ORDER BY c.fecha_vencimiento ASC");
$pendientesStmt->execute([$barrio_id]);
$cobros_pendientes = $pendientesStmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Cobros ya marcados como pagados pero NO enviados al Gestor (por encargado de barrio)
$por_enviarStmt = $pdo->prepare("SELECT c.*, v.propietario 
                                 FROM cobros c 
                                 JOIN viviendas v ON c.vivienda_id = v.id 
                                 WHERE v.barrio_id = ? AND c.estado = 'Pagado' AND c.recaudacion_id IS NULL AND c.tipo_cobro = 'Servicio'");
$por_enviarStmt->execute([$barrio_id]);
$cobros_por_enviar = $por_enviarStmt->fetchAll(PDO::FETCH_ASSOC);
$total_acumulado = array_sum(array_column($cobros_por_enviar, 'monto'));

$title = "Marcar Pagos - EcoCusco";
$header_title = "Gestión de Recaudación";
$header_subtitle = "Administra los pagos, multas y tarifas de tu barrio.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div style="display: grid; grid-template-columns: 1fr 300px; gap: 20px;">
        
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- SECCIÓN CONFIGURACIÓN -->
            <div class="card" style="border-left: 4px solid #3B82F6;">
                <h3 style="margin:0 0 10px 0; font-size:16px;">⚙️ Configuración de Tarifas</h3>
                <form method="POST" style="display: flex; gap: 15px; align-items: flex-end;">
                    <input type="hidden" name="form_type" value="configurar_tarifas">
                    <div style="flex: 1;">
                        <label style="font-size: 11px; font-weight: 700; color: #6B7280;">COSTO SERVICIO MENSUAL / MULTA (S/)</label>
                        <input type="number" step="0.01" name="monto_mensual" value="<?= $barrio_info['monto_mensual'] ?>" 
                               style="width: 100%; padding: 8px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 14px; font-weight: 700;">
                    </div>
                    <button type="submit" class="btn-primary" style="background:#3B82F6; padding: 10px 20px;">Actualizar Tarifas</button>
                </form>
                <p style="font-size: 11px; color: #9CA3AF; margin-top: 10px;">* Este valor se usará para las multas automáticas y nuevos cobros.</p>
            </div>

            <!-- TABLA DE COBROS -->
            <div class="card">
                <h3 style="color: #10B981; margin-top:0;">🏠 Cobros Pendientes en el Barrio</h3>
                <div style="overflow-x: auto;">
                    <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                                <th style="padding: 12px;">Vivienda</th>
                                <th style="padding: 12px;">Tipo / Fecha</th>
                                <th style="padding: 12px;">Monto</th>
                                <th style="padding: 12px; text-align: center;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($cobros_pendientes)): ?>
                                <tr><td colspan="4" style="text-align: center; color: #9CA3AF; padding: 40px;">No hay deudas pendientes en el barrio.</td></tr>
                            <?php endif; ?>
                            <?php foreach($cobros_pendientes as $c): ?>
                                <tr style="border-bottom: 1px solid #F3F4F6;">
                                    <td style="padding: 12px;">
                                        <div style="font-weight: 700;"><?= htmlspecialchars($c['propietario']) ?></div>
                                        <div style="font-size: 11px; color: #6B7280;">#<?= $c['numero_casa'] ?> - <?= htmlspecialchars($c['direccion']) ?></div>
                                    </td>
                                    <td style="padding: 12px;">
                                        <?php $isMulta = ($c['tipo_cobro'] == 'Multa'); ?>
                                        <span class="badge" style="background: <?= $isMulta ? '#FEE2E2' : '#E0F2FE' ?>; color: <?= $isMulta ? '#991B1B' : '#0369A1' ?>; font-size: 10px;">
                                            <?= $c['tipo_cobro'] ?>
                                        </span>
                                        <div style="font-size: 11px; color: #9CA3AF; margin-top:4px;"><?= $c['mes'] ?>/<?= $c['anio'] ?></div>
                                    </td>
                                    <td style="padding: 12px; font-weight: 800; color: #111827;">S/ <?= number_format($c['monto'], 2) ?></td>
                                    <td style="padding: 12px; text-align: center;">
                                        <form method="POST">
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
        </div>

        <!-- SECCIÓN RECAUDACIÓN -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card" style="border-top: 4px solid #F59E0B; position: sticky; top: 20px;">
                <h3 style="color: #F59E0B; margin-top:0;">💰 Por Liquidar</h3>
                <div style="text-align: center; padding: 15px 0;">
                    <div style="font-size: 12px; color: #6B7280; margin-bottom: 5px;">Total en Recaudación:</div>
                    <div style="font-size: 32px; font-weight: 800; color: #F59E0B;">S/ <?= number_format($total_acumulado, 2) ?></div>
                    <div style="font-size: 11px; color: #9CA3AF; margin-top: 5px;"><?= count($cobros_por_enviar) ?> pagos realizados</div>
                </div>

                <?php if($total_acumulado > 0): ?>
                    <form method="POST">
                        <input type="hidden" name="form_type" value="enviar_recaudacion_gestor">
                        <button type="submit" class="btn-primary" style="width: 100%; background: #F59E0B; margin-top: 10px; font-size: 13px;">
                            Enviar al Gestor
                        </button>
                    </form>
                <?php else: ?>
                    <button disabled style="width: 100%; padding: 12px; background: #F3F4F6; color: #9CA3AF; border: none; border-radius: 8px; cursor: not-allowed; font-size: 13px;">
                        Sin fondos por enviar
                    </button>
                <?php endif; ?>
                
                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #E5E7EB; font-size: 11px; color: #6B7280; line-height: 1.4;">
                    <strong>Nota:</strong> Solo se consolidan los pagos de 'Servicio' para la liquidación mensual. Las multas se gestionan por separado.
                </div>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>

