<?php
$title = "Recuperar Contraseña | EcoCusco";
ob_start();
?>

<div class="forgot-container">
    <div class="forgot-card">
        <i class="fas fa-key forgot-icon"></i>
        <h1>Recuperar contraseña</h1>
        <p>Esta función aún no está implementada plenamente en el sistema corporativo.</p>
        <p class="text-muted">Por favor, contacte con el administrador del sistema o envíe un correo a soporte@ecocusco.com para restablecer su acceso.</p>
        <div class="forgot-actions">
            <a href="/reciclaje/views/public/login.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver a iniciar sesión
            </a>
        </div>
    </div>
</div>

<?php
$extra_css = "
    body {
        background-color: #F3F4F6;
    }
    .forgot-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 60vh;
        padding: 20px;
    }
    .forgot-card {
        background: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        max-width: 500px;
        width: 100%;
        text-align: center;
    }
    .forgot-icon {
        font-size: 48px;
        color: #10B981;
        margin-bottom: 20px;
    }
    .forgot-card h1 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 15px;
        color: #1F2937;
    }
    .forgot-card p {
        color: #4B5563;
        margin-bottom: 15px;
        line-height: 1.6;
    }
    .forgot-actions {
        margin-top: 30px;
    }
    .btn-back {
        color: #10B981;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-back:hover {
        text-decoration: underline;
    }
";
$content = ob_get_clean();
include __DIR__ . '/../layouts/public_layout.php';
?>

