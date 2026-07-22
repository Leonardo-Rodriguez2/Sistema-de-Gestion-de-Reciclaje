<?php
$user = check_dashboard_access([6]);

$calleStmt = $pdo->prepare("SELECT dc.calle_id, c.barrio_id, c.nombre as calle_nombre FROM detalles_encargado_calle dc JOIN calles c ON dc.calle_id = c.id WHERE dc.usuario_id = ?");
$calleStmt->execute([$user['id']]);
$calleData = $calleStmt->fetch(PDO::FETCH_ASSOC);
$calle_id = $calleData['calle_id'];
$barrio_id = $calleData['barrio_id'];
$calle_nombre = $calleData['calle_nombre'];

$viviendasStmt = $pdo->prepare("SELECT id, numero_casa, propietario, exento_cobro FROM viviendas WHERE calle_id = ? AND estado_servicio = 'Activo' ORDER BY numero_casa");
$viviendasStmt->execute([$calle_id]);
$viviendas = $viviendasStmt->fetchAll(PDO::FETCH_ASSOC);

$exencionesStmt = $pdo->prepare("
    SELECT e.*, v.numero_casa, v.propietario 
    FROM exenciones_cobro e 
    JOIN viviendas v ON e.vivienda_id = v.id 
    WHERE e.calle_id = ? 
    ORDER BY e.fecha_creacion DESC
");
$exencionesStmt->execute([$calle_id]);
$exenciones = $exencionesStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Solicitar Exoneración - EcoCusco";
$header_title = "Exoneraciones de Cobro";
$header_subtitle = "Solicita que una vivienda quede exonerada del pago mensual.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="grid">
        <div class="card">
            <h3 style="margin-top:0;">Nueva Solicitud de Exoneración</h3>
            <p style="color:#6B7280; font-size:13px; margin-bottom:16px;">
                Selecciona una vivienda de tu calle (<strong><?= htmlspecialchars($calle_nombre) ?></strong>) y explica el motivo.
            </p>
            <form method="POST" action="router.php?page=solicitar_exoneracion">
                <input type="hidden" name="form_type" value="solicitar_exoneracion">

                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="font-weight:700; font-size:12px; color:#374151; display:block; margin-bottom:4px;">VIVIENDA</label>
                    <select name="vivienda_id" required style="width:100%; padding:10px; border:1px solid #E5E7EB; border-radius:6px; font-size:13px;">
                        <option value="">-- Seleccionar vivienda --</option>
                        <?php foreach($viviendas as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= $v['exento_cobro'] ? 'disabled' : '' ?>>
                                Casa #<?= htmlspecialchars($v['numero_casa'] ?: '?') ?> - <?= htmlspecialchars($v['propietario']) ?>
                                <?= $v['exento_cobro'] ? '(YA EXENTA)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                        <label style="font-weight:700; font-size:12px; color:#374151; display:block; margin-bottom:4px;">TIPO DE EXONERACIÓN</label>
                    <select name="tipo_exencion" required style="width:100%; padding:10px; border:1px solid #E5E7EB; border-radius:6px; font-size:13px;">
                        <option value="pobreza">Pobreza / Dificultad económica</option>
                        <option value="adulto_mayor">Adulto mayor / Jubilado</option>
                        <option value="empleado">Empleado de la empresa</option>
                        <option value="otro">Otro (especificar)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="font-weight:700; font-size:12px; color:#374151; display:block; margin-bottom:4px;">DESCRIPCIÓN / MOTIVO</label>
                    <textarea name="descripcion" rows="3" placeholder="Explica con detalle por qué esta vivienda debería quedar exenta..." style="width:100%; padding:10px; border:1px solid #E5E7EB; border-radius:6px; font-size:13px; resize:vertical;"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:12px;">
                    Enviar Solicitud al Barrio
                </button>
            </form>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Mis Solicitudes de Exención</h3>
            <p style="color:#6B7280; font-size:13px; margin-bottom:16px;">
                Historial de solicitudes enviadas al encargado de barrio.
            </p>
            <?php if(empty($exenciones)): ?>
                <div style="text-align:center; padding:30px; color:#9CA3AF;">Aún no has realizado solicitudes de exoneración.</div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <?php foreach($exenciones as $ex): ?>
                        <div style="border:1px solid #E5E7EB; border-radius:8px; padding:12px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                <span style="font-weight:700; font-size:13px;">
                                    Casa #<?= htmlspecialchars($ex['numero_casa'] ?: '?') ?>
                                </span>
                                <?php if($ex['estado'] == 'Pendiente'): ?>
                                    <span class="badge" style="background:#FEF3C7; color:#92400E; border:none;">PENDIENTE</span>
                                <?php elseif($ex['estado'] == 'Aprobado'): ?>
                                    <span class="badge" style="background:#D1FAE5; color:#065F46; border:none;">APROBADO</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#FEE2E2; color:#991B1B; border:none;">RECHAZADO</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:11px; color:#6B7280;">
                                <?= htmlspecialchars($ex['propietario']) ?> — 
                                Tipo: <?= htmlspecialchars($ex['tipo_exencion']) ?>
                            </div>
                            <?php if($ex['descripcion']): ?>
                                <div style="font-size:11px; color:#9CA3AF; margin-top:4px; font-style:italic;">
                                    <?= htmlspecialchars($ex['descripcion']) ?>
                                </div>
                            <?php endif; ?>
                            <div style="font-size:10px; color:#9CA3AF; margin-top:4px;">
                                <?= date('d/m/Y', strtotime($ex['fecha_creacion'])) ?>
                                <?php if($ex['motivo_rechazo']): ?> — Motivo: <?= htmlspecialchars($ex['motivo_rechazo']) ?><?php endif; ?>
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
