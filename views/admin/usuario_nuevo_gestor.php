<?php
// views/admin/usuario_nuevo_gestor.php
$user = check_dashboard_access([1]);

$title = "Registrar Gestor de Pagos - EcoCusco";
$header_title = "Nuevo Gestor de Pagos";
$header_subtitle = "Asigna personal para la verificación y fiscalización de recaudaciones.";

ob_start();
?>
<div class="form-container">
    <form action="router.php" method="POST" class="premium-form">
        <input type="hidden" name="action" value="add_user">
        <input type="hidden" name="rol_id" value="2">
        
        <div class="form-section">
            <h3><span class="icon">👤</span> Datos del Usuario</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre(s)</label>
                    <input type="text" name="nombre" required placeholder="Ej. Juan">
                </div>
                <div class="form-group">
                    <label>Apellido(s)</label>
                    <input type="text" name="apellido" required placeholder="Ej. Pérez">
                </div>
                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="email" required placeholder="juan.perez@ejemplo.com">
                </div>
                <div class="form-group">
                    <label>Contraseña (Mín. 6)</label>
                    <input type="password" name="password" placeholder="Por defecto: 123456">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3><span class="icon">💼</span> Detalles Profesionales</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>DNI</label>
                    <input type="text" name="dni_gestor" placeholder="Ej. 70654321">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono_gestor" placeholder="Ej. 987654321">
                </div>
                <div class="form-group">
                    <label>Área Responsable</label>
                    <input type="text" name="area" placeholder="Ej. Cobranza Sector Norte">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="router.php?page=usuarios&rol_id=2" class="btn-cancel">Volver a la lista</a>
            <button type="submit" class="btn-submit">Registrar Gestor</button>
        </div>
    </form>
</div>

<style>
.form-container { margin: 0 auto; padding-bottom: 30px; }
.premium-form { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.form-section { margin-bottom: 25px; border-bottom: 1px solid #F3F4F6; padding-bottom: 15px; }
.form-section h3 { margin: 0 0 15px 0; font-size: 16px; color: #111827; display: flex; align-items: center; gap: 8px; }
.form-section h3 .icon { width: 24px; height: 24px; background: #ECFDF5; color: #10B981; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 14px; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 12px; font-weight: 500; color: #4B5563; }
.form-group input, .form-group select { padding: 10px 12px; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 13px; transition: 0.3s; }
.form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #F3F4F6; }
.btn-submit { background: #10B981; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 13px; }
.btn-cancel { background: #F3F4F6; color: #4B5563; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: 0.3s; font-size: 13px; }
</style>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
