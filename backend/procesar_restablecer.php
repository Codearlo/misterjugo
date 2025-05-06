<?php
// Iniciar sesión para mensajes
session_start();

// Incluir archivo de conexión a la base de datos
require_once 'conexion.php';

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtener y limpiar datos
    $token = trim($_POST['token']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validar datos
    if (empty($token) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error_restablecer'] = "Todos los campos son obligatorios.";
        header("Location: ../restablecer-password.php?token=$token");
        exit;
    }
    
    // Verificar que las contraseñas coincidan
    if ($password !== $confirm_password) {
        $_SESSION['error_restablecer'] = "Las contraseñas no coinciden.";
        header("Location: ../restablecer-password.php?token=$token");
        exit;
    }
    
    // Verificar longitud mínima de contraseña
    if (strlen($password) < 8) {
        $_SESSION['error_restablecer'] = "La contraseña debe tener al menos 8 caracteres.";
        header("Location: ../restablecer-password.php?token=$token");
        exit;
    }
    
    try {
        // Verificar si el token existe, no ha expirado y no ha sido usado
        $ahora = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("SELECT id FROM recuperacion_password WHERE token = ? AND email = ? AND expira > ? AND usado = 0");
        $stmt->execute([$token, $email, $ahora]);
        
        if ($stmt->rowCount() > 0) {
            // Hashear la nueva contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Actualizar la contraseña del usuario
            $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
            $stmt->execute([$password_hash, $email]);
            
            // Marcar el token como usado
            $stmt = $conn->prepare("UPDATE recuperacion_password SET usado = 1 WHERE token = ?");
            $stmt->execute([$token]);
            
            // Guardar mensaje de éxito
            $_SESSION['exito_login'] = "¡Tu contraseña ha sido actualizada correctamente! Ya puedes iniciar sesión con tu nueva contraseña.";
            
            // Almacenar el correo para autocompletar el formulario de login
            $_SESSION['email'] = $email;
            
            // Redirigir al login
            header("Location: ../login.php");
            exit;
        } else {
            $_SESSION['error_login'] = "El enlace de recuperación no es válido o ha expirado.";
            header("Location: ../login.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error_restablecer'] = "Ha ocurrido un error. Por favor, intenta nuevamente más tarde.";
        // Registrar el error en un archivo de log (para desarrollo)
        error_log("Error al restablecer contraseña: " . $e->getMessage());
        header("Location: ../restablecer-password.php?token=$token");
        exit;
    }
}
?>