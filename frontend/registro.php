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
    <title>Registro - MisterJuco</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/form-styles.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo-container">
                <a href="index.php" class="logo">
                    <img src="images/logo.png" alt="MisterJuco Logo">
                </a>
                <h1 class="company-name"><a href="index.php">MisterJuco</a></h1>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="productos.php">Productos</a></li>
                    <li><a href="contacto.php">Contacto</a></li>
                </ul>
            </nav>
            
            <div class="actions">
                <a href="pedido.php" class="btn-order">Ordenar Ahora</a>
                <a href="login.php" class="btn-login">Iniciar Sesión</a>
            </div>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-content">
        <div class="auth-container fade-in">
            <div class="auth-card slide-up">
                <div class="auth-header">
                    <h2 class="auth-title">Crear Cuenta</h2>
                    <p class="auth-subtitle">Únete a nuestra comunidad y disfruta de beneficios exclusivos</p>
                </div>
                
                <form id="registroForm" action="backend/procesar_registro.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="nombre">Nombre completo</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="nombre" name="nombre" placeholder="Tu nombre completo" required minlength="3">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
                            <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar"></div>
                            <span class="strength-text">Fortaleza de contraseña</span>
                        </div>
                    </div>
                    
                    <div class="form-group terms-group">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">Acepto los <a href="terminos.php">términos y condiciones</a></label>
                    </div>
                    
                    <button type="submit" class="btn-submit">Registrarse</button>
                    
                    <div class="form-divider">
                        <span>o</span>
                    </div>
                    
                    <div class="social-auth">
                        <button type="button" class="btn-social google">
                            <i class="fab fa-google"></i>
                            <span>Registrarse con Google</span>
                        </button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p>¿Ya tienes cuenta? <a href="login.php">Inicia Sesión</a></p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="MisterJuco Logo" class="logo">
                    <h3>MisterJuco</h3>
                </div>
                <div class="footer-links">
                    <h4>Enlaces rápidos</h4>
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="productos.php">Productos</a></li>
                        <li><a href="contacto.php">Contacto</a></li>
                        <li><a href="terminos.php">Términos y condiciones</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contacto</h4>
                    <p><i class="fas fa-envelope"></i> info@misterjuco.com</p>
                    <p><i class="fas fa-phone"></i> +123 456 7890</p>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 MisterJuco. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Botón de volver arriba -->
    <div class="scroll-top">
        <i class="fas fa-arrow-up"></i>
    </div>

    <!-- Scripts -->
    <script src="js/auth.js"></script>
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
        
        // Script para scroll top
        const scrollTopButton = document.querySelector('.scroll-top');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollTopButton.classList.add('visible');
            } else {
                scrollTopButton.classList.remove('visible');
            }
        });
        
        scrollTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>