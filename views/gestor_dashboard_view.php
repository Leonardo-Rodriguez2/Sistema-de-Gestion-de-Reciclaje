<?php
// gestor_dashboard_view.php
// Rediseñado con Componentes y Helper

$user = check_dashboard_access([1, 2]);

// Obtener barrios para el formulario
$barriosStmt = $pdo->query("SELECT * FROM barrios ORDER BY nombre ASC");
$barrios = $barriosStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener cobros pendientes y vencidos
$cobrosStmt = $pdo->query("
    SELECT c.id, v.direccion, v.numero_casa, b.nombre as barrio, u.nombre, u.apellido, c.mes, c.anio, c.monto, c.estado, c.fecha_vencimiento 
    FROM cobros c
    JOIN viviendas v ON c.vivienda_id = v.id
    JOIN barrios b ON v.barrio_id = b.id
    JOIN usuarios u ON v.usuario_id = u.id
    WHERE c.estado IN ('Pendiente', 'Vencido')
    ORDER BY c.fecha_vencimiento ASC
");
$cobros_lista = $cobrosStmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener últimos pagos
$pagosStmt = $pdo->query("
    SELECT p.id, p.monto_pagado, p.fecha_pago, p.metodo_pago, u.nombre, u.apellido, v.direccion
    FROM pagos p
    JOIN cobros c ON p.cobro_id = c.id
    JOIN viviendas v ON c.vivienda_id = v.id
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.fecha_pago DESC LIMIT 5
");
$pagos_lista = $pagosStmt->fetchAll(PDO::FETCH_ASSOC);

$total_pagos = array_sum(array_column($pagos_lista, 'monto_pagado'));

$title = "Gestor de Pagos - EcoCusco";
$primary_color = "#D97706";
$secondary_color = "#B45309";
$bg_color = "#FFFBEB";
$header_title = "Gestión de Pagos";
$header_subtitle = "Módulo de Finanzas";
$user_greeting = "Hola";

ob_start();
?>
    <!-- Feedback Messages -->
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <!-- Stats -->
    <?php render_dashboard_stats([
        ['title' => 'Cobros Pendientes', 'value' => count($cobros_lista), 'color' => '#D97706', 'icon' => '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'],
        ['title' => 'Caja Semanal', 'value' => 'S/ ' . number_format($total_pagos, 2), 'color' => '#059669', 'icon' => '<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>']
    ]); ?>

    <!-- Formulario de Registro Nuevo Vecino -->
    <section class="table-container" id="registrar_vecino" style="background: #FEF3C7; border: 1px solid #FDE68A; margin-bottom: 30px;">
      <h3 style="color: #B45309; margin-top:0;">Afiliar Nuevo Vecino y Vivienda</h3>
      <p style="color: #92400E; font-size: 14px; margin-bottom: 20px;">Use este formulario exclusivo para inscribir una nueva casa al servicio.</p>
      
      <form action="router.php?page=dashboard" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <input type="hidden" name="form_type" value="nuevo_vecino">
        <div>
          <label class="form-label">Nombre</label>
          <input type="text" name="nombre" required class="form-input">
        </div>
        <div>
          <label class="form-label">Apellido</label>
          <input type="text" name="apellido" required class="form-input">
        </div>
        <div>
          <label class="form-label">Email</label>
          <input type="email" name="email" required class="form-input">
        </div>
        <div>
          <label class="form-label">Barrio</label>
          <select name="barrio_id" required class="form-select">
            <?php foreach($barrios as $b): ?>
              <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['nombre']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label">Dirección</label>
          <input type="text" name="direccion" required class="form-input">
        </div>
        <div>
          <label class="form-label">Número</label>
          <input type="text" name="numero" required class="form-input">
        </div>
        <div style="grid-column: 1 / -1; margin-top: 10px;">
          <button type="submit" class="btn-action" style="background: #B45309; width: 100%;">Registrar Vivienda y Usuario</button>
        </div>
      </form>
    </section>

    <!-- Viviendas con Deuda Table -->
    <section class="table-container">
      <h3>Viviendas con Deuda Activa</h3>
      <table>
        <thead>
          <tr>
            <th>Propietario</th>
            <th>Vivienda</th>
            <th>Período</th>
            <th>Monto</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($cobros_lista)): ?>
            <tr><td colspan="6" style="text-align:center;">No hay viviendas con deuda.</td></tr>
          <?php else: ?>
            <?php foreach ($cobros_lista as $c): ?>
            <tr>
              <td style="font-weight: 600;"><?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido']); ?></td>
              <td><?php echo htmlspecialchars($c['direccion'] . ' ' . $c['numero_casa']); ?> <br><small style="color: #6B7280;"><?php echo htmlspecialchars($c['barrio']); ?></small></td>
              <td><?php echo htmlspecialchars(str_pad($c['mes'], 2, "0", STR_PAD_LEFT) . '/' . $c['anio']); ?></td>
              <td style="font-weight: 700; color: #111827;">S/ <?php echo number_format($c['monto'], 2); ?></td>
              <td><span class="badge <?php echo strtolower($c['estado']); ?>"><?php echo htmlspecialchars($c['estado']); ?></span></td>
              <td>
                <form action="router.php?page=dashboard" method="POST" style="margin:0;">
                  <input type="hidden" name="form_type" value="procesar_pago">
                  <input type="hidden" name="cobro_id" value="<?php echo $c['id']; ?>">
                  <button type="submit" class="btn-action">Marcar Pagado</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </section>

<style>
    .form-label { display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #4B5563; }
    .form-input, .form-select { width: 100%; padding: 10px; border: 1px solid #D1D5DB; border-radius: 8px; font-family: inherit; box-sizing: border-box; }
    .form-input:focus, .form-select:focus { outline: none; border-color: #D97706; box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.1); }
    .table-container { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 12px; border-bottom: 1px solid #E5E7EB; color: #6B7280; font-size: 12px; text-transform: uppercase; }
    td { padding: 15px 12px; border-bottom: 1px solid #F3F4F6; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/dashboard_layout.php';
?>

