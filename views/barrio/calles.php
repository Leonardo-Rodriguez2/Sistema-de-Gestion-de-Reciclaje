<?php
// views/barrio/calles.php
$user = check_dashboard_access([5]);

// 1. Obtener el barrio asignado
$barrioStmt = $pdo->prepare("SELECT b.* FROM detalles_encargado_barrio d JOIN barrios b ON d.barrio_id = b.id WHERE d.usuario_id = ?");
$barrioStmt->execute([$user['id']]);
$barrio_info = $barrioStmt->fetch(PDO::FETCH_ASSOC);

if (!$barrio_info) {
    die("No tienes un barrio asignado.");
}

// 2. Obtener calles con estadísticas (Mes Actual)
$mes_actual = date('n');
$anio_actual = date('Y');

$callesStmt = $pdo->prepare("SELECT c.*, 
                            (SELECT COUNT(*) FROM viviendas WHERE calle_id = c.id) as total_viviendas,
                            (SELECT COUNT(DISTINCT v.id) 
                             FROM viviendas v 
                             JOIN cobros co ON v.id = co.vivienda_id 
                             WHERE v.calle_id = c.id 
                               AND co.mes = ? 
                               AND co.anio = ? 
                               AND co.estado = 'Pagado') as pagados
                            FROM calles c WHERE c.barrio_id = ?
                            ORDER BY c.nombre ASC");
$callesStmt->execute([$mes_actual, $anio_actual, $barrio_info['id']]);
$calles = $callesStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Lista de Calles - EcoCusco";
$header_title = "Calles en " . htmlspecialchars($barrio_info['nombre']);
$header_subtitle = "Monitorea el progreso de recaudación de cada calle en tu barrio.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <style>
        .street-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 10px; }
        .street-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-top: 4px solid #10B981; transition: 0.3s; }
        .street-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        .street-name { font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .street-stats { background: #F9FAFB; padding: 12px; border-radius: 8px; margin-bottom: 15px; }
        .stat-row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px; }
        .progress-bar { width: 100%; height: 8px; background: #E5E7EB; border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; background: #10B981; border-radius: 10px; transition: 1s ease-in-out; }
    </style>

    <div class="street-grid">
        <?php foreach($calles as $c): 
            $porcentaje = ($c['total_viviendas'] > 0) ? ($c['pagados'] / $c['total_viviendas']) * 100 : 0;
        ?>
            <div class="street-card">
                <div class="street-name">
                    <span>🛣️</span> <?= htmlspecialchars($c['nombre']) ?>
                </div>
                
                <div class="street-stats">
                    <div class="stat-row">
                        <span style="color: #6B7280;">Total Viviendas:</span>
                        <span style="font-weight: 700; color: #111827;"><?= $c['total_viviendas'] ?></span>
                    </div>
                    <div class="stat-row">
                        <span style="color: #6B7280;">Pagos Verificados:</span>
                        <span style="font-weight: 700; color: #10B981;"><?= $c['pagados'] ?></span>
                    </div>
                    <div style="margin-top: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <span style="font-size: 11px; font-weight: 700; color: #9CA3AF;">PROGRESO DEL MES</span>
                            <span style="font-size: 14px; font-weight: 800; color: #111827;"><?= round($porcentaje) ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $porcentaje ?>%;"></div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <a href="router.php?page=viviendas&calle_id=<?= $c['id'] ?>" style="flex: 1; text-align: center; padding: 8px; background: #E0F2FE; color: #0369A1; text-decoration: none; border-radius: 6px; font-size: 12px; font-weight: 700;">Ver Viviendas</a>
                    <a href="router.php?page=registrar_vivienda&calle_id=<?= $c['id'] ?>" style="padding: 8px; background: #F3F4F6; color: #4B5563; text-decoration: none; border-radius: 6px; font-size: 12px; font-weight: 700;" title="Registrar Vivienda en esta calle">+</a>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if(empty($calles)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background: white; border-radius: 12px;">
                <div style="font-size: 50px; margin-bottom: 10px;">🏘️</div>
                <h3 style="color: #9CA3AF;">No se encontraron calles asignadas a tu barrio.</h3>
            </div>
        <?php endif; ?>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
