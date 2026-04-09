<?php
// views/calle/dashboard.php
$user = check_dashboard_access([1, 6]); // Admin o Encargado de Calle

// Obtener datos de la calle asignada
$calleStmt = $pdo->prepare("SELECT c.id, c.nombre, c.barrio_id, b.nombre as barrio_nombre 
                           FROM detalles_encargado_calle d 
                           JOIN calles c ON d.calle_id = c.id
                           JOIN barrios b ON c.barrio_id = b.id
                           WHERE d.usuario_id = ?");
$calleStmt->execute([$user['id']]);
$calle_info = $calleStmt->fetch(PDO::FETCH_ASSOC);

if (!$calle_info) {
    die("No tienes una calle asignada. Contacta al administrador.");
}

// Obtener viviendas de su calle
$viviendasStmt = $pdo->prepare("SELECT * FROM viviendas WHERE calle_id = ? ORDER BY numero_casa ASC");
$viviendasStmt->execute([$calle_info['id']]);
$viviendas = $viviendasStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener cobros pendientes de su calle
$cobrosStmt = $pdo->prepare("SELECT c.*, v.propietario, v.numero_casa 
                            FROM cobros c 
                            JOIN viviendas v ON c.vivienda_id = v.id 
                            WHERE v.calle_id = ? AND c.estado != 'Pagado'
                            ORDER BY c.fecha_vencimiento ASC");
$cobrosStmt->execute([$calle_info['id']]);
$cobros_pendientes = $cobrosStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Gestión de Calle - EcoCusco";
$header_title = "Calle: " . htmlspecialchars($calle_info['nombre']);
$header_subtitle = "Barrio: " . htmlspecialchars($calle_info['barrio_nombre']);

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Stats -->
    <?php render_dashboard_stats([
        ['title' => 'Viviendas', 'value' => count($viviendas), 'color' => '#10B981', 'icon' => '🏠'],
        ['title' => 'Pendientes', 'value' => count($cobros_pendientes), 'color' => '#EF4444', 'icon' => '⏳'],
        ['title' => 'Calle', 'value' => $calle_info['nombre'], 'color' => '#3B82F6', 'icon' => '📍']
    ]); ?>

    <style>
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px; }
        .card-title { font-weight: 700; font-size: 16px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; color: var(--secondary); border-bottom: 1px solid #F3F4F6; padding-bottom: 10px; }
        .table-mini { width: 100%; border-collapse: collapse; font-size: 13px; }
        .table-mini th { text-align: left; padding: 10px 8px; border-bottom: 1px solid #E5E7EB; color: #6B7280; font-size: 11px; text-transform: uppercase; }
        .table-mini td { padding: 10px 8px; border-bottom: 1px solid #F9FAFB; }
        @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="dashboard-grid">
        <!-- Solicitar Nueva Vivienda -->
        <div class="card">
            <div class="card-title"><span>➕ Solicitar Registro</span></div>
            <p style="font-size: 12px; color: #6B7280; margin-bottom: 15px;">Completa los datos para que el encargado de barrio apruebe la nueva vivienda.</p>
            <form method="POST">
                <input type="hidden" name="form_type" value="solicitar_alta">
                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="display:block; font-size:11px; font-weight:600; color:#6B7280; margin-bottom:4px;">PROPIETARIO / FAMILIA</label>
                    <input type="text" name="propietario" class="form-control" style="width:100%; padding:8px; border:1px solid #E5E7EB; border-radius:6px;" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label style="display:block; font-size:11px; font-weight:600; color:#6B7280; margin-bottom:4px;">N° CASA</label>
                        <input type="text" name="numero_casa" class="form-control" style="width:100%; padding:8px; border:1px solid #E5E7EB; border-radius:6px;" required>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:600; color:#6B7280; margin-bottom:4px;">REFERENCIA</label>
                        <input type="text" name="referencia" class="form-control" style="width:100%; padding:8px; border:1px solid #E5E7EB; border-radius:6px;">
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">Enviar Solicitud</button>
            </form>
        </div>

        <!-- Cobros -->
        <div class="card">
            <div class="card-title"><span>💰 Pagos Pendientes</span></div>
            <div style="max-height: 300px; overflow-y: auto;">
                <table class="table-mini">
                    <thead>
                        <tr><th>Vivienda</th><th>Monto</th><th>Acción</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cobros_pendientes)): ?>
                            <tr><td colspan="3" style="text-align:center; padding:20px; color:#9CA3AF;">No hay pagos pendientes.</td></tr>
                        <?php endif; ?>
                        <?php foreach($cobros_pendientes as $c): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600;"><?= htmlspecialchars($c['propietario']) ?></div>
                                    <div style="font-size:11px; color:#6B7280;">Casa <?= htmlspecialchars($c['numero_casa']) ?></div>
                                </td>
                                <td style="font-weight:700;">S/ <?= number_format($c['monto'], 2) ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="form_type" value="procesar_pago">
                                        <input type="hidden" name="cobro_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn-primary" style="padding:4px 8px; font-size:11px;">PAGÓ</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 15px; border-top: 1px solid #F3F4F6; padding-top: 15px;">
                <form method="POST" onsubmit="return confirm('¿Confirmas el envío de la recaudación al encargado de barrio?')">
                    <input type="hidden" name="form_type" value="enviar_recaudacion_barrio">
                    <button type="submit" class="btn-secondary" style="width: 100%; background: #1E40AF; color: white;">Enviar Recaudación al Barrio</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Viviendas -->
    <div class="card" style="margin-top: 20px;">
        <div class="card-title">🏠 Viviendas de la Calle</div>
        <table class="table-mini">
            <thead>
                <tr><th>Casa</th><th>Propietario</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach($viviendas as $v): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($v['numero_casa']) ?></strong></td>
                        <td><?= htmlspecialchars($v['propietario']) ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('¿Solicitar baja de este servicio?')">
                                <input type="hidden" name="form_type" value="solicitar_baja">
                                <input type="hidden" name="vivienda_id" value="<?= $v['id'] ?>">
                                <button type="submit" style="background:none; border:none; color:#EF4444; font-size:11px; cursor:pointer; text-decoration:underline;">Solicitar Baja</button>
                            </form>
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
