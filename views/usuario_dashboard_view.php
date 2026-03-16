<?php
session_start();
require_once 'data/conexion.php';

// Verificar que la sesión exista
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar que el usuario tenga rol de Usuario/Vecino (rol_id = 4)
$stmt = $pdo->prepare("SELECT u.nombre, u.apellido, u.rol_id, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['rol_id'] != 4) {
    die("Acceso denegado. Este panel es exclusivo para usuarios residenciales.");
}

// Obtener las viviendas del usuario
$viviendasStmt = $pdo->prepare("SELECT v.id, v.direccion, v.numero_casa, b.nombre as barrio FROM viviendas v JOIN barrios b ON v.barrio_id = b.id WHERE v.usuario_id = ?");
$viviendasStmt->execute([$_SESSION['user_id']]);
$viviendas = $viviendasStmt->fetchAll(PDO::FETCH_ASSOC);

$vivienda_ids = array_column($viviendas, 'id');
$cobros_lista = [];

if (count($vivienda_ids) > 0) {
    // Preparar el query para obtener los cobros de SUS viviendas
    $in = str_repeat('?,', count($vivienda_ids) - 1) . '?';
    $cobrosStmt = $pdo->prepare("
        SELECT c.id, v.direccion, v.numero_casa, c.mes, c.anio, c.monto, c.estado, c.fecha_vencimiento 
        FROM cobros c
        JOIN viviendas v ON c.vivienda_id = v.id
        WHERE c.vivienda_id IN ($in)
        ORDER BY c.fecha_vencimiento DESC
    ");
    $cobrosStmt->execute($vivienda_ids);
    $cobros_lista = $cobrosStmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Panel Residencial - EcoCusco</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #10B981; /* Verde característico */
      --secondary: #059669;
      --bg: #F0FDF4; /* Fondo verde muy claro */
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
    .badge.pagado { background: #D1FAE5; color: #059669; }
    .badge.pendiente { background: #FEF3C7; color: #D97706; }
    .badge.vencido { background: #FEE2E2; color: #DC2626; }
    
    .btn-pagar { background: var(--primary); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 500; transition: 0.2s;}
    .btn-pagar:hover { background: var(--secondary); }

  </style>
</head>
<body>

  <!-- Sidebar -->
  <aside class="sidebar">
    <h2>EcoCusco</h2>
    <ul class="nav-links">
      <li><a href="router.php?page=dashboard" class="<?php echo ($page == 'dashboard') ? 'active' : ''; ?>">Mis Cobros y Pagos</a></li>
      <li><a href="reportes.php">Reportar Basura</a></li>
      <li><a href="router.php?page=propiedades">Mis Propiedades</a></li>
      <li><a href="index.php">Ir a Web Principal</a></li>
    </ul>
    <a href="login.php?logout=true" class="logout">Cerrar Sesión</a>
  </aside>

  <!-- Main Content -->
  <main class="main">
    <header class="header">
      <div>
        <h1>Mi Estado de Cuenta</h1>
        <div style="color: #6B7280; margin-top: 5px;">Módulo Residencial</div>
      </div>
      <div class="user-info">
        Bienvenido(a), <?php echo htmlspecialchars($user['nombre']); ?>
      </div>
    </header>

    <!-- Stats -->
    <section class="grid">
      <div class="card">
        <h3>Viviendas Registradas</h3>
        <div class="value"><?php echo count($viviendas); ?></div>
      </div>
      <div class="card" style="border-top-color: #DC2626;">
        <h3 style="color: #DC2626;">Total Por Pagar (Atrasado)</h3>
        <div class="value" style="color: #DC2626;">S/ <?php 
          $deuda = 0;
          foreach($cobros_lista as $c) if($c['estado'] == 'Vencido') $deuda += $c['monto'];
          echo number_format($deuda, 2);
        ?></div>
      </div>
    </section>

    <!-- Alertas -->
    <?php if(isset($mensaje_exito)): ?>
      <div style="background: #D1FAE5; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">✓ <?php echo $mensaje_exito; ?></div>
    <?php endif; ?>
    <?php if(isset($mensaje_error)): ?>
      <div style="background: #FEE2E2; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px;">✕ <?php echo $mensaje_error; ?></div>
    <?php endif; ?>

    <!-- Report Table -->
    <section class="table-container">
      <h3>Historial de Recibos y Mensualidades</h3>
      <table>
        <thead>
          <tr>
            <th>Propiedad</th>
            <th>Período Cobrado</th>
            <th>Vencimiento</th>
            <th>Monto</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($cobros_lista)): ?>
            <tr><td colspan="6" style="text-align:center;">No tienes propiedades registradas ni cobros pendientes.</td></tr>
          <?php else: ?>
            <?php foreach ($cobros_lista as $c): ?>
            <tr>
              <td style="font-weight: 500; color: #4B5563;"><?php echo htmlspecialchars($c['direccion'] . ' ' . $c['numero_casa']); ?></td>
              <td><?php echo htmlspecialchars(str_pad($c['mes'], 2, "0", STR_PAD_LEFT) . ' / ' . $c['anio']); ?></td>
              <td><?php echo date('d/m/Y', strtotime($c['fecha_vencimiento'])); ?></td>
              <td style="font-weight: 700; color: #374151;">S/ <?php echo number_format($c['monto'], 2); ?></td>
              <td>
                <span class="badge <?php echo strtolower($c['estado']); ?>"><?php echo htmlspecialchars($c['estado']); ?></span>
              </td>
              <td>
                  <?php if($c['estado'] != 'Pagado'): ?>
                    <form action="router.php?page=dashboard" method="POST" style="margin:0;">
                      <input type="hidden" name="form_type" value="procesar_pago">
                      <input type="hidden" name="cobro_id" value="<?php echo $c['id']; ?>">
                      <button type="submit" class="btn-pagar">Pagar Ahora</button>
                    </form>
                  <?php else: ?>
                    <span style="color: #059669; font-weight: 500; font-size: 14px;">✔ Cancelado</span>
                  <?php endif; ?>
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
