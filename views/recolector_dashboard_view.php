<?php
session_start();
require_once 'data/conexion.php';

// Verificar que la sesión exista
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar que el usuario tenga rol de Recolector (rol_id = 3) o superior
$stmt = $pdo->prepare("SELECT u.nombre, u.apellido, u.rol_id, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || ($user['rol_id'] != 1 && $user['rol_id'] != 3)) {
    die("Acceso denegado. Se requiere nivel de Recolector.");
}

// Obtener reportes activos (En proceso o Pendiente de asignar)
$reportesStmt = $pdo->query("
    SELECT r.id, r.ubicacion_nombre, r.tipo_residuo, r.descripcion, r.cantidad, r.estado, r.fecha_reporte, u.nombre, u.apellido 
    FROM reportes r
    JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.estado IN ('Pendiente', 'En proceso')
    ORDER BY r.fecha_reporte ASC
");
$reportes_lista = $reportesStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recolector - EcoCusco</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #4F46E5; /* Color morado/índigo para el recolector */
      --secondary: #4338CA;
      --bg: #EEF2FF; /* Fondo morado claro */
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
    .badge.pendiente { background: #FEF3C7; color: #D97706; }
    .badge.en-proceso { background: #DBEAFE; color: #1D4ED8; }
    
    .btn-completar { background: var(--primary); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 500; transition: 0.2s;}
    .btn-completar:hover { background: var(--secondary); }

  </style>
</head>
<body>

  <!-- Sidebar -->
  <aside class="sidebar">
    <h2>EcoCusco</h2>
    <ul class="nav-links">
      <li><a href="router.php?page=dashboard" class="<?php echo ($page == 'dashboard') ? 'active' : ''; ?>">Rutas Pendientes</a></li>
      <li><a href="router.php?page=completados">Reportes Completados</a></li>
      <li><a href="router.php?page=mapa">Mapa de Zonas</a></li>
      <li><a href="router.php?page=vehiculo">Mi Vehículo</a></li>
    </ul>
    <a href="login.php?logout=true" class="logout">Cerrar Sesión</a>
  </aside>

  <!-- Main Content -->
  <main class="main">
    <header class="header">
      <div>
        <h1>Mis Rutas y Pendientes</h1>
        <div style="color: #6B7280; margin-top: 5px;">Módulo Logístico y de Recolección</div>
      </div>
      <div class="user-info">
        Hola, <?php echo htmlspecialchars($user['nombre']); ?> (<?php echo htmlspecialchars($user['rol_nombre']); ?>)
      </div>
    </header>

    <!-- Stats -->
    <section class="grid">
      <div class="card">
        <h3>Reportes por Atender</h3>
        <div class="value"><?php echo count($reportes_lista); ?></div>
      </div>
      <div class="card">
        <h3>Material Recolectado Hoy</h3>
        <div class="value">0.0 kg</div>
      </div>
    </section>

    <!-- Alertas -->
    <?php if(isset($mensaje_exito)): ?>
      <div style="background: #DBEAFE; color: #1E40AF; padding: 15px; border-radius: 8px; margin-bottom: 20px;">✓ <?php echo $mensaje_exito; ?></div>
    <?php endif; ?>
    <?php if(isset($mensaje_error)): ?>
      <div style="background: #FEE2E2; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px;">✕ <?php echo $mensaje_error; ?></div>
    <?php endif; ?>

    <!-- Report Table -->
    <section class="table-container">
      <h3>Lista de Puntos de Recolección</h3>
      <table>
        <thead>
          <tr>
            <th>Ubicación</th>
            <th>Tipo de Residuo</th>
            <th>Cantidad</th>
            <th>Reportado Por</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($reportes_lista)): ?>
            <tr><td colspan="6" style="text-align:center;">No hay reportes pendientes para recolectar hoy. ¡Buen trabajo!</td></tr>
          <?php else: ?>
            <?php foreach ($reportes_lista as $r): ?>
            <tr>
              <td style="font-weight: 500;"><?php echo htmlspecialchars($r['ubicacion_nombre']); ?> <br> <small style="color:#6B7280;font-weight:400;"><?php echo htmlspecialchars($r['descripcion']); ?></small></td>
              <td><?php echo htmlspecialchars($r['tipo_residuo']); ?></td>
              <td style="font-weight: 700; color: #374151;"><?php echo number_format($r['cantidad'], 2); ?> kg</td>
              <td><?php echo htmlspecialchars($r['nombre'] . ' ' . $r['apellido']); ?><br><small><?php echo date('d/m/Y H:i', strtotime($r['fecha_reporte'])); ?></small></td>
              <td>
                <span class="badge <?php echo str_replace(' ', '-', strtolower($r['estado'])); ?>"><?php echo htmlspecialchars($r['estado']); ?></span>
              </td>
              <td>
                <form action="router.php?page=dashboard" method="POST" style="margin:0;">
                  <input type="hidden" name="form_type" value="completar_reporte">
                  <input type="hidden" name="reporte_id" value="<?php echo $r['id']; ?>">
                  <button type="submit" class="btn-completar">Marcar Recogido</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
  </main>

</body>
</html>
