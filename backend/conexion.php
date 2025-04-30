<?php
$host = "localhost";
$user = "u347334547_admin_mrjugo"; 
$password = "CH7322a#"; 
$dbname = "u347334547_mrjugo"; 

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Eliminado el mensaje de "Conexión exitosa" que estaba causando problemas
?>