<?php
// Definir variables para el header
$titulo_pagina = "Dashboard";
$breadcrumbs = [
    ['texto' => 'Dashboard']
];

// Incluir el header
require_once 'includes/admin_header.php';

// Obtener mensajes de estado si existen
$exito = isset($_SESSION['admin_exito']) ? $_SESSION['admin_exito'] : '';
$error = isset($_SESSION['admin_error']) ? $_SESSION['admin_error'] : '';
unset($_SESSION['admin_exito'], $_SESSION['admin_error']);

// Código para obtener estadísticas
try {
    // Ejemplo básico: contar el número de usuarios
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
    $stmt->execute();
    $usuarios_total = $stmt->get_result()->fetch_assoc()['total'];
    
    // Ejemplo básico: contar el número de productos
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM productos");
    $stmt->execute();
    $productos_total = $stmt->get_result()->fetch_assoc()['total'];
    
    // Obtener conteo de pedidos por estado
    $estados_conteo = [];
    $sql_conteo = "SELECT estado, COUNT(*) as total FROM pedidos GROUP BY estado";
    $result_conteo = $conn->query($sql_conteo);
    while ($row = $result_conteo->fetch_assoc()) {
        $estados_conteo[$row['estado']] = $row['total'];
    }
    
    // Agregar conteo total de pedidos
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos");
    $stmt->execute();
    $pedidos_total = $stmt->get_result()->fetch_assoc()['total'];
    
    // Obtener pedidos pendientes (los más recientes, limitado a 5)
    $stmt = $conn->prepare("SELECT p.*, u.nombre as usuario_nombre, u.email as usuario_email 
                           FROM pedidos p 
                           JOIN usuarios u ON p.usuario_id = u.id 
                           WHERE p.estado = 'pendiente' 
                           ORDER BY p.fecha_pedido DESC 
                           LIMIT 5");
    $stmt->execute();
    $pedidos_pendientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    echo "<div class='notification danger'><i class='fas fa-exclamation-circle'></i><span>Error al obtener datos: " . $e->getMessage() . "</span></div>";
}
?>

<!-- Estilos especiales para el dashboard -->
<style>
/* Aquí puedes incluir los estilos CSS que te proporcioné anteriormente */
.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    padding: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.12);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background-color: rgba(255, 122, 0, 0.1);
    color: var(--admin-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-icon.users {
    background-color: rgba(33, 150, 243, 0.1);
    color: #2196F3;
}

.stat-icon.products {
    background-color: rgba(76, 175, 80, 0.1);
    color: #4CAF50;
}

.stat-icon.orders {
    background-color: rgba(255, 152, 0, 0.1);
    color: #FF9800;
}

.stat-info {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.2;
    color: var(--admin-dark);
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--admin-medium);
    font-weight: 500;
}

/* Pedidos recientes */
.recent-orders {
    margin-bottom: 30px;
}

.orders-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.orders-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--admin-dark);
    margin: 0;
}

.view-all {
    font-size: 0.9rem;
    color: var(--admin-primary);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: color 0.2s ease;
}

.view-all:hover {
    color: var(--admin-primary-dark);
}

/* Accesos rápidos */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.action-card {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    display: block;
}

.action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.12);
}

.action-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: rgba(255, 122, 0, 0.1);
    color: var(--admin-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin: 0 auto 15px;
}

.action-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--admin-dark);
    margin-bottom: 5px;
}

.action-description {
    font-size: 0.85rem;
    color: var(--admin-medium);
}

