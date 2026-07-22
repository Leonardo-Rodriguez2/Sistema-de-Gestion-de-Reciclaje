<?php

// views/calle/viviendas.php
global $pdo;
require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../app/helpers.php';
use app\models\mainModel;

if (empty($pdo)) $pdo = (new mainModel())->conectar();

$user = check_dashboard_access([6]);
$sid = $_SESSION['active_sid'] ?? '';

// Obtener la calle asignada
$calleStmt = $pdo->prepare("SELECT calle_id FROM detalles_encargado_calle WHERE usuario_id = ?");
$calleStmt->execute([$user['id']]);
$calle_id = $calleStmt->fetchColumn();

if (!$calle_id) {
    die("<div class='alert alert-error'>No tienes una calle asignada. Contacta al administrador.</div>");
}

// Mes y Año en curso para controlar la nómina
$mes_actual  = date('n');
$anio_actual = date('Y');
$meses_nombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

// Validar si el lote mensual ya se cerró
$loteStmt = $pdo->prepare("SELECT * FROM lotes_calle WHERE calle_id=? AND mes=? AND anio=?");
$loteStmt->execute([$calle_id, $mes_actual, $anio_actual]);
$lote_data = $loteStmt->fetch(PDO::FETCH_ASSOC);
$lote_estado = $lote_data['estado'] ?? 'Abierto';

// Contar casas activas (excluye bajas aprobadas y exentas)
$total_activas = $pdo->prepare("SELECT COUNT(*) FROM viviendas WHERE calle_id=? AND estado_servicio='Activo' AND exento_cobro=0 AND id NOT IN (SELECT vivienda_id FROM solicitudes_vivienda WHERE tipo='Baja' AND estado='Aprobado')");
$total_activas->execute([$calle_id]);
$total_casas = (int)$total_activas->fetchColumn();

// Contar exentas
$total_exentas = $pdo->prepare("SELECT COUNT(*) FROM viviendas WHERE calle_id=? AND estado_servicio='Activo' AND exento_cobro=1");
$total_exentas->execute([$calle_id]);
$exentas_count = (int)$total_exentas->fetchColumn();

$pagadas_lote = $pdo->prepare("SELECT COUNT(DISTINCT vivienda_id) FROM cobros WHERE vivienda_id IN (SELECT id FROM viviendas WHERE calle_id=?) AND mes=? AND anio=? AND estado='Pagado'");
$pagadas_lote->execute([$calle_id, $mes_actual, $anio_actual]);
$pagadas = (int)$pagadas_lote->fetchColumn();

$pendientes = $total_casas - $pagadas;
$porcentaje = $total_casas > 0 ? round($pagadas / $total_casas * 100) : 0;

$estado_color = ['Abierto'=>'#F59E0B','Enviado'=>'#3B82F6','Aprobado'=>'#10B981','Rechazado'=>'#DC2626'];
$estado_icon  = ['Abierto'=>'🟡','Enviado'=>'📤','Aprobado'=>'✅','Rechazado'=>'❌'];

// Obtener filtros de búsqueda
$f_search = trim($_GET['search'] ?? '');

// Estructurar consulta — incluye exento_cobro
if ($f_search !== '') {
    $sql = "SELECT v.*, b.nombre as barrio_nombre, c.nombre as calle_nombre,
                   cb.estado as pago_mes_estado, cb.estado_verificacion as verificacion_mes
            FROM viviendas v 
            JOIN barrios b ON v.barrio_id = b.id 
            JOIN calles c ON v.calle_id = c.id
            LEFT JOIN cobros cb ON v.id = cb.vivienda_id AND cb.mes = ? AND cb.anio = ? AND cb.estado != 'Anulado'
            WHERE v.calle_id = ?
            AND v.id NOT IN (SELECT sv.vivienda_id FROM solicitudes_vivienda sv WHERE sv.tipo = 'Baja' AND sv.estado = 'Aprobado')
            AND (v.propietario LIKE ? OR v.numero_casa LIKE ?)
            ORDER BY CAST(v.numero_casa AS UNSIGNED) ASC, v.numero_casa ASC";
    $stmt = $pdo->prepare($sql);
    $search_param = "%$f_search%";
    $stmt->execute([$mes_actual, $anio_actual, $calle_id, $search_param, $search_param]);
} else {
    $sql = "SELECT v.*, b.nombre as barrio_nombre, c.nombre as calle_nombre,
                   cb.estado as pago_mes_estado, cb.estado_verificacion as verificacion_mes
            FROM viviendas v 
            JOIN barrios b ON v.barrio_id = b.id 
            JOIN calles c ON v.calle_id = c.id
            LEFT JOIN cobros cb ON v.id = cb.vivienda_id AND cb.mes = ? AND cb.anio = ? AND cb.estado != 'Anulado'
            WHERE v.calle_id = ?
            AND v.id NOT IN (SELECT sv.vivienda_id FROM solicitudes_vivienda sv WHERE sv.tipo = 'Baja' AND sv.estado = 'Aprobado')
            ORDER BY CAST(v.numero_casa AS UNSIGNED) ASC, v.numero_casa ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mes_actual, $anio_actual, $calle_id]);
}

$viviendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Indicador visual del estado del lote -->
    <div class="card" style="margin-bottom:18px; border-left:4px solid <?= $estado_color[$lote_estado] ?? '#6B7280' ?>; background:<?= $estado_color[$lote_estado] ?? '#6B7280' ?>08;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
            <div style="flex:1; min-width:200px;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                    <span style="font-size:18px;"><?= $estado_icon[$lote_estado] ?? '📦' ?></span>
                    <span style="font-size:16px; font-weight:700; color:<?= $estado_color[$lote_estado] ?? '#6B7280' ?>;">
                        Lote <?= $meses_nombres[$mes_actual] ?> <?= $anio_actual ?> — <?= $lote_estado ?>
                    </span>
                </div>
                <div style="display:flex; gap:20px; flex-wrap:wrap; font-size:13px; color:#6B7280;">
                    <span>🏠 <strong><?= $total_casas ?></strong> casas por cobrar</span>
                    <span style="color:#059669;">✅ <strong><?= $pagadas ?></strong> pagadas</span>
                    <span style="color:#8B5CF6;">🛡️ <strong><?= $exentas_count ?></strong> exoneradas</span>
                    <span style="color:#DC2626;">⏳ <strong><?= max(0,$pendientes) ?></strong> pendientes</span>
                    <span>📊 <strong><?= $porcentaje ?>%</strong> completado</span>
                </div>
                <!-- Barra de progreso -->
                <div style="margin-top:10px; background:#E5E7EB; height:8px; border-radius:4px; overflow:hidden; max-width:400px;">
                    <div style="width:<?= $porcentaje ?>%; height:100%; background:<?= $porcentaje==100?'#10B981':'#F59E0B' ?>; border-radius:4px; transition:width .5s;"></div>
                </div>
            </div>
            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <?php if ($lote_estado === 'Abierto' || $lote_estado === 'Rechazado'): ?>
                    <a href="router.php?page=reportar_pago" class="btn-primary" style="background:#10B981; text-decoration:none; padding:8px 16px;">
                        📤 <?= $pagadas > 0 ? 'Enviar Lote al Barrio' : 'Ir a Reportar Pagos' ?>
                    </a>
                <?php elseif ($lote_estado === 'Enviado'): ?>
                    <span style="background:#EFF6FF; color:#1E40AF; padding:8px 16px; border-radius:6px; font-size:13px;">📤 Esperando revisión del Barrio</span>
                <?php elseif ($lote_estado === 'Aprobado'): ?>
                    <span style="background:#D1FAE5; color:#065F46; padding:8px 16px; border-radius:6px; font-size:13px;">✅ Aprobado por el Barrio</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
        <form method="GET" action="router.php" style="display: flex; gap: 5px; flex-grow: 1; max-width: 400px; flex-wrap: wrap;">
            <input type="hidden" name="page" value="viviendas">
            <input type="hidden" name="sid" value="<?= htmlspecialchars($sid) ?>">
            <input type="text" name="search" value="<?= htmlspecialchars($f_search) ?>" placeholder="Buscar propietario o casa..." style="flex-grow: 1; padding: 6px 10px; font-size: 13px; border: 1px solid #D1D5DB; border-radius: 4px;">
            <button type="submit" style="padding: 6px 12px; font-size: 13px; background: #4B5563; color: white; border: none; border-radius: 4px; cursor: pointer;">Buscar</button>
            <?php if ($f_search !== ''): ?>
                <a href="router.php?page=viviendas&sid=<?= htmlspecialchars($sid) ?>" style="padding: 6px 10px; font-size: 13px; background: #E5E7EB; color: #374151; border-radius: 4px; text-decoration: none;">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrap">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                <thead>
                    <tr style="background: #F9FAFB; border-bottom: 1px solid #E5E7EB; color: #374151;">
                        <th style="padding: 10px 12px;">Casa</th>
                        <th style="padding: 10px 12px;">Propietario</th>
                        <th style="padding: 10px 12px;">Deuda</th>
                        <th style="padding: 10px 12px;">Estado</th>
                        <th style="padding: 10px 12px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($viviendas as $v): ?>
                        <?php 
                        $compStmt = $pdo->prepare("SELECT id, comprobante_calle FROM cobros WHERE vivienda_id = ? AND mes = ? AND anio = ? AND estado != 'Anulado'");
                        $compStmt->execute([$v['id'], $mes_actual, $anio_actual]);
                        $cobroData = $compStmt->fetch(PDO::FETCH_ASSOC);
                        $cobro_id = $cobroData['id'] ?? null;
                        $comprobante = $cobroData['comprobante_calle'] ?? null;

                        // Deuda acumulada de meses anteriores
                        $deudaStmt = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) FROM cobros WHERE vivienda_id = ? AND estado NOT IN ('Pagado','Anulado')");
                        $deudaStmt->execute([$v['id']]);
                        $deuda_acumulada = (float)$deudaStmt->fetchColumn();
                        ?>
                        <tr style="<?= $v['exento_cobro'] ? 'background:#F5F3FF;' : '' ?>">
                            <td style="padding: 10px 12px;">#<?= htmlspecialchars($v['numero_casa']) ?></td>
                            <td style="padding: 10px 12px;">
                                <?= htmlspecialchars($v['propietario']) ?>
                                <?php if ($v['exento_cobro']): ?>
                                    <span style="background:#EDE9FE; color:#6D28D9; padding:1px 6px; border-radius:4px; font-size:9px; font-weight:700; margin-left:4px; display:inline-block;">EXENTA</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px 12px;">
                                <?php if ($deuda_acumulada > 0 && !$v['exento_cobro']): ?>
                                    <span style="color:#DC2626; font-weight:700; font-size:12px;">S/ <?= number_format($deuda_acumulada, 2) ?></span>
                                <?php else: ?>
                                    <span style="color:#9CA3AF;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px 12px;">
                                <?php if ($v['exento_cobro']): ?>
                                    <span style="background:#EDE9FE; color:#6D28D9; padding:2px 10px; border-radius:4px; font-size:11px; font-weight:700;">
                                        🛡️ Exenta
                                    </span>
                                <?php elseif ($v['pago_mes_estado'] === 'Pagado'): ?>
                                    <span style="background:#D1FAE5; color:#065F46; padding:2px 8px; border-radius:4px; font-size:11px;">✓ Pagado</span>
                                    <?php if (!empty($comprobante)): ?>
                                        <a href="#" data-url="<?= htmlspecialchars($comprobante) ?>" onclick="return openComprobanteModal(this)" style="background: #E0F2FE; color: #0369A1; padding: 2px 6px; border-radius: 4px; font-size: 10px; text-decoration: none;">📎</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="background:#FEE2E2; color:#991B1B; padding:2px 8px; border-radius:4px; font-size:11px;">⏳ Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px 12px; text-align: right;">
                                <?php if ($v['pago_mes_estado'] === 'Pagado' && !in_array($lote_estado, ['Enviado', 'Aprobado'])): ?>
                                    <button class="btn-anular" data-cobroid="<?= $cobro_id ?>" style="background:#FEE2E2; color:#DC2626; border:1px solid #FECACA; padding:5px 10px; cursor:pointer; font-size:11px; border-radius:4px; font-weight:600;">↩ Anular</button>
                                <?php elseif (!$v['exento_cobro'] && $v['pago_mes_estado'] !== 'Pagado' && !in_array($lote_estado, ['Enviado', 'Aprobado'])): ?>
                                    <button class="btn-pagar" data-vivienda="<?= $v['id'] ?>" data-casa="<?= htmlspecialchars($v['numero_casa'], ENT_QUOTES) ?>" data-propietario="<?= htmlspecialchars($v['propietario'], ENT_QUOTES) ?>" 
                                            style="background:#3B82F6; color:white; border:none; padding:7px 14px; cursor:pointer; font-size:12px; border-radius:6px; font-weight:600;">
                                        💰 Pagar
                                    </button>
                                <?php elseif ($v['exento_cobro']): ?>
                                    <span style="font-size:10px; color:#8B5CF6;">Exenta</span>
                                <?php endif; ?>
                                <div style="margin-top:4px;">
                                    <button class="btn-historial" data-vivienda="<?= $v['id'] ?>" style="background:transparent; color:#6B7280; border:1px solid #E5E7EB; padding:2px 6px; border-radius:4px; font-size:9px; cursor:pointer;">
                                        📋 Historial
                                    </button>
                                </div>
                                <div id="historial-<?= $v['id'] ?>" style="display:none; margin-top:8px; background:#F9FAFB; border-radius:6px; padding:10px; border:1px solid #E5E7EB; text-align:left; width:300px; position:absolute; right:10px; z-index:10; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                                    <?php
                                    $histStmt = $pdo->prepare("SELECT c.*, c.estado as cobro_estado, c.comprobante_calle
                                        FROM cobros c WHERE c.vivienda_id = ? ORDER BY c.anio DESC, c.mes DESC LIMIT 12");
                                    $histStmt->execute([$v['id']]);
                                    $historial = $histStmt->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    <div style="font-weight:700; font-size:11px; margin-bottom:6px;">📋 Historial de Pagos</div>
                                    <?php if(empty($historial)): ?>
                                        <div style="font-size:10px; color:#9CA3AF;">Sin movimientos registrados.</div>
                                    <?php else: ?>
                                        <table style="width:100%; border-collapse:collapse; font-size:10px;">
                                            <thead><tr style="border-bottom:1px solid #E5E7EB;">
                                                <th style="padding:3px; text-align:left;">Periodo</th>
                                                <th style="padding:3px;">Monto</th>
                                                <th style="padding:3px;">Estado</th>
                                            </tr></thead>
                                            <tbody>
                                            <?php foreach($historial as $h): ?>
                                                <tr style="border-bottom:1px solid #F3F4F6;">
                                                    <td style="padding:3px;"><?= $meses_nombres[$h['mes']] ?> <?= $h['anio'] ?></td>
                                                    <td style="padding:3px; font-weight:600;">S/ <?= number_format($h['monto'],2) ?></td>
                                                    <td style="padding:3px;">
                                                        <span style="background:<?= $h['cobro_estado']=='Pagado'?'#D1FAE5':'#FEE2E2' ?>; color:<?= $h['cobro_estado']=='Pagado'?'#065F46':'#991B1B' ?>; padding:1px 5px; border-radius:3px; font-size:9px;">
                                                            <?= $h['cobro_estado'] == 'Pagado' ? '✓ Pagado' : ($h['cobro_estado'] == 'Anulado' ? '✗ Anulado' : '⏳ Pendiente') ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                    <button onclick="document.getElementById('historial-<?= $v['id'] ?>').style.display='none'" style="margin-top:6px; background:#F3F4F6; border:none; padding:3px 8px; border-radius:4px; font-size:9px; cursor:pointer; width:100%;">Cerrar</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($viviendas)): ?>
                        <tr><td colspan="5" style="padding:40px; text-align:center; color:#9CA3AF;">
                            <div style="font-size:36px; margin-bottom:12px;">🏘️</div>
                            <div style="font-weight:600; color:#374151; margin-bottom:4px;">No hay viviendas registradas</div>
                            <div style="font-size:12px;">No se encontraron viviendas en esta calle</div>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:15px; padding:12px; background:#F5F3FF; border-radius:8px; border:1px solid #EDE9FE; font-size:12px; color:#6D28D9;">
        <strong>🛡️ Viviendas Exoneradas:</strong> Las viviendas marcadas como <strong>EXENTA</strong> están exoneradas del pago mensual por decisión del Encargado de Barrio. 
        No requieren cobro ni aparecen en los totales del lote.
        <?php if ($exentas_count > 0): ?>
            <span style="display:inline-block; margin-left:8px; background:#EDE9FE; padding:2px 8px; border-radius:4px; font-weight:700;"><?= $exentas_count ?> exonerada(s)</span>
        <?php endif; ?>
    </div>

