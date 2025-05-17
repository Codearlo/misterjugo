<?php
session_start();

// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Si no es admin, redirigir al login
    $_SESSION['error_login'] = "Debes iniciar sesión como administrador para acceder";
    header("Location: /login");
    exit;
}

// Incluir conexión a la base de datos
require_once '../conexion.php';

// Obtener estadísticas básicas
$stats = [
    'productos' => 0,
    'categorias' => 0,
    'pedidos' => 0,
    'usuarios' => 0
];

// Contar productos
$result = $conn->query("SELECT COUNT(*) as total FROM productos");
if ($result) {
    $stats['productos'] = $result->fetch_assoc()['total'];
}

// Contar categorías
$result = $conn->query("SELECT COUNT(*) as total FROM categorias");
if ($result) {
    $stats['categorias'] = $result->fetch_assoc()['total'];
}

// Contar pedidos
$result = $conn->query("SELECT COUNT(*) as total FROM pedidos");
if ($result) {
    $stats['pedidos'] = $result->fetch_assoc()['total'];
}

// Contar usuarios
$result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
if ($result) {
    $stats['usuarios'] = $result->fetch_assoc()['total'];
}

// Obtener productos recientes
$productos_recientes = [];
$result = $conn->query("SELECT p.*, c.nombre as categoria_nombre 
                       FROM productos p 
                       LEFT JOIN categorias c ON p.categoria_id = c.id 
                       ORDER BY p.id DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $productos_recientes[] = $row;
    }
}

// Obtener pedidos recientes
$pedidos_recientes = [];
$result = $conn->query("SELECT p.*, u.nombre as usuario_nombre 
                       FROM pedidos p 
                       LEFT JOIN usuarios u ON p.usuario_id = u.id 
                       ORDER BY p.fecha_pedido DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pedidos_recientes[] = $row;
    }
}

$titulo_pagina = "Panel de Administración - MisterJugo";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <img src="/images/logo_mrjugo.png" alt="MisterJugo Logo" class="admin-logo">
                        <h5 class="text-white mt-2">Panel de Administración</h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="productos.php">
                                <i class="fas fa-shopping-basket me-2"></i> Productos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categorias.php">
                                <i class="fas fa-tags me-2"></i> Categorías
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pedidos.php">
                                <i class="fas fa-shopping-cart me-2"></i> Pedidos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="usuarios.php">
                                <i class="fas fa-users me-2"></i> Usuarios
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a class="nav-link" href="/" target="_blank">
                                <i class="fas fa-external-link-alt me-2"></i> Ver Tienda
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="/backend/cerrar_sesion.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Contenido principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <span class="text-muted">Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>

                <!-- Tarjetas de estadísticas -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card shadow stats-card stats-products">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Productos</h6>
                                        <h3 class="fw-bold"><?php echo $stats['productos']; ?></h3>
                                    </div>
                                    <div class="icon-container">
                                        <i class="fas fa-shopping-basket"></i>
                                    </div>
                                </div>
                                <a href="productos.php" class="btn btn-sm btn-outline-secondary mt-3">Ver todos</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card shadow stats-card stats-categories">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Categorías</h6>
                                        <h3 class="fw-bold"><?php echo $stats['categorias']; ?></h3>
                                    </div>
                                    <div class="icon-container">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                </div>
                                <a href="categorias.php" class="btn btn-sm btn-outline-secondary mt-3">Ver todas</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card shadow stats-card stats-orders">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Pedidos</h6>
                                        <h3 class="fw-bold"><?php echo $stats['pedidos']; ?></h3>
                                    </div>
                                    <div class="icon-container">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div>
                                <a href="pedidos.php" class="btn btn-sm btn-outline-secondary mt-3">Ver todos</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card shadow stats-card stats-users">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Usuarios</h6>
                                        <h3 class="fw-bold"><?php echo $stats['usuarios']; ?></h3>
                                    </div>
                                    <div class="icon-container">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <a href="usuarios.php" class="btn btn-sm btn-outline-secondary mt-3">Ver todos</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accesos rápidos -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i> Acciones Rápidas</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="productos.php?action=new" class="btn btn-success w-100 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-plus me-2"></i> Nuevo Producto
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="categorias.php?action=new" class="btn btn-info w-100 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-plus me-2"></i> Nueva Categoría
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="pedidos.php" class="btn btn-warning w-100 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-clipboard-list me-2"></i> Ver Pedidos
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="/" target="_blank" class="btn btn-secondary w-100 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-external-link-alt me-2"></i> Ver Tienda
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos recientes -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-shopping-basket me-2"></i> Productos Recientes</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($productos_recientes) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Categoría</th>
                                                    <th>Precio</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($productos_recientes as $producto): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                                        <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                                                        <td>S/<?php echo number_format($producto['precio'], 2); ?></td>
                                                        <td>
                                                            <?php if ($producto['disponible']): ?>
                                                                <span class="badge bg-success">Disponible</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">No Disponible</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No hay productos registrados aún.</p>
                                <?php endif; ?>
                                <a href="productos.php" class="btn btn-sm btn-primary">Ver todos los productos</a>
                            </div>
                        </div>
                    </div>

                    <!-- Pedidos recientes -->
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Pedidos Recientes</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($pedidos_recientes) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Cliente</th>
                                                    <th>Total</th>
                                                    <th>Estado</th>
                                                    <th>Fecha</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pedidos_recientes as $pedido): ?>
                                                    <tr>
                                                        <td>#<?php echo $pedido['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></td>
                                                        <td>S/<?php echo number_format($pedido['total'], 2); ?></td>
                                                        <td>
                                                            <?php 
                                                            switch ($pedido['estado']) {
                                                                case 'pendiente':
                                                                    echo '<span class="badge bg-warning">Pendiente</span>';
                                                                    break;
                                                                case 'procesando':
                                                                    echo '<span class="badge bg-info">Procesando</span>';
                                                                    break;
                                                                case 'completado':
                                                                    echo '<span class="badge bg-success">Completado</span>';
                                                                    break;
                                                                case 'cancelado':
                                                                    echo '<span class="badge bg-danger">Cancelado</span>';
                                                                    break;
                                                                default:
                                                                    echo '<span class="badge bg-secondary">'. ucfirst($pedido['estado']) .'</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No hay pedidos registrados aún.</p>
                                <?php endif; ?>
                                <a href="pedidos.php" class="btn btn-sm btn-primary">Ver todos los pedidos</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Admin JS -->
    <script src="js/admin.js"></script>
</body>
</html>