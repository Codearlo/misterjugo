<!-- /includes/header.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MisterJugo - Jugos Naturales</title>
    <link rel="stylesheet" href="./css/styles.css"> <!-- Solo el CSS global -->
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo-container">
                <img src="images/logo_mrjugo.png" alt="Logo MisterJugo" class="logo">
                <h1 class="company-name"><a href="index.php">MISTER JUGO</a></h1>
                <nav class="main-nav">
                    <ul>
                        <li><a href="./nosotros.php">Nosotros</a></li>
                    </ul>
                </nav>
            </div>

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
        // Script para mostrar/ocultar el menú lateral
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
</body>
</html>