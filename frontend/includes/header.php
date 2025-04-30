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
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos para las animaciones */
        .menu-item-animated {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .menu-item-animated:hover {
            padding-left: 10px;
            color: #f8a100; /* Color naranja para hover */
        }
        
        .menu-item-animated::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #f8a100;
            transition: width 0.3s ease;
        }
        
        .menu-item-animated:hover::after {
            width: 100%;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo-container">
                <a href="/">
                    <img src="/images/logo_mrjugo.png" alt="Logo MisterJugo" class="logo">
                </a>
                <h1 class="company-name"><a href="/">MISTER JUGO</a></h1>
            </div>
            
            <!-- Navegación simplificada para móviles y escritorio -->
            <nav class="main-nav" id="main-nav">
                <!-- Se eliminó la lista con "MISTERJUGO" que no forma parte del logo -->
            </nav>

            <div class="actions">
                <a href="/ordenar" class="btn-order">Ordenar</a>

                <!-- Icono de cuenta con diferentes opciones según el estado de inicio de sesión -->
                <div class="user-icon" id="user-menu-toggle">
                    <?php if ($isLoggedIn): ?>
                        <!-- Si está logueado mostrar avatar o inicial -->
                        <div class="user-avatar"><?php echo substr($userName, 0, 1); ?></div>
                    <?php else: ?>
                        <!-- Si no está logueado mostrar icono genérico -->
                        <img src="/images/profile-icon.png" alt="Cuenta">
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
                    <li><a href="/perfil"><i class="fas fa-user"></i> Mi Perfil</a></li>
                    <li><a href="/direcciones"><i class="fas fa-map-marker-alt"></i> Mis Direcciones</a></li>
                    <li><a href="/pedidos"><i class="fas fa-shopping-bag"></i> Mis Pedidos</a></li>
                    <li><a href="/nosotros" class="menu-item-animated"><i class="fas fa-info-circle"></i> Nosotros</a></li>
                    <li><a href="#" id="logout-link" class="menu-item-animated"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                <?php else: ?>
                    <!-- Menú para usuarios no logueados -->
                    <li><a href="/login"><i class="fas fa-sign-in-alt"></i> Iniciar sesión</a></li>
                    <li><a href="/registro"><i class="fas fa-user-plus"></i> Registrarse</a></li>
                    <li><a href="/nosotros" class="menu-item-animated"><i class="fas fa-info-circle"></i> Nosotros</a></li>
                <?php endif; ?>
            </ul>
            <button class="close-btn" id="close-menu-btn">&times;</button>
        </div>

        <!-- Overlay para cuando el menú está abierto -->
        <div class="overlay" id="overlay"></div>
    </header>
    
    <!-- Script para activar las funcionalidades -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Manejo del menú lateral de usuario
            const userMenuToggle = document.getElementById('user-menu-toggle');
            const sideMenu = document.getElementById('side-menu');
            const closeMenuBtn = document.getElementById('close-menu-btn');
            const overlay = document.getElementById('overlay');
            
            if (userMenuToggle && sideMenu && closeMenuBtn && overlay) {
                userMenuToggle.addEventListener('click', function() {
                    sideMenu.classList.add('active');
                    overlay.classList.add('active');
                    document.body.style.overflow = 'hidden'; // Evitar scroll
                });
                
                function closeMenu() {
                    sideMenu.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.style.overflow = ''; // Restaurar scroll
                }
                
                closeMenuBtn.addEventListener('click', closeMenu);
                overlay.addEventListener('click', closeMenu);
            }
            
            // Manejo del cierre de sesión - CORREGIDO para evitar errores en el cierre de sesión
            const logoutLink = document.getElementById('logout-link');
            if (logoutLink) {
                logoutLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Crear una solicitud para cerrar la sesión
                    fetch('/backend/cerrar_sesion.php', {
                        method: 'POST',
                        credentials: 'include', // Asegura que las cookies se envíen con la solicitud
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            // Redirigir a la página de inicio después de cerrar sesión exitosamente
                            window.location.href = '/';
                        } else {
                            console.error('Error al cerrar sesión. Estado:', response.status);
                        }
                    })
                    .catch(error => {
                        console.error('Error en la solicitud de cierre de sesión:', error);
                    });
                });
            }
            
            // Añadir animaciones a los enlaces del menú con clase "menu-item-animated"
            const animatedMenuItems = document.querySelectorAll('.menu-item-animated');
            animatedMenuItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                });
            });
        });
    </script>