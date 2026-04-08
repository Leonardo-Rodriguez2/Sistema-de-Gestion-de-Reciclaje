<?php
// views/personal/dashboard.php
$user = check_dashboard_access([1, 3]);

// Obtener cargo del personal
$detallesStmt = $pdo->prepare("SELECT cargo, turno FROM detalles_personal_obrero WHERE usuario_id = ?");
$detallesStmt->execute([$user['id']]);
$detalles = $detallesStmt->fetch(PDO::FETCH_ASSOC);
$cargo = $detalles['cargo'] ?? 'Personal';

$title = "Dashboard Personal - EcoCusco";
$header_title = "Panel Operativo";
$header_subtitle = "Bienvenido, " . htmlspecialchars($user['nombre']) . " [" . htmlspecialchars($cargo) . "]";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <!-- Card Info Personal -->
        <div class="card" style="background: white; padding: 20px; border-radius: 12px; border-left: 4px solid #10B981;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="font-size: 30px;">👷</div>
                <div>
                    <h4 style="margin: 0; color: #111827;"><?= htmlspecialchars($cargo) ?></h4>
                    <p style="margin: 0; font-size: 13px; color: #6B7280;">Turno: <?= htmlspecialchars($detalles['turno'] ?? 'No asignado') ?></p>
                </div>
            </div>
        </div>

        <!-- Card Próxima Tarea -->
        <div class="card" style="background: white; padding: 20px; border-radius: 12px;">
            <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #374151;">📍 Próxima Tarea</h4>
            <p style="margin: 0; font-size: 13px; color: #6B7280;">Módulo de asignación de rutas en desarrollo técnico.</p>
        </div>
    </div>

    <div class="card" style="margin-top: 20px; text-align: center; padding: 40px; color: #9CA3AF;">
        <div style="font-size: 50px; margin-bottom: 20px;">🛣️</div>
        <h3>Gestión Operativa</h3>
        <p>Próximamente aquí verás tu cronograma de trabajo y rutas de recolección.</p>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
