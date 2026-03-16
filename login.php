<?php
session_start();

// Manejar cierre de sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Conexión a la base de datos centralizada
require_once 'data/conexion.php';

// Procesar el formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validaciones básicas
    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } else {
        // Verificar si el usuario existe en la base de datos
        try {
            $stmt = $pdo->prepare("SELECT id, password_hash, rol_id FROM usuarios WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Credenciales correctas, iniciar sesión
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                echo "<div style='text-align: center; margin-top: 20px; font-family: Poppins, sans-serif;'>
                        <h2 style='color: #10B981;'>Inicio de sesión exitoso</h2>
                        <p>Bienvenido de nuevo</p>
                      </div>";
                // Redirigir siempre al enrutador central
                $redirect_url = 'router.php'; // Controlador único

                echo "<script>
                        setTimeout(function() {
                            window.location.href = '$redirect_url';
                        }, 1500);
                      </script>";
                exit;
            } else {
                // Credenciales incorrectas
                $error = 'Correo o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            $error = 'Error al procesar el inicio de sesión.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #D1FAE5; /* Color de fondo */
      /* Elimina flex y height: 100vh para que el header quede arriba */
    }

    .row {
      display: flex;
      width: 90%;
      max-width: 1200px;
      gap: 40px;
      margin: 0 auto;
      margin-top: 100px; /* Deja espacio para el header */
    }

    .left, .right {
      flex: 1;
      min-height: 500px; /* Asegura que ambos contenedores tengan la misma altura */
      display: flex;
      flex-direction: column;
      justify-content: center;
      border-radius: 12px; /* Bordes redondeados */
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Sombra ligera */
    }

    .left {
      background-color: white;
      align-items: center;
      padding: 30px;
      text-align: center;
    }

    .right {
      background-image: url('https://img.freepik.com/vector-premium/grupo-personas-estan-sosteniendo-bolsas-basura-contenedores-reciclaje_697880-16185.jpg');
      background-size: cover;
      background-position: center;
      align-items: center;
      justify-content: center;
      display: flex;
      border-radius: 12px; /* Bordes redondeados */
    }

    .logo {
      width: 60px; /* Ancho fijo */
      height: 60px; /* Alto fijo */
      margin-bottom: 20px; /* Espacio debajo de la imagen */
    }

    input[type="email"], input[type="password"] {
      padding: 10px 10px 10px 40px; /* Asegura espacio suficiente para los íconos */
      border: 1px solid #ccc;
      border-radius: 5px;
      width: 100%; /* Asegura que no exceda el ancho del contenedor */
      box-sizing: border-box; /* Incluye el padding y el borde en el ancho total */
      opacity: 0.7;
    }

    .input-icon {
      position: absolute;
      top: 50%;
      left: 10px;
      transform: translateY(-50%);
      color: black; /* Íconos no transparentes */
      font-size: 16px; /* Tamaño del ícono */
    }

    a {
      color: #10B981; /* Verde */
      text-decoration: none;
      font-weight: 500;
    }

    a:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      .row {
        flex-direction: column;
        gap: 20px;
      }

      .right {
        height: 200px; /* Ajusta la altura para pantallas pequeñas */
      }
    }
  </style>
</head>
<body>
  <!-- HEADER INCLUIDO DESDE components/header.php -->
  <?php include 'components/header.php'; ?>

  <div class="row">
    <div class="left">
      <img class="logo" src="https://i.pinimg.com/564x/a9/46/e3/a946e3253ead512044565855265b1635.jpg" alt="Logo de reciclaje">
      <div class="title" style="font-weight: 700; font-family: 'Poppins', sans-serif; font-size: 24px;">Bienvenido de nuevo</div>
      <div class="subtitle">Inicia sesión en tu cuenta de reciclaje</div>

      <!-- Mostrar errores -->
      <?php if (isset($error)): ?>
        <div style="color: red; text-align: center; margin-bottom: 10px;"><?php echo $error; ?></div>
      <?php endif; ?>

      <!-- Formulario -->
      <form method="POST" style="display: flex; flex-direction: column; align-items: flex-start; gap: 20px; margin-top: 20px; width: 80%;">
        <!-- Correo Electrónico -->
        <div style="width: 100%; display: flex; flex-direction: column;">
          <label for="email" style="font-weight: 400; font-size: 16px; text-align: left;">Correo Electrónico</label>
          <div style="position: relative; width: 100%;">
            <span class="input-icon">&#9993;</span> <!-- Ícono de carta -->
            <input type="email" id="email" name="email" placeholder="tu@email.com" />
          </div>
        </div>

        <!-- Contraseña -->
        <div style="width: 100%; display: flex; flex-direction: column;">
          <label for="password" style="font-weight: 400; font-size: 16px; text-align: left;">Contraseña</label>
          <div style="position: relative; width: 100%;">
            <span class="input-icon">&#128274;</span> <!-- Ícono de candado -->
            <input type="password" id="password" name="password" placeholder="***********" />
          </div>
        </div>

        <!-- Olvidaste tu contraseña -->
        <div style="width: 100%; text-align: right;">
          <a href="forgot_password.php">Olvidaste tu contraseña</a>
        </div>

        <!-- Botón Iniciar Sesión -->
        <button type="submit" style="width: 100%; padding: 10px; background-color: #10B981; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
          Iniciar Sesión
        </button>

        <!-- Enlace Registrarse -->
        <div style="width: 100%; text-align: center; margin-top: 10px;">
          <span style="font-size: 14px;">¿No tienes una cuenta? </span>
          <a href="register.php">Regístrate</a>
        </div>
      </form>
    </div>

    <div class="right"></div>
  </div>
</body>
</html>