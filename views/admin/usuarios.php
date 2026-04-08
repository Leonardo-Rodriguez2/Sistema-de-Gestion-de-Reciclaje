<?php
// admin_dashboard_view.php - Versión Compacta
$user = check_dashboard_access([1]);
$filter_rol_id = (int)($_GET['rol_id'] ?? 0);

// Mapeo dinámico para etiquetas y títulos
$roles_map = [
    5 => "Encargados de Barrio", 
    6 => "Encargados de Calle",
    2 => "Gestores de Pago", 
    3 => "Personal Obrero"
];
$role_name = $roles_map[$filter_rol_id] ?? "Usuarios";

$title = "$role_name - EcoCusco";
$header_title = "Gestión $role_name";

// Consulta SQL optimizada
$sql = "SELECT u.id, u.nombre, u.apellido, u.email, u.rol_id, r.nombre as rol_nombre, u.creado_en, dp.cargo 
        FROM usuarios u 
        JOIN roles r ON u.rol_id = r.id 
        LEFT JOIN detalles_personal_obrero dp ON u.id = dp.usuario_id
        WHERE 1=1";
if ($filter_rol_id > 0) $sql .= " AND u.rol_id = :rol_id";
$sql .= " ORDER BY u.creado_en DESC";

$stmt = $pdo->prepare($sql);
if ($filter_rol_id > 0) $stmt->bindParam(':rol_id', $filter_rol_id, PDO::PARAM_INT);
$stmt->execute();
$usuarios_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<style>
    /* Estilos Reducidos */
    .actions-bar { display: flex; justify-content: space-between; margin-bottom: 10px; gap: 10px; }
    .search-container { position: relative; flex-grow: 1; max-width: 280px; }
    .search-input { width: 100%; padding: 5px 10px 5px 30px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 12px; }
    .search-icon { position: absolute; left: 8px; top: 50%; transform: translateY(-50%); color: #9CA3AF; }
    .btn-new-user { background: var(--primary); color: white; padding: 5px 12px; border-radius: 6px; text-decoration: none; display: flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; }
    .table-container { background: white; padding: 10px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 12px; }
    th, td { padding: 6px 8px; text-align: left; border-bottom: 1px solid #F3F4F6; }
    th { background: #F9FAFB; color: #4B5563; text-transform: uppercase; font-size: 11px; }
    .user-avatar { width: 24px; height: 24px; background: #EEE; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; margin-right: 8px; flex-shrink: 0; }
    .name-col { display: flex; align-items: center; }
    .table-actions { display: flex; gap: 4px; }
    .action-icon { width: 24px; height: 24px; border-radius: 4px; display: flex; align-items: center; justify-content: center; background: #F3F4F6; color: #4B5563; text-decoration: none; }
    .badge { font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 600; }
</style>

<?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

<?php render_dashboard_stats([
    ['title' => 'Total', 'value' => count($usuarios_lista), 'color' => '#10B981', 'icon' => '👥'],
    ['title' => 'Admins', 'value' => count(array_filter($usuarios_lista, fn($u) => $u['rol_id'] == 1)), 'color' => '#DC2626', 'icon' => '🛡️'],
    ['title' => 'Personal', 'value' => count(array_filter($usuarios_lista, fn($u) => $u['rol_id'] == 3)), 'color' => '#D97706', 'icon' => '👷']
]); ?>

<?php
// Determinar enlace de creación según el filtro
$create_links = [
    5 => 'usuario_nuevo_barrio',
    6 => 'usuario_nuevo_calle',
    2 => 'usuario_nuevo_gestor',
    3 => 'usuario_nuevo_personal'
];
$new_link = $create_links[$filter_rol_id] ?? 'usuario_nuevo';
?>

<div class="actions-bar">
    <div class="search-container">
        <span class="search-icon">🔍</span>
        <input type="text" id="userSearch" class="search-input" placeholder="Buscar <?= strtolower($role_name) ?>..." onkeyup="filterUsers()">
    </div>
    
    <div style="display: flex; gap: 8px;">
        <?php if ($filter_rol_id == 0): ?>
            <!-- Accesos Rápidos si no hay filtro -->
            <a href="router.php?page=usuario_nuevo_barrio" class="btn-new-user" style="background:#059669;" title="Nuevo Encargado de Barrio">+ Barrio</a>
            <a href="router.php?page=usuario_nuevo_personal" class="btn-new-user" style="background:#D97706;" title="Nuevo Personal Obrero">+ Personal</a>
        <?php else: ?>
            <a href="router.php?page=<?= $new_link ?>" class="btn-new-user">
                <span>+</span> Registrar <?= rtrim($role_name, 's') ?>
            </a>
        <?php endif; ?>
    </div>
</div>

<section class="table-container">
    <table id="userTable">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Rol / Cargo</th>
                <th>Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios_lista as $u): ?>
            <tr>
                <td>
                    <div class="name-col">
                        <div class="user-avatar"><?= strtoupper(substr($u['nombre'],0,1).substr($u['apellido'],0,1)) ?></div>
                        <div>
                            <div style="font-weight: 600;"><?= htmlspecialchars($u['nombre'].' '.$u['apellido']) ?></div>
                            <div style="color: #6B7280; font-size: 11px;"><?= htmlspecialchars($u['email']) ?></div>
                        </div>
                    </div>
                </td>
                <td>
                    <?php 
                        $roles_css = [1=>'admin', 2=>'gestor', 3=>'recolector', 5=>'jefe', 6=>'calle'];
                        $class = $roles_css[$u['rol_id']] ?? 'usuario';
                    ?>
                    <span class="badge <?= $class ?>">
                        <?= htmlspecialchars($u['rol_nombre']) ?>
                        <?= ($u['rol_id'] == 3 && $u['cargo']) ? ' - '.htmlspecialchars($u['cargo']) : '' ?>
                    </span>
                </td>
                <td style="color: #6B7280;"><?= date('d/m/y', strtotime($u['creado_en'])) ?></td>
                <td>
                    <div class="table-actions">
                        <a href="router.php?page=usuario_ver&id=<?= $u['id'] ?>" class="action-icon" title="Ver">👁</a>
                        <a href="router.php?page=usuario_editar&id=<?= $u['id'] ?>" class="action-icon" title="Editar">✏️</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<script>
function filterUsers() {
    let filter = document.getElementById("userSearch").value.toLowerCase();
    let rows = document.querySelectorAll("#userTable tbody tr");
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>