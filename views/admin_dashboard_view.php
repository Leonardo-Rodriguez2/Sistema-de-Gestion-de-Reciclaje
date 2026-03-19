<?php
// admin_dashboard_view.php
// Rediseñado con Componentes y Helper

$user = check_dashboard_access([1]);

// Fetch users
$usuariosStmt = $pdo->query("SELECT u.id, u.nombre, u.apellido, u.email, u.rol_id, r.nombre as rol_nombre, u.creado_en 
                             FROM usuarios u JOIN roles r ON u.rol_id = r.id 
                             ORDER BY u.creado_en DESC");
$usuarios_lista = $usuariosStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Gestión de Usuarios - EcoCusco";
$header_title = "Gestión de Usuarios";
$header_subtitle = "Administra las cuentas y permisos de todo el sistema.";
$user_greeting = "Administrador";

ob_start();
?>
    <!-- Feedback Messages -->
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Stats -->
    <?php render_dashboard_stats([
        ['title' => 'Total Usuarios', 'value' => count($usuarios_lista), 'color' => '#10B981', 'icon' => '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>'],
        ['title' => 'Administradores', 'value' => count(array_filter($usuarios_lista, fn($u) => $u['rol_id'] == 1)), 'color' => '#DC2626', 'icon' => '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>'],
        ['title' => 'Personal Operativo', 'value' => count(array_filter($usuarios_lista, fn($u) => in_array($u['rol_id'], [2, 3]))), 'color' => '#D97706', 'icon' => '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>']
    ]); ?>

<style>
    .actions-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 20px; }
    .search-container { position: relative; flex-grow: 1; max-width: 400px; }
    .search-input { width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #E5E7EB; border-radius: 10px; font-family: inherit; font-size: 14px; background: #FFFFFF; transition: 0.3s; }
    .search-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
    .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9CA3AF; pointer-events: none; }
    .btn-new-user { background: var(--primary); color: white; padding: 12px 24px; border-radius: 10px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: 0.3s; border: none; cursor: pointer; }
    .btn-new-user:hover { background: var(--secondary); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
    .table-container { overflow-x: auto; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    .table-actions { display: flex; gap: 8px; }
    .action-icon { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; border: none; background: #F3F4F6; color: #4B5563; }
    .action-icon:hover { background: #E5E7EB; color: #1F2937; }
    .action-icon.edit:hover { background: #DBEAFE; color: #1D4ED8; }
    .action-icon.view:hover { background: #D1FAE5; color: #059669; }
    .action-icon.delete:hover { background: #FEE2E2; color: #DC2626; }
    .user-avatar { width: 36px; height: 36px; background: #E5E7EB; color: #4B5563; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; margin-right: 12px; }
    .name-col { display: flex; align-items: center; }
</style>

    <!-- Header Actions -->
    <div class="actions-bar">
        <div class="search-container">
            <span class="search-icon"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></span>
            <input type="text" id="userSearch" class="search-input" placeholder="Buscar por nombre, email o rol..." onkeyup="filterUsers()">
        </div>
        <button class="btn-new-user" onclick="openModal('addUserModal')">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Nuevo Usuario
        </button>
    </div>
    
    <!-- User Table -->
    <section class="table-container">
      <table id="userTable">
        <thead>
          <tr>
            <th>Nombre y Email</th>
            <th>Rol</th>
            <th>Fecha Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios_lista as $u): ?>
          <tr>
            <td>
                <div class="name-col">
                    <div class="user-avatar"><?php echo strtoupper(substr($u['nombre'], 0, 1) . substr($u['apellido'], 0, 1)); ?></div>
                    <div>
                        <div style="font-weight: 600; color: #111827;"><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?></div>
                        <div style="font-size: 13px; color: #6B7280;"><?php echo htmlspecialchars($u['email']); ?></div>
                    </div>
                </div>
            </td>
            <td>
              <?php 
                $badgeClass = 'usuario';
                if($u['rol_id'] == 1) $badgeClass = 'admin';
                if($u['rol_id'] == 2) $badgeClass = 'gestor';
                if($u['rol_id'] == 3) $badgeClass = 'recolector';
              ?>
              <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($u['rol_nombre']); ?></span>
            </td>
            <td style="color: #6B7280; font-size: 14px;"><?php echo date('d/m/Y', strtotime($u['creado_en'])); ?></td>
            <td>
                <div class="table-actions">
                    <button class="action-icon view" title="Ver Detalles" onclick='openViewModal(<?php echo json_encode($u); ?>)'>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                    <button class="action-icon edit" title="Editar" onclick='openEditModal(<?php echo json_encode($u); ?>)'>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </button>
                    <button class="action-icon delete" title="Eliminar" onclick="openDeleteModal(<?php echo $u['id']; ?>, '<?php echo $u['nombre'] . ' ' . $u['apellido']; ?>')">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

    <!-- Modals -->
    <?php include __DIR__ . '/components/user_modals.php'; ?>

    <script>
    function filterUsers() {
        let input = document.getElementById("userSearch");
        let filter = input.value.toLowerCase();
        let table = document.getElementById("userTable");
        let tr = table.getElementsByTagName("tr");
        for (let i = 1; i < tr.length; i++) {
            let visible = false;
            let tds = tr[i].getElementsByTagName("td");
            for (let j = 0; j < tds.length - 1; j++) {
                if (tds[j] && tds[j].textContent.toLowerCase().indexOf(filter) > -1) { visible = true; break; }
            }
            tr[i].style.display = visible ? "" : "none";
        }
    }
    </script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/dashboard_layout.php';
?>

