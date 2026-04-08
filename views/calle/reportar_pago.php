<?php
// views/calle/reportar_pago.php
$user = check_dashboard_access([6]);

$vivienda_id = isset($_GET['vivienda_id']) ? (int)$_GET['vivienda_id'] : null;

// Obtener datos de la vivienda y su último cobro pendiente
$stmt = $pdo->prepare("SELECT v.*, c.id as cobro_id, c.monto, c.estado as cobro_estado 
                        FROM viviendas v 
                        LEFT JOIN cobros c ON v.id = c.vivienda_id AND c.estado = 'Pendiente'
                        WHERE v.id = ? AND v.calle_id = (SELECT calle_id FROM detalles_encargado_calle WHERE usuario_id = ?)");
$stmt->execute([$vivienda_id, $user['id']]);
$vivienda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vivienda) {
    echo "<div class='alert error'>Vivienda no encontrada o no pertenece a tu calle.</div>";
    return;
}

$title = "Reportar Pago - EcoCusco";
$header_title = "Gestión de Cobro";
$header_subtitle = "Registra el pago mensual de los vecinos.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div style="display: flex; gap: 20px;">
        <!-- Card Info Vivienda -->
        <div class="card" style="flex: 1; border-left: 4px solid #10B981;">
            <div style="font-size: 11px; font-weight: 700; color: #10B981; margin-bottom: 8px; text-transform: uppercase;">PROPIETARIO</div>
            <h2 style="margin: 0; font-size: 20px;"><?= htmlspecialchars($vivienda['propietario']) ?></h2>
            <div style="margin-top: 10px; font-size: 13px; color: #6B7280;">
                📍 <strong>#<?= htmlspecialchars($vivienda['numero_casa']) ?></strong> - <?= htmlspecialchars($vivienda['direccion']) ?>
            </div>
        </div>

        <!-- Card Procesar Pago -->
        <div class="card" style="flex: 1.5;">
            <?php if ($vivienda['cobro_id']): ?>
                <div style="text-align: center; padding: 20px;">
                    <div style="font-size: 32px; font-weight: 700; color: #111827; margin-bottom: 5px;">S/ <?= number_format($vivienda['monto'], 2) ?></div>
                    <div style="font-size: 12px; color: #6B7280; margin-bottom: 25px;">Monto pendiente del mes actual</div>
                    
                    <form method="POST">
                        <input type="hidden" name="form_type" value="procesar_pago">
                        <input type="hidden" name="cobro_id" value="<?= $vivienda['cobro_id'] ?>">
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; font-size: 15px;">✓ Marcar como Pagado</button>
                    </form>
                    <p style="font-size: 11px; color: #9CA3AF; margin-top: 15px;">Al confirmar, el estado cambiará a 'Pagado' para la fiscalización del barrio.</p>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px;">
                    <div style="font-size: 40px; margin-bottom: 15px;">🎉</div>
                    <h3 style="margin:0;">¡Al Día!</h3>
                    <p style="color:#6B7280; font-size: 14px;">Esta vivienda no tiene deudas pendientes en el sistema.</p>
                    <a href="router.php?page=viviendas" class="btn-primary" style="display:inline-block; margin-top:20px; text-decoration:none;">Volver al Listado</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
