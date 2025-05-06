<?php
// procesar_recuperacion.php sin envío de correos
session_start();

try {
    // Incluir conexión
    require_once 'conexion.php';
    
    // Obtener el email
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_recuperacion'] = "Correo electrónico inválido";
        header("Location: ../recuperar-password.php");
        exit;
    }
    
    // Verificar si el usuario existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        // Generar token
        $token = bin2hex(random_bytes(16)); // 32 caracteres
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Crear tabla si no existe
        $conn->exec("CREATE TABLE IF NOT EXISTS recuperacion_password (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            token VARCHAR(64) NOT NULL,
            expira DATETIME NOT NULL,
            usado TINYINT(1) DEFAULT 0,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Eliminar tokens anteriores
        $stmt = $conn->prepare("DELETE FROM recuperacion_password WHERE email = ?");
        $stmt->execute([$email]);
        
        // Guardar nuevo token
        $stmt = $conn->prepare("INSERT INTO recuperacion_password (email, token, expira) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expira]);
        
        // En lugar de enviar correo, mostrar el enlace directamente (solo para pruebas)
        $_SESSION['exito_recuperacion'] = "Enlace de recuperación (solo para pruebas): <a href='../restablecer-password.php?token=$token'>Restablecer contraseña</a>";
    } else {
        // Usuario no encontrado
        $_SESSION['exito_recuperacion'] = "Si tu correo está registrado, recibirás instrucciones para recuperar tu contraseña.";
    }
    
} catch (Exception $e) {
    // Guardar el error en un archivo
    file_put_contents(__DIR__ . '/error_log.txt', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n", FILE_APPEND);
    
    $_SESSION['error_recuperacion'] = "Ha ocurrido un error. Por favor, intenta nuevamente más tarde.";
}

header("Location: ../recuperar-password.php");
exit;
?>