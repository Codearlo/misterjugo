<?php
// Iniciar sesión para mensajes de error/éxito
session_start();

// Verificar si hay un mensaje de error o éxito
$mensaje_error = isset($_SESSION['error_recuperacion']) ? $_SESSION['error_recuperacion'] : '';
$mensaje_exito = isset($_SESSION['exito_recuperacion']) ? $_SESSION['exito_recuperacion'] : '';

// Limpiamos las variables de sesión después de usarlas
unset($_SESSION['error_recuperacion']);
unset($_SESSION['exito_recuperacion']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - MisterJugo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="auth-page">
    <div class="auth-container fade-in">
        <div class="auth-card slide-up">
            <div class="auth-logo">
                <img src="images/logo_mrjugo.png" alt="Logo MisterJugo" class="logo">
                <h2 class="logo-text">MISTER JUGO</h2>
                <p class="auth-subtitle">Recupera tu contraseña</p>
            </div>
            
            <a href="login.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver al inicio de sesión</a>
            
            <?php if ($mensaje_error): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <p><?php echo $mensaje_error; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($mensaje_exito): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <p><?php echo $mensaje_exito; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <form action="backend/procesar_recuperacion.php" method="POST" class="auth-form">
                <div class="form-group">
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Ingresa tu correo electrónico" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Enviar enlace de recuperación</button>
                
                <div class="auth-footer">
                    <p>¿Recordaste tu contraseña? <a href="login.php">Iniciar sesión</a></p>
                </div>
            </form>
            
            <div class="copyright">
                <p>&copy; 2025 MisterJugo. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>