<?php
session_start();
require_once 'data/conexion.php';

// Verificar que la sesión exista
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar que el usuario tenga rol de Administrador (rol_id = 1)
$stmt = $pdo->prepare("SELECT u.nombre, u.apellido, u.rol_id, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['rol_id'] != 1) {
    die("Acceso denegado. Se requiere nivel de administrador.");
}

// Obtener todos los usuarios y sus roles
$usuariosStmt = $pdo->query("SELECT u.id, u.nombre, u.apellido, u.email, r.nombre as rol FROM usuarios u JOIN roles r ON u.rol_id = r.id ORDER BY u.creado_en DESC");
$usuarios_lista = $usuariosStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener barrios
$barriosStmt = $pdo->query("SELECT * FROM barrios ORDER BY nombre ASC");
$barrios_lista = $barriosStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - EcoCusco</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #10B981;
      --secondary: #059669;
      --bg: #F3F4F6;
      --text: #1F2937;
      --card-bg: #FFFFFF;
    }
    body { font-family: 'Poppins', sans-serif; background: var(--bg); margin: 0; display: flex; height: 100vh; color: var(--text); }
    
    /* Sidebar */
    .sidebar { width: 250px; background: var(--card-bg); padding: 20px; box-shadow: 2px 0 10px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
    .sidebar h2 { color: var(--primary); font-weight: 700; font-size: 24px; text-align: center; margin-bottom: 30px; }
    .nav-links { list-style: none; padding: 0; flex-grow: 1;}
    .nav-links li { margin-bottom: 15px; }
    .nav-links a { text-decoration: none; color: var(--text); font-weight: 500; font-size: 16px; display: block; padding: 10px; border-radius: 8px; transition: 0.3s; }
    .nav-links a:hover, .nav-links a.active { background: var(--primary); color: white; }
    .logout { text-decoration: none; color: #DC2626; font-weight: 500; padding: 10px; text-align: center; border: 1px solid #DC2626; border-radius: 8px; transition: 0.3s;}
    .logout:hover { background: #DC2626; color: white; }

    /* Main Content */
    .main { flex-grow: 1; padding: 40px; overflow-y: auto; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    h1 { font-size: 28px; font-weight: 700; margin: 0; }
    .user-info { font-size: 16px; color: #6B7280; font-weight: 500;}

    /* Cards/Grids */
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .card { background: var(--card-bg); padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-top: 4px solid var(--primary); }
    .card h3 { margin-top: 0; font-size: 18px; color: #4B5563; }
    .card .value { font-size: 32px; font-weight: 700; color: var(--text); }

    /* Tables */
    .table-container { background: var(--card-bg); padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px;}
    .table-container h3 { margin-top: 0; margin-bottom: 20px; font-size: 20px; color: var(--text); }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 15px; text-align: left; border-bottom: 1px solid #E5E7EB; }
    th { background: #F9FAFB; font-weight: 500; color: #6B7280; text-transform: uppercase; font-size: 12px; }
    tr:hover { background: #F9FAFB; }
    .badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 500;}
    .badge.admin { background: #FEE2E2; color: #DC2626; }
    .badge.gestor { background: #FEF3C7; color: #D97706; }
    .badge.recolector { background: #E0E7FF; color: #4338CA; }
    .badge.usuario { background: #D1FAE5; color: #059669; }

  </style>
</head>
<body>

  <!-- Sidebar -->
  <aside class="sidebar">
    <h2>EcoCusco</h2>
    <ul class="nav-links">
      <li><a href="router.php?page=dashboard" class="<?php echo ($page == 'dashboard') ? 'active' : ''; ?>">Dashboard General</a></li>
      <li><a href="router.php?page=usuarios" class="<?php echo ($page == 'usuarios') ? 'active' : ''; ?>">Gestión de Usuarios</a></li>
      <li><a href="router.php?page=zonas">Zonas y Barrios</a></li>
      <li><a href="router.php?page=reportes">Reportes del Sistema</a></li>
    </ul>
    <a href="login.php?logout=true" class="logout">Cerrar Sesión</a>
  </aside>

  <!-- Main Content -->
  <main class="main">
    <header class="header">
      <h1>Panel de Administración</h1>
      <div class="user-info">
        Hola, <?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?> (<?php echo htmlspecialchars($user['rol_nombre']); ?>)
      </div>
    </header>

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
            <td><a href="router.php?page=editar_rol&id=<?php echo $u['id']; ?>" style="color: var(--primary); font-size: 14px; text-decoration: none;">Editar Rol</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>

</body>
</html>
