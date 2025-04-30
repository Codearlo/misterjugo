<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

// Iniciar sesión para recuperar mensajes de error o datos de formulario
session_start();

// Recuperar errores si existen
$errores = isset($_SESSION['errores_registro']) ? $_SESSION['errores_registro'] : [];

// Recuperar datos previos del formulario si existen
$datos_form = isset($_SESSION['datos_form']) ? $_SESSION['datos_form'] : [
    'nombre' => '',
    'email' => ''
];

// Limpiar datos de sesión para que no persistan
unset($_SESSION['errores_registro']);
unset($_SESSION['datos_form']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - MisterJugo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/registro.css">
</head>
<body class="auth-page">
    <div class="auth-container fade-in">
        <div class="auth-card slide-up">
            <div class="auth-logo">
                <img src="images/logo_mrjugo.png" alt="Logo MisterJugo" class="logo">
                <h2 class="logo-text">MISTER JUGO</h2>
                <p class="auth-subtitle">Regístrate y disfruta de los mejores jugos naturales</p>
            </div>
            
            <a href="javascript:history.back()" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
            
            <?php if (!empty($errores)): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <?php foreach($errores as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <form id="registroForm" action="backend/procesar_registro.php" method="POST" class="auth-form">
                <div class="form-group">
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="nombre" name="nombre" placeholder="Nombre completo" required minlength="3" maxlength="100" value="<?php echo htmlspecialchars($datos_form['nombre']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Correo electrónico" required maxlength="100" value="<?php echo htmlspecialchars($datos_form['email']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Contraseña" required minlength="6" maxlength="255">
                        <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar"></div>
                        <span class="strength-text">Fortaleza de contraseña</span>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Registrarse</button>
                
                <div class="auth-footer">
                    <p>¿Ya tienes cuenta? <a href="login">Inicia Sesión</a></p>
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
        
        // Script para medir fortaleza de contraseña
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.querySelector('.strength-bar');
            const strengthText = document.querySelector('.strength-text');
            
            // Calcular fortaleza (simple)
            let strength = 0;
            if (password.length >= 8) strength += 25;
            if (password.match(/[A-Z]/)) strength += 25;
            if (password.match(/[0-9]/)) strength += 25;
            if (password.match(/[^A-Za-z0-9]/)) strength += 25;
            
            // Actualizar barra y texto
            strengthBar.style.width = strength + '%';
            
            if (strength <= 25) {
                strengthBar.style.backgroundColor = '#ff4d4d';
                strengthText.textContent = 'Débil';
            } else if (strength <= 50) {
                strengthBar.style.backgroundColor = '#ffa64d';
                strengthText.textContent = 'Moderada';
            } else if (strength <= 75) {
                strengthBar.style.backgroundColor = '#ffff4d';
                strengthText.textContent = 'Buena';
            } else {
                strengthBar.style.backgroundColor = '#4dff4d';
                strengthText.textContent = 'Fuerte';
            }
        });
    </script>
</body>
</html>