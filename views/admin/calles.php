<?php
// views/admin/calles.php
$user = check_dashboard_access([1]); // Solo Admin

// Obtener barrios para el filtro/formulario
$barriosStmt = $pdo->query("SELECT id, nombre FROM barrios ORDER BY nombre");
$barrios = $barriosStmt->fetchAll(PDO::FETCH_ASSOC);

$barrio_filtro = isset($_GET['barrio_id']) ? (int)$_GET['barrio_id'] : null;
$where = $barrio_filtro ? "WHERE c.barrio_id = $barrio_filtro" : "";

// Obtener calles
$callesStmt = $pdo->query("SELECT c.*, b.nombre as barrio_nombre, 
                            (SELECT COUNT(*) FROM viviendas WHERE calle_id = c.id) as total_viviendas
                            FROM calles c 
                            JOIN barrios b ON c.barrio_id = b.id 
                            $where 
                            ORDER BY b.nombre, c.nombre");
$calles = $callesStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Administrar Calles - EcoCusco";
$header_title = "Gestión de Calles";
$header_subtitle = "Configura las calles de cada barrio para una mejor organización.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div class="card-title" style="margin-bottom: 0; border: none;">🏠 Listado de Calles</div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <form method="GET">
                    <input type="hidden" name="page" value="calles">
                    <select name="barrio_id" onchange="this.form.submit()" style="padding: 6px; border-radius: 6px; font-size: 13px; border: 1px solid #E5E7EB;">
                        <option value="">Todos los barrios</option>
                        <?php foreach($barrios as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $barrio_filtro == $b['id'] ? 'selected' : '' ?>><?= $b['nombre'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <a href="router.php?page=calle_nueva" class="btn-primary" style="text-decoration: none;">+ Nueva Calle</a>
            </div>
        </div>
        
        <table class="table-mini" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #F3F4F6;">
                    <th style="padding: 12px; text-align: left;">Calle</th>
                    <th style="padding: 12px; text-align: left;">Barrio</th>
                    <th style="padding: 12px; text-align: center;">Viviendas</th>
                    <th style="padding: 12px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($calles)): ?>
                    <tr><td colspan="4" style="text-align:center; padding:20px; color:#9CA3AF;">No se encontraron calles.</td></tr>
                <?php endif; ?>
                <?php foreach($calles as $c): ?>
                    <tr style="border-bottom: 1px solid #F9FAFB;">
                        <td style="padding: 12px;"><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                        <td style="padding: 12px;"><span class="badge" style="background:#E5E7EB; border:none;"><?= htmlspecialchars($c['barrio_nombre']) ?></span></td>
                        <td style="padding: 12px; text-align: center;"><?= $c['total_viviendas'] ?></td>
                        <td style="padding: 12px; text-align: center;">
                            <a href="router.php?page=viviendas&calle_id=<?= $c['id'] ?>" style="color:#3B82F6; text-decoration:none; font-size:11px; font-weight:600;">Ver Viviendas</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