<div id="modal-pago" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;" onclick="if(event.target===this)closePagoModal()">
    <div style="background:white; border-radius:16px; padding:28px; width:90%; max-width:440px; box-shadow:0 20px 60px rgba(0,0,0,0.3); position:relative; animation:modalIn .25s ease;">
        <button onclick="closePagoModal()" style="position:absolute; top:12px; right:16px; background:none; border:none; font-size:22px; cursor:pointer; color:#9CA3AF;">✕</button>
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:40px; margin-bottom:8px;">💰</div>
            <h3 style="margin:0; font-size:18px;">Registrar Pago</h3>
            <p style="color:#6B7280; font-size:13px; margin:4px 0 0;">Complete los datos para registrar el cobro</p>
        </div>
        
        <!-- Step indicator -->
        <div style="display:flex; justify-content:center; gap:8px; margin-bottom:20px;">
            <div style="display:flex; align-items:center; gap:4px; font-size:11px; color:#059669; font-weight:600;">
                <span style="background:#059669; color:white; width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:10px;">1</span>
                Referencia
            </div>
            <div style="color:#D1D5DB;">→</div>
            <div style="display:flex; align-items:center; gap:4px; font-size:11px; color:#9CA3AF;">
                <span style="background:#E5E7EB; color:#6B7280; width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:10px;">2</span>
                Comprobante
            </div>
            <div style="color:#D1D5DB;">→</div>
            <div style="display:flex; align-items:center; gap:4px; font-size:11px; color:#9CA3AF;">
                <span style="background:#E5E7EB; color:#6B7280; width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:10px;">3</span>
                Confirmar
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" id="form-pago-modal">
            <input type="hidden" name="action" value="marcar_pago_simple">
            <input type="hidden" name="vivienda_id" id="modal-vivienda-id">
            
            <!-- Vivienda info -->
            <div style="background:#F3F4F6; border-radius:3px; padding:14px; margin-bottom:18px; display:flex; gap:12px; align-items:center;">
                <div style="background:#3B82F6; color:white; width:44px; height:44px; border-radius:3px; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:16px; flex-shrink:0;" id="modal-casa-num">#</div>
                <div>
                    <div style="font-weight:700; font-size:14px;" id="modal-propietario">—</div>
                    <div style="font-size:11px; color:#6B7280;">Vivienda a registrar pago</div>
                </div>
            </div>
            
            <!-- Step 1: Referencia -->
            <div style="margin-bottom:14px;">
                <label style="font-size:12px; font-weight:700; color:#374151; display:block; margin-bottom:5px;">
                    <span style="background:#059669; color:white; width:16px; height:16px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:9px; margin-right:4px;">1</span>
                    REFERENCIA DE PAGO (obligatorio)
                </label>
                <input type="text" name="referencia_pago" required placeholder="Ej: Voucher #12345, Código de operación" 
                       style="width:100%; padding:12px; border:2px solid #E5E7EB; border-radius:3px; font-size:14px; box-sizing:border-box;">
                <div style="font-size:10px; color:#9CA3AF; margin-top:4px;">Número de voucher, código de transferencia o referencia del pago</div>
            </div>
            
            <!-- Step 2: Comprobante -->
            <div style="margin-bottom:20px;">
                <label style="font-size:12px; font-weight:700; color:#374151; display:block; margin-bottom:5px;">
                    <span style="background:#E5E7EB; color:#6B7280; width:16px; height:16px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:9px; margin-right:4px;">2</span>
                    COMPROBANTE (opcional)
                </label>
                <div style="border:2px dashed #D1D5DB; border-radius:3px; padding:20px; text-align:center; cursor:pointer; background:#F9FAFB; transition:.2s;"
                     onclick="document.getElementById('file-input-modal').click()" id="upload-area">
                    <div style="font-size:36px; margin-bottom:6px;">📷</div>
                    <div style="font-size:13px; color:#6B7280;">Haz clic para subir una imagen</div>
                    <div style="font-size:11px; color:#9CA3AF; margin-top:2px;">PNG, JPG o PDF</div>
                </div>
                <input type="file" name="comprobante_pago" id="file-input-modal" accept="image/*,.pdf" style="display:none;" onchange="document.getElementById('upload-area').innerHTML='<div style=font-size:24px;margin-bottom:4px;>✅</div><div style=font-size:13px;color:#059669;font-weight:600;>Archivo seleccionado</div><div style=font-size:11px;color:#6B7280;>' + this.files[0].name + '</div>'">
            </div>
            
            <!-- Step 3: Confirmar -->
            <button type="submit" style="width:100%; background:#059669; color:white; border:none; padding:14px; border-radius:3px; font-size:15px; font-weight:700; cursor:pointer;">
                <span style="background:white; color:#059669; width:18px; height:18px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:10px; margin-right:6px;">3</span>
                Confirmar Pago
            </button>
        </form>
    </div>
