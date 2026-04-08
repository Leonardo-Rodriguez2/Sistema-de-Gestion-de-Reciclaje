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
    <style>
        <?php echo $extra_css ?? ''; ?>
    </style>
    <?php echo $extra_head ?? ''; ?>
</head>
<body>

    <?php include __DIR__ . '/../components/navbar.php'; ?>

    <?php echo $content; ?>

    <?php include __DIR__ . '/../components/footer_public.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $extra_js ?? ''; ?>
</body>
</html>
