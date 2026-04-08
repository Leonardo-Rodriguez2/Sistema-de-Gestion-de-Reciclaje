<?php
// components/sidebar.php
$rol_id = $user['rol_id'] ?? 0;
$page = $page ?? 'dashboard';
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h2 class="brand">EPSIC</h2>
    </div>

    <nav class="nav-menu">
        <ul class="nav-links">
            <!-- MÓDULO DASHBOARD -->
            <li>
                <a href="router.php?page=dashboard" class="<?= ($page == 'dashboard') ? 'active' : '' ?>">
                    <span class="icon">📊</span> Dashboard
                </a>
            </li>

            <!-- ADMIN MODULES -->
            <?php if ($rol_id == 1): ?>
                <!-- BARRIOS -->
                <li class="has-submenu <?= in_array($page, ['barrios', 'barrio_nuevo']) ? 'open' : '' ?>">
                    <a href="javascript:void(0)" class="submenu-toggle" onclick="toggleSubmenu(this)">
                        <span class="icon">🏢</span> Barrios <span class="arrow">▼</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="router.php?page=barrios" class="<?= ($page == 'barrios') ? 'active' : '' ?>">Listar Barrios</a></li>
                        <li><a href="router.php?page=barrio_nuevo" class="<?= ($page == 'barrio_nuevo') ? 'active' : '' ?>">Nuevo Barrio</a></li>
                    </ul>
                </li>

                <!-- CALLES -->
                <li class="has-submenu <?= in_array($page, ['calles']) ? 'open' : '' ?>">
                    <a href="javascript:void(0)" class="submenu-toggle" onclick="toggleSubmenu(this)">
                        <span class="icon">🛣️</span> Calles <span class="arrow">▼</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="router.php?page=calles" class="<?= ($page == 'calles') ? 'active' : '' ?>">Gestionar Calles</a></li>
                    </ul>
                </li>

                <!-- GESTORES DE PAGOS (Role 2) -->
                <li class="has-submenu <?= (isset($_GET['rol_id']) && $_GET['rol_id'] == 2) || $page == 'usuario_nuevo_gestor' ? 'open' : '' ?>">
                    <a href="javascript:void(0)" class="submenu-toggle" onclick="toggleSubmenu(this)">
                        <span class="icon">💳</span> Gestores de Pago <span class="arrow">▼</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="router.php?page=usuarios&rol_id=2" class="<?= ($page == 'usuarios' && ($_GET['rol_id']??0) == 2) ? 'active' : '' ?>">Listar Gestores</a></li>
                        <li><a href="router.php?page=usuario_nuevo_gestor" class="<?= ($page == 'usuario_nuevo_gestor') ? 'active' : '' ?>">Nuevo Gestor</a></li>
                    </ul>
                </li>

                <!-- PERSONAL OBRERO (Role 3) -->
                <li class="has-submenu <?= (isset($_GET['rol_id']) && $_GET['rol_id'] == 3) || $page == 'usuario_nuevo_personal' ? 'open' : '' ?>">
                    <a href="javascript:void(0)" class="submenu-toggle" onclick="toggleSubmenu(this)">
                        <span class="icon">👷</span> Personal Obrero <span class="arrow">▼</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="router.php?page=usuarios&rol_id=3" class="<?= ($page == 'usuarios' && ($_GET['rol_id']??0) == 3) ? 'active' : '' ?>">Listar Personal</a></li>
                        <li><a href="router.php?page=usuario_nuevo_personal" class="<?= ($page == 'usuario_nuevo_personal') ? 'active' : '' ?>">Nuevo Personal</a></li>
                    </ul>
                </li>

                <!-- ENCARGADOS DE BARRIO (Role 5) -->
                <li class="has-submenu <?= (isset($_GET['rol_id']) && $_GET['rol_id'] == 5) || $page == 'usuario_nuevo_barrio' ? 'open' : '' ?>">
                    <a href="javascript:void(0)" class="submenu-toggle" onclick="toggleSubmenu(this)">
                        <span class="icon">🏘️</span> Encargados Barrio <span class="arrow">▼</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="router.php?page=usuarios&rol_id=5" class="<?= ($page == 'usuarios' && ($_GET['rol_id']??0) == 5) ? 'active' : '' ?>">Listar Encargados</a></li>
                        <li><a href="router.php?page=usuario_nuevo_barrio" class="<?= ($page == 'usuario_nuevo_barrio') ? 'active' : '' ?>">Nuevo Encargado</a></li>
                    </ul>
                </li>

                <!-- ENCARGADOS DE CALLE (Role 6) -->
                <li class="has-submenu <?= (isset($_GET['rol_id']) && $_GET['rol_id'] == 6) || $page == 'usuario_nuevo_calle' ? 'open' : '' ?>">
                    <a href="javascript:void(0)" class="submenu-toggle" onclick="toggleSubmenu(this)">
                        <span class="icon">🏘️</span> Encargados Calle <span class="arrow">▼</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="router.php?page=usuarios&rol_id=6" class="<?= ($page == 'usuarios' && ($_GET['rol_id']??0) == 6) ? 'active' : '' ?>">Listar Encargados</a></li>
                        <li><a href="router.php?page=usuario_nuevo_calle" class="<?= ($page == 'usuario_nuevo_calle') ? 'active' : '' ?>">Nuevo Encargado</a></li>
                    </ul>
                </li>

                <!-- VIVIENDAS -->
                <li class="has-submenu <?= in_array($page, ['viviendas', 'registrar_vivienda', 'solicitudes']) ? 'open' : '' ?>">
                    <a href="javascript:void(0)" class="submenu-toggle" onclick="toggleSubmenu(this)">
                        <span class="icon">🏠</span> Viviendas <span class="arrow">▼</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="router.php?page=viviendas" class="<?= ($page == 'viviendas') ? 'active' : '' ?>">Lista General</a></li>
                        <li><a href="router.php?page=registrar_vivienda" class="<?= ($page == 'registrar_vivienda') ? 'active' : '' ?>">Registrar Casa</a></li>
                        <li><a href="router.php?page=solicitudes" class="<?= ($page == 'solicitudes') ? 'active' : '' ?>">Solicitudes Pendientes</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- MÓDULO BARRIO (Encargado de Barrio) -->
            <?php if ($rol_id == 5): ?>
                <li class="has-submenu <?= in_array($page, ['calles', 'solicitudes', 'viviendas', 'registrar_vivienda']) ? 'open' : '' ?>">
                    <a href="javascript:void(0)" class="submenu-toggle" onclick="toggleSubmenu(this)">
                        <span class="icon">🏘️</span> Mi Barrio <span class="arrow">▼</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="router.php?page=calles" class="<?= ($page == 'calles') ? 'active' : '' ?>">Lista de Calles</a></li>
                        <li><a href="router.php?page=registrar_vivienda" class="<?= ($page == 'registrar_vivienda') ? 'active' : '' ?>">Registrar Casa</a></li>
                        <li><a href="router.php?page=solicitudes" class="<?= ($page == 'solicitudes') ? 'active' : '' ?>">Solicitudes Vivienda</a></li>
                        <li><a href="router.php?page=viviendas" class="<?= ($page == 'viviendas') ? 'active' : '' ?>">Ver Todas Viviendas</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- MÓDULO CALLE (Encargado de Calle) -->
            <?php if ($rol_id == 6): ?>
                <li class="has-submenu <?= in_array($page, ['viviendas', 'registrar_vivienda', 'reportar_pago']) ? 'open' : '' ?>">
                    <a href="javascript:void(0)" class="submenu-toggle" onclick="toggleSubmenu(this)">
                        <span class="icon">🏠</span> Mi Calle <span class="arrow">▼</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="router.php?page=viviendas" class="<?= ($page == 'viviendas') ? 'active' : '' ?>">Mis Viviendas</a></li>
                        <li><a href="router.php?page=registrar_vivienda" class="<?= ($page == 'registrar_vivienda') ? 'active' : '' ?>">Solicitar Registro</a></li>
                        <li><a href="router.php?page=reportar_pago" class="<?= ($page == 'reportar_pago') ? 'active' : '' ?>">Marcar Pagos</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- MÓDULO GESTOR (Gestor de Pagos) -->
            <?php if ($rol_id == 2): ?>
                <li class="has-submenu <?= in_array($page, ['viviendas', 'historial', 'recibos']) ? 'open' : '' ?>">
                    <a href="javascript:void(0)" class="submenu-toggle" onclick="toggleSubmenu(this)">
                        <span class="icon">💰</span> Pagos y Finanzas <span class="arrow">▼</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="router.php?page=viviendas" class="<?= ($page == 'viviendas') ? 'active' : '' ?>">Estado de Viviendas</a></li>
                        <li><a href="router.php?page=registrar_vivienda" class="<?= ($page == 'registrar_vivienda') ? 'active' : '' ?>">Registrar Casa</a></li>
                        <li><a href="router.php?page=usuario_nuevo_personal" class="<?= ($page == 'usuario_nuevo_personal') ? 'active' : '' ?>">Nuevo Personal</a></li>
                        <li><a href="router.php?page=historial" class="<?= ($page == 'historial') ? 'active' : '' ?>">Historial General</a></li>
                        <li><a href="router.php?page=recibos" class="<?= ($page == 'recibos') ? 'active' : '' ?>">Recibos / Reportes</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- MÓDULO RECOLECTOR (Recolector) -->
            <?php if ($rol_id == 3): ?>
                <li class="has-submenu <?= in_array($page, ['recolecciones', 'rutas']) ? 'open' : '' ?>">
                    <a href="javascript:void(0)" class="submenu-toggle" onclick="toggleSubmenu(this)">
                        <span class="icon">🚛</span> Logística <span class="arrow">▼</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="router.php?page=recolecciones" class="<?= ($page == 'recolecciones') ? 'active' : '' ?>">Mis Recolecciones</a></li>
                        <li><a href="router.php?page=rutas" class="<?= ($page == 'rutas') ? 'active' : '' ?>">Rutas Asignadas</a></li>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="/reciclaje/views/public/login.php?logout=true" class="logout-btn">
            <span class="icon">🚪</span> Salir del Sistema
        </a>
    </div>
