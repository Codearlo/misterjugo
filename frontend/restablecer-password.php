<?php
// Iniciar sesión para mensajes
session_start();

// Incluir archivo de conexión a la base de datos
require_once 'backend/conexion.php';

// Verificar si se proporcionó un token
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $_SESSION['error_login'] = "Enlace de recuperación inválido.";
    header("Location: login.php");
    exit;
}

$token = $_GET['token'];
$token_valido = false;
$email = '';

try {
    // Verificar si el token existe y es válido
    $ahora = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("SELECT email FROM recuperacion_password WHERE token = ? AND expira > ? AND usado = 0");
    $stmt->execute([$token, $ahora]);
    
    if ($stmt->rowCount() > 0) {
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $email = $resultado['email'];
        $token_valido = true;
    }
} catch (Exception $e) {
    // Token inválido
    $token_valido = false;
}

// Si el token no es válido, redirigir al login
if (!$token_valido) {
    $_SESSION['error_login'] = "El enlace de recuperación no es válido o ha expirado.";
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - MisterJugo</title>
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
                <p class="auth-subtitle">Establece tu nueva contraseña</p>
            </div>
            
            <?php if (isset($_SESSION['error_restablecer'])): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <p><?php echo $_SESSION['error_restablecer']; ?></p>
                    </div>
                </div>
                <?php unset($_SESSION['error_restablecer']); ?>
            <?php endif; ?>
            
            <form action="backend/procesar_restablecer.php" method="POST" class="auth-form" id="resetForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                
                <div class="form-group">
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Nueva contraseña" required>
                        <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmar contraseña" required>
                        <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Cambiar Contraseña</button>
            </form>
            
            <div class="copyright">
                <p>&copy; 2025 MisterJugo. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>

    <script>
        // Script para mostrar/ocultar contraseña
        document.querySelectorAll('.toggle-password').forEach(function(button) {
            button.addEventListener('click', function() {
                const passwordInput = this.parentNode.querySelector('input');
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
        });
        
        // Validación de contraseñas
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres');
            }
        });
    </script>
</body>
</html>