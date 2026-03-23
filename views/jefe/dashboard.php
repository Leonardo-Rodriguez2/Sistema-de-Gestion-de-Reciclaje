<?php
// views/jefe_cuadra_dashboard_view.php
$user = check_dashboard_access([1, 5]); // Admin o Jefe de Cuadra

// Obtener barrios para el formulario
$barriosStmt = $pdo->query("SELECT id, nombre FROM barrios ORDER BY nombre");
$barrios = $barriosStmt->fetchAll(PDO::FETCH_ASSOC);

// Si es Jefe de Cuadra, solo ve sus viviendas. Si es Admin, ve todas (o podemos filtrar).
$jefe_id = $user['id'];
$where_clause = ($user['rol_id'] == 5) ? "WHERE v.jefe_cuadra_id = $jefe_id" : "";

$viviendasStmt = $pdo->query("SELECT v.*, b.nombre as barrio_nombre 
                              FROM viviendas v 
                              JOIN barrios b ON v.barrio_id = b.id 
                              $where_clause 
                              ORDER BY v.fecha_registro DESC");
$viviendas = $viviendasStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener cobros pendientes de su cuadra
$cobrosStmt = $pdo->query("SELECT c.*, v.propietario, v.direccion, v.numero_casa 
                           FROM cobros c 
                           JOIN viviendas v ON c.vivienda_id = v.id 
                           $where_clause AND c.estado != 'Pagado'
                           ORDER BY c.fecha_vencimiento ASC");
$cobros_pendientes = $cobrosStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Mi Cuadra - EcoCusco";
$header_title = "Gestión de Cuadra";
$header_subtitle = "Registra viviendas y pagos de tus vecinos.";
$user_greeting = "Jefe de Cuadra: " . $user['nombre'];

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Stats -->
    <?php render_dashboard_stats([
        ['title' => 'Mis Viviendas', 'value' => count($viviendas), 'color' => '#4B5563', 'icon' => '🏠'],
        ['title' => 'Cobros Pendientes', 'value' => count($cobros_pendientes), 'color' => '#92400E', 'icon' => '⏳'],
        ['title' => 'Barrios Asignados', 'value' => count(array_unique(array_column($viviendas, 'barrio_id'))), 'color' => '#1E40AF', 'icon' => '📍']
    ]); ?>

    <style>
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px; }
        .card-title { font-weight: 700; font-size: 16px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; color: var(--secondary); border-bottom: 1px solid #F3F4F6; padding-bottom: 10px; }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; margin-bottom: 4px; font-size: 12px; font-weight: 600; color: #6B7280; text-transform: uppercase; }
        .form-control { width: 100%; padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 13px; font-family: inherit; }
        .form-control:focus { outline: none; border-color: #10B981; }
        .table-mini { width: 100%; border-collapse: collapse; font-size: 13px; }
        .table-mini th { text-align: left; padding: 10px 8px; border-bottom: 1px solid #E5E7EB; color: #6B7280; font-size: 11px; text-transform: uppercase; }
        .table-mini td { padding: 10px 8px; border-bottom: 1px solid #F9FAFB; }
        @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="dashboard-grid">
        <!-- Registrar Vivienda -->
        <div class="card">
            <div class="card-title">
                <span>➕ Registrar Nueva Vivienda</span>
            </div>
            <form method="POST">
                <input type="hidden" name="form_type" value="nuevo_vecino">
                <div class="form-group">
                    <label>Nombre del Propietario / Familia</label>
                    <input type="text" name="propietario" class="form-control" placeholder="Ej: Familia Quispe" required>
                </div>
                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label>Barrio</label>
                        <select name="barrio_id" class="form-control" required>
                            <?php foreach($barrios as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= $b['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control" placeholder="987654321">
                    </div>
                </div>
                <div class="form-group" style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                    <div>
                        <label>Dirección / Calle</label>
                        <input type="text" name="direccion" class="form-control" placeholder="Av. Principal" required>
                    </div>
                    <div>
                        <label>N° Casa</label>
                        <input type="text" name="numero" class="form-control" placeholder="123">
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">Registrar Vivienda</button>
            </form>
        </div>

        <!-- Cobros Pendientes -->
        <div class="card">
            <div class="card-title">
                <span>💰 Reportar Pagos Recibidos</span>
            </div>
            <div style="max-height: 400px; overflow-y: auto;">
                <table class="table-mini">
                    <thead>
                        <tr>
                            <th>Dueño / Dirección</th>
                            <th>Monto</th>
                            <th>Mes</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cobros_pendientes)): ?>
                            <tr><td colspan="4" style="text-align: center; color: #9CA3AF; padding: 20px;">No hay cobros pendientes en tu cuadra.</td></tr>
                        <?php endif; ?>
                        <?php foreach($cobros_pendientes as $c): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;"><?= htmlspecialchars($c['propietario']) ?></div>
                                    <div style="font-size: 12px; color: #6B7280;"><?= htmlspecialchars($c['direccion'] . ' ' . $c['numero_casa']) ?></div>
                                </td>
                                <td style="font-weight: 700; color: #065F46;">S/ <?= number_format($c['monto'], 2) ?></td>
                                <td><?= date('M', mktime(0, 0, 0, $c['mes'], 10)) ?> <?= $c['anio'] ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('¿Confirmas que recibiste el pago de esta vivienda?')">
                                        <input type="hidden" name="form_type" value="procesar_pago">
                                        <input type="hidden" name="cobro_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn-primary" style="padding: 5px 10px; font-size: 12px;">PAGÓ</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Lista de Viviendas -->
    <div class="card" style="margin-top: 20px;">
        <div class="card-title">📋 Mis Viviendas Registradas</div>
        <div class="table-container" style="overflow-x: auto;">
            <table class="table-mini">
                <thead>
                    <tr>
                        <th>Propietario</th>
                        <th>Dirección</th>
                        <th>Barrio</th>
                        <th>Teléfono</th>
                        <th>Fecha Reg.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($viviendas as $v): ?>
                        <tr>
                            <td style="font-weight: 600;"><?= htmlspecialchars($v['propietario']) ?></td>
                            <td><?= htmlspecialchars($v['direccion'] . ' ' . $v['numero_casa']) ?></td>
                            <td><span class="badge" style="background: #E5E7EB;"><?= htmlspecialchars($v['barrio_nombre']) ?></span></td>
                            <td><?= htmlspecialchars($v['telefono'] ?: '-') ?></td>
                            <td style="color: #6B7280;"><?= date('d/m/Y', strtotime($v['fecha_registro'])) ?></td>
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
