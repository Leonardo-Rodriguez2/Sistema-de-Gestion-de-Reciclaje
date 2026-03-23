<?php
// views/admin/registrar_vivienda.php
$user = check_dashboard_access([1, 5]);

// Barrios y Jefes para el form
$barrios = $pdo->query("SELECT id, nombre FROM barrios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$jefes = $pdo->query("SELECT id, nombre, apellido FROM usuarios WHERE rol_id = 5")->fetchAll(PDO::FETCH_ASSOC);

$title = "Registrar Vivienda - EcoCusco";
$header_title = "Nueva Vivienda";
$header_subtitle = "Asigna una nueva vivienda a una cuadra y barrio.";

ob_start();
?>
    <?php render_dashboard_alerts($mensaje_exito ?? null, $mensaje_error ?? null); ?>

    <div class="card" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin: 0 auto;">
        <form method="POST" action="router.php?page=viviendas">
            <input type="hidden" name="form_type" value="nuevo_vecino">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Propietario / Familia</label>
                <input type="text" name="propietario" style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 8px;" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Barrio</label>
                    <select name="barrio_id" style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 8px;" required>
                        <?php foreach($barrios as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= $b['nombre'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Teléfono</label>
                    <input type="text" name="telefono" style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 8px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Calle / Dirección</label>
                    <input type="text" name="direccion" style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 8px;" required>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">N°</label>
                    <input type="text" name="numero" style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 8px;">
                </div>
            </div>

            <?php if ($user['rol_id'] == 1): ?>
            <div style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Jefe de Cuadra Responsable</label>
                <select name="jefe_cuadra_id" style="width: 100%; padding: 12px; border: 1px solid #D1D5DB; border-radius: 8px;">
                    <?php foreach($jefes as $j): ?>
                        <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['nombre'] . ' ' . $j['apellido']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <button type="submit" style="width: 100%; padding: 14px; background: #10B981; color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 16px;">
                Confirmar Registro
            </button>
        </form>
    </div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>