</div>

<div id="modal-anular" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;" onclick="if(event.target===this)closeAnularModal()">
    <div style="background:white; border-radius:16px; padding:28px; width:90%; max-width:400px; box-shadow:0 20px 60px rgba(0,0,0,0.3); position:relative; animation:modalIn .25s ease;">
        <button onclick="closeAnularModal()" style="position:absolute; top:12px; right:16px; background:none; border:none; font-size:22px; cursor:pointer; color:#9CA3AF;">✕</button>
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:40px; margin-bottom:8px;">↩</div>
            <h3 style="margin:0; font-size:18px;">Anular Pago</h3>
            <p style="color:#6B7280; font-size:13px; margin:4px 0 0;">Se marcará como ANULADO</p>
        </div>
        <form method="POST" id="form-anular-modal">
            <input type="hidden" name="action" value="anular_pago">
            <input type="hidden" name="cobro_id" id="modal-cobro-id">
            <div style="margin-bottom:18px;">
                <label style="font-size:12px; font-weight:700; color:#374151; display:block; margin-bottom:5px;">MOTIVO DE ANULACIÓN</label>
                <input type="text" name="motivo_anulacion" required placeholder="Ej: Pago duplicado, error..." 
                       style="width:100%; padding:12px; border:2px solid #FCA5A5; border-radius:10px; font-size:14px; box-sizing:border-box;">
            </div>
            <button type="submit" style="width:100%; background:#DC2626; color:white; border:none; padding:14px; border-radius:10px; font-size:15px; font-weight:700; cursor:pointer;"
                onclick="return confirm('¿Estás seguro de anular este pago?')">
                Confirmar Anulación
            </button>
        </form>
    </div>
