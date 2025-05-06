<?php
// Iniciar sesión para guardar mensajes
session_start();

// Incluir archivo de conexión a la base de datos
require_once 'conexion.php';

// Función para registrar errores (para depuración)
function registrar_error($mensaje) {
    // Crear un archivo de log en la carpeta backend
    $log_file = __DIR__ . '/error_log.txt';
    $fecha = date('Y-m-d H:i:s');
    $mensaje_log = "[$fecha] $mensaje\n";
    
    // Escribir en el archivo
    file_put_contents($log_file, $mensaje_log, FILE_APPEND);
}

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Registrar inicio del proceso
    registrar_error("Iniciando proceso de recuperación");
    
    // Obtener y limpiar el correo electrónico
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Verificar que el email existe
    if (empty($email)) {
        $_SESSION['error_recuperacion'] = "Por favor ingresa tu correo electrónico.";
        header("Location: ../recuperar-password.php");
        exit;
    }
    
    // Validar formato de correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_recuperacion'] = "El formato del correo electrónico no es válido.";
        header("Location: ../recuperar-password.php");
        exit;
    }
    
    try {
        // Registrar conexión a la base de datos
        registrar_error("Intentando conectar a la base de datos");
        
        // Verificar si la conexión está establecida correctamente
        if (!isset($conn) || !$conn) {
            throw new Exception("Error de conexión a la base de datos");
        }
        
        // Verificar primero si la tabla existe
        $stmt = $conn->query("SHOW TABLES LIKE 'recuperacion_password'");
        $table_exists = ($stmt->rowCount() > 0);
        
        if (!$table_exists) {
            // Crear la tabla si no existe
            registrar_error("Creando tabla de recuperación_password");
            $sql = "CREATE TABLE recuperacion_password (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                token VARCHAR(64) NOT NULL,
                expira DATETIME NOT NULL,
                usado TINYINT(1) DEFAULT 0,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $conn->exec($sql);
        }
        
        // Verificar si el correo existe en la base de datos
        $stmt = $conn->prepare("SELECT id, nombre FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // Obtener datos del usuario
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Generar token único
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour')); // El token expira en 1 hora
            
            // Eliminar tokens anteriores de este usuario
            $stmt = $conn->prepare("DELETE FROM recuperacion_password WHERE email = ?");
            $stmt->execute([$email]);
            
            // Guardar el token en la base de datos
            $stmt = $conn->prepare("INSERT INTO recuperacion_password (email, token, expira) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expira]);
            
            // Construir el enlace de recuperación
            $enlace = "https://misterjugo.codearlo.com/restablecer-password.php?token=" . $token;
            
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
            
            // Intentar enviar el correo y registrar el resultado
            $mail_enviado = mail($email, $asunto, $mensaje, $cabeceras);
            registrar_error("Intento de envío de correo a $email: " . ($mail_enviado ? "Éxito" : "Fallo"));
            
            if ($mail_enviado) {
                $_SESSION['exito_recuperacion'] = "Se ha enviado un enlace de recuperación a tu correo electrónico. Por favor revisa tu bandeja de entrada.";
            } else {
                // Si falla el envío, aún mostrar mensaje de éxito por seguridad
                // pero registrar el error internamente
                registrar_error("Error al enviar correo a $email");
                $_SESSION['exito_recuperacion'] = "Si tu correo está registrado en nuestro sistema, recibirás un enlace para restablecer tu contraseña.";
            }
        } else {
            // No informamos si el correo existe o no por seguridad
            registrar_error("Email no encontrado: $email");
            $_SESSION['exito_recuperacion'] = "Si tu correo está registrado en nuestro sistema, recibirás un enlace para restablecer tu contraseña.";
        }
        
    } catch (PDOException $e) {
        registrar_error("Error PDO: " . $e->getMessage());
        $_SESSION['error_recuperacion'] = "Ha ocurrido un error. Por favor, intenta nuevamente más tarde.";
    } catch (Exception $e) {
        registrar_error("Error general: " . $e->getMessage());
        $_SESSION['error_recuperacion'] = "Ha ocurrido un error. Por favor, intenta nuevamente más tarde.";
    }
    
    // Registrar finalización del proceso
    registrar_error("Finalizando proceso de recuperación");
    
    header("Location: ../recuperar-password.php");
    exit;
} else {
    // Si alguien intenta acceder directamente a este archivo
    header("Location: ../recuperar-password.php");
    exit;
}
?>