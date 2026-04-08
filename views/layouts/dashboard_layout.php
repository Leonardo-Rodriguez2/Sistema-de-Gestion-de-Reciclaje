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
  <style>
    :root {
      --primary: #374151; /* Slate Gray */
      --secondary: #111827; /* Deep Gray */
      --accent: #10B981; /* Green only for highlights */
      --bg: #F9FAFB;
      --text: #374151;
      --card-bg: #FFFFFF;
      --border: #E5E7EB;
    }
    body { font-family: 'Poppins', sans-serif; background: var(--bg); margin: 0; display: flex; color: var(--text); font-size: 14px; }
    
    /* Layout */
    .main { flex-grow: 1; margin-left: 240px; padding: 25px 35px; min-height: 100vh; box-sizing: border-box; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }
    h1 { font-size: 22px; font-weight: 700; margin: 0; color: var(--secondary); }
    .user-info { font-size: 13px; color: #6B7280; font-weight: 500;}

    /* Comunes */
    .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
    .badge.admin { background: #F3F4F6; color: #374151; border: 1px solid #D1D5DB; }
    .badge.gestor { background: #F3F4F6; color: #374151; border: 1px solid #D1D5DB; }
    .badge.recolector { background: #F3F4F6; color: #374151; border: 1px solid #D1D5DB; }
    
    .btn-primary { background: var(--primary); color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; transition: 0.2s; }
    .btn-primary:hover { background: var(--secondary); }

    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th { text-align: left; padding: 12px; border-bottom: 1px solid var(--border); color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
    td { padding: 12px; border-bottom: 1px solid #F9FAFB; }

    @media (max-width: 1024px) {
        .sidebar { width: 0; overflow: hidden; }
        .main { margin-left: 0; }
    }
    <?php echo $extra_css ?? ''; ?>
  </style>
</head>
<body>

  <?php include __DIR__ . '/../components/sidebar.php'; ?>

  <main class="main">
    <header class="header">
      <div>
        <h1><?php echo $header_title ?? 'Panel de Control'; ?></h1>
        <div style="color: #6B7280; margin-top: 5px;"><?php echo $header_subtitle ?? ''; ?></div>
      </div>
      <div class="user-info">
        <?php echo $user_greeting ?? 'Hola'; ?>, <strong><?php echo htmlspecialchars($user['nombre']); ?></strong>
      </div>
    </header>

    <?php echo $content; ?>
  </main>

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
