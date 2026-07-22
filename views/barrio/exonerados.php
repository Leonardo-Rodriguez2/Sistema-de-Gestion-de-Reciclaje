<?php
$user = check_dashboard_access([5]);

$barrioStmt = $pdo->prepare("SELECT barrio_id FROM detalles_encargado_barrio WHERE usuario_id = ?");
$barrioStmt->execute([$user['id']]);
$barrio_id = (int)$barrioStmt->fetchColumn();

$tab = $_GET['tab'] ?? 'pendientes';

// Obtener solicitudes pendientes
$pendientesStmt = $pdo->prepare("
    SELECT e.*, v.numero_casa, v.propietario, c.nombre as calle_nombre, u.nombre as creador_nombre, u.apellido as creador_apellido
    FROM exenciones_cobro e
    JOIN viviendas v ON e.vivienda_id = v.id
    JOIN calles c ON e.calle_id = c.id
    LEFT JOIN usuarios u ON e.creado_por = u.id
    WHERE e.barrio_id = ? AND e.estado = 'Pendiente'
    ORDER BY e.fecha_creacion DESC
");
$pendientesStmt->execute([$barrio_id]);
$pendientes = $pendientesStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener viviendas exentas activas
$exentasStmt = $pdo->prepare("
    SELECT e.*, v.numero_casa, v.propietario, c.nombre as calle_nombre, 
           u.nombre as aprobador_nombre, u.apellido as aprobador_apellido,
           uc.nombre as creador_nombre, uc.apellido as creador_apellido
    FROM exenciones_cobro e
    JOIN viviendas v ON e.vivienda_id = v.id
    JOIN calles c ON e.calle_id = c.id
    LEFT JOIN usuarios u ON e.aprobado_por = u.id
    LEFT JOIN usuarios uc ON e.creado_por = uc.id
    WHERE e.barrio_id = ? AND e.estado = 'Aprobado'
    ORDER BY e.fecha_revision DESC
");
$exentasStmt->execute([$barrio_id]);
$exentas = $exentasStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener viviendas activas del barrio para agregar directo
$viviendasStmt = $pdo->prepare("
    SELECT v.id, v.numero_casa, v.propietario, c.nombre as calle_nombre, v.exento_cobro
    FROM viviendas v
    JOIN calles c ON v.calle_id = c.id
    WHERE v.barrio_id = ? AND v.estado_servicio = 'Activo'
    ORDER BY c.nombre, v.numero_casa
");
$viviendasStmt->execute([$barrio_id]);
$viviendas = $viviendasStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Exenciones de Cobro - EcoCusco";
$header_title = "🛡️ Exenciones de Cobro";
$header_subtitle = "Gestiona qué viviendas quedan exoneradas del pago mensual.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <style>
    .ex-tab { padding: 10px 18px; border-radius: 8px 8px 0 0; font-weight: 600; font-size: 13px; cursor: pointer; border: 1px solid #E5E7EB; border-bottom: none; background: #F9FAFB; color: #6B7280; text-decoration: none; display: inline-block; }
    .ex-tab.active { background: white; color: #111827; border-bottom: 2px solid #8B5CF6; }
    .ex-tab-bar { margin-bottom: 0; display: flex; gap: 4px; }
    .ex-content { border: 1px solid #E5E7EB; border-radius: 0 8px 8px 8px; padding: 14px; background: white; margin-top: -1px; }
    @media(max-width:600px){.ex-tab{font-size:11px;padding:8px 10px}.ex-content{padding:12px}}
    .ex-card { border:1px solid #E5E7EB; border-radius:10px; padding:14px; background:white; transition:0.2s; }
    .ex-card:hover { box-shadow:0 2px 8px rgba(0,0,0,0.06); }
    .ex-badge { padding:2px 8px; border-radius:4px; font-size:10px; font-weight:700; }
    .ex-info { font-size:11px; color:#6B7280; }
    .ex-label { font-size:10px; color:#9CA3AF; font-weight:600; text-transform:uppercase; }
    </style>

    <div class="ex-tab-bar">
        <a href="router.php?page=exonerados&tab=pendientes" class="ex-tab <?= $tab == 'pendientes' ? 'active' : '' ?>">
            📩 Solicitudes Pendientes (<?= count($pendientes) ?>)
        </a>
        <a href="router.php?page=exonerados&tab=exentas" class="ex-tab <?= $tab == 'exentas' ? 'active' : '' ?>">
            🛡️ Viviendas Exoneradas (<?= count($exentas) ?>)
        </a>
        <a href="router.php?page=exonerados&tab=agregar" class="ex-tab <?= $tab == 'agregar' ? 'active' : '' ?>">
            ➕ Exonerar Directo
        </a>
    </div>

    <?php if ($tab == 'pendientes'): ?>
    <div class="ex-content">
        <p style="color:#6B7280; font-size:13px; margin-top:0;">
            Estas son las solicitudes que los encargados de calle han enviado para exonerar viviendas.
            Revisa el motivo y decide si apruebas o rechazas.
        </p>

        <?php if(empty($pendientes)): ?>
            <div style="text-align:center; padding:50px 20px; color:#9CA3AF;">
                <div style="font-size:50px; margin-bottom:10px;">📭</div>
                No hay solicitudes de exención pendientes.
            </div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <?php foreach($pendientes as $ex): ?>
                <div class="ex-card" style="border-left:4px solid #F59E0B;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px;">
                        <div>
                            <div style="font-weight:700; font-size:15px;">
                                🏠 Casa #<?= htmlspecialchars($ex['numero_casa'] ?: '?') ?>
                                <span style="font-weight:400; font-size:12px; color:#6B7280;">— <?= htmlspecialchars($ex['calle_nombre']) ?></span>
                            </div>
                            <div style="font-size:13px; color:#374151; margin-top:2px;">
                                <?= htmlspecialchars($ex['propietario']) ?>
                            </div>
                        </div>
                        <span class="ex-badge" style="background:#FEF3C7; color:#92400E;">⏳ PENDIENTE</span>
                    </div>

                    <div style="margin-top:10px; display:flex; gap:15px; flex-wrap:wrap; font-size:12px; color:#6B7280;">
                        <span><strong>Motivo:</strong> 
                            <?php $tipos = ['pobreza'=>'Pobreza / Dificultad económica', 'adulto_mayor'=>'Adulto mayor / Jubilado', 'empleado'=>'Empleado de la empresa', 'otro'=>'Otro']; ?>
                            <?= $tipos[$ex['tipo_exencion']] ?? $ex['tipo_exencion'] ?>
                        </span>
                        <span><strong>Solicitó:</strong> <?= htmlspecialchars(($ex['creador_nombre'] ?? '') . ' ' . ($ex['creador_apellido'] ?? '')) ?></span>
                        <span><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($ex['fecha_creacion'])) ?></span>
                    </div>

                    <?php if($ex['descripcion']): ?>
                        <div style="margin-top:8px; padding:10px; background:#FFFBEB; border-radius:6px; font-size:12px; color:#92400E;">
                            💬 <?= nl2br(htmlspecialchars($ex['descripcion'])) ?>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                        <form method="POST" action="router.php?page=exonerados&tab=pendientes" style="display:inline;">
                            <input type="hidden" name="form_type" value="aprobar_exencion">
                            <input type="hidden" name="exencion_id" value="<?= $ex['id'] ?>">
                            <button type="submit" style="background:#10B981; color:white; border:none; border-radius:6px; padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:4px;"
                                onclick="return confirm('¿Aprobar esta exención?\n\nLa vivienda quedará 🛡️ EXENTA DE PAGO y ya no aparecerá en los cobros mensuales.')">
                                ✅ Aprobar Exención
                            </button>
                        </form>

                        <form method="POST" action="router.php?page=exonerados&tab=pendientes" style="display:flex; gap:4px; align-items:center;">
                            <input type="hidden" name="form_type" value="rechazar_exencion">
                            <input type="hidden" name="exencion_id" value="<?= $ex['id'] ?>">
                            <input type="text" name="motivo_rechazo" placeholder="Escribe el motivo del rechazo..." required
                                style="padding:8px 10px; border:1px solid #E5E7EB; border-radius:6px; font-size:12px; width:220px;">
                            <button type="submit" style="background:#EF4444; color:white; border:none; border-radius:6px; padding:8px 14px; font-size:12px; font-weight:600; cursor:pointer;">
                                ❌ Rechazar
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php elseif ($tab == 'exentas'): ?>
    <div class="ex-content">
        <p style="color:#6B7280; font-size:13px; margin-top:0;">
            Lista de viviendas que están exoneradas del pago mensual. Puedes revocar la exención
            si la situación cambió y la vivienda debe volver a pagar.
        </p>

        <?php if(empty($exentas)): ?>
            <div style="text-align:center; padding:50px 20px; color:#9CA3AF;">
                <div style="font-size:50px; margin-bottom:10px;">🛡️</div>
                No hay viviendas exoneradas actualmente.
                <div style="font-size:12px; margin-top:6px;">Puedes agregar una desde la pestaña <strong>"Exonerar Directo"</strong>.</div>
            </div>
        <?php else: ?>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:12px;">
                <?php foreach($exentas as $ex): ?>
                <div class="ex-card" style="border-left:4px solid #8B5CF6; background:#FAFAFF;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div>
                            <div style="font-weight:700; font-size:14px;">
                                🏠 Casa #<?= htmlspecialchars($ex['numero_casa'] ?: '?') ?>
                            </div>
                            <div style="font-size:12px; color:#6B7280;">
                                <?= htmlspecialchars($ex['propietario']) ?>
                            </div>
                            <div style="font-size:11px; color:#6B7280;">
                                📍 <?= htmlspecialchars($ex['calle_nombre']) ?>
                            </div>
                        </div>
                        <span class="ex-badge" style="background:#EDE9FE; color:#6D28D9; border:none;">🛡️ EXENTA</span>
                    </div>

                    <div style="margin-top:10px; display:grid; grid-template-columns:1fr 1fr; gap:6px; font-size:11px; color:#6B7280;">
                        <span><span class="ex-label">Motivo</span><br>
                            <?php $tipos = ['pobreza'=>'Pobreza', 'adulto_mayor'=>'Adulto mayor', 'empleado'=>'Empleado', 'otro'=>'Otro']; ?>
                            <?= $tipos[$ex['tipo_exencion']] ?? $ex['tipo_exencion'] ?>
                        </span>
                        <span><span class="ex-label">Aprobó</span><br>
                            <?= htmlspecialchars(($ex['aprobador_nombre'] ?? '') . ' ' . ($ex['aprobador_apellido'] ?? '')) ?>
                        </span>
                        <span><span class="ex-label">Solicitó</span><br>
                            <?= htmlspecialchars(($ex['creador_nombre'] ?? '') . ' ' . ($ex['creador_apellido'] ?? '')) ?>
                        </span>
                        <span><span class="ex-label">Desde</span><br>
                            <?= date('d/m/Y', strtotime($ex['fecha_revision'])) ?>
                        </span>
                    </div>

                    <?php if($ex['descripcion']): ?>
                        <div style="margin-top:8px; font-size:11px; color:#6D28D9; background:#F5F3FF; padding:6px 8px; border-radius:4px;">
                            💬 <?= nl2br(htmlspecialchars($ex['descripcion'])) ?>
                        </div>
                    <?php endif; ?>

                    <div style="margin-top:10px; padding-top:8px; border-top:1px solid #F3F4F6;">
                        <form method="POST" action="router.php?page=exonerados&tab=exentas" style="display:inline;">
                            <input type="hidden" name="form_type" value="quitar_exencion">
                            <input type="hidden" name="exencion_id" value="<?= $ex['id'] ?>">
                            <button type="submit" style="background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; border-radius:6px; padding:6px 14px; font-size:12px; font-weight:600; cursor:pointer; width:100%;"
                                onclick="return confirm('¿REVOCAR esta exención?\n\nCasa #<?= htmlspecialchars($ex['numero_casa'] ?: '?') ?> — <?= htmlspecialchars($ex['propietario']) ?>\n\nAl revocar, la vivienda VOLVERÁ A GENERAR COBROS mensuales.\n¿Estás seguro?')">
                                🔄 Revocar Exención — Volverá a pagar
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php elseif ($tab == 'agregar'): ?>
    <div class="ex-content">
        <p style="color:#6B7280; font-size:13px; margin-top:0;">
            Agrega una vivienda directamente a la lista de exoneradas. Esto <strong>no requiere</strong>
            solicitud del encargado de calle — tú decides como encargado de barrio.
        </p>

        <div style="max-width:500px; margin-top:15px;">
            <form method="POST" action="router.php?page=exonerados&tab=agregar">
                <input type="hidden" name="form_type" value="agregar_exencion_directa">

                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:700; font-size:12px; color:#374151; display:block; margin-bottom:4px;">VIVIENDA A EXONERAR</label>
                    <select name="vivienda_id" required style="width:100%; padding:10px; border:1px solid #E5E7EB; border-radius:6px; font-size:13px;">
                        <option value="">-- Seleccionar vivienda --</option>
                        <?php foreach($viviendas as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= $v['exento_cobro'] ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($v['calle_nombre']) ?> — Casa #<?= htmlspecialchars($v['numero_casa'] ?: '?') ?> — <?= htmlspecialchars($v['propietario']) ?>
                                <?= $v['exento_cobro'] ? '(YA EXONERADA)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:700; font-size:12px; color:#374151; display:block; margin-bottom:4px;">MOTIVO DE LA EXONERACIÓN</label>
                    <select name="tipo_exencion" required style="width:100%; padding:10px; border:1px solid #E5E7EB; border-radius:6px; font-size:13px;">
                        <option value="pobreza">Pobreza / Dificultad económica</option>
                        <option value="adulto_mayor">Adulto mayor / Jubilado</option>
                        <option value="empleado">Empleado de la empresa</option>
                        <option value="otro">Otro motivo</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:700; font-size:12px; color:#374151; display:block; margin-bottom:4px;">DESCRIPCIÓN (opcional pero recomendado)</label>
                    <textarea name="descripcion" rows="3" placeholder="Ej: Doña María tiene 78 años, vive sola y no tiene ingresos fijos..." style="width:100%; padding:10px; border:1px solid #E5E7EB; border-radius:6px; font-size:13px; resize:vertical;"></textarea>
                </div>

                <button type="submit" class="btn-primary" style="padding:12px 24px; background:#8B5CF6;"
                    onclick="return confirm('¿Exonerar esta vivienda?\n\nQuedará 🛡️ EXENTA DE PAGO y no aparecerá en los cobros mensuales.')">
                    🛡️ Exonerar Vivienda
                </button>
            </form>
        </div>

        <div style="margin-top:20px; padding:12px; background:#F5F3FF; border-radius:8px; border:1px solid #EDE9FE; font-size:12px; color:#6D28D9;">
            <strong>💡 Información:</strong> Todas las viviendas exoneradas aparecerán en la pestaña 
            <strong>"Viviendas Exoneradas"</strong> y en el listado del encargado de calle con un 
            badge 🛡️ indicando que no requieren pago. Si la situación cambia, puedes revocar 
            la exención desde la pestaña de Viviendas Exoneradas.
        </div>
    </div>
    <?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
