<?php
// views/calle/viviendas.php
$user = check_dashboard_access([6]);

// Obtener la calle asignada
$calleStmt = $pdo->prepare("SELECT calle_id FROM detalles_encargado_calle WHERE usuario_id = ?");
$calleStmt->execute([$user['id']]);
$calle_id = $calleStmt->fetchColumn();

// 2. Obtener filtros
$f_search = trim($_GET['search'] ?? '');

// 3. Obtener viviendas de esa calle
$sql = "SELECT v.*, b.nombre as barrio_nombre, c.nombre as calle_nombre
        FROM viviendas v 
        JOIN barrios b ON v.barrio_id = b.id 
        JOIN calles c ON v.calle_id = c.id
        WHERE v.calle_id = :calle";

$params = [':calle' => $calle_id];
if ($f_search !== '') {
    $sql .= " AND (v.propietario LIKE :search OR v.direccion LIKE :search OR v.numero_casa LIKE :search)";
    $params[':search'] = "%$f_search%";
}

$sql .= " ORDER BY v.numero_casa ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$viviendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Mis Viviendas - EcoCusco";
$header_title = "Viviendas en mi Calle";
$header_subtitle = "Casas bajo tu supervisión directa.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Barra de Búsqueda -->
    <div class="card" style="margin-bottom: 15px; padding: 10px;">
        <form method="GET" action="router.php" style="display: flex; gap: 10px; align-items: center;">
            <input type="hidden" name="page" value="viviendas">
            <input type="text" name="search" value="<?= htmlspecialchars($f_search) ?>" placeholder="Buscar propietario o dirección..." 
                   style="flex: 1; padding: 6px 12px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 13px;">
            <button type="submit" class="btn-primary" style="padding: 6px 12px; font-size: 12px;">Buscar</button>
            <?php if ($f_search): ?>
                <a href="router.php?page=viviendas" style="font-size: 11px; color: #6B7280;">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">🏠 Listado de la Calle</h3>
            <a href="router.php?page=registrar_vivienda" class="btn-primary" style="text-decoration: none;">+ Solicitar Alta</a>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="table-mini" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #F3F4F6;">
                        <th style="padding: 12px; text-align: left;">Propietario</th>
                        <th style="padding: 12px; text-align: left;">Casa / Dirección</th>
                        <th style="padding: 12px; text-align: center;">Estado Pago</th>
                        <th style="padding: 12px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($viviendas)): ?>
                        <tr><td colspan="4" style="text-align:center; padding:20px; color:#9CA3AF;">No hay viviendas registradas en esta calle.</td></tr>
                    <?php endif; ?>
                    <?php foreach($viviendas as $v): ?>
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 12px; font-weight: 600;"><?= htmlspecialchars($v['propietario']) ?></td>
                            <td style="padding: 12px;">
                                <strong>#<?= htmlspecialchars($v['numero_casa']) ?></strong><br>
                                <span style="font-size: 11px; color: #6B7280;"><?= htmlspecialchars($v['direccion']) ?></span>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <?php
                                // Obtener cobro del mes actual
                                $mes = date('n'); $anio = date('Y');
                                $cobroStmt = $pdo->prepare("SELECT estado FROM cobros WHERE vivienda_id = ? AND mes = ? AND anio = ? LIMIT 1");
                                $cobroStmt->execute([$v['id'], $mes, $anio]);
                                $estado = $cobroStmt->fetchColumn() ?: 'Sin Cobro';
                                
                                $bg = ($estado == 'Pagado') ? '#DEF7EC' : '#FDE8E8';
                                $color = ($estado == 'Pagado') ? '#03543F' : '#9B1C1C';
                                ?>
                                <span class="badge" style="background: <?= $bg ?>; color: <?= $color ?>; border:none;"><?= $estado ?></span>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <a href="router.php?page=reportar_pago&vivienda_id=<?= $v['id'] ?>" style="color:#10B981; text-decoration:none; font-size:11px; font-weight:600;">Reportar Pago</a>
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
