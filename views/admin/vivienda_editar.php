<?php
$user = check_dashboard_access([1, 2, 5, 6]);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM viviendas WHERE id = ?");
$stmt->execute([$id]);
$vivienda = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$vivienda) { echo "<div class='alert alert-error'>Vivienda no encontrada.</div>"; return; }

$barrios = $pdo->query("SELECT id, nombre FROM barrios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$calles = $pdo->prepare("SELECT id, nombre FROM calles WHERE barrio_id = ? ORDER BY nombre");
$calles->execute([$vivienda['barrio_id']]);
$calles_list = $calles->fetchAll(PDO::FETCH_ASSOC);

$title = "Editar Vivienda - EcoCusco";
$header_title = "Editar Vivienda";
$header_subtitle = "Modifica los datos de la vivienda #" . $vivienda['id'];

ob_start();
?>
<div class="form-container" style="margin: 0 auto;">
    <form action="router.php" method="POST" class="premium-form">
        <input type="hidden" name="action" value="editar_vivienda">
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="form-section">
            <h3><span class="icon">👤</span> Propietario</h3>
            <div class="form-group">
                <label>Nombre Completo / Familia</label>
                <input type="text" name="propietario" required value="<?= htmlspecialchars($vivienda['propietario']) ?>">
            </div>
        </div>

        <div class="form-section">
            <h3><span class="icon">📍</span> Ubicación</h3>
            <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Barrio</label>
                    <select name="barrio_id" id="barrio_select" required onchange="updateStreets(this.value)">
                        <?php foreach($barrios as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $b['id'] == $vivienda['barrio_id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Calle</label>
                    <select name="calle_id" id="calle_select" required>
                        <?php foreach($calles_list as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $vivienda['calle_id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px; margin-top: 15px;">
                <div class="form-group">
                    <label>Dirección Específica</label>
                    <input type="text" name="direccion" required value="<?= htmlspecialchars($vivienda['direccion']) ?>">
                </div>
                <div class="form-group">
                    <label>Número de Casa</label>
                    <input type="text" name="numero_casa" value="<?= htmlspecialchars($vivienda['numero_casa'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3><span class="icon">📝</span> Referencia</h3>
            <div class="form-group">
                <label>Referencia de Ubicación</label>
                <input type="text" name="referencia" value="<?= htmlspecialchars($vivienda['referencia'] ?? '') ?>">
            </div>
        </div>

        <div class="form-actions">
            <a href="javascript:history.back()" class="btn-cancel">Cancelar</a>
            <button type="submit" class="btn-submit">Guardar Cambios</button>
        </div>
    </form>
</div>

<script>
async function updateStreets(barrioId) {
    const calleSelect = document.getElementById('calle_select');
    calleSelect.innerHTML = '<option value="">Cargando...</option>';
    calleSelect.disabled = true;
    if (!barrioId) return;
    try {
        const response = await fetch('router.php?page=ajax_get_calles&barrio_id=' + barrioId);
        const calles = await response.json();
        calleSelect.innerHTML = '<option value="">Seleccione calle...</option>';
        calles.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nombre;
            calleSelect.appendChild(opt);
        });
        calleSelect.disabled = false;
    } catch (e) {
        calleSelect.innerHTML = '<option value="">Error al cargar</option>';
    }
}
</script>

<style>
.form-container { padding-bottom: 30px; }
.premium-form { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.form-section { margin-bottom: 20px; border-bottom: 1px solid #F3F4F6; padding-bottom: 20px; }
.form-section h3 { margin: 0 0 15px 0; font-size: 15px; color: #111827; display: flex; align-items: center; gap: 8px; }
.form-section h3 .icon { width: 22px; height: 22px; background: #F3F4F6; color: #374151; border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 12px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 11px; font-weight: 700; color: #6B7280; text-transform: uppercase; }
.form-group input, .form-group select { padding: 10px 12px; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 13px; transition: 0.3s; }
.form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 15px; }
.btn-submit { background: #111827; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 13px; }
.btn-cancel { background: #F3F4F6; color: #4B5563; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; }
@media(max-width:600px){.form-grid{grid-template-columns:1fr!important}}
</style>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
