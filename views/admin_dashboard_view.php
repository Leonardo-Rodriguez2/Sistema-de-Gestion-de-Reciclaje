<?php
// admin_dashboard_view.php (Logic is already handled by router.php, but keeping check for safety if accessed directly)
if (!isset($user)) {
    session_start();
    require_once 'data/conexion.php';
    $stmt = $pdo->prepare("SELECT u.nombre, u.apellido, u.rol_id, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$user || $user['rol_id'] != 1) {
    die("Acceso denegado. Se requiere nivel de administrador.");
}

// Data fetching (already in file)
$usuariosStmt = $pdo->query("SELECT u.id, u.nombre, u.apellido, u.email, r.nombre as rol FROM usuarios u JOIN roles r ON u.rol_id = r.id ORDER BY u.creado_en DESC");
$usuarios_lista = $usuariosStmt->fetchAll(PDO::FETCH_ASSOC);

$barriosStmt = $pdo->query("SELECT * FROM barrios ORDER BY nombre ASC");
$barrios_lista = $barriosStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Admin Dashboard - EcoCusco";
$header_title = "Panel de Administración";
$user_greeting = "Hola";

ob_start();
?>
    <!-- Stats -->
    <section class="grid">
      <div class="card">
        <h3>Total Usuarios</h3>
        <div class="value"><?php echo count($usuarios_lista); ?></div>
      </div>
      <div class="card">
        <h3>Barrios Operativos</h3>
        <div class="value"><?php echo count($barrios_lista); ?></div>
      </div>
      <div class="card">
        <h3>Reportes Críticos</h3>
        <div class="value">0</div>
      </div>
    </section>
    
    <!-- Users Table -->
    <section class="table-container">
      <h3>Usuarios Registrados</h3>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios_lista as $u): ?>
          <tr>
            <td>#<?php echo $u['id']; ?></td>
            <td style="font-weight: 500;"><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td>
              <?php 
                $badgeClass = 'usuario';
                if($u['rol'] == 'Administrador') $badgeClass = 'admin';
                if($u['rol'] == 'Gestor de Pagos') $badgeClass = 'gestor';
                if($u['rol'] == 'Recolector') $badgeClass = 'recolector';
              ?>
              <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($u['rol']); ?></span>
            </td>
            <td><a href="router.php?page=editar_rol&id=<?php echo $u['id']; ?>" class="btn-action">Editar Rol</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/dashboard_layout.php';
?>

