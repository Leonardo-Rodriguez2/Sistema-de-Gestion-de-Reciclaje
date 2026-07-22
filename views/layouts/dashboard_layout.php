<?php
// views/layouts/dashboard_layout.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $title ?? 'Dashboard - EPSIC'; ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/reciclaje/assets/css/style.css">
  <style>
    :root {
      --primary: #2d2d3d;
      --secondary: #222;
      --accent: #10B981;
      --bg: #F5F5F5;
      --text: #374151;
      --card-bg: #fff;
      --border: #ddd;
    }
    * { box-sizing: border-box; }
    body { font-family: 'Poppins', sans-serif; background: var(--bg); margin: 0; display: flex; color: var(--text); font-size: 12px; }
    
    /* Layout */
    .main { flex-grow: 1; margin-left: 220px; padding: 18px 24px; min-height: 100vh; box-sizing: border-box; width: 100%; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid var(--border); padding-bottom: 12px; gap: 10px; }
    h1 { font-size: 18px; font-weight: 600; margin: 0; color: var(--secondary); }
    .user-info { font-size: 12px; color: #6B7280; font-weight: 500; white-space: nowrap;}

    /* Hamburger Toggle */
    .menu-toggle {
      display: none;
      background: var(--primary);
      color: white;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 4px;
      font-size: 20px;
      cursor: pointer;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: 0.2s;
      z-index: 1001;
      position: relative;
    }
    .menu-toggle:hover { background: var(--secondary); }
    .menu-toggle.active { background: #DC2626; }

    /* Sidebar Overlay */
    .sidebar-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      z-index: 998;
      opacity: 0;
      transition: opacity 0.3s;
    }
    .sidebar-overlay.show { opacity: 1; }

    /* Comunes */
    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 4px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .badge { padding: 4px 10px; border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase; }
    .badge.admin { background: #F3F4F6; color: #374151; border: 1px solid #D1D5DB; }
    .badge.gestor { background: #F3F4F6; color: #374151; border: 1px solid #D1D5DB; }
    .badge.recolector { background: #F3F4F6; color: #374151; border: 1px solid #D1D5DB; }
    
    .btn-primary { background: var(--primary); color: white; border: none; padding: 8px 16px; border-radius: 4px; font-weight: 600; font-size: 12px; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-primary:hover { background: var(--secondary); }
    .btn-edit { background: #EBF5FF; color: #1E40AF; padding: 4px 10px; border-radius: 5px; font-size: 11px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
    .btn-edit:hover { background: #DBEAFE; }

    .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; margin: 0; }
    .table-wrap table { min-width: 480px; }
    table { width: 100%; border-collapse: collapse; font-size: 12px; }
    th { text-align: left; padding: 12px; border-bottom: 1px solid var(--border); color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap; }
    td { padding: 12px; border-bottom: 1px solid #F9FAFB; }
    @media (max-width: 768px) { th { white-space: normal; } }

    /* Grid helper */
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }

    /* RESPONSIVE */
    @media (max-width: 1024px) {
        .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
        .sidebar.open { transform: translateX(0); }
        .main { margin-left: 0; padding: 15px; }
        .menu-toggle { display: flex; }
        .header { flex-wrap: wrap; }
        .user-info { font-size: 12px; }
        h1 { font-size: 18px; }
    }

    @media (max-width: 768px) {
        .main { padding: 12px; }
        .card { padding: 15px; }
        h1 { font-size: 16px; }
        .header { flex-direction: column; align-items: stretch; gap: 8px; }
        .user-info { text-align: right; font-size: 11px; white-space: normal; }
        table { font-size: 12px; }
        th, td { padding: 8px 6px; }
        .btn-primary { padding: 10px 14px; font-size: 12px; min-height: 44px; }
        .grid { grid-template-columns: 1fr; }
        [style*="grid-column: span"] { grid-column: span 1 !important; }
        div[style*="grid-template-columns:repeat(3,"],
        div[style*="grid-template-columns: repeat(3,"] { grid-template-columns: 1fr !important; }
        div[style*="grid-template-columns:repeat(4,"],
        div[style*="grid-template-columns: repeat(4,"] { grid-template-columns: 1fr 1fr !important; }
        div[style*="grid-template-columns: 2fr 1fr"],
        div[style*="grid-template-columns: 1fr 320px"],
        div[style*="grid-template-columns: 350px 1fr"] { grid-template-columns: 1fr !important; }
    }
    @media (max-width: 480px) {
        .main { padding: 10px; }
        .card { padding: 12px; border-radius: 4px; }
        h1 { font-size: 15px; }
        .btn-primary { width: 100%; justify-content: center; }
        div[style*="grid-template-columns:repeat(4,"],
        div[style*="grid-template-columns: repeat(4,"] { grid-template-columns: 1fr 1fr !important; }
    }

    /* Modal de comprobantes */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.75);
      z-index: 9999;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .modal-overlay.show { display: flex; }
    .modal-box {
      background: white;
      border-radius: 4px;
      max-width: 90vw;
      max-height: 90vh;
      overflow: auto;
      position: relative;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    .modal-box img {
      display: block;
      max-width: 100%;
      max-height: 80vh;
      border-radius: 4px;
    }
    .modal-close {
      position: absolute;
      top: 10px;
      right: 14px;
      background: rgba(0,0,0,0.5);
      color: white;
      border: none;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      font-size: 20px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10;
      transition: 0.2s;
    }
    .modal-close:hover { background: rgba(0,0,0,0.8); }
    .modal-pdf-fallback {
      padding: 40px;
      text-align: center;
    }
    .modal-pdf-fallback a {
      display: inline-block;
      background: #0369A1;
      color: white;
      padding: 12px 24px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 600;
    }

    /* Notificaciones */
    .notif-wrap { position:relative; display:inline-flex; align-items:center; }
    .notif-bell {
      background:none; border:none; font-size:20px; cursor:pointer; padding:6px 10px;
      border-radius:8px; transition:.2s; position:relative; color:#6B7280; line-height:1;
    }
    .notif-bell:hover { background:#F3F4F6; color:#111827; }
    .notif-badge {
      position:absolute; top:0; right:2px; background:#DC2626; color:white;
      font-size:10px; font-weight:700; min-width:18px; height:18px;
      border-radius:9px; display:flex; align-items:center; justify-content:center;
      border:2px solid white;
    }
    .notif-dropdown {
      display:none; position:absolute; top:100%; right:0; margin-top:6px;
      width:340px; max-height:400px; overflow-y:auto; background:white;
      border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.15);
      z-index:9999; border:1px solid #E5E7EB;
    }
    .notif-dropdown.show { display:block; }
    .notif-dropdown-header {
      padding:12px 16px; border-bottom:1px solid #E5E7EB;
      font-size:13px; font-weight:700; color:#111827;
    }
    .notif-item {
      display:flex; align-items:center; gap:12px; padding:12px 16px;
      text-decoration:none; color:#374151; border-bottom:1px solid #F9FAFB;
      transition:.15s;
    }
    .notif-item:hover { background:#F9FAFB; }
    .notif-item:last-child { border-bottom:none; }
    .notif-icon { font-size:20px; flex-shrink:0; }
    .notif-text { flex:1; font-size:13px; line-height:1.3; }
    .notif-text small { display:block; font-size:11px; color:#9CA3AF; margin-top:2px; }
    .notif-empty { padding:30px; text-align:center; color:#9CA3AF; font-size:13px; }

    /* ===== SQUARE/BOXY DESIGN SYSTEM ===== */
    .card, .premium-form, .compact-form, .f-section, .modal-box,
    .btn-primary, .btn-submit, .btn-cancel, .btn-edit, .btn,
    input[type="text"], input[type="email"], input[type="password"],
    input[type="number"], input[type="date"], input[type="month"],
    select, textarea, .badge, .notif-dropdown, .menu-toggle {
      border-radius: 3px !important;
    }
    .card { box-shadow: none !important; border: 1px solid var(--border); }
    .form-section, .role-section { border-radius: 3px !important; }
    table { border-collapse: collapse; }
    th, td { border-radius: 0 !important; }

    <?php echo $extra_css ?? ''; ?>
  </style>
</head>
<body>

  <?php
  // ─── Notificaciones según el rol ───
  $notificaciones = [];
  $rol_notif = $user['rol_id'] ?? 0;
  try {
    if ($rol_notif == 1) { // Admin
      $st = $pdo->query("SELECT COUNT(*) FROM lotes_barrio WHERE estado='Enviado'");
      $n = (int)$st->fetchColumn();
      if ($n > 0) $notificaciones[] = ['icon'=>'⏳','text'=>"$n lote(s) de barrio pendiente(s) de aprobación",'link'=>'router.php?page=gestor_dashboard'];
      $st = $pdo->query("SELECT COUNT(*) FROM solicitudes_vivienda WHERE estado='Pendiente'");
      $n = (int)$st->fetchColumn();
      if ($n > 0) $notificaciones[] = ['icon'=>'📩','text'=>"$n solicitud(es) pendiente(s) de revisión",'link'=>'router.php?page=solicitudes'];
    } elseif ($rol_notif == 2) { // Gestor
      $st = $pdo->query("SELECT COUNT(*) FROM lotes_barrio WHERE estado='Enviado'");
      $n = (int)$st->fetchColumn();
      if ($n > 0) $notificaciones[] = ['icon'=>'⏳','text'=>"$n lote(s) de barrio pendiente(s) de aprobación",'link'=>'router.php?page=dashboard'];
    } elseif ($rol_notif == 5) { // Barrio
      $barrioQ = $pdo->prepare("SELECT b.id FROM detalles_encargado_barrio d JOIN barrios b ON d.barrio_id=b.id WHERE d.usuario_id=?");
      $barrioQ->execute([$user['id']]);
      $bid = (int)$barrioQ->fetchColumn();
      if ($bid) {
        $st = $pdo->prepare("SELECT COUNT(*) FROM lotes_calle lc JOIN calles c ON lc.calle_id=c.id WHERE c.barrio_id=? AND lc.estado='Enviado'");
        $st->execute([$bid]);
        $n = (int)$st->fetchColumn();
        if ($n > 0) $notificaciones[] = ['icon'=>'⏳','text'=>"$n lote(s) de calle pendiente(s) de aprobación",'link'=>'router.php?page=reportar_pago'];
        $st = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_vivienda s JOIN calles c ON s.calle_id=c.id WHERE c.barrio_id=? AND s.estado='Pendiente'");
        $st->execute([$bid]);
        $n = (int)$st->fetchColumn();
        if ($n > 0) $notificaciones[] = ['icon'=>'📩','text'=>"$n solicitud(es) pendiente(s)",'link'=>'router.php?page=solicitudes'];
      }
    } elseif ($rol_notif == 6) { // Calle
      $calleQ = $pdo->prepare("SELECT calle_id FROM detalles_encargado_calle WHERE usuario_id=?");
      $calleQ->execute([$user['id']]);
      $cid = (int)$calleQ->fetchColumn();
      if ($cid) {
        $st = $pdo->prepare("SELECT estado FROM lotes_calle WHERE calle_id=? AND mes=? AND anio=? ORDER BY id DESC LIMIT 1");
        $st->execute([$cid, date('n'), date('Y')]);
        $est = $st->fetchColumn();
        if ($est === 'Rechazado') $notificaciones[] = ['icon'=>'❌','text'=>'Tu lote fue rechazado. Corrige y reenvía.','link'=>'router.php?page=reportar_pago'];
        elseif (!$est || $est === 'Abierto') {
          $st = $pdo->prepare("SELECT COUNT(*) FROM viviendas WHERE calle_id=? AND estado_servicio='Activo'");
          $st->execute([$cid]); $tc = (int)$st->fetchColumn();
          $st = $pdo->prepare("SELECT COUNT(DISTINCT vivienda_id) FROM cobros WHERE vivienda_id IN (SELECT id FROM viviendas WHERE calle_id=?) AND mes=? AND anio=? AND estado='Pagado'");
          $st->execute([$cid, date('n'), date('Y')]); $pg = (int)$st->fetchColumn();
          $pen = $tc - $pg;
          if ($pen > 0) $notificaciones[] = ['icon'=>'⏳','text'=>"$pen casa(s) sin marcar pago este mes",'link'=>'router.php?page=viviendas'];
        }
      }
    }
  } catch (\Throwable $e) {}
  $notif_count = count($notificaciones);
  ?>

  <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <main class="main">
    <header class="header">
      <div style="display: flex; align-items: center; gap: 12px;">
        <button class="menu-toggle" id="menuToggle" onclick="toggleSidebar()" aria-label="Toggle menu">☰</button>
        <div>
          <h1><?php echo $header_title ?? 'Panel de Control'; ?></h1>
          <div style="color: #6B7280; margin-top: 5px;"><?php echo $header_subtitle ?? ''; ?></div>
        </div>
      </div>
      <div style="display:flex; align-items:center; gap:12px;">
        <div class="notif-wrap">
          <button class="notif-bell" onclick="toggleNotif(event)" title="Notificaciones">
            🔔<?php if($notif_count > 0): ?><span class="notif-badge"><?= $notif_count ?></span><?php endif; ?>
          </button>
          <div class="notif-dropdown" id="notifDropdown">
            <div class="notif-dropdown-header">Notificaciones</div>
            <?php if(empty($notificaciones)): ?>
              <div class="notif-empty">No hay notificaciones</div>
            <?php else: ?>
              <?php foreach($notificaciones as $n): ?>
                <a href="<?= $n['link'] ?>" class="notif-item" onclick="cerrarNotif()">
                  <span class="notif-icon"><?= $n['icon'] ?></span>
                  <span class="notif-text"><?= $n['text'] ?></span>
                </a>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
        <div class="user-info">
          <?php echo $user_greeting ?? 'Hola'; ?>, <strong><?php echo htmlspecialchars($user['nombre']); ?></strong>
        </div>
      </div>
    </header>

    <?php echo $content; ?>

    <!-- Modal de comprobante -->
    <div class="modal-overlay" id="comprobanteModal" onclick="closeComprobanteModal(event)">
      <div class="modal-box" id="comprobanteModalBox">
        <button class="modal-close" onclick="closeComprobanteModal()">&times;</button>
        <div id="comprobanteModalContent"></div>
      </div>
    </div>
  </main>

  <script>
  function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggle = document.getElementById('menuToggle');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
    overlay.style.display = overlay.classList.contains('show') ? 'block' : 'none';
    toggle.classList.toggle('active');
    document.body.style.overflow = overlay.classList.contains('show') ? 'hidden' : '';
  }
  // Close sidebar on resize if going to desktop
  window.addEventListener('resize', function() {
    if (window.innerWidth > 1024) {
      const sidebar = document.querySelector('.sidebar');
      const overlay = document.getElementById('sidebarOverlay');
      const toggle = document.getElementById('menuToggle');
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
      overlay.style.display = 'none';
      toggle.classList.remove('active');
      document.body.style.overflow = '';
    }
  });
  function openComprobanteModal(el) {
    const url = el.dataset.url;
    if (!url) return true;
    const modal = document.getElementById('comprobanteModal');
    const content = document.getElementById('comprobanteModalContent');
    const isImage = url.match(/\.(jpe?g|png|gif|webp|bmp)(\?.*)?$/i);
    if (isImage) {
      content.innerHTML = '<img src="' + encodeURI(url) + '" alt="Comprobante" onerror="this.parentElement.innerHTML=\'<div class=modal-pdf-fallback><p style=color:#6B7280;margin-bottom:16px;>No se pudo mostrar la imagen.</p><a href=' + encodeURI(url) + ' target=_blank>Abrir enlace directo</a></div>\'">';
    } else {
      content.innerHTML = '<div class="modal-pdf-fallback"><p style="color:#6B7280;margin-bottom:16px;">El comprobante es un archivo PDF. Haz clic para abrirlo:</p><a href="' + encodeURI(url) + '" target="_blank">Ver PDF</a></div>';
    }
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    return false;
  }
  function closeComprobanteModal(e) {
    if (e && e.target !== e.currentTarget) return;
    const modal = document.getElementById('comprobanteModal');
    modal.classList.remove('show');
    document.body.style.overflow = '';
  }
  function toggleNotif(e) {
    e.stopPropagation();
    const dd = document.getElementById('notifDropdown');
    dd.classList.toggle('show');
  }
  function cerrarNotif() {
    document.getElementById('notifDropdown').classList.remove('show');
  }
  document.addEventListener('click', function(e) {
    const dd = document.getElementById('notifDropdown');
    if (dd && dd.classList.contains('show') && !e.target.closest('.notif-wrap')) {
      dd.classList.remove('show');
    }
  });
  </script>

  <script>
    // Bridge Multi-Sesión (Aislamiento por Pestaña)
    (function() {
        // 1. Obtener o generar SID único para esta pestaña
        if (!sessionStorage.getItem('eco_sid')) {
            sessionStorage.setItem('eco_sid', 'ts' + Date.now() + Math.floor(Math.random() * 1000));
        }
        const sid = sessionStorage.getItem('eco_sid');

        // 2. Función para inyectar SID en URLs internas
        function injectSid(url) {
            if (!url || url.startsWith('javascript:') || url.startsWith('#')) return url;
            try {
                const u = new URL(url, window.location.href);
                if (u.origin === window.location.origin) {
                    u.searchParams.set('sid', sid);
                    return u.pathname + u.search + u.hash;
                }
            } catch(e) {}
            return url;
        }

        // 3. Interceptar clics en enlaces
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href) {
                link.href = injectSid(link.href);
            }
        }, true);

        // 4. Inyectar en formularios al enviar
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const action = form.getAttribute('action') || window.location.href;
            if (new URL(action, window.location.href).origin === window.location.origin) {
                if (!form.querySelector('input[name="sid"]')) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'sid';
                    input.value = sid;
                    form.appendChild(input);
                }
            }
        }, true);

        // 5. Forzar SID en la URL si falta (Asegura que el servidor conozca la identidad)
        const urlParams = new URLSearchParams(window.location.search);
        if (!urlParams.has('sid')) {
            urlParams.set('sid', sid);
            // No hacer replaceState, hacer una recarga real si es la primera vez que entra sin SID
            // Esto evita que vea el dashboard de otra cuenta por error
            window.location.search = urlParams.toString();
        }

        // 6. Polleo de enlaces dinámicos (opcional por si se añaden enlaces vía JS)
        setInterval(() => {
            document.querySelectorAll('a').forEach(link => {
                if (link.href && !link.href.includes('sid=') && !link.href.startsWith('javascript:') && !link.href.startsWith('#')) {
                    const u = new URL(link.href, window.location.href);
                    if (u.origin === window.location.origin) {
                        u.searchParams.set('sid', sid);
                        link.href = u.pathname + u.search + u.hash;
                    }
                }
            });
        }, 1000);
    })();
  </script>
</body>
</html>