</div>

<style>
@keyframes modalIn { from { opacity:0; transform:scale(.95); } to { opacity:1; transform:scale(1); } }
</style>

<script>
document.addEventListener('click', function(e) {
    var target = e.target.closest('.btn-historial');
    if (target) {
        var id = target.dataset.vivienda;
        var div = document.getElementById('historial-' + id);
        document.querySelectorAll('[id^="historial-"]').forEach(function(el) { el.style.display = 'none'; });
        div.style.display = div.style.display === 'none' ? 'block' : 'none';
        return;
    }
    if (!e.target.closest('[id^="historial-"]')) {
        document.querySelectorAll('[id^="historial-"]').forEach(function(el) { el.style.display = 'none'; });
    }
});

document.addEventListener('click', function(e) {
    var target = e.target.closest('.btn-pagar');
    if (target) {
        document.getElementById('modal-vivienda-id').value = target.dataset.vivienda;
        document.getElementById('modal-casa-num').textContent = '#' + target.dataset.casa;
        document.getElementById('modal-propietario').textContent = target.dataset.propietario;
        document.getElementById('modal-pago').style.display = 'flex';
        document.getElementById('upload-area').innerHTML = '<div style="font-size:36px;margin-bottom:6px;">📷</div><div style="font-size:13px;color:#6B7280;">Haz clic para subir una imagen</div><div style="font-size:11px;color:#9CA3AF;margin-top:2px;">PNG, JPG o PDF</div>';
        document.getElementById('file-input-modal').value = '';
        return;
    }
});

document.addEventListener('click', function(e) {
    var target = e.target.closest('.btn-anular');
    if (target) {
        document.getElementById('modal-cobro-id').value = target.dataset.cobroid;
        document.getElementById('modal-anular').style.display = 'flex';
        return;
    }
});

function closePagoModal() {
    document.getElementById('modal-pago').style.display = 'none';
}

function closeAnularModal() {
    document.getElementById('modal-anular').style.display = 'none';
}
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