@media (max-width: 768px) {
    .stats-overview {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .stat-card {
        padding: 15px;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .stats-overview {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Notificaciones -->
<?php if (!empty($exito)): ?>
    <div class="notification success">
        <i class="fas fa-check-circle"></i>
        <span><?php echo $exito; ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="notification danger">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo $error; ?></span>
    </div>
<?php endif; ?>

<!-- Bienvenida al dashboard -->
<div class="welcome-message" style="margin-bottom: 25px;">
    <h1 style="font-size: 1.8rem; margin-top: 0; color: var(--admin-dark);">¡Bienvenido al panel de administración!</h1>
    <p style="color: var(--admin-medium);">Desde aquí puedes gestionar todos los aspectos de tu negocio Mister Jugo.</p>
</div>

<!-- Tarjetas de estadísticas -->
<div class="stats-overview">
    <div class="stat-card">
        <div class="stat-icon users">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo isset($usuarios_total) ? $usuarios_total : 0; ?></div>
            <div class="stat-label">Usuarios</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon products">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo isset($productos_total) ? $productos_total : 0; ?></div>
            <div class="stat-label">Productos</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orders">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo isset($pedidos_total) ? $pedidos_total : 0; ?></div>
            <div class="stat-label">Pedidos Totales</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background-color: rgba(244, 67, 54, 0.1); color: #F44336;">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo isset($estados_conteo['pendiente']) ? $estados_conteo['pendiente'] : 0; ?></div>
            <div class="stat-label">Pedidos Pendientes</div>
        </div>
    </div>
</div>

<!-- Accesos rápidos -->
<h2 style="font-size: 1.4rem; margin-top: 30px; margin-bottom: 15px; color: var(--admin-dark);">Acciones rápidas</h2>
<div class="quick-actions">
    <a href="productos.php" class="action-card">
        <div class="action-icon">
            <i class="fas fa-plus"></i>
        </div>
        <div class="action-title">Añadir Producto</div>
        <div class="action-description">Crear un nuevo producto en el catálogo</div>
    </a>
    
    <a href="pedidos.php" class="action-card">
        <div class="action-icon" style="background-color: rgba(255, 152, 0, 0.1); color: #FF9800;">
            <i class="fas fa-list"></i>
        </div>
        <div class="action-title">Gestionar Pedidos</div>
        <div class="action-description">Ver y procesar los pedidos pendientes</div>
    </a>
    
    <a href="categorias.php" class="action-card">
        <div class="action-icon" style="background-color: rgba(76, 175, 80, 0.1); color: #4CAF50;">
            <i class="fas fa-tags"></i>
        </div>
        <div class="action-title">Categorías</div>
        <div class="action-description">Administrar categorías de productos</div>
    </a>
    
    <a href="usuarios.php" class="action-card">
        <div class="action-icon" style="background-color: rgba(33, 150, 243, 0.1); color: #2196F3;">
            <i class="fas fa-user"></i>
        </div>
        <div class="action-title">Usuarios</div>
        <div class="action-description">Gestionar usuarios de la plataforma</div>
    </a>
</div>

<!-- Pedidos recientes -->
<div class="recent-orders">
    <div class="orders-header">
        <h2 class="orders-title">Pedidos Pendientes</h2>
        <a href="pedidos.php?estado=pendiente" class="view-all">
            Ver todos <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pedidos_pendientes)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px;">
                            <i class="fas fa-check-circle" style="font-size: 2rem; color: #4CAF50; margin-bottom: 10px;"></i>
                            <p style="margin: 0;">¡No hay pedidos pendientes!</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pedidos_pendientes as $pedido): ?>
                        <tr>
                            <td>#<?php echo $pedido['id']; ?></td>
                            <td>
                                <div class="customer-info">
                                    <span class="customer-name"><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></span>
                                    <span class="customer-email"><?php echo htmlspecialchars($pedido['usuario_email']); ?></span>
                                </div>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                            <td>S/<?php echo number_format($pedido['total'], 2); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="ver_pedido.php?id=<?php echo $pedido['id']; ?>" class="btn btn-primary btn-sm btn-icon" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="procesar_pedido.php?action=update_status&id=<?php echo $pedido['id']; ?>&status=procesando" class="btn btn-info btn-sm btn-icon" title="Marcar como En Proceso">
                                        <i class="fas fa-cog"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Incluir el footer
require_once 'includes/admin_footer.php';
?>