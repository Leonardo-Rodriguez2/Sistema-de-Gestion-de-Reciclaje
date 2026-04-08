<?php
// views/gestor/recibos.php
$user = check_dashboard_access([1, 2]);

$title = "Reportes y Recibos - EcoCusco";
$header_title = "Centro de Reportes";
$header_subtitle = "Generación de comprobantes y estados de cuenta.";

ob_start();
?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <!-- Card Reporte Mensual -->
        <div class="card" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center;">
            <div style="font-size: 40px; margin-bottom: 15px;">📊</div>
            <h3 style="margin-bottom: 10px;">Reporte Mensual</h3>
            <p style="color: #6B7280; font-size: 14px; margin-bottom: 20px;">Genera un resumen detallado de los ingresos por barrio del mes actual.</p>
            <button class="btn-primary" onclick="alert('Generando PDF...')">Generar PDF</button>
        </div>

        <!-- Card Recibos de Vivienda -->
        <div class="card" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center;">
            <div style="font-size: 40px; margin-bottom: 15px;">🧾</div>
            <h3 style="margin-bottom: 10px;">Recibos por Vivienda</h3>
            <p style="color: #6B7280; font-size: 14px; margin-bottom: 20px;">Busca una vivienda para generar su comprobante de pago histórico.</p>
            <a href="router.php?page=viviendas" class="btn-primary" style="text-decoration: none;">Buscar Vivienda</a>
        </div>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
