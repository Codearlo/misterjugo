<?php
// Iniciar sesión
session_start();

// Incluir conexión a la base de datos
require_once '../backend/conexion.php';

// Variables para mensajes
$mensaje = '';
$tipo_mensaje = '';

// Variables para el formulario de restablecer
$mostrar_form_reset = false;
$email_reset = '';
$token_reset = '';

// Verificar si se está solicitando restablecer contraseña (con token)
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Verificar si el token es válido
        $ahora = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("SELECT email FROM recuperacion_password WHERE token = ? AND expira > ? AND usado = 0");
        $stmt->execute([$token, $ahora]);
        
        if ($stmt->rowCount() > 0) {
            // Token válido, mostrar formulario de reset
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            $email_reset = $resultado['email'];
            $token_reset = $token;
            $mostrar_form_reset = true;
        } else {
            // Token inválido
            $mensaje = "El enlace de recuperación no es válido o ha expirado.";
            $tipo_mensaje = "error";
        }
    } catch (Exception $e) {
        $mensaje = "Ha ocurrido un error al verificar el token.";
        $tipo_mensaje = "error";
    }
}

// Procesar solicitud de recuperación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && !isset($_POST['password'])) {
    $email = trim($_POST['email']);
    
    // Validar formato de correo
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El formato del correo electrónico no es válido.";
        $tipo_mensaje = "error";
    } else {
        try {
            // Verificar si el correo existe
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                // Generar token
                $token = bin2hex(random_bytes(16));
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
                
                // Mostrar enlace (en producción enviarías correo)
                $mensaje = "Se ha generado un enlace de recuperación: <a href='recuperar-password-simple.php?token=" . $token . "'>Haz clic aquí para restablecer tu contraseña</a>";
                $tipo_mensaje = "success";
            } else {
                // Usuario no encontrado (mensaje genérico por seguridad)
                $mensaje = "Si tu correo está registrado en nuestro sistema, recibirás instrucciones para restablecer tu contraseña.";
                $tipo_mensaje = "success";
            }
        } catch (Exception $e) {
            $mensaje = "Ha ocurrido un error. Por favor, intenta nuevamente más tarde.";
            $tipo_mensaje = "error";
        }
    }
}

// Procesar cambio de contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password']) && isset($_POST['token']) && isset($_POST['email'])) {
    $token = trim($_POST['token']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validaciones básicas
    if (empty($password) || empty($confirm_password)) {
        $mensaje = "Todos los campos son obligatorios.";
        $tipo_mensaje = "error";
        $mostrar_form_reset = true;
        $email_reset = $email;
        $token_reset = $token;
    } elseif ($password !== $confirm_password) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo_mensaje = "error";
        $mostrar_form_reset = true;
        $email_reset = $email;
        $token_reset = $token;
    } elseif (strlen($password) < 8) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres.";
        $tipo_mensaje = "error";
        $mostrar_form_reset = true;
        $email_reset = $email;
        $token_reset = $token;
    } else {
        try {
            // Verificar token
            $ahora = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("SELECT id FROM recuperacion_password WHERE token = ? AND email = ? AND expira > ? AND usado = 0");
            $stmt->execute([$token, $email, $ahora]);
            
            if ($stmt->rowCount() > 0) {
                // Actualizar contraseña
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
                $stmt->execute([$password_hash, $email]);
                
                // Marcar token como usado
                $stmt = $conn->prepare("UPDATE recuperacion_password SET usado = 1 WHERE token = ?");
                $stmt->execute([$token]);
                
                $mensaje = "¡Tu contraseña ha sido actualizada correctamente! <a href='login.php'>Haz clic aquí para iniciar sesión</a>";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "El enlace de recuperación no es válido o ha expirado.";
                $tipo_mensaje = "error";
            }
        } catch (Exception $e) {
            $mensaje = "Ha ocurrido un error al cambiar la contraseña. Por favor, intenta nuevamente más tarde.";
            $tipo_mensaje = "error";
            $mostrar_form_reset = true;
            $email_reset = $email;
            $token_reset = $token;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $mostrar_form_reset ? 'Restablecer Contraseña' : 'Recuperar Contraseña'; ?> - MisterJugo</title>
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
                <p class="auth-subtitle"><?php echo $mostrar_form_reset ? 'Establece tu nueva contraseña' : 'Recupera tu contraseña'; ?></p>
            </div>
            
            <a href="login.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver al inicio de sesión</a>
            
            <?php if (!empty($mensaje)): ?>
                <div class="notification <?php echo $tipo_mensaje; ?>">
                    <i class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check' : 'exclamation'; ?>-circle"></i>
                    <div>
                        <p><?php echo $mensaje; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($mostrar_form_reset): ?>
                <!-- Formulario para restablecer contraseña -->
                <form action="recuperar-password-simple.php" method="POST" class="auth-form" id="resetForm">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token_reset); ?>">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email_reset); ?>">
                    
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
            <?php else: ?>
                <!-- Formulario para solicitar recuperación -->
                <form action="recuperar-password-simple.php" method="POST" class="auth-form">
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
            <?php endif; ?>
            
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
        
        // Validación de contraseñas (solo para el formulario de reset)
        const resetForm = document.getElementById('resetForm');
        if (resetForm) {
            resetForm.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden');
                    return false;
                }
                
                if (password.length < 8) {
                    e.preventDefault();
                    alert('La contraseña debe tener al menos 8 caracteres');
                    return false;
                }
            });
        }
    </script>
</body>
</html>