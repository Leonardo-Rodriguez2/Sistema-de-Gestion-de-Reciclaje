<?php
// components/sidebar.php
// Se asume que $user y $page están definidos en el scope que incluye este archivo.
$rol_id = $user['rol_id'] ?? 0;
$page = $page ?? 'dashboard';
?>
<aside class="sidebar">
    <h2>EcoCusco</h2>
    <ul class="nav-links">
        <?php if ($rol_id == 1): // ADMIN ?>
            <li><a href="router.php?page=dashboard" class="<?= ($page == 'dashboard') ? 'active' : '' ?>">Dashboard General</a></li>
            <li><a href="router.php?page=usuarios" class="<?= ($page == 'usuarios') ? 'active' : '' ?>">Gestión de Usuarios</a></li>
            <!-- <li><a href="router.php?page=zonas" class="<?= ($page == 'zonas') ? 'active' : '' ?>">Zonas y Barrios</a></li>
            <li><a href="router.php?page=reportes" class="<?= ($page == 'reportes') ? 'active' : '' ?>">Reportes del Sistema</a></li> -->
        
        <?php elseif ($rol_id == 2): // GESTOR ?>
            <li><a href="router.php?page=dashboard" class="<?= ($page == 'dashboard') ? 'active' : '' ?>">Resumen de Cobros</a></li>
            <li><a href="#registrar_vecino">Registrar Vivienda</a></li>
            <li><a href="router.php?page=historial" class="<?= ($page == 'historial') ? 'active' : '' ?>">Historial de Pagos</a></li>
            <li><a href="router.php?page=recibos" class="<?= ($page == 'recibos') ? 'active' : '' ?>">Generar Recibos</a></li>
            
        <?php elseif ($rol_id == 3): // RECOLECTOR ?>
            <li><a href="router.php?page=dashboard" class="<?= ($page == 'dashboard') ? 'active' : '' ?>">Rutas Pendientes</a></li>
            <li><a href="router.php?page=completados" class="<?= ($page == 'completados') ? 'active' : '' ?>">Reportes Completados</a></li>
            <li><a href="router.php?page=mapa" class="<?= ($page == 'mapa') ? 'active' : '' ?>">Mapa de Zonas</a></li>
            <li><a href="router.php?page=vehiculo" class="<?= ($page == 'vehiculo') ? 'active' : '' ?>">Mi Vehículo</a></li>
            
        <?php elseif ($rol_id == 4): // USUARIO/VECINO ?>
            <li><a href="router.php?page=dashboard" class="<?= ($page == 'dashboard') ? 'active' : '' ?>">Mis Cobros y Pagos</a></li>
            <li><a href="/reciclaje/views/public/reportes.php">Reportar Basura</a></li>
            <li><a href="router.php?page=propiedades" class="<?= ($page == 'propiedades') ? 'active' : '' ?>">Mis Propiedades</a></li>
            <li><a href="/reciclaje/index.php">Ir a Web Principal</a></li>
        <?php endif; ?>
    </ul>
    <a href="/reciclaje/views/public/login.php?logout=true" class="logout">Cerrar Sesión</a>
</aside>
