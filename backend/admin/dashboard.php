<?php
// Definir variables para el header
$titulo_pagina = "Dashboard";
$breadcrumbs = [
    ['texto' => 'Dashboard']
];

// Incluir el header
require_once 'includes/admin_header.php';

// Incluir estilos específicos para el dashboard
echo '<link rel="stylesheet" href="css/dashboard-styles.css">';

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
<div class="welcome-message">
    <h1>¡Bienvenido al panel de administración!</h1>
    <p>Desde aquí puedes gestionar todos los aspectos de tu negocio Mister Jugo.</p>
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
        <div class="stat-icon pending">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo isset($estados_conteo['pendiente']) ? $estados_conteo['pendiente'] : 0; ?></div>
            <div class="stat-label">Pedidos Pendientes</div>
        </div>
    </div>
</div>

<!-- Acciones rápidas -->
<h2 class="actions-section-title">Acciones rápidas</h2>
<div class="quick-actions">
    <a href="editar_producto.php" class="action-card">
        <div class="action-icon add-product">
            <i class="fas fa-plus"></i>
        </div>
        <div class="action-title">Añadir Producto</div>
        <div class="action-description">Crear un nuevo producto en el catálogo</div>
    </a>
    
    <a href="pedidos.php?estado=pendiente" class="action-card">
        <div class="action-icon manage-orders">
            <i class="fas fa-list-alt"></i>
        </div>
        <div class="action-title">Gestionar Pedidos</div>
        <div class="action-description">Ver y procesar los pedidos pendientes</div>
    </a>
    
    <a href="categorias.php" class="action-card">
        <div class="action-icon categories">
            <i class="fas fa-tags"></i>
        </div>
        <div class="action-title">Categorías</div>
        <div class="action-description">Administrar categorías de productos</div>
    </a>
    
    <a href="usuarios.php" class="action-card">
        <div class="action-icon users">
            <i class="fas fa-user-cog"></i>
        </div>
        <div class="action-title">Usuarios</div>
        <div class="action-description">Gestionar usuarios de la plataforma</div>
    </a>
</div>

<!-- Pedidos Pendientes -->
<div class="pending-orders-section">
    <div class="section-header">
        <h2 class="section-title">Pedidos Pendientes</h2>
        <a href="pedidos.php?estado=pendiente" class="view-all-link">
            Ver todos <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    
    <div class="orders-table-container">
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
                        <td colspan="5" class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p>¡No hay pedidos pendientes!</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pedidos_pendientes as $pedido): ?>
                        <tr>
                            <td>#<?php echo $pedido['id']; ?></td>
                            <td>
                                <div>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></div>
                                    <div style="font-size: 0.85rem; color: #777;"><?php echo htmlspecialchars($pedido['usuario_email']); ?></div>
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