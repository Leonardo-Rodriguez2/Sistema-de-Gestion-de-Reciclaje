<?php
// views/admin/usuario_nuevo_personal.php
$user = check_dashboard_access([1, 2]);

$title = "Registrar Personal Obrero - EcoCusco";
$header_title = "Nuevo Personal";
$header_subtitle = "Registra personal operativo indicando su cargo específico para las labores de campo.";

ob_start();
?>
<div class="form-container">
    <form action="router.php" method="POST" class="premium-form">
        <input type="hidden" name="action" value="add_user">
        <input type="hidden" name="rol_id" value="3">
        
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
            <h3><span class="icon">👷</span> Detalles del Trabajador</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Cargo / Función</label>
                    <select name="cargo" required>
                        <option value="Recolector">Recolector</option>
                        <option value="Chofer">Chofer</option>
                        <option value="Operario de Planta">Operario de Planta</option>
                        <option value="Mecánico">Mecánico</option>
                        <option value="Supervisor de Campo">Supervisor de Campo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>DNI</label>
                    <input type="text" name="dni_personal" placeholder="Ej. 70654321">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono_personal" placeholder="Ej. 987654321">
                </div>
                <div class="form-group">
                    <label>Turno Asignado</label>
                    <select name="turno" required>
                        <option value="Mañana">Mañana</option>
                        <option value="Tarde">Tarde</option>
                        <option value="Noche">Noche</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="router.php?page=usuarios&rol_id=3" class="btn-cancel">Volver a la lista</a>
            <button type="submit" class="btn-submit">Registrar Trabajador</button>
        </div>
    </form>
</div>

<style>
.form-container { margin: 0 auto; padding-bottom: 30px; }
.premium-form { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.form-section { margin-bottom: 25px; border-bottom: 1px solid #F3F4F6; padding-bottom: 20px; }
.form-section h3 { margin: 0 0 20px 0; font-size: 16px; color: #111827; display: flex; align-items: center; gap: 8px; }
.form-section h3 .icon { width: 28px; height: 28px; background: #ECFDF5; color: #10B981; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 12px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; }
.form-group input, .form-group select { padding: 12px; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 14px; transition: 0.3s; background: #F9FAFB; }
.form-group input:focus, .form-group select:focus { border-color: #10B981; background: white; outline: none; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }
.form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 15px; }
.btn-submit { background: #10B981; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.3s; font-size: 14px; }
.btn-submit:hover { background: #059669; transform: translateY(-1px); }
.btn-cancel { background: #F3F4F6; color: #4B5563; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: 700; transition: 0.3s; font-size: 14px; }
.btn-cancel:hover { background: #E5E7EB; }
</style>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
