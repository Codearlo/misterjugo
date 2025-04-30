<!-- Este archivo debe estar en /includes/header.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MisterJugo - Jugos Naturales</title>
    <link rel="stylesheet" href="./css/styles.css">
    <!-- Font Awesome para íconos del menú móvil -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo-container">
                <a href="https://misterjugo.codearlo.com">
                    <img src="images/logo_mrjugo.png" alt="Logo MisterJugo" class="logo">
                </a>
                <h1 class="company-name"><a href="https://misterjugo.codearlo.com">MISTER JUGO</a></h1>
                
                <!-- Botón menú hamburguesa para móviles -->
                <button class="main-nav-toggle" id="main-nav-toggle" aria-label="Menú de navegación">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <nav class="main-nav" id="main-nav">
                <ul>
                    <li><a href="./nosotros.php">Nosotros</a></li>
                </ul>
            </nav>

            <div class="actions">
                <button class="btn-order">Ordenar</button>

                <!-- Icono de cuenta -->
                <div class="user-icon" id="user-menu-toggle">
                    <img src="images/profile-icon.png" alt="Cuenta">
                </div>
            </div>
        </div>

        <!-- Menú lateral que aparece al hacer clic en el icono de cuenta -->
        <div class="side-menu" id="side-menu">
            <ul>
                <li><a href="login.php">Iniciar sesión</a></li>
                <li><a href="registro.php">Registrarse</a></li>
            </ul>
            <button class="close-btn" id="close-menu-btn">&times;</button>
        </div>
    </header>
    
    <!-- Script para activar las funcionalidades -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejo del menú lateral de usuario
            const userMenuToggle = document.getElementById('user-menu-toggle');
            const sideMenu = document.getElementById('side-menu');
            const closeMenuBtn = document.getElementById('close-menu-btn');
            
            if (userMenuToggle && sideMenu && closeMenuBtn) {
                userMenuToggle.addEventListener('click', function() {
                    sideMenu.classList.add('active');
                });
                
                closeMenuBtn.addEventListener('click', function() {
                    sideMenu.classList.remove('active');
                });
            }
            
            // Manejo del menú de navegación en móviles
            const mainNavToggle = document.getElementById('main-nav-toggle');
            const mainNav = document.getElementById('main-nav');
            
            if (mainNavToggle && mainNav) {
                mainNavToggle.addEventListener('click', function() {
                    mainNav.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>