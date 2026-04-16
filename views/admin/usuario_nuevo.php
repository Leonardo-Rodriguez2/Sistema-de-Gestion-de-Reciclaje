<?php
// views/admin/usuario_nuevo.php
$user = check_dashboard_access([1, 2]);

// Si es Gestor (2), forzar que solo registre Personal Obrero (3)
if ($user['rol_id'] == 2) {
    if (!isset($_GET['rol_id']) || $_GET['rol_id'] != 3) {
        $sid = $_GET['sid'] ?? '';
        header("Location: router.php?page=usuario_nuevo_personal" . ($sid ? "&sid=$sid" : ""));
        exit;
    }
}

// Fetch neighborhoods for the dropdown
$barriosStmt = $pdo->query("SELECT id, nombre FROM barrios ORDER BY nombre ASC");
$barrios_lista = $barriosStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Nuevo Usuario - EcoCusco";
$header_title = "";

ob_start();
?>
<div class="form-container">
    <form action="router.php" method="POST" class="premium-form">
        <input type="hidden" name="action" value="add_user">
        
        <div class="form-section">
            <h3><span class="icon">👤</span> Información Básica</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre">Nombre(s)</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej. Juan">
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido(s)</label>
                    <input type="text" id="apellido" name="apellido" required placeholder="Ej. Pérez">
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required placeholder="juan.perez@ejemplo.com">
                </div>
                <div class="form-group">
                    <label for="password">Contraseña (por defecto: 123456)</label>
                    <input type="password" id="password" name="password" placeholder="Mín. 6 caracteres">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3><span class="icon">📅</span> Detalles Personales</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="genero">Género</label>
                    <select id="genero" name="genero">
                        <option value="">Seleccione...</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento">
                </div>
                <div class="form-group">
                    <label for="rol_id">Rol en el Sistema</label>
                    <select id="rol_id" name="rol_id" required onchange="toggleRoleFields()" <?= $locked_rol_id > 0 ? 'disabled' : '' ?>>
                        <option value="">Seleccione un rol...</option>
                        <option value="5" <?= ($user['rol_id'] == 2) ? 'disabled' : '' ?>>Encargado de Barrio</option>
                        <option value="6" <?= ($user['rol_id'] == 2) ? 'disabled' : '' ?>>Encargado de Calle</option>
                        <option value="3">Recolector</option>
                        <option value="2" <?= ($user['rol_id'] == 2) ? 'disabled' : '' ?>>Gestor de Pagos</option>
                        <option value="1" <?= ($user['rol_id'] == 2) ? 'disabled' : '' ?>>Administrador</option>
                    </select>
                    <?php if ($locked_rol_id > 0): ?>
                        <input type="hidden" name="rol_id" value="<?= $locked_rol_id ?>">
                        <div style="font-size: 11px; color: #10B981; margin-top: 4px; font-weight: 600;">✨ Registro especializado</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Role Specific Sections -->
        <div id="role_fields_container">
            <!-- Encargado de Barrio Section -->
            <div id="jefe_fields" class="form-section role-section" style="display: none;">
                <h3><span class="icon">🏠</span> Detalles: Encargado de Barrio</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>DNI / Identificación</label>
                        <input type="text" name="dni" placeholder="Ej. 70654321">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" placeholder="Ej. 987654321">
                    </div>
                    <div class="form-group">
                        <label>Barrio Asignado</label>
                        <select name="barrio_id">
                            <?php foreach ($barrios_lista as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="direccion" placeholder="Ej. Calle Saphy 123">
                    </div>
                </div>
            </div>
            
            <!-- Encargado de Calle Section -->
            <div id="calle_fields" class="form-section role-section" style="display: none;">
                <h3><span class="icon">📍</span> Detalles: Encargado de Calle</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>DNI</label>
                        <input type="text" name="dni" placeholder="Ej. 70654321">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" placeholder="Ej. 987654321">
                    </div>
                    <div class="form-group">
                        <label>Calle Asignada</label>
                        <select name="calle_id">
                            <?php 
                            $stmt = $pdo->query("SELECT c.id, c.nombre, b.nombre as barrio FROM calles c JOIN barrios b ON c.barrio_id = b.id ORDER BY b.nombre, c.nombre");
                            while($c = $stmt->fetch()): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['barrio'] . " - " . $c['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Gestor Section -->
            <div id="gestor_fields" class="form-section role-section" style="display: none;">
                <h3><span class="icon">💼</span> Detalles: Gestor de Pagos</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>DNI</label>
                        <input type="text" name="dni" placeholder="Ej. 70654321">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" placeholder="Ej. 987654321">
                    </div>
                    <div class="form-group">
                        <label>Área Responsable</label>
                        <input type="text" name="area" placeholder="Ej. Cobranza Norte">
                    </div>
                </div>
            </div>

            <!-- Recolector Section -->
            <div id="recolector_fields" class="form-section role-section" style="display: none;">
                <h3><span class="icon">🚛</span> Detalles: Recolector</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>DNI</label>
                        <input type="text" name="dni" placeholder="Ej. 70654321">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" placeholder="Ej. 987654321">
                    </div>
                    <div class="form-group">
                        <label>Turno</label>
                        <select name="turno">
                            <option value="Mañana">Mañana</option>
                            <option value="Tarde">Tarde</option>
                            <option value="Noche">Noche</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Contacto de Emergencia</label>
                        <input type="text" name="contacto_emergencia" placeholder="Nombre y Teléfono">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="router.php?page=usuarios" class="btn-cancel">Ver el listado</a>
            <button type="submit" class="btn-submit">Crear Usuario</button>
        </div>
    </form>
</div>

<style>
.form-container { margin: 0 auto; padding-bottom: 30px; }
.premium-form { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.form-section { margin-bottom: 25px; border-bottom: 1px solid #F3F4F6; padding-bottom: 15px; animation: fadeIn 0.4s ease; }
.form-section:last-of-type { border-bottom: none; }
.form-section h3 { margin: 0 0 15px 0; font-size: 16px; color: #111827; display: flex; align-items: center; gap: 8px; }
.form-section h3 .icon { width: 24px; height: 24px; background: #ECFDF5; color: #10B981; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 14px; }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 12px; font-weight: 500; color: #4B5563; }
.form-group input, .form-group select { padding: 8px 12px; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 13px; transition: 0.3s; }
.form-group input:focus, .form-group select:focus { outline: none; border-color: #10B981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
.form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #F3F4F6; }
.btn-submit { background: #10B981; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 13px; }
.btn-submit:hover { background: #059669; transform: translateY(-1px); box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2); }
.btn-cancel { background: #F3F4F6; color: #4B5563; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: 0.3s; font-size: 13px; }
.btn-cancel:hover { background: #E5E7EB; }

@keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
function toggleRoleFields() {
    const rolId = document.getElementById('rol_id').value;
    const sections = document.querySelectorAll('.role-section');
    sections.forEach(s => s.style.display = 'none');

    if (rolId == 5) document.getElementById('jefe_fields').style.display = 'block';
    else if (rolId == 6) document.getElementById('calle_fields').style.display = 'block';
    else if (rolId == 2) document.getElementById('gestor_fields').style.display = 'block';
    else if (rolId == 3) document.getElementById('recolector_fields').style.display = 'block';
}

// Si se pasa un rol por URL, activarlo al cargar
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    const rolId = urlParams.get('rol_id');
    if (rolId) {
        document.getElementById('rol_id').value = rolId;
        toggleRoleFields();
    }
};
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
