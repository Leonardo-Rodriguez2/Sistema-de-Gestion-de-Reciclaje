<?php
// views/barrio/configuracion.php
$user = check_dashboard_access([5]);

// 1. Obtener el barrio del encargado
$barrioStmt = $pdo->prepare("SELECT barrio_id FROM detalles_encargado_barrio WHERE usuario_id = ?");
$barrioStmt->execute([$user['id']]);
$barrio_id = $barrioStmt->fetchColumn();

// 2. Obtener la configuración actual
$configStmt = $pdo->prepare("SELECT * FROM configuraciones_barrio WHERE barrio_id = ?");
$configStmt->execute([$barrio_id]);
$config = $configStmt->fetch(PDO::FETCH_ASSOC);

// Si no existe, usar valores por defecto (aunque la migración debería haberlos creado)
if (!$config) {
    $config = ['cuota_mensual' => 10.00, 'multa_renovacion' => 5.00];
}

$title = "Configuración de Tasas - EcoCusco";
$header_title = "Configuración del Barrio";
$header_subtitle = "Define los montos de cobro y multas para tu jurisdicción.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <style>@media(max-width:768px){.config-flex{flex-direction:column!important;align-items:stretch!important}}</style>
    <div class="config-flex" style="display: flex; justify-content: center; padding: 30px 15px; gap: 30px;">

    <div class="card" style="margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="background: #E0F2FE; color: #0369A1; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 24px;">
                <i class="fas fa-money-check-alt"></i>
            </div>
            <h3 style="margin: 0;">Parámetros Financieros</h3>
            <p style="color: #6B7280; font-size: 14px;">Estos valores se aplicarán a todas las viviendas del barrio.</p>
        </div>

        <form action="router.php?page=configuracion" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="hidden" name="sid">
            <input type="hidden" name="action" value="actualizar_configuracion_barrio">
            
            <div class="form-group" style="max-width: 90%;">
                <label style="font-weight: 700; display: block; margin-bottom: 8px; color: #374151;">💰 Cuota Mensual de Servicio (S/)</label>
                <input type="number" step="0.01" name="cuota_mensual" value="<?= number_format($config['cuota_mensual'], 2, '.', '') ?>" class="form-control" required style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 8px;">
                <small style="color: #6B7280;">Monto base que se genera cada mes por vivienda activa.</small>
            </div>

            <div class="form-group" style="margin-top: 10px; max-width: 90%;">
                <label style="font-weight: 700; display: block; margin-bottom: 8px; color: #374151;">⚠️ Multa por Renovación de Servicio (S/)</label>
                <input type="number" step="0.01" name="multa_renovacion" value="<?= number_format($config['multa_renovacion'], 2, '.', '') ?>" class="form-control" required style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 8px;">
                <small style="color: #6B7280;">Monto adicional que se cobrará cuando una vivienda solicite reactivar el servicio tras una baja.</small>
            </div>

            <div style="margin-top: 20px; padding: 15px; background: #FFF7ED; border-left: 4px solid #F97316; border-radius: 4px;">
                <p style="margin: 0; font-size: 13px; color: #9A3412;">
                    <strong>Nota:</strong> Los cambios afectarán solo a los nuevos cobros y solicitudes generadas a partir de este momento.
                </p>
            </div>

            <button type="submit" class="btn-primary" style="background: #0F172A; color: white; padding: 14px; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; margin-top: 10px;">
                💾 Guardar Configuración
            </button>
        </form>
    </div>


    <div class="card" style="margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="background: #E0F2FE; color: #0369A1; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 24px;">
                <i class="fas fa-info-circle"></i>
            </div>
            <h3 style="margin: 0;">Información Importante</h3>
            <p style="color: #6B7280; font-size: 14px;">Asegúrate de comunicar cualquier cambio a los residentes para evitar confusiones.</p>
        </div>
    </div>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
