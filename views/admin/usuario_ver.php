<?php
// views/admin/usuario_ver.php - Versión Compacta
$user_id = (int)($_GET['id'] ?? 0);
if (!$user_id) { header("Location: router.php?page=usuarios"); exit; }

$sql = "SELECT u.*, r.nombre as rol_nombre,
               dj.dni as jefe_dni, dj.telefono as jefe_telefono, dj.direccion as jefe_direccion, b.nombre as barrio_nombre,
               dg.dni as gestor_dni, dg.telefono as gestor_telefono, dg.area as gestor_area,
               dr.dni as recolector_dni, dr.telefono as recolector_telefono, dr.turno as recolector_turno, dr.contacto_emergencia
        FROM usuarios u 
        JOIN roles r ON u.rol_id = r.id 
        LEFT JOIN detalles_jefe_cuadra dj ON u.id = dj.usuario_id LEFT JOIN barrios b ON dj.barrio_id = b.id
        LEFT JOIN detalles_gestor dg ON u.id = dg.usuario_id
        LEFT JOIN detalles_recolector dr ON u.id = dr.usuario_id
        WHERE u.id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$u) die("Usuario no encontrado.");

$title = "Perfil - EcoCusco";
$header_title = "Perfil: " . $u['nombre'];

// Mapeo de campos por rol para reducir bloques IF pesados
$extra_info = [];
if ($u['rol_id'] == 5) {
    $extra_info = ['Título' => '🏠 Jefe de Cuadra', 'DNI' => $u['jefe_dni'], 'Tel' => $u['jefe_telefono'], 'Barrio' => $u['barrio_nombre'], 'Dirección' => $u['jefe_direccion']];
} elseif ($u['rol_id'] == 2) {
    $extra_info = ['Título' => '💼 Gestor', 'DNI' => $u['gestor_dni'], 'Tel' => $u['gestor_telefono'], 'Área' => $u['gestor_area']];
} elseif ($u['rol_id'] == 3) {
    $extra_info = ['Título' => '🚛 Recolector', 'DNI' => $u['recolector_dni'], 'Turno' => $u['recolector_turno'], 'Emerg.' => $u['contacto_emergencia']];
}

ob_start();
?>
<style>
    .profile-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; max-width: 900px; margin: 0 auto; }
    .profile-card { background: white; border-radius: 8px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #EEE; }
    
    .header-mini { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; background: white; padding: 15px; border-radius: 8px; border: 1px solid #EEE; }
    .avatar-mini { width: 60px; height: 60px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: bold; }
    
    .info-group { display: flex; flex-direction: column; gap: 6px; }
    .info-row { display: flex; justify-content: space-between; font-size: 13px; border-bottom: 1px inset #F9FAFB; padding-bottom: 2px; }
    .info-row .lab { color: #6B7280; font-weight: 500; }
    .info-row .val { color: #111827; font-weight: 600; text-align: right; }
    
    .card-title { font-size: 14px; font-weight: 700; margin-bottom: 12px; color: #374151; display: flex; justify-content: space-between; align-items: center; }
    .btn-sm { padding: 4px 10px; font-size: 11px; border-radius: 4px; text-decoration: none; font-weight: 600; }
    .btn-edit { background: #EBF5FF; color: #1E40AF; }
    .btn-back { background: #F3F4F6; color: #4B5563; display: inline-block; margin-top: 15px; }
</style>

<div style="max-width: 900px; margin: 0 auto;">
    <div class="header-mini">
        <div class="avatar-mini"><?= strtoupper(substr($u['nombre'], 0, 1) . substr($u['apellido'], 0, 1)) ?></div>
        <div style="flex-grow: 1;">
            <h2 style="margin:0; font-size: 18px;"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></h2>
            <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px;">
                <span class="badge <?= strtolower($u['rol_nombre']) ?>" style="font-size: 10px; padding: 2px 6px;"><?= $u['rol_nombre'] ?></span>
                <span style="font-size: 12px; color: #6B7280;"><?= htmlspecialchars($u['email']) ?></span>
            </div>
        </div>
        <a href="router.php?page=usuario_editar&id=<?= $u['id'] ?>" class="btn-sm btn-edit">✏️ Editar</a>
    </div>

    <div class="profile-grid">
        <div class="profile-card">
            <div class="card-title">📌 General</div>
            <div class="info-group">
                <div class="info-row"><span class="lab">ID:</span><span class="val">#<?= $u['id'] ?></span></div>
                <div class="info-row"><span class="lab">Género:</span><span class="val"><?= $u['genero'] ?: '-' ?></span></div>
                <div class="info-row"><span class="lab">Nacimiento:</span><span class="val"><?= $u['fecha_nacimiento'] ?: '-' ?></span></div>
                <div class="info-row"><span class="lab">Registro:</span><span class="val"><?= date('d/m/y', strtotime($u['creado_en'])) ?></span></div>
            </div>
        </div>

        <?php if (!empty($extra_info)): ?>
        <div class="profile-card">
            <div class="card-title"><?= $extra_info['Título'] ?></div>
            <div class="info-group">
                <?php foreach($extra_info as $label => $value): if($label === 'Título') continue; ?>
                    <div class="info-row">
                        <span class="lab"><?= $label ?>:</span>
                        <span class="val"><?= $value ?: '-' ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <a href="router.php?page=usuarios" class="btn-sm btn-back">⬅ Volver</a>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard_layout.php';
?>