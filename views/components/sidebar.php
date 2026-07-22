<?php
$rol_id = $user['rol_id'] ?? 0;
$page = $page ?? 'dashboard';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <span class="brand">EPSIC</span>
        <button class="sidebar-close" onclick="toggleSidebar()">&#x2715;</button>
    </div>
    <div class="sidebar-search">
        <input type="text" id="sidebarSearch" placeholder="Buscar en menu..." oninput="filterSidebar(this.value)">
    </div>
    <nav class="nav-menu" id="navMenu">
        <ul class="nav-links" id="navLinks">
            <li><a href="router.php?page=dashboard" class="<?= $page=='dashboard'?'active':'' ?>">&#x1f4ca; Dashboard</a></li>

            <!-- ═══════ ADMIN ═══════ -->
            <?php if ($rol_id == 1): ?>
            <li class="sep">ADMIN</li>
            <li class="has-sub <?= in_array($page,['barrios','barrio_nuevo','barrio_editar'])?'open':'' ?>" data-sub="a-barrios">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f3e2; Barrios<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=barrios" class="<?= $page=='barrios'?'active':'' ?>">Listar Barrios</a></li>
                    <li><a href="router.php?page=barrio_nuevo" class="<?= $page=='barrio_nuevo'?'active':'' ?>">Nuevo Barrio</a></li>
                </ul>
            </li>
            <li class="has-sub <?= in_array($page,['calles','calle_nueva','calle_editar'])?'open':'' ?>" data-sub="a-calles">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f6e3;&#xfe0f; Calles<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=calles" class="<?= $page=='calles'?'active':'' ?>">Listar Calles</a></li>
                    <li><a href="router.php?page=calle_nueva" class="<?= $page=='calle_nueva'?'active':'' ?>">Nueva Calle</a></li>
                </ul>
            </li>
            <li class="has-sub <?= (isset($_GET['rol_id']) && $_GET['rol_id']==2)?'open':'' ?>" data-sub="a-gestores">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f4b3; Gestores Pago<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=usuarios&rol_id=2" class="<?= ($page=='usuarios'&&($_GET['rol_id']??0)==2)?'active':'' ?>">Listar</a></li>
                    <li><a href="router.php?page=usuario_nuevo&rol_id=2">Nuevo</a></li>
                </ul>
            </li>
            <li class="has-sub <?= (isset($_GET['rol_id']) && $_GET['rol_id']==3)?'open':'' ?>" data-sub="a-obreros">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f477; Personal Obrero<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=usuarios&rol_id=3" class="<?= ($page=='usuarios'&&($_GET['rol_id']??0)==3)?'active':'' ?>">Listar</a></li>
                    <li><a href="router.php?page=usuario_nuevo&rol_id=3">Nuevo</a></li>
                </ul>
            </li>
            <li class="has-sub <?= (isset($_GET['rol_id']) && $_GET['rol_id']==5)?'open':'' ?>" data-sub="a-encbar">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f3d8;&#xfe0f; Enc. Barrio<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=usuarios&rol_id=5" class="<?= ($page=='usuarios'&&($_GET['rol_id']??0)==5)?'active':'' ?>">Listar</a></li>
                    <li><a href="router.php?page=usuario_nuevo&rol_id=5">Nuevo</a></li>
                </ul>
            </li>
            <li class="has-sub <?= (isset($_GET['rol_id']) && $_GET['rol_id']==6)?'open':'' ?>" data-sub="a-enccal">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f3d8;&#xfe0f; Enc. Calle<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=usuarios&rol_id=6" class="<?= ($page=='usuarios'&&($_GET['rol_id']??0)==6)?'active':'' ?>">Listar</a></li>
                    <li><a href="router.php?page=usuario_nuevo&rol_id=6">Nuevo</a></li>
                </ul>
            </li>
            <li class="has-sub <?= in_array($page,['viviendas','registrar_vivienda','solicitudes','quitar_servicio','reporte_bajas'])?'open':'' ?>" data-sub="a-viviendas">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f3e0; Viviendas<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=viviendas" class="<?= $page=='viviendas'?'active':'' ?>">Lista</a></li>
                    <li><a href="router.php?page=registrar_vivienda">Registrar</a></li>
                    <li><a href="router.php?page=solicitudes" class="<?= $page=='solicitudes'?'active':'' ?>">Solicitudes</a></li>
                    <li><a href="router.php?page=quitar_servicio">Quitar Servicio</a></li>
                    <li><a href="router.php?page=reporte_bajas">Reporte Bajas</a></li>
                </ul>
            </li>
            <li class="has-sub <?= in_array($page,['gestor_dashboard','gestor_viviendas','gestor_historial','gestor_recibos','gestor_usuarios','gestor_usuario_nuevo_personal'])?'open':'' ?>" data-sub="a-gestor">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f4b0; Pagos / Gestor<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=gestor_dashboard" class="<?= $page=='gestor_dashboard'?'active':'' ?>">Aprobar Lotes</a></li>
                    <li><a href="router.php?page=gestor_viviendas">Viviendas</a></li>
                    <li><a href="router.php?page=gestor_usuarios">Personal</a></li>
                    <li><a href="router.php?page=gestor_historial">Historial</a></li>
                    <li><a href="router.php?page=gestor_recibos">Recibos</a></li>
                </ul>
            </li>
            <li class="has-sub <?= in_array($page,['monitor_pagos','reportes','certificado_calle'])?'open':'' ?>" data-sub="a-reportes">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f4ca; Reportes<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=monitor_pagos" class="<?= $page=='monitor_pagos'?'active':'' ?>">Monitor Pagos</a></li>
                    <li><a href="router.php?page=reportes">Reportes</a></li>
                    <li><a href="router.php?page=certificado_calle">Certificado Calle</a></li>
                </ul>
            </li>

            <!-- ═══════ GESTOR ═══════ -->
            <?php elseif ($rol_id == 2): ?>
            <li class="sep">GESTOR</li>
            <li><a href="router.php?page=revisar_lotes" class="<?= $page=='revisar_lotes'?'active':'' ?>">📋 Revisar Lotes</a></li>
            <li class="has-sub <?= in_array($page,['barrios'])?'open':'' ?>" data-sub="g-barrios">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f5fa;&#xfe0f; Barrios / Calles<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=barrios" class="<?= $page=='barrios'?'active':'' ?>">Ver Barrios</a></li>
                </ul>
            </li>
            <li class="has-sub <?= in_array($page,['viviendas','registrar_vivienda'])?'open':'' ?>" data-sub="g-viviendas">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f3e0; Viviendas<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=viviendas" class="<?= $page=='viviendas'?'active':'' ?>">Estado Viviendas</a></li>
                    <li><a href="router.php?page=registrar_vivienda">Registrar</a></li>
                </ul>
            </li>
            <li class="has-sub <?= in_array($page,['usuarios','usuario_nuevo_personal','usuario_editar','usuario_ver'])?'open':'' ?>" data-sub="g-personal">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f465; Personal<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=usuarios" class="<?= $page=='usuarios'?'active':'' ?>">Listar</a></li>
                    <li><a href="router.php?page=usuario_nuevo_personal">Nuevo Personal</a></li>
                </ul>
            </li>
            <li><a href="router.php?page=historial" class="<?= $page=='historial'?'active':'' ?>">&#x1f4cb; Historial</a></li>
            <li><a href="router.php?page=recibos" class="<?= $page=='recibos'?'active':'' ?>">&#x1f4c4; Recibos</a></li>
            <li><a href="router.php?page=reportes" class="<?= $page=='reportes'?'active':'' ?>">&#x1f4ca; Reportes</a></li>
            <li><a href="router.php?page=certificado_calle" class="<?= $page=='certificado_calle'?'active':'' ?>">&#x1f4dc; Certificado Calle</a></li>

            <!-- ═══════ BARRIO ═══════ -->
            <?php elseif ($rol_id == 5): ?>
            <li class="sep">BARRIO</li>
            <li class="has-sub <?= in_array($page,['calles','registrar_vivienda','solicitudes','ordenes_baja','solicitudes_renovacion','historial_solicitudes','configuracion','viviendas'])?'open':'' ?>" data-sub="b-barrio">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f6e3;&#xfe0f; Barrio<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=calles" class="<?= $page=='calles'?'active':'' ?>">Lista de Calles</a></li>
                    <li><a href="router.php?page=registrar_vivienda" class="<?= $page=='registrar_vivienda'?'active':'' ?>">Registrar Casa</a></li>
                    <li><a href="router.php?page=solicitudes" class="<?= $page=='solicitudes'?'active':'' ?>">Solicitudes Registro</a></li>
                    <li><a href="router.php?page=ordenes_baja" class="<?= $page=='ordenes_baja'?'active':'' ?>">Ordenes de Baja</a></li>
                    <li><a href="router.php?page=solicitudes_renovacion" class="<?= $page=='solicitudes_renovacion'?'active':'' ?>">Ordenes Renovacion</a></li>
                    <li><a href="router.php?page=historial_solicitudes" class="<?= $page=='historial_solicitudes'?'active':'' ?>">Historial Tramites</a></li>
                    <li><a href="router.php?page=configuracion" class="<?= $page=='configuracion'?'active':'' ?>">Configuracion Tasas</a></li>
                    <li><a href="router.php?page=viviendas" class="<?= $page=='viviendas'?'active':'' ?>">Ver Todas Viviendas</a></li>
                </ul>
            </li>
            <li class="has-sub <?= in_array($page,['reportar_pago','seguimiento_pagos','exonerados'])?'open':'' ?>" data-sub="b-pagos">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f4b0; Pagos<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=reportar_pago" class="<?= $page=='reportar_pago'?'active':'' ?>">Marcar Pagos</a></li>
                    <li><a href="router.php?page=seguimiento_pagos" class="<?= $page=='seguimiento_pagos'?'active':'' ?>">Seguimiento</a></li>
                    <li><a href="router.php?page=exonerados" class="<?= $page=='exonerados'?'active':'' ?>">Exonerados</a></li>
                </ul>
            </li>
            <li class="has-sub <?= in_array($page,['quitar_servicio','reporte_bajas'])?'open':'' ?>" data-sub="b-servicio">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f6ab; Servicio<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=quitar_servicio" class="<?= $page=='quitar_servicio'?'active':'' ?>">Quitar Servicio</a></li>
                    <li><a href="router.php?page=reporte_bajas" class="<?= $page=='reporte_bajas'?'active':'' ?>">Reporte de Bajas</a></li>
                </ul>
            </li>
            <li><a href="router.php?page=certificados" class="<?= $page=='certificados'?'active':'' ?>">&#x1f4dc; Certificados</a></li>
            <li><a href="router.php?page=reportes" class="<?= $page=='reportes'?'active':'' ?>">&#x1f4ca; Reportes</a></li>

            <!-- ═══════ CALLE ═══════ -->
            <?php elseif ($rol_id == 6): ?>
            <li class="sep">CALLE</li>
            <li class="has-sub <?= in_array($page,['viviendas','registrar_vivienda'])?'open':'' ?>" data-sub="c-viviendas">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f3e0; Mis Viviendas<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=viviendas" class="<?= $page=='viviendas'?'active':'' ?>">Ver Viviendas</a></li>
                    <li><a href="router.php?page=registrar_vivienda" class="<?= $page=='registrar_vivienda'?'active':'' ?>">Solicitar Registro</a></li>
                </ul>
            </li>
            <li class="has-sub <?= in_array($page,['reportar_pago','solicitar_exoneracion'])?'open':'' ?>" data-sub="c-pagos">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f4b0; Pagos<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=reportar_pago" class="<?= $page=='reportar_pago'?'active':'' ?>">Marcar Pagos</a></li>
                    <li><a href="router.php?page=solicitar_exoneracion" class="<?= $page=='solicitar_exoneracion'?'active':'' ?>">Solicitar Exoneracion</a></li>
                </ul>
            </li>
            <li class="has-sub <?= in_array($page,['solicitudes','historial_solicitudes'])?'open':'' ?>" data-sub="c-solicitudes">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f4e9; Solicitudes<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=solicitudes" class="<?= $page=='solicitudes'?'active':'' ?>">Solicitudes Pendientes</a></li>
                    <li><a href="router.php?page=historial_solicitudes" class="<?= $page=='historial_solicitudes'?'active':'' ?>">Historial Tramites</a></li>
                </ul>
            </li>
            <li class="has-sub <?= in_array($page,['quitar_servicio','reporte_bajas'])?'open':'' ?>" data-sub="c-servicio">
                <a href="javascript:void(0)" onclick="tSub(this)">&#x1f6ab; Servicio<span class="arr">&#x25bc;</span></a>
                <ul class="sub">
                    <li><a href="router.php?page=quitar_servicio" class="<?= $page=='quitar_servicio'?'active':'' ?>">Quitar Servicio</a></li>
                    <li><a href="router.php?page=reporte_bajas" class="<?= $page=='reporte_bajas'?'active':'' ?>">Reporte de Bajas</a></li>
                </ul>
            </li>
            <li><a href="router.php?page=historial_vivienda" class="<?= $page=='historial_vivienda'?'active':'' ?>">&#x1f4cb; Historial Vivienda</a></li>
            <li><a href="router.php?page=facturas" class="<?= $page=='facturas'?'active':'' ?>">&#x1f4c4; Facturas</a></li>

            <?php endif; ?>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="/reciclaje/views/public/login.php?logout=true" class="logout">Salir</a>
    </div>
