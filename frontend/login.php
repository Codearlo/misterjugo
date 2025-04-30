<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

// Iniciar sesión para recuperar datos
session_start();

// Verificar si hay un mensaje de registro exitoso
$registro_exitoso = isset($_GET['registro']) && $_GET['registro'] === 'exitoso';

// Obtener el email guardado en la sesión (si existe)
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
unset($_SESSION['email']); // Limpiar después de usar
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - MisterJugo</title>
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
                <p class="auth-subtitle">Inicia sesión para hacer tu pedido</p>
            </div>
            
            <?php if ($registro_exitoso): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <p>¡Registro completado con éxito!</p>
                        <p>Ahora puedes iniciar sesión con tus credenciales.</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_login'])): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <p><?php echo $_SESSION['error_login']; ?></p>
                    </div>
                </div>
                <?php unset($_SESSION['error_login']); ?>
            <?php endif; ?>
            
            <form id="loginForm" action="backend/procesar_login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Correo electrónico" required value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Contraseña" required>
                        <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group remember-group">
                    <label>
                        <input type="checkbox" name="recordar" id="recordar">
                        <span>Recordar mi cuenta</span>
                    </label>
                    <a href="recuperar-password.php" class="forgot-password">¿Olvidaste tu contraseña?</a>
                </div>
                
                <button type="submit" class="btn-submit">Iniciar Sesión</button>
                
                <div class="auth-footer">
                    <p>¿No tienes cuenta? <a href="registro">Regístrate</a></p>
                </div>
            </form>
            
            <div class="copyright">
                <p>&copy; 2025 MisterJugo. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>

    <script>
        // Script para mostrar/ocultar contraseña
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>