<?php
session_start();
require_once '../backend/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // ✅ Encriptado seguro

    // Verificar si el email ya existe
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('El correo ya está registrado'); window.history.back();</script>";
        exit();
    }

    // Insertar nuevo usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $email, $password);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Registro exitoso";
        header("Location: ../frontend/login.php");
        exit();
    } else {
        echo "<script>alert('Error al registrar'); window.history.back();</script>";
    }
}
?>