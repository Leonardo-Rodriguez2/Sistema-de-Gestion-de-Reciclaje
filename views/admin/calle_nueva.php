<?php
// views/admin/calle_nueva.php
$user = check_dashboard_access([1]);

// Obtener barrios for the dropdown
$barriosStmt = $pdo->query("SELECT id, nombre FROM barrios ORDER BY nombre");
$barrios = $barriosStmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Registrar Nueva Calle - EcoCusco";
$header_title = "Nueva Calle";
$header_subtitle = "Agrega una nueva calle a cualquiera de los barrios registrados.";

ob_start();
?>
<div class="form-container" style="max-width: 500px; margin: 0 auto;">
    <form action="router.php" method="POST" class="premium-form">
        <input type="hidden" name="action" value="nueva_calle">
        <input type="hidden" name="form_type" value="nueva_calle">
        
        <div class="form-section">
            <h3><span class="icon">🛣️</span> Información de la Calle</h3>
            <div class="form-group">
                <label>Nombre de la Calle</label>
                <input type="text" name="nombre" required placeholder="Ej. Avenida Los Próceres">
            </div>
            
            <div class="form-group" style="margin-top: 15px;">
                <label>Barrio Asignado</label>
                <select name="barrio_id" required style="width: 100%; padding: 10px; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 13px;">
                    <option value="">Selecciona un barrio...</option>
                    <?php foreach($barrios as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <a href="router.php?page=calles" class="btn-cancel">Volver a la lista</a>
            <button type="submit" class="btn-submit">Registrar Calle</button>
        </div>
    </form>
</div>

<style>
.form-container { padding-bottom: 30px; }
.premium-form { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.form-section { margin-bottom: 20px; }
.form-section h3 { margin: 0 0 15px 0; font-size: 16px; color: #111827; display: flex; align-items: center; gap: 8px; }
.form-section h3 .icon { width: 24px; height: 24px; background: #ECFDF5; color: #059669; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 14px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 12px; font-weight: 500; color: #4B5563; }
.form-group input { padding: 10px 12px; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 13px; transition: 0.3s; }
.form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #F3F4F6; }
.btn-submit { background: #059669; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 13px; }
.btn-cancel { background: #F3F4F6; color: #4B5563; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: 0.3s; font-size: 13px; }
</style>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
