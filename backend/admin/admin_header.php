<?php
// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    $_SESSION['admin_error'] = "Debes iniciar sesión como administrador para acceder";
    header("Location: index.php");
    exit;
}

// Incluir conexión a la base de datos
require_once '../conexion.php';

// Obtener cantidad de pedidos pendientes para notificación
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'");
$stmt->execute();
$result = $stmt->get_result();
$pedidos_pendientes = $result->fetch_assoc()['total'];

// Definir variables para breadcrumbs y título si no están definidas
if (!isset($titulo_pagina)) {
    $titulo_pagina = "Panel de Administración";
}

if (!isset($breadcrumbs)) {
    $breadcrumbs = [];
}

// Determinar la página actual basado en el nombre del archivo
$pagina_actual = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?> - MisterJugo Admin</title>
    <!-- Archivos CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-styles.css">
</head>
<body class="admin-panel">
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="../../images/logo_mrjugo.png" alt="MisterJugo Logo" class="logo">
                <h2>Panel Admin</h2>
                <button id="close-sidebar" class="btn-close-sidebar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="admin-profile">
                <div class="admin-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="admin-info">
                    <p class="admin-name"><?php echo $_SESSION['admin_name']; ?></p>
                    <p class="admin-role">Administrador</p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="<?php echo $pagina_actual === 'dashboard' ? 'active' : ''; ?>">
                        <a href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="<?php echo $pagina_actual === 'pedidos' || $pagina_actual === 'ver_pedido' ? 'active' : ''; ?>">
                        <a href="pedidos.php">
                            <i class="fas fa-shopping-basket"></i>
                            <span>Pedidos</span>
                            <?php if ($pedidos_pendientes > 0): ?>
                                <span class="badge"><?php echo $pedidos_pendientes; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="<?php echo $pagina_actual === 'productos' || $pagina_actual === 'editar_producto' ? 'active' : ''; ?>">
                        <a href="productos.php">
                            <i class="fas fa-carrot"></i>
                            <span>Productos</span>
                        </a>
                    </li>
                    <li class="<?php echo $pagina_actual === 'categorias' || $pagina_actual === 'editar_categoria' ? 'active' : ''; ?>">
                        <a href="categorias.php">
                            <i class="fas fa-tags"></i>
                            <span>Categorías</span>
                        </a>
                    </li>
                    <li class="<?php echo $pagina_actual === 'usuarios' ? 'active' : ''; ?>">
                        <a href="usuarios.php">
                            <i class="fas fa-users"></i>
                            <span>Usuarios</span>
                        </a>
                    </li>
                    <li class="separator"></li>
                    <li>
                        <a href="cerrar_sesion.php" class="logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Cerrar Sesión</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Contenido principal -->
        <main class="main-content">
            <header class="top-header">
                <div class="toggle-sidebar" id="toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </div>
                <h1 class="page-title"><?php echo $titulo_pagina; ?></h1>
                <div class="actions">
                    <a href="/" class="visit-site" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Ver Sitio</span>
                    </a>
                    <div class="admin-dropdown">
                        <button class="dropdown-toggle">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="#">
                                <i class="fas fa-user-cog"></i>
                                <span>Mi Perfil</span>
                            </a>
                            <a href="cerrar_sesion.php">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="content-wrapper">
                <!-- Breadcrumbs -->
                <div class="breadcrumbs">
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                    </a>
                    <span class="separator">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                    <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                        <?php foreach($breadcrumbs as $index => $crumb): ?>
                            <?php if ($index > 0): ?>
                                <span class="separator">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (isset($crumb['url'])): ?>
                                <a href="<?php echo $crumb['url']; ?>">
                                    <?php echo $crumb['texto']; ?>
                                </a>
                            <?php else: ?>
                                <span class="active">
                                    <?php echo $crumb['texto']; ?>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Contenido de la página -->
                <div class="content-container">