<!-- backend/conexion.php -->
<?php
$host = "localhost";
$user = "u347334547_admin_mrjugo"; // Reemplaza con tu usuario de Hostinger
$password = "CH7322a#"; // Contraseña de tu BD
$dbname = "u347334547_mrjugo"; // Nombre de tu BD

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
echo "Conexión exitosa a la base de datos.";
?>