</aside>

<style>
/* Estilos del Sidebar Accordion Premium */
.sidebar {
    width: 240px;
    background: #111827; /* Deep Navy Black */
    height: 100vh;
    color: #9CA3AF;
    display: flex;
    flex-direction: column;
    position: fixed;
    left: 0;
    top: 0;
    font-size: 13px;
}
.brand { padding: 20px; margin: 0; color: #F9FAFB; font-size: 20px; font-weight: 800; text-align: center; border-bottom: 1px solid #1F2937; letter-spacing: 1px; }
.nav-menu { flex-grow: 1; padding: 15px 10px; overflow-y: auto; }
.nav-links { list-style: none; padding: 0; margin: 0; }
.nav-links > li { margin-bottom: 4px; }

/* Links Principales */
.nav-links a {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    color: #9CA3AF;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s;
    font-weight: 500;
}
.nav-links a:hover { background: #1F2937; color: white; }
.nav-links a.active { background: #1F2937; color: #10B981; border-left: 3px solid #10B981; border-radius: 2px 6px 6px 2px; }

/* Submenu/Accordion */
.has-submenu .submenu {
    display: none;
    list-style: none;
    padding: 0 0 0 15px;
    background: transparent;
    margin-top: 2px;
}
.has-submenu.open .submenu { display: block; }
.submenu li a { padding: 8px 12px; font-size: 12px; color: #6B7280; }
.submenu li a:hover, .submenu li a.active { color: #10B981; background: transparent; }

.arrow { margin-left: auto; transition: 0.3s; font-size: 8px; color: #4B5563; }
.has-submenu.open .arrow { transform: rotate(180deg); color: #10B981; }

.sidebar-footer { padding: 15px; border-top: 1px solid #1F2937; }
.logout-btn { color: #9CA3AF; text-decoration: none; font-size: 12px; font-weight: 600; display: flex; align-items: center; padding: 10px; border-radius: 6px; transition: 0.3s; }
.logout-btn:hover { background: rgba(239, 68, 68, 0.1); color: #6B7280; }
</style>

<script>
function toggleSubmenu(el) {
    const parent = el.parentElement;
    parent.classList.toggle('open');
}
</script>
