<?php
// Iniciar sesión
session_start();

// Incluir archivo de conexión
require_once 'conexion.php';

// Verificar método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtener datos
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Buscar usuario
    $sql = "SELECT id, nombre, email, password, is_admin FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        
        // Verificar contraseña
        if (password_verify($password, $usuario['password'])) {
            
            // Guardar en sesión
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_name'] = $usuario['nombre'];
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['is_admin'] = $usuario['is_admin'];
            
            // Redireccionar según tipo de usuario
            if ($usuario['is_admin'] == 1) {
                header("Location: /backend/admin");
            } else {
                header("Location: /");
            }
            exit;
            
        } else {
            $_SESSION['error_login'] = "Correo o contraseña incorrectos.";
        }
    } else {
        $_SESSION['error_login'] = "Correo o contraseña incorrectos.";
    }
    
    // Redirigir a login si hay error
    header("Location: /login");
    exit;
}

// Si se accede directo, redirigir a login
header("Location: /login");
exit;
?>