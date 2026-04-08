<?php
// views/admin/usuario_editar.php - Versión Compacta
$user_id = (int)($_GET['id'] ?? 0);
if (!$user_id) { header("Location: router.php?page=usuarios"); exit; }

$sql = "SELECT u.*, r.nombre as rol_nombre,
               dj.dni as jefe_dni, dj.telefono as jefe_telefono, dj.direccion as jefe_direccion, dj.barrio_id,
               dg.dni as gestor_dni, dg.telefono as gestor_telefono, dg.area as gestor_area,
               dp.cargo as personal_cargo, dp.dni as personal_dni, dp.telefono as personal_telefono, dp.turno as personal_turno
        FROM usuarios u 
        JOIN roles r ON u.rol_id = r.id 
        LEFT JOIN detalles_jefe_cuadra dj ON u.id = dj.usuario_id
        LEFT JOIN detalles_gestor dg ON u.id = dg.usuario_id
        LEFT JOIN detalles_personal_obrero dp ON u.id = dp.usuario_id
        WHERE u.id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$u) die("Usuario no encontrado.");

$barrios_lista = $pdo->query("SELECT id, nombre FROM barrios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

$title = "Editar - EcoCusco";
$header_title = "Editar: " . $u['nombre'];

ob_start();
?>
<style>
    .compact-form { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin: 0 auto; }
    .f-section { margin-bottom: 20px; border: 1px solid #F3F4F6; padding: 15px; border-radius: 6px; }
    .f-section h3 { margin: -15px -15px 15px -15px; background: #F9FAFB; padding: 8px 15px; font-size: 13px; color: #374151; border-bottom: 1px solid #F3F4F6; border-radius: 6px 6px 0 0; display: flex; align-items: center; gap: 8px; }
    .f-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
    .f-group { display: flex; flex-direction: column; gap: 4px; }
    .f-group label { font-size: 12px; font-weight: 600; color: #6B7280; }
    .f-group input, .f-group select { padding: 6px 10px; border: 1px solid #D1D5DB; border-radius: 5px; font-size: 13px; transition: 0.2s; }
    .f-group input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.1); }
    .f-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px; border-top: 1px solid #EEE; padding-top: 15px; }
    .btn { padding: 8px 16px; border-radius: 5px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; border: none; }
    .btn-save { background: var(--primary); color: white; }
    .btn-cancel { background: #F3F4F6; color: #4B5563; }
    .role-section { animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="compact-form">
    <form action="router.php" method="POST">
        <input type="hidden" name="action" value="edit_user">
        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
        
        <div class="f-section">
            <h3>👤 Datos de Cuenta</h3>
            <div class="f-grid">
                <div class="f-group"><label>Nombre</label><input type="text" name="nombre" required value="<?= htmlspecialchars($u['nombre']) ?>"></div>
                <div class="f-group"><label>Apellido</label><input type="text" name="apellido" required value="<?= htmlspecialchars($u['apellido']) ?>"></div>
                <div class="f-group"><label>Email</label><input type="email" name="email" required value="<?= htmlspecialchars($u['email']) ?>"></div>
                <div class="f-group"><label>Password (Opcional)</label><input type="password" name="password" placeholder="Mín. 6 caracteres"></div>
            </div>
        </div>

        <div class="f-section">
            <h3>📅 Perfil Personal</h3>
            <div class="f-grid">
                <div class="f-group">
                    <label>Género</label>
                    <select name="genero">
                        <option value="">-</option>
                        <option value="M" <?= $u['genero'] == 'M' ? 'selected' : '' ?>>Masc</option>
                        <option value="F" <?= $u['genero'] == 'F' ? 'selected' : '' ?>>Fem</option>
                        <option value="Otro" <?= $u['genero'] == 'Otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
                <div class="f-group"><label>Nacimiento</label><input type="date" name="fecha_nacimiento" value="<?= $u['fecha_nacimiento'] ?>"></div>
                <div class="f-group">
                    <label>Rol</label>
                    <select name="rol_id" id="rol_id" required onchange="toggleRoleFields()">
                        <option value="5" <?= $u['rol_id'] == 5 ? 'selected' : '' ?>>Encargado de Barrio</option>
                        <option value="3" <?= $u['rol_id'] == 3 ? 'selected' : '' ?>>Personal Obrero</option>
                        <option value="2" <?= $u['rol_id'] == 2 ? 'selected' : '' ?>>Gestor</option>
                        <option value="1" <?= $u['rol_id'] == 1 ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="role_fields_container">
            <div id="j_f" class="f-section role-section" style="display: <?= $u['rol_id'] == 5 ? 'block' : 'none' ?>;">
                <h3>🏠 Encargado de Barrio</h3>
                <div class="f-grid">
                    <div class="f-group"><label>DNI</label><input type="text" name="dni" value="<?= $u['jefe_dni'] ?>"></div>
                    <div class="f-group"><label>Teléfono</label><input type="text" name="telefono" value="<?= $u['jefe_telefono'] ?>"></div>
                    <div class="f-group">
                        <label>Barrio</label>
                        <select name="barrio_id">
                            <?php foreach ($barrios_lista as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= $u['barrio_id'] == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div id="g_f" class="f-section role-section" style="display: <?= $u['rol_id'] == 2 ? 'block' : 'none' ?>;">
                <h3>💼 Gestor</h3>
                <div class="f-grid">
                    <div class="f-group"><label>DNI</label><input type="text" name="dni_gestor" value="<?= $u['gestor_dni'] ?>"></div>
                    <div class="f-group"><label>Teléfono</label><input type="text" name="telefono_gestor" value="<?= $u['gestor_telefono'] ?>"></div>
                    <div class="f-group"><label>Área</label><input type="text" name="area" value="<?= $u['gestor_area'] ?>"></div>
                </div>
            </div>

            <div id="r_f" class="f-section role-section" style="display: <?= $u['rol_id'] == 3 ? 'block' : 'none' ?>;">
                <h3>👷 Personal Obrero</h3>
                <div class="f-grid">
                    <div class="f-group">
                        <label>Cargo / Función</label>
                        <select name="cargo">
                            <option value="Recolector" <?= $u['personal_cargo'] == 'Recolector' ? 'selected' : '' ?>>Recolector</option>
                            <option value="Chofer" <?= $u['personal_cargo'] == 'Chofer' ? 'selected' : '' ?>>Chofer</option>
                            <option value="Operario de Planta" <?= $u['personal_cargo'] == 'Operario de Planta' ? 'selected' : '' ?>>Operario de Planta</option>
                            <option value="Mecánico" <?= $u['personal_cargo'] == 'Mecánico' ? 'selected' : '' ?>>Mecánico</option>
                            <option value="Supervisor de Campo" <?= $u['personal_cargo'] == 'Supervisor de Campo' ? 'selected' : '' ?>>Supervisor de Campo</option>
                        </select>
                    </div>
                    <div class="f-group"><label>DNI</label><input type="text" name="dni_personal" value="<?= $u['personal_dni'] ?>"></div>
                    <div class="f-group"><label>Teléfono</label><input type="text" name="telefono_personal" value="<?= $u['personal_telefono'] ?>"></div>
                    <div class="f-group">
                        <label>Turno</label>
                        <select name="turno">
                            <option value="Mañana" <?= $u['personal_turno']=='Mañana'?'selected':'' ?>>Mañana</option>
                            <option value="Tarde" <?= $u['personal_turno']=='Tarde'?'selected':'' ?>>Tarde</option>
                            <option value="Noche" <?= $u['personal_turno']=='Noche'?'selected':'' ?>>Noche</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="f-actions">
            <a href="router.php?page=usuario_ver&id=<?= $u['id'] ?>" class="btn btn-cancel">Cancelar</a>
            <button type="submit" class="btn btn-save">Actualizar</button>
        </div>
    </form>
</div>

<script>
function toggleRoleFields() {
    const r = document.getElementById('rol_id').value;
    document.getElementById('j_f').style.display = (r == 5) ? 'block' : 'none';
    document.getElementById('g_f').style.display = (r == 2) ? 'block' : 'none';
    document.getElementById('r_f').style.display = (r == 3) ? 'block' : 'none';
}
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>