<!-- frontend/login.php -->
<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - MisterJuco</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card fade-in">
            <div class="auth-logo">
                <span class="logo-text">MisterJuco</span>
                <p class="auth-subtitle">Inicia sesión para continuar</p>
            </div>
            
            <form class="auth-form" action="backend/iniciar_sesion.php" method="POST">
                <div class="form-group input-with-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Correo electrónico" required>
                </div>
                
                <div class="form-group input-with-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                
                <button type="submit" class="btn-submit">Entrar</button>
            </form>
            
            <div class="auth-footer">
                <p>¿No tienes cuenta? <a href="registro">Regístrate</a></p>
            </div>
        </div>
        
        <div class="copyright">
            &copy; 2024 MisterJuco. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>