<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, redirigir al login con un mensaje
    $_SESSION['error_login'] = "Debes iniciar sesión para modificar tu perfil";
    header("Location: /login");
    exit;
}

// Incluir archivo de conexión
require_once 'conexion.php';

// Verificar método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtener datos del formulario
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    
    // Validar datos
    if (empty($nombre)) {
        $_SESSION['error_perfil'] = "El nombre es obligatorio";
        header("Location: /perfil");
        exit;
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_perfil'] = "El email no es válido";
        header("Location: /perfil");
        exit;
    }
    
    // Verificar si el email ya está en uso (excepto por el usuario actual)
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error_perfil'] = "El email ya está en uso por otro usuario";
        header("Location: /perfil");
        exit;
    }
    
    // Actualizar datos del usuario
    $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nombre, $email, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        // Actualizar datos de sesión
        $_SESSION['user_name'] = $nombre;
        $_SESSION['user_email'] = $email;
        
        $_SESSION['exito_perfil'] = "Datos actualizados correctamente";
    } else {
        $_SESSION['error_perfil'] = "Error al actualizar los datos: " . $conn->error;
    }
    
    // Redireccionar de vuelta al perfil
    header("Location: /perfil");
    exit;
}

// Si no es POST, redireccionar
header("Location: /perfil");
exit;
?>