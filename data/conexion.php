<?php
// conexion.php (PDO)
$host = 'localhost';
$dbname = 'reciclaje_platform';
$username = 'root';
$password = ''; // Contraseña en blanco por defecto en XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>
