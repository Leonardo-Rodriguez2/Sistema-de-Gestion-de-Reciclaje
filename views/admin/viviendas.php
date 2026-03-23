<?php
// views/admin/viviendas.php
$user = check_dashboard_access([1]);

// Obtener todas las viviendas
$viviendasStmt = $pdo->query("SELECT v.*, b.nombre as barrio_nombre, u.nombre as jefe_nombre, u.apellido as jefe_apellido 
                              FROM viviendas v 
                              JOIN barrios b ON v.barrio_id = b.id 
                              JOIN usuarios u ON v.jefe_cuadra_id = u.id
                              ORDER BY v.fecha_registro DESC");
$viviendas = $viviendasStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Viviendas - EcoCusco";
$header_title = "Gestión de Viviendas";
$header_subtitle = "Lista maestra de todas las casas registradas en el sistema.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="card" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">📋 Listado General</h3>
            <a href="router.php?page=registrar_vivienda" class="btn-primary" style="text-decoration: none;">+ Nueva Vivienda</a>
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #F3F4F6;">
                        <th style="padding: 12px; text-align: left; color: #6B7280; font-size: 13px;">Propietario</th>
                        <th style="padding: 12px; text-align: left; color: #6B7280; font-size: 13px;">Dirección</th>
                        <th style="padding: 12px; text-align: left; color: #6B7280; font-size: 13px;">Barrio</th>
                        <th style="padding: 12px; text-align: left; color: #6B7280; font-size: 13px;">Jefe de Cuadra</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($viviendas as $v): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 12px; font-weight: 600;"><?= htmlspecialchars($v['propietario']) ?></td>
                            <td style="padding: 12px;"><?= htmlspecialchars($v['direccion'] . ' ' . $v['numero_casa']) ?></td>
                            <td style="padding: 12px;"><span style="background: #E5E7EB; padding: 4px 8px; border-radius: 6px; font-size: 12px;"><?= htmlspecialchars($v['barrio_nombre']) ?></span></td>
                            <td style="padding: 12px; font-size: 13px; color: #4B5563;"><?= htmlspecialchars($v['jefe_nombre'] . ' ' . $v['jefe_apellido']) ?></td>
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
