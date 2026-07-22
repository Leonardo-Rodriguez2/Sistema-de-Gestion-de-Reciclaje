<?php
// views/calle/solicitar_baja.php
$user = check_dashboard_access([6]);
global $pdo;

$calleStmt = $pdo->prepare("SELECT dc.calle_id, c.nombre as calle_nombre FROM detalles_encargado_calle dc JOIN calles c ON dc.calle_id = c.id WHERE dc.usuario_id = ?");
$calleStmt->execute([$user['id']]);
$calleData = $calleStmt->fetch(PDO::FETCH_ASSOC);
$calle_id = $calleData['calle_id'];
$calle_nombre = $calleData['calle_nombre'] ?? '';

$viviendasStmt = $pdo->prepare("SELECT id, numero_casa, propietario, estado_servicio FROM viviendas WHERE calle_id = ? AND estado_servicio = 'Activo' ORDER BY numero_casa");
$viviendasStmt->execute([$calle_id]);
$viviendas = $viviendasStmt->fetchAll(PDO::FETCH_ASSOC);

// Solicitudes pendientes de baja
$pendientesStmt = $pdo->prepare("
    SELECT s.*, v.numero_casa, v.propietario 
    FROM solicitudes_vivienda s 
    LEFT JOIN viviendas v ON s.vivienda_id = v.id 
    WHERE s.calle_id = ? AND s.tipo = 'Baja' AND s.estado = 'Pendiente'
    ORDER BY s.fecha_creacion DESC
");
$pendientesStmt->execute([$calle_id]);
$pendientes = $pendientesStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Solicitar Baja - EcoCusco";
$header_title = "Solicitar Baja de Vivienda";
$header_subtitle = "Envía una solicitud de baja al encargado de barrio.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="grid">
        <div class="card">
            <h3 style="margin-top:0;">Nueva Solicitud de Baja</h3>
            <p style="color:#6B7280;font-size:13px;margin-bottom:16px;">
                Selecciona una vivienda de tu calle (<strong><?= htmlspecialchars($calle_nombre) ?></strong>) para solicitar la baja del servicio.
            </p>
            <form method="POST" action="router.php?page=solicitar_baja" onsubmit="return confirm('¿Estás seguro de solicitar la baja?')">
                <input type="hidden" name="action" value="solicitar_baja">

                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:700;font-size:12px;color:#374151;display:block;margin-bottom:4px;">VIVIENDA</label>
                    <select name="vivienda_id" required style="width:100%;padding:10px;border:1px solid #E5E7EB;border-radius:6px;font-size:13px;">
                        <option value="">-- Seleccionar vivienda --</option>
                        <?php foreach($viviendas as $v): ?>
                            <option value="<?= $v['id'] ?>">
                                Casa #<?= htmlspecialchars($v['numero_casa'] ?: '?') ?> - <?= htmlspecialchars($v['propietario']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:12px;">
                    Enviar Solicitud de Baja
                </button>
            </form>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Solicitudes de Baja Pendientes</h3>
            <?php if(empty($pendientes)): ?>
                <div style="text-align:center;padding:30px;color:#9CA3AF;">No tienes solicitudes de baja pendientes.</div>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <?php foreach($pendientes as $p): ?>
                        <div style="border:1px solid #E5E7EB;border-radius:8px;padding:12px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                <span style="font-weight:700;font-size:13px;">
                                    Casa #<?= htmlspecialchars($p['numero_casa'] ?? '?') ?> - <?= htmlspecialchars($p['propietario'] ?? 'N/A') ?>
                                </span>
                                <span class="badge" style="background:#FEF3C7;color:#92400E;border:none;font-size:10px;">PENDIENTE</span>
                            </div>
                            <div style="font-size:11px;color:#6B7280;">
                                Deuda: S/ <?= number_format($p['monto_deuda'] ?? 0, 2) ?>
                            </div>
                            <div style="font-size:10px;color:#9CA3AF;margin-top:4px;">
                                <?= date('d/m/Y', strtotime($p['fecha_creacion'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
