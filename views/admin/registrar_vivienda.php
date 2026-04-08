<?php
// views/admin/registrar_vivienda.php
$user = check_dashboard_access([1, 2]);

// Get all neighborhoods for selection
$barrios = $pdo->query("SELECT id, nombre FROM barrios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

$title = "Registrar Vivienda (Admin) - EcoCusco";
$header_title = "Nueva Vivienda";
$header_subtitle = "Registro administrativo directo de predios en el sistema.";

ob_start();
?>
<div class="form-container" style="max-width: 650px; margin: 0 auto;">
    <form method="POST" class="premium-form">
        <input type="hidden" name="action" value="nuevo_vecino_admin">
        
        <div class="form-section">
            <h3><span class="icon">👤</span> Propietario</h3>
            <div class="form-group">
                <label>Nombre Completo / Familia</label>
                <input type="text" name="propietario" required placeholder="Ej. Familia Rodríguez">
            </div>
        </div>

        <div class="form-section">
            <h3><span class="icon">📍</span> Ubicación</h3>
            <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Barrio</label>
                    <select name="barrio_id" id="barrio_select" required onchange="updateStreets(this.value)">
                        <option value="">Seleccione barrio...</option>
                        <?php foreach($barrios as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Calle</label>
                    <select name="calle_id" id="calle_select" required disabled>
                        <option value="">Primero elija barrio...</option>
                    </select>
                </div>
            </div>
            <div class="form-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px; margin-top: 15px;">
                <div class="form-group">
                    <label>Dirección Específica</label>
                    <input type="text" name="direccion" required placeholder="Ej. Av. Principal con Calle 4">
                </div>
                <div class="form-group">
                    <label>Número de Casa</label>
                    <input type="text" name="numero_casa" placeholder="Ej. A-12">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="router.php?page=viviendas" class="btn-cancel">Volver al listado</a>
            <button type="submit" class="btn-submit">Registrar Vivienda</button>
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
        const response = await fetch('app/ajax/get_calles.php?barrio_id=' + barrioId);
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
        console.error(e);
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
</style>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
