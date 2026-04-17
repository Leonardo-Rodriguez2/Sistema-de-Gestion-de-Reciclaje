<?php
// views/calle/solicitudes.php
$user = check_dashboard_access([6]);

// 1. Obtener la calle asignada
$calleStmt = $pdo->prepare("SELECT calle_id FROM detalles_encargado_calle WHERE usuario_id = ?");
$calleStmt->execute([$user['id']]);
$calle_id = $calleStmt->fetchColumn();

// 2. Obtener historial de solicitudes de esta calle (últimas 20)
$sql = "SELECT s.*, v.propietario, v.numero_casa 
        FROM solicitudes_vivienda s
        JOIN viviendas v ON s.vivienda_id = v.id
        WHERE s.creado_por = ? AND s.estado = 'Pendiente'
        ORDER BY s.fecha_creacion DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$calle_id]);
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Mis Solicitudes - EcoCusco";
$header_title = "Seguimiento de Trámites";
$header_subtitle = "Revisa el estado de tus solicitudes de alta, baja o renovación.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="card">
        <h3 style="margin-top:0;">📋 Estado de Solicitudes</h3>
        <p style="font-size: 13px; color: #6B7280; margin-bottom: 20px;">
            Aquí puedes ver si el Encargado de Barrio ya procesó tus peticiones.
        </p>
        
        <div style="overflow-x: auto;">
            <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #F3F4F6;">
                        <th style="padding: 12px;">Fecha / Tipo</th>
                        <th style="padding: 12px;">Vivienda</th>
                        <th style="padding: 12px;">Estado</th>
                        <th style="padding: 12px;">Detalles / Deuda</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($solicitudes)): ?>
                        <tr><td colspan="4" style="text-align: center; color: #9CA3AF; padding: 40px;">Aún no has realizado ninguna solicitud.</td></tr>
                    <?php endif; ?>
                    <?php foreach($solicitudes as $s): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 12px;">
                                <div style="font-size: 11px; color: #6B7280;"><?= date('d/m/Y', strtotime($s['fecha_creacion'])) ?></div>
                                <span class="badge" style="background: <?= $s['tipo'] == 'Alta' ? '#D1FAE5' : ($s['tipo'] == 'Baja' ? '#FEE2E2' : '#E0F2FE') ?>; color: <?= $s['tipo'] == 'Alta' ? '#065F46' : ($s['tipo'] == 'Baja' ? '#991B1B' : '#0369A1') ?>; border: none; font-size: 10px; font-weight: 700;">
                                    <?= strtoupper($s['tipo']) ?>
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                <div style="font-weight: 700;"><?= htmlspecialchars($s['propietario']) ?></div>
                                <div style="font-size: 11px; color: #6B7280;">Casa #<?= htmlspecialchars($s['numero_casa']) ?></div>
                            </td>
                            <td style="padding: 12px;">
                                <?php if($s['estado'] == 'Pendiente'): ?>
                                    <span class="badge" style="background: #FEF3C7; color: #92400E; border:none; font-size: 10px;">⏳ EN ESPERA</span>
                                <?php elseif($s['estado'] == 'Aprobado'): ?>
                                    <span class="badge" style="background: #D1FAE5; color: #065F46; border:none; font-size: 10px;">✅ PROCESADO</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #F3F4F6; color: #374151; border:none; font-size: 10px;">❌ RECHAZADO</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <?php if($s['tipo'] == 'Baja'): ?>
                                    <div style="font-weight: 800; color: #991B1B; font-size: 12px;">S/ <?= number_format($s['monto_deuda'], 2) ?></div>
                                    <div style="font-size: 10px; color: #6B7280;"><?= htmlspecialchars($s['detalles_deuda']) ?></div>
                                <?php else: ?>
                                    <span style="color: #9CA3AF; font-size: 11px;">-</span>
                                <?php endif; ?>
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
