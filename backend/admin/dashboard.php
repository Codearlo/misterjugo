<?php
// Incluir la conexión a la base de datos
require_once __DIR__ . '/../conexion.php';

// Incluir el header de administración

// El resto del código de dashboard.php
try {
    // Usuarios totales
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
    $stmt->execute();
    $usuarios_total = $stmt->get_result()->fetch_assoc()['total'];

    // Productos totales
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM productos");
    $stmt->execute();
    $productos_total = $stmt->get_result()->fetch_assoc()['total'];

    // Pedidos totales
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos");
    $stmt->execute();
    $pedidos_total = $stmt->get_result()->fetch_assoc()['total'];

    // Ingresos totales
    $stmt = $conn->prepare("SELECT SUM(total) as total FROM pedidos WHERE estado != 'cancelado'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $ingresos_total = $row['total'] ?? 0;

    // Estadísticas de pedidos por estado
    $stmt = $conn->prepare("SELECT estado, COUNT(*) as total FROM pedidos GROUP BY estado");
    $stmt->execute();
    $result = $stmt->get_result();
    $estados_pedidos = [];
    while ($row = $result->fetch_assoc()) {
        $estados_pedidos[$row['estado']] = $row['total'];
    }

    // Pedidos recientes (últimos 5)
    $stmt = $conn->prepare("
        SELECT p.id, p.fecha_pedido, p.total, p.estado, u.nombre as usuario
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.fecha_pedido DESC
        LIMIT 5
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $pedidos_recientes = [];
    while ($row = $result->fetch_assoc()) {
        $pedidos_recientes[] = $row;
    }

    // Productos más vendidos
    $stmt = $conn->prepare("
        SELECT p.id, p.nombre, p.precio, COUNT(dp.id) as vendidos
        FROM productos p
        JOIN detalle_pedidos dp ON p.id = dp.producto_id
        JOIN pedidos ped ON dp.pedido_id = ped.id
        WHERE ped.estado != 'cancelado'
        GROUP BY p.id
        ORDER BY vendidos DESC
        LIMIT 5
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $productos_populares = [];
    while ($row = $result->fetch_assoc()) {
        $productos_populares[] = $row;
    }
} catch (Exception $e) {
    // Si hay algún error, asignar valores por defecto
    $usuarios_total = 0;
    $productos_total = 0;
    $pedidos_total = 0;
    $ingresos_total = 0;
    $estados_pedidos = [];
    $pedidos_recientes = [];
    $productos_populares = [];

    // Opcional: Registrar el error (si tienes acceso a logs)
    error_log("Error en dashboard.php: " . $e->getMessage());
}

// Función para formatear fecha
function formatearFecha($fecha) {
    return date('d/m/Y H:i', strtotime($fecha));
}

// Función para formatear estado
function formatearEstado($estado) {
    switch ($estado) {
        case 'pendiente':
            return 'Pendiente';
        case 'procesando':
            return 'En proceso';
        case 'completado':
            return 'Completado';
        case 'cancelado':
            return 'Cancelado';
        default:
            return ucfirst($estado);
    }
}

// Función para obtener clase CSS según estado del pedido
function getEstadoClass($estado) {
    switch ($estado) {
        case 'pendiente':
            return 'warning';
        case 'procesando':
            return 'info';
        case 'completado':
            return 'success';
        case 'cancelado':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>

<div class="content-wrapper">
    <div class="breadcrumbs">
        <a href="dashboard.php">
            <i class="fas fa-home"></i>
        </a>
        <span class="separator">
            <i class="fas fa-chevron-right"></i>
        </span>
        <span class="active">
            Dashboard
        </span>
    </div>

    <div class="content-container">
        <div class="card-grid">
            <div class="card">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $usuarios_total; ?></div>
                        <div class="stat-label">Usuarios Registrados</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-carrot"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $productos_total; ?></div>
                        <div class="stat-label">Productos</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $pedidos_total; ?></div>
                        <div class="stat-label">Pedidos Totales</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">S/<?php echo number_format($ingresos_total, 2); ?></div>
                        <div class="stat-label">Ingresos Totales</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div class="card" style="flex: 1;">
                <div class="card-header">
                    <h3 class="card-title">Pedidos Recientes</h3>
                    <div class="card-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($pedidos_recientes)): ?>
                        <p class="empty-message">No hay pedidos recientes.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pedidos_recientes as $pedido): ?>
                                        <tr>
                                            <td>#<?php echo $pedido['id']; ?></td>
                                            <td><?php echo htmlspecialchars($pedido['usuario']); ?></td>
                                            <td><?php echo formatearFecha($pedido['fecha_pedido']); ?></td>
                                            <td>S/<?php echo number_format($pedido['total'], 2); ?></td>
                                            <td>
                                                <span class="badge <?php echo getEstadoClass($pedido['estado']); ?>">
                                                    <?php echo formatearEstado($pedido['estado']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="ver_pedido.php?id=<?php echo $pedido['id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="text-align: right; margin-top: 10px;">
                            <a href="pedidos.php" class="btn btn-secondary btn-sm">
                                Ver todos los pedidos <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card" style="flex: 1;">
                <div class="card-header">
                    <h3 class="card-title">Productos Más Vendidos</h3>
                    <div class="card-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($productos_populares)): ?>
                        <p class="empty-message">No hay datos de ventas disponibles.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                        <th>Unidades Vendidas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productos_populares as $producto): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                            <td>S/<?php echo number_format($producto['precio'], 2); ?></td>
                                            <td>
                                                <strong><?php echo $producto['vendidos']; ?></strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="text-align: right; margin-top: 10px;">
                            <a href="productos.php" class="btn btn-secondary btn-sm">
                                Ver todos los productos <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Estado de los Pedidos</h3>
                <div class="card-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
            </div>
            <div class="card-body">
                <div class="order-status-summary" style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div class="status-box" style="flex: 1; min-width: 150px; padding: 15px; border-radius: 8px; background-color: rgba(255, 152, 0, 0.15); color: #FF9800; text-align: center;">
                        <h4 style="margin: 0 0 10px 0;">Pendientes</h4>
                        <div style="font-size: 2rem; font-weight: 700;"><?php echo isset($estados_pedidos['pendiente']) ? $estados_pedidos['pendiente'] : 0; ?></div>
                    </div>

                    <div class="status-box" style="flex: 1; min-width: 150px; padding: 15px; border-radius: 8px; background-color: rgba(33, 150, 243, 0.15); color: #2196F3; text-align: center;">
                        <h4 style="margin: 0 0 10px 0;">En Proceso</h4>
                        <div style="font-size: 2rem; font-weight: 700;"><?php echo isset($estados_pedidos['procesando']) ? $estados_pedidos['procesando'] : 0; ?></div>
                    </div>

                    <div class="status-box" style="flex: 1; min-width: 150px; padding: 15px; border-radius: 8px; background-color: rgba(76, 175, 80, 0.15); color: #4CAF50; text-align: center;">
                        <h4 style="margin: 0 0 10px 0;">Completados</h4>
                        <div style="font-size: 2rem; font-weight: 700;"><?php echo isset($estados_pedidos['completado']) ? $estados_pedidos['completado'] : 0; ?></div>
                    </div>

                    <div class="status-box" style="flex: 1; min-width: 150px; padding: 15px; border-radius: 8px; background-color: rgba(244, 67, 54, 0.15); color: #F44336; text-align: center;">
                        <h4 style="margin: 0 0 10px 0;">Cancelados</h4>
                        <div style="font-size: 2rem; font-weight: 700;"><?php echo isset($estados_pedidos['cancelado']) ? $estados_pedidos['cancelado'] : 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir el footer (si tienes uno específico para el admin)
// require_once 'includes/admin_footer.php';
?>
</body>
</html>