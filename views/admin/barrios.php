<?php
// views/admin/barrios.php
$user = check_dashboard_access([1]);

// 1. Obtener lista de barrios con estadísticas
$barriosStmt = $pdo->query("
    SELECT b.*, 
    (SELECT COUNT(*) FROM viviendas WHERE barrio_id = b.id) as total_viviendas,
    (SELECT SUM(monto_total) FROM recaudaciones WHERE barrio_id = b.id AND estado = 'Verificado') as total_recaudado
    FROM barrios b
    ORDER BY b.nombre ASC
");
$barrios = $barriosStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Gestión de Barrios - EcoCusco";
$header_title = "Barrios y Comunidades";
$header_subtitle = "Administra las zonas de recolección y monitorea su recaudación.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="grid">
        <!-- LISTADO DE BARRIOS -->
        <div class="card" style="grid-column: span 2;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0;">🗺️ Zonas del Sistema</h3>
                <button class="btn-primary" onclick="alert('Funcionalidad de añadir barrio próximamente')">+ Añadir Barrio</button>
            </div>
            
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre del Barrio</th>
                            <th>Viviendas Registradas</th>
                            <th>Recaudación Histórica</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($barrios as $b): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($b['nombre']) ?></strong></td>
                                <td><?= $b['total_viviendas'] ?> casas</td>
                                <td style="font-weight: 700; color: #10B981;">S/ <?= number_format($b['total_recaudado'] ?? 0, 2) ?></td>
                                <td><span class="badge pagado">Activo</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RESUMEN RÁPIDO -->
        <div class="card">
            <h3>📈 Rendimiento Global</h3>
            <div style="padding: 10px 0;">
                <div style="margin-bottom: 15px;">
                    <small style="color: #6B7280; display: block;">Barrios Activos</small>
                    <span style="font-size: 24px; font-weight: 700;"><?= count($barrios) ?></span>
                </div>
                <div>
                    <small style="color: #6B7280; display: block;">Promedio por Barrio</small>
                    <?php 
                        $total = array_sum(array_column($barrios, 'total_recaudado'));
                        $promedio = count($barrios) > 0 ? $total / count($barrios) : 0;
                    ?>
                    <span style="font-size: 24px; font-weight: 700; color: #10B981;">S/ <?= number_format($promedio, 2) ?></span>
                </div>
            </div>
        </div>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
