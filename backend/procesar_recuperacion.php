<?php
// Iniciar sesión para guardar mensajes
session_start();

// Incluir archivo de conexión a la base de datos
require_once 'conexion.php';
require_once 'funciones.php';

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtener y limpiar el correo electrónico
    $email = trim($_POST['email']);
    
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
            // Obtener datos del usuario
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Generar token único
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour')); // El token expira en 1 hora
            
            // Guardar el token en la base de datos
            $stmt = $conn->prepare("INSERT INTO recuperacion_password (email, token, expira) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expira]);
            
            // Construir el enlace de recuperación
            $enlace = "https://tudominio.com/restablecer-password.php?token=" . $token;
            
            // Enviar correo electrónico
            $asunto = "Recuperación de contraseña - MisterJugo";
            $mensaje = "Hola " . $usuario['nombre'] . ",\n\n";
            $mensaje .= "Has solicitado restablecer tu contraseña en MisterJugo. Haz clic en el siguiente enlace para crear una nueva contraseña:\n\n";
            $mensaje .= $enlace . "\n\n";
            $mensaje .= "Este enlace expirará en 1 hora.\n\n";
            $mensaje .= "Si no solicitaste este cambio, puedes ignorar este correo y tu contraseña seguirá siendo la misma.\n\n";
            $mensaje .= "Saludos,\n";
            $mensaje .= "Equipo MisterJugo";
            
            $cabeceras = "From: noreply@misterjugo.com" . "\r\n";
            
            if (mail($email, $asunto, $mensaje, $cabeceras)) {
                $_SESSION['exito_recuperacion'] = "Se ha enviado un enlace de recuperación a tu correo electrónico. Por favor revisa tu bandeja de entrada.";
            } else {
                $_SESSION['error_recuperacion'] = "Error al enviar el correo electrónico. Por favor, intenta nuevamente más tarde.";
            }
        } else {
            // No informamos si el correo existe o no por seguridad
            $_SESSION['exito_recuperacion'] = "Si tu correo está registrado en nuestro sistema, recibirás un enlace para restablecer tu contraseña.";
        }
        
    } catch (PDOException $e) {
        $_SESSION['error_recuperacion'] = "Ha ocurrido un error. Por favor, intenta nuevamente más tarde.";
        // Registrar el error en un archivo de log (para desarrollo)
        error_log("Error en recuperación de contraseña: " . $e->getMessage());
    }
    
    header("Location: ../recuperar-password.php");
    exit;
}
?>