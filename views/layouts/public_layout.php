<?php
// views/layouts/public_layout.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'EcoCusco'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="/reciclaje/assets/img/icono.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/reciclaje/assets/css/style.css">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }

        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-wrap table { min-width: 400px; }

        img { max-width: 100%; height: auto; }

        @media (max-width: 768px) {
            .container { padding-left: 15px; padding-right: 15px; }
            h1 { font-size: 22px !important; }
            h2 { font-size: 18px !important; }
        }

        <?php echo $extra_css ?? ''; ?>
    </style>
    <?php echo $extra_head ?? ''; ?>
</head>
<body>

    <?php include __DIR__ . '/../components/navbar.php'; ?>

    <?php echo $content; ?>

    <?php include __DIR__ . '/../components/footer_public.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Bridge Multi-Sesión (Público)
    (function() {
        if (!sessionStorage.getItem('eco_sid')) {
            sessionStorage.setItem('eco_sid', 'ts' + Date.now() + Math.floor(Math.random() * 1000));
        }
        const sid = sessionStorage.getItem('eco_sid');

        // Inyectar en todos los formularios al enviar
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form.querySelector('input[name="sid"]')) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'sid';
                input.value = sid;
                form.appendChild(input);
            }
        });

        // Forzar SID en la URL si falta (para que el controlador lo reciba vía GET si es necesario)
        const urlParams = new URLSearchParams(window.location.search);
        if (!urlParams.has('sid')) {
            urlParams.set('sid', sid);
            window.location.search = urlParams.toString();
        }
    })();
    </script>
    <?php echo $extra_js ?? ''; ?>
</body>
</html>
