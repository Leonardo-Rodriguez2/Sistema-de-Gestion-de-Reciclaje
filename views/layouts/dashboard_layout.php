<?php
// views/layouts/dashboard_layout.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title ?? 'Dashboard - EcoCusco'; ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: <?php echo $primary_color ?? '#10B981'; ?>;
      --secondary: <?php echo $secondary_color ?? '#059669'; ?>;
      --bg: <?php echo $bg_color ?? '#F3F4F6'; ?>;
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
    /* Status Badges */
    .badge.admin { background: #FEE2E2; color: #DC2626; }
    .badge.gestor { background: #FEF3C7; color: #D97706; }
    .badge.recolector { background: #E0E7FF; color: #4338CA; }
    .badge.usuario { background: #D1FAE5; color: #059669; }
    .badge.pagado { background: #D1FAE5; color: #059669; }
    .badge.pendiente { background: #FEF3C7; color: #D97706; }
    .badge.vencido { background: #FEE2E2; color: #DC2626; }
    .badge.en-proceso { background: #DBEAFE; color: #1D4ED8; }
    
    .btn-action { background: var(--primary); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 500; transition: 0.2s; text-decoration: none; display: inline-block;}
    .btn-action:hover { background: var(--secondary); }

    /* Custom styles for specific views could go here if injected through a variable */
    <?php echo $extra_css ?? ''; ?>
  </style>
</head>
<body>

  <?php include __DIR__ . '/../../components/sidebar.php'; ?>

  <main class="main">
    <header class="header">
      <div>
        <h1><?php echo $header_title ?? 'Panel de Control'; ?></h1>
        <div style="color: #6B7280; margin-top: 5px;"><?php echo $header_subtitle ?? ''; ?></div>
      </div>
      <div class="user-info">
        <?php echo $user_greeting ?? 'Hola'; ?>, <?php echo htmlspecialchars($user['nombre'] . (isset($user['apellido']) ? ' ' . $user['apellido'] : '')); ?> (<?php echo htmlspecialchars($user['rol_nombre'] ?? ''); ?>)
      </div>
    </header>

    <?php echo $content; ?>
  </main>

</body>
</html>
