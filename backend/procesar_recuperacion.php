<?php
// Versión ultraminimalista - solo registra la solicitud sin hacer nada más
session_start();

// Redirigir inmediatamente con un mensaje de éxito ficticio
$_SESSION['exito_recuperacion'] = "Hemos enviado instrucciones a tu correo electrónico para restablecer tu contraseña. (Nota: Esta es una versión de prueba, en un entorno real recibirías un correo)";

// Redireccionar a la página de recuperación
header("Location: ../recuperar-password.php");
exit;
?>