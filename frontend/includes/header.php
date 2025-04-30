<?php
// Comprobar si el usuario está logueado
session_start();
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MisterJugo - Jugos Naturales</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo-container">
                <a href="https://misterjugo.codearlo.com">
                    <img src="../images/logo_mrjugo.png" alt="Logo MisterJugo" class="logo">
                </a>
                <h1 class="company-name"><a href="https://misterjugo.codearlo.com">MISTER JUGO</a></h1>
            </div>
            
            <!-- Navegación siempre visible (no desplegable) -->
            <nav class="main-nav" id="main-nav">
                <ul>
                    <li><a href="./nosotros.php">Nosotros</a></li>
                    <?php if ($isLoggedIn): ?>
                    <li><a href="./pedidos.php">Pedidos</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="actions">
                <button class="btn-order">Ordenar</button>

                <!-- Icono de cuenta con diferentes opciones según el estado de inicio de sesión -->
                <div class="user-icon" id="user-menu-toggle">
                    <?php if ($isLoggedIn): ?>
                        <!-- Si está logueado mostrar avatar o inicial -->
                        <div class="user-avatar"><?php echo substr($userName, 0, 1); ?></div>
                    <?php else: ?>
                        <!-- Si no está logueado mostrar icono genérico -->
                        <img src="../images/profile-icon.png" alt="Cuenta">
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Menú lateral que aparece al hacer clic en el icono de cuenta -->
        <div class="side-menu" id="side-menu">
            <ul>
                <?php if ($isLoggedIn): ?>
                    <!-- Menú para usuarios logueados -->
                    <li class="user-welcome">Hola, <?php echo $userName; ?></li>
                    <li><a href="./perfil.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
                    <li><a href="./pedidos.php"><i class="fas fa-shopping-bag"></i> Mis Pedidos</a></li>
                    <li><a href="./logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                <?php else: ?>
                    <!-- Menú para usuarios no logueados -->
                    <li><a href="./login.php"><i class="fas fa-sign-in-alt"></i> Iniciar sesión</a></li>
                    <li><a href="./registro.php"><i class="fas fa-user-plus"></i> Registrarse</a></li>
                <?php endif; ?>
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
        });
    </script>