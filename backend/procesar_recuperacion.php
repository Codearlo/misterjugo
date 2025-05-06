<?php
// Versión mejorada de procesar_recuperacion.php
session_start();

// Incluir archivo de conexión a la base de datos
require_once 'conexion.php';

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtener y limpiar el correo electrónico
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Validar formato de correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_recuperacion'] = "El formato del correo electrónico no es válido.";
        header("Location: ../recuperar-password.php");
        exit;
    }
    
    try {
        // Verificar si el correo existe en la base de datos
        $stmt = $conn->prepare("SELECT id, nombre FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // El usuario existe, generar token
            $token = bin2hex(random_bytes(16)); // Token de 32 caracteres
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Crear la tabla si no existe
            $conn->exec("CREATE TABLE IF NOT EXISTS recuperacion_password (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                token VARCHAR(64) NOT NULL,
                expira DATETIME NOT NULL,
                usado TINYINT(1) DEFAULT 0,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Eliminar tokens anteriores del mismo usuario
            $stmt = $conn->prepare("DELETE FROM recuperacion_password WHERE email = ?");
            $stmt->execute([$email]);
            
            // Guardar el nuevo token
            $stmt = $conn->prepare("INSERT INTO recuperacion_password (email, token, expira) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expira]);
            
            // Para pruebas, mostrar el enlace directamente
            $_SESSION['exito_recuperacion'] = "Se ha generado un enlace de recuperación. <a href='../restablecer-password.php?token=" . $token . "'>Haz clic aquí para restablecer tu contraseña</a>";
        } else {
            // Usuario no encontrado - dar mensaje genérico por seguridad
            $_SESSION['exito_recuperacion'] = "Si tu correo está registrado en nuestro sistema, recibirás instrucciones para restablecer tu contraseña.";
        }
    } catch (Exception $e) {
        // Mensaje genérico de error
        $_SESSION['error_recuperacion'] = "Ha ocurrido un error. Por favor, intenta nuevamente más tarde.";
    }
    
    // Redireccionar a la página de recuperación
    header("Location: ../recuperar-password.php");
    exit;
} else {
    // Si alguien intenta acceder directamente a este archivo
    header("Location: ../recuperar-password.php");
    exit;
}
?>