</aside>

<style>
.sidebar{width:220px;background:#1e1e2a;height:100vh;display:flex;flex-direction:column;position:fixed;left:0;top:0;font-size:12px;z-index:999;color:#888}
.sidebar-header{padding:12px 16px;border-bottom:1px solid #2a2a3a;display:flex;align-items:center;justify-content:space-between}
.brand{color:#eee;font-size:17px;font-weight:700}
.sidebar-close{display:none;background:#333;color:#aaa;border:none;width:28px;height:28px;border-radius:4px;font-size:13px;cursor:pointer;align-items:center;justify-content:center}
@media(max-width:1024px){.sidebar-close{display:flex}}
.sidebar-search{padding:8px 12px;border-bottom:1px solid #2a2a3a}
.sidebar-search input{width:100%;padding:6px 8px;border:1px solid #333;border-radius:4px;background:#2a2a3a;color:#ccc;font-size:12px;outline:none;box-sizing:border-box}
.nav-menu{flex:1;overflow-y:auto;padding:4px 0}
.nav-links{list-style:none;padding:0;margin:0}
.sep{font-size:9px;color:#555;padding:10px 16px 2px;text-transform:uppercase;letter-spacing:1px;font-weight:600}
.step-num{background:#3B82F6;color:#fff;font-size:9px;font-weight:700;padding:2px 6px;border-radius:3px;margin-right:6px;min-width:16px;text-align:center;display:inline-block}
.nav-links a{display:flex;align-items:center;padding:7px 16px;color:#888;text-decoration:none;font-size:12px;transition:.15s;gap:6px;border-left:3px solid transparent}
.nav-links a:hover{color:#eee;background:#2a2a3a}
.nav-links a.active{color:#fff;background:#2a2a3a;border-left-color:#10B981}
.sub{list-style:none;padding:0;margin:0;display:none}
.has-sub.open .sub{display:block}
.has-sub.open > a .arr{transform:rotate(180deg)}
.arr{margin-left:auto;font-size:7px;transition:.2s;color:#555}
.sub a{padding-left:34px!important;font-size:11px!important}
.sidebar-footer{padding:8px 16px;border-top:1px solid #2a2a2a}
.logout{display:flex;padding:7px 12px;color:#888;text-decoration:none;border-radius:4px;font-size:12px}
.logout:hover{background:#2a2a2a;color:#eee}
</style>

<script>
(function(){
    var KEY='sidebar_open';
    function save(){
        var a=[];
        document.querySelectorAll('#navLinks .has-sub.open').forEach(function(e){
            var s=e.getAttribute('data-sub');
            if(s) a.push(s);
        });
        try{localStorage.setItem(KEY,JSON.stringify(a));}catch(e){}
    }
    function restore(){
        try{
            var a=JSON.parse(localStorage.getItem(KEY)||'[]');
            a.forEach(function(id){
                var el=document.querySelector('#navLinks .has-sub[data-sub="'+id+'"]');
                if(el) el.classList.add('open');
            });
        }catch(e){}
    }
    restore();
    window.tSub=function(el){
        el.parentElement.classList.toggle('open');
        save();
    };
    window.filterSidebar=function(val){
        document.querySelectorAll('#navLinks > li').forEach(function(li){
            if(li.classList.contains('sep')) return;
            li.style.display=li.textContent.toLowerCase().indexOf(val.toLowerCase())>-1?'':'none';
        });
    };
})();
</script>
