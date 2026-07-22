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
      <div class="login-title">EcoCusco</div>
      <div class="login-subtitle">Sistema de Gestión de Reciclaje</div>

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
      background-image: url('https://images.unsplash.com/photo-1511497584788-876760111969?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80');
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
      background: #FEF2F2;
      border: 1px solid #FECACA;
      border-left: 3px solid #EF4444;
      color: #991B1B;
      padding: 10px 14px;
      margin-bottom: 15px;
      font-size: 13px;
      border-radius: 3px;
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

    @media (max-width: 768px) {
      .login-row {
        flex-direction: column;
        margin-top: 20px;
        width: 95%;
        gap: 0;
        min-height: auto;
      }
      .login-right {
        display: none;
      }
      .login-left {
        padding: 25px 20px;
        border-radius: 12px;
      }
      .login-title { font-size: 20px; }
      .login-form { max-width: 100%; }
      .login-form input { padding: 14px 14px 14px 40px; font-size: 16px; }
      .login-btn { padding: 16px; font-size: 16px; }
    }
    @media (max-width: 400px) {
      .login-left { padding: 20px 15px; }
      .login-title { font-size: 18px; }
      .login-logo { width: 50px; height: 50px; }
    }
  ";
$content = ob_get_clean();
include __DIR__ . '/../layouts/public_layout.php';
?>