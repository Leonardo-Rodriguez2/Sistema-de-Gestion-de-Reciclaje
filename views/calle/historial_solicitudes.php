<?php
// views/historial_solicitudes.php
$allowed = [5, 6];
$user = check_dashboard_access($allowed);

// 1. Obtener la calle_id (si es calle manager) o barrio_id (si es barrio manager)
$sid = $_SESSION['active_sid'] ?? 'main';
$user_id = $user['id'];

$sql = "";
$params = [];

if($user['rol_id'] == 6) {
    // Calle Manager: Ver sus solicitudes procesadas
    $sql = "SELECT s.*, v.propietario, v.numero_casa 
            FROM solicitudes_vivienda s
            JOIN viviendas v ON s.vivienda_id = v.id
            WHERE s.creado_por = ? AND s.estado != 'Pendiente'
            ORDER BY s.fecha_revision DESC LIMIT 50";
    $params = [$user_id];
} else {
    // Barrio Manager: Ver todas las procesadas de su barrio
    $sql = "SELECT s.*, v.propietario, v.numero_casa, c.nombre as calle_nombre
            FROM solicitudes_vivienda s
            JOIN viviendas v ON s.vivienda_id = v.id
            JOIN calles c ON v.calle_id = c.id
            JOIN detalles_encargado_barrio deb ON c.barrio_id = deb.barrio_id
            WHERE deb.usuario_id = ? AND s.estado != 'Pendiente'
            ORDER BY s.fecha_revision DESC LIMIT 100";
    $params = [$user_id];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Historial de Trámites - EcoCusco";
$header_title = "Historial de Solicitudes";
$header_subtitle = "Revisa los trámites de alta, baja y renovación ya procesados.";

ob_start();
?>
    <div class="card">
        <h3 style="margin-top:0;">📜 Trámites Finalizados</h3>
        <p style="font-size: 13px; color: #6B7280; margin-bottom: 20px;">
            Este es un registro histórico de las solicitudes aprobadas o rechazadas.
        </p>
        
        <div style="overflow-x: auto;">
            <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                        <th style="padding: 12px;">Fecha / Tipo</th>
                        <th style="padding: 12px;">Vivienda</th>
                        <th style="padding: 12px;">Estado</th>
                        <th style="padding: 12px;">Resolución</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($historial)): ?>
                        <tr><td colspan="4" style="text-align: center; color: #9CA3AF; padding: 40px;">No hay trámites finalizados en el registro.</td></tr>
                    <?php endif; ?>
                    <?php foreach($historial as $h): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 12px;">
                                <div style="font-size: 11px; color: #6B7280;"><?= date('d/m/Y', strtotime($h['fecha_revision'] ?: $h['fecha_creacion'])) ?></div>
                                <span class="badge" style="background: <?= $h['tipo'] == 'Alta' ? '#D1FAE5' : ($h['tipo'] == 'Baja' ? '#FEE2E2' : '#E0F2FE') ?>; color: <?= $h['tipo'] == 'Alta' ? '#065F46' : ($h['tipo'] == 'Baja' ? '#991B1B' : '#0369A1') ?>; border: none; font-size: 9px; font-weight: 700;">
                                    <?= strtoupper($h['tipo']) ?>
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                <div style="font-weight: 700;"><?= htmlspecialchars($h['propietario']) ?></div>
                                <div style="font-size: 11px; color: #6B7280;">
                                    Casa #<?= htmlspecialchars($h['numero_casa']) ?> 
                                    <?= isset($h['calle_nombre']) ? ' | ' . htmlspecialchars($h['calle_nombre']) : '' ?>
                                </div>
                            </td>
                            <td style="padding: 12px;">
                                <?php if($h['estado'] == 'Aprobado'): ?>
                                    <span class="badge" style="background: #D1FAE5; color: #065F46; border:none; font-size: 10px;">✅ APROBADO</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #FEF2F2; color: #991B1B; border:none; font-size: 10px;">❌ RECHAZADO</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <div style="font-size: 11px; color: #4B5563;">
                                    <?= ($h['tipo'] == 'Baja' || $h['tipo'] == 'Renovacion') ? '<b>Deuda:</b> S/' . number_format($h['monto_deuda'], 2) : 'Registro de Vivienda' ?>
                                </div>
                                <div style="font-size: 9px; color: #9CA3AF;">Procesado el: <?= $h['fecha_revision'] ?></div>
                            </td>
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
