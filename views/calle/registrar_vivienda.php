<?php
// views/calle/registrar_vivienda.php
$user = check_dashboard_access([6]);

$title = "Solicitar Registro - EcoCusco";
$header_title = "Nueva Vivienda";
$header_subtitle = "Completa los datos para enviar una solicitud de registro al encargado de barrio.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <form method="POST">
            <input type="hidden" name="form_type" value="solicitar_alta">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display:block; font-size:11px; font-weight:600; color:#6B7280; margin-bottom:4px;">NOMBRE DEL PROPIETARIO</label>
                <input type="text" name="propietario" class="form-control" style="width:100%; padding:10px; border:1px solid #E5E7EB; border-radius:8px;" placeholder="Ej: Juan Pérez" required>
            </div>

            <div class="form-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div class="form-group">
                    <label style="display:block; font-size:11px; font-weight:600; color:#6B7280; margin-bottom:4px;">NÚMERO DE CASA</label>
                    <input type="text" name="numero_casa" class="form-control" style="width:100%; padding:10px; border:1px solid #E5E7EB; border-radius:8px;" placeholder="Ej: B-12" required>
                </div>
                <div class="form-group">
                    <label style="display:block; font-size:11px; font-weight:600; color:#6B7280; margin-bottom:4px;">TELÉFONO (OPCIONAL)</label>
                    <input type="text" name="telefono" class="form-control" style="width:100%; padding:10px; border:1px solid #E5E7EB; border-radius:8px;" placeholder="987654321">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display:block; font-size:11px; font-weight:600; color:#6B7280; margin-bottom:4px;">REFERENCIA / NOTA</label>
                <textarea name="referencia" class="form-control" style="width:100%; padding:10px; border:1px solid #E5E7EB; border-radius:8px; height:80px;" placeholder="Ej: Casa de color verde, frente al parque."></textarea>
            </div>

            <div style="display: flex; gap: 10px;">
                <a href="router.php?page=viviendas" class="btn-cancel" style="flex:1; text-align:center; padding:10px; border-radius:8px; text-decoration:none; background:#F3F4F6; color:#4B5563; font-weight:600;">Cancelar</a>
                <button type="submit" class="btn-primary" style="flex:2; padding:10px; border-radius:8px;">Enviar Solicitud</button>
            </div>
        </form>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
