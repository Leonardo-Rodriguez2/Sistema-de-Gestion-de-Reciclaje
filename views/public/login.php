<?php
session_start();
require_once '../../app/config.php';
require_once '../../autoload.php';

use app\controllers\loginController;

// Delegar login/logout al controlador
$loginCtrl = new loginController();

if (isset($_GET['logout'])) {
    $loginCtrl->cerrarSesion();
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = $loginCtrl->iniciarSesion(); // Redirige si OK, devuelve mensaje si falla
}


$title = "Iniciar Sesión | EcoCusco";
ob_start();
?>

  <div class="login-row">
    <div class="login-left">
      <img class="login-logo" src="https://i.pinimg.com/564x/a9/46/e3/a946e3253ead512044565855265b1635.jpg" alt="Logo de reciclaje">
      <div class="login-title">Bienvenido de nuevo</div>
      <div class="login-subtitle">Inicia sesión en tu cuenta de reciclaje</div>

      <!-- Mostrar errores -->
      <?php if (isset($error)): ?>
        <div class="login-error"><?php echo $error; ?></div>
      <?php endif; ?>

      <!-- Formulario -->
      <form method="POST" class="login-form">
        <!-- Correo Electrónico -->
        <div class="login-input-group">
          <label for="email">Correo Electrónico</label>
          <div class="login-input-relative">
            <span class="login-input-icon">&#9993;</span> <!-- Ícono de carta -->
            <input type="email" id="email" name="email" placeholder="tu@email.com" required />
          </div>
        </div>

        <!-- Contraseña -->
        <div class="login-input-group">
          <label for="password">Contraseña</label>
          <div class="login-input-relative">
            <span class="login-input-icon">&#128274;</span> <!-- Ícono de candado -->
            <input type="password" id="password" name="password" placeholder="***********" required />
          </div>
        </div>

        <!-- Olvidaste tu contraseña -->
        <div class="login-forgot">
          <a href="/reciclaje/views/public/forgot_password.php">Olvidaste tu contraseña</a>
        </div>

        <!-- Botón Iniciar Sesión -->
        <button type="submit" class="login-btn">
          Iniciar Sesión
        </button>

        <!-- Enlace Registrarse -->
        <div class="login-register">
          <span>¿No tienes una cuenta? </span>
          <a href="register.php">Regístrate</a>
        </div>
      </form>
    </div>

    <div class="login-right"></div>
  </div>

<?php
$extra_css = "
    body {
      background-color: #D1FAE5; /* Color de fondo específico para login */
    }

    .login-row {
      display: flex;
      width: 90%;
      max-width: 1200px;
      gap: 40px;
      margin: 50px auto;
      min-height: 500px;
    }

    .login-left, .login-right {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .login-left {
      background-color: white;
      align-items: center;
      padding: 40px;
      text-align: center;
    }

    .login-right {
      background-image: url('https://img.freepik.com/vector-premium/grupo-personas-estan-sosteniendo-bolsas-basura-contenedores-reciclaje_697880-16185.jpg');
      background-size: cover;
      background-position: center;
    }

    .login-logo {
      width: 60px;
      height: 60px;
      margin-bottom: 20px;
    }

    .login-title {
      font-weight: 700;
      font-size: 24px;
      margin-bottom: 5px;
    }

    .login-subtitle {
      color: #6B7280;
      margin-bottom: 25px;
    }

    .login-error {
      color: #EF4444;
      margin-bottom: 15px;
      font-size: 14px;
    }

    .login-form {
      display: flex;
      flex-direction: column;
      gap: 20px;
      width: 100%;
      max-width: 400px;
    }

    .login-input-group {
      text-align: left;
    }

    .login-input-group label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      font-weight: 500;
    }

    .login-input-relative {
      position: relative;
    }

    .login-input-icon {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #9CA3AF;
    }

    .login-form input {
      width: 100%;
      padding: 12px 12px 12px 40px;
      border: 1px solid #D1D5DB;
      border-radius: 8px;
      font-size: 14px;
      box-sizing: border-box;
    }

    .login-forgot {
      text-align: right;
    }

    .login-forgot a {
      color: #10B981;
      font-size: 14px;
      text-decoration: none;
    }

    .login-btn {
      background-color: #10B981;
      color: white;
      border: none;
      padding: 14px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s;
    }

    .login-btn:hover {
      background-color: #059669;
    }

    .login-register {
      font-size: 14px;
      color: #6B7280;
    }

    .login-register a {
      color: #10B981;
      font-weight: 600;
      text-decoration: none;
    }

    @media (max-width: 768px) {
      .login-row {
        flex-direction: column;
        margin-top: 20px;
      }
      .login-right {
        display: none;
      }
    }
";
$content = ob_get_clean();
include __DIR__ . '/../layouts/public_layout.php';
?>