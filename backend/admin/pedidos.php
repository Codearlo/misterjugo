<?php
session_start();

// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    $_SESSION['error_login'] = "Debes iniciar sesión como administrador para acceder";
    header("Location: /login");
    exit;
}

// Incluir conexión a la base de datos
require_once '../conexion.php';

// Paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registros_por_pagina = 15;
$inicio = ($pagina - 1) * $registros_por_pagina;

// Filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

// Construir consulta base
$consulta_base = "FROM pedidos p LEFT JOIN usuarios u ON p.usuario_id = u.id WHERE 1=1";
$params = [];
$tipos = "";

// Aplicar filtros
if ($filtro_estado !== '') {
    $consulta_base .= " AND p.estado = ?";
    $params[] = $filtro_estado;
    $tipos .= "s";
}

if (!empty($filtro_fecha)) {
    $consulta_base .= " AND DATE(p.fecha_pedido) = ?";
    $params[] = $filtro_fecha;
    $tipos .= "s";
}

if (!empty($busqueda)) {
    $busqueda_param = "%$busqueda%";
    $consulta_base .= " AND (u.nombre LIKE ? OR u.email LIKE ? OR p.id LIKE ?)";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $tipos .= "sss";
}

// Contar total de registros para paginación
$consulta_count = "SELECT COUNT(*) as total " . $consulta_base;
$stmt = $conn->prepare($consulta_count);

if (!empty($params)) {
    $stmt->bind_param($tipos, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$total_registros = $result->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener pedidos
$consulta_pedidos = "SELECT p.*, u.nombre as usuario_nombre, u.email as usuario_email, DATE_FORMAT(p.fecha_pedido, '%d/%m/%Y %H:%i') as fecha_pedido_formateada " . $consulta_base . " ORDER BY p.fecha_pedido DESC LIMIT ?, ?";
$stmt = $conn->prepare($consulta_pedidos);

$tipos .= "ii";
$params[] = $inicio;
$params[] = $registros_por_pagina;

if (!empty($params)) {
    $stmt->bind_param($tipos, ...$params);
}

$stmt->execute();
$pedidos = [];
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $pedidos[] = $row;
}

// Obtener estadísticas rápidas
$stats = [
    'pendiente' => 0,
    'procesando' => 0,
    'completado' => 0,
    'cancelado' => 0
];

$result = $conn->query("SELECT estado, COUNT(*) as total FROM pedidos GROUP BY estado");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stats[$row['estado']] = $row['total'];
    }
}

// Obtener mensajes de sesión
$admin_exito = isset($_SESSION['admin_exito']) ? $_SESSION['admin_exito'] : '';
$admin_error = isset($_SESSION['admin_error']) ? $_SESSION['admin_error'] : '';

// Limpiar mensajes de sesión
unset($_SESSION['admin_exito']);
unset($_SESSION['admin_error']);

$titulo_pagina = "Gestión de Pedidos - MisterJugo";
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
                            <a class="nav-link" href="index.php">
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
                            <a class="nav-link active" href="pedidos.php">
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
                    <h1 class="h2">Gestión de Pedidos</h1>
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-3">Total: <?php echo $total_registros; ?> pedidos</span>
                    </div>
                </div>

                <!-- Mensajes de éxito o error -->
                <?php if (!empty($admin_exito)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $admin_exito; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($admin_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $admin_error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Estadísticas rápidas -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card shadow stats-card border-warning">
                            <div class="card-body text-center">
                                <h3 class="text-warning"><?php echo $stats['pendiente']; ?></h3>
                                <p class="mb-0">Pendientes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card shadow stats-card border-info">
                            <div class="card-body text-center">
                                <h3 class="text-info"><?php echo $stats['procesando']; ?></h3>
                                <p class="mb-0">Procesando</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card shadow stats-card border-success">
                            <div class="card-body text-center">
                                <h3 class="text-success"><?php echo $stats['completado']; ?></h3>
                                <p class="mb-0">Completados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card shadow stats-card border-danger">
                            <div class="card-body text-center">
                                <h3 class="text-danger"><?php echo $stats['cancelado']; ?></h3>
                                <p class="mb-0">Cancelados</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros y buscador -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form method="GET" action="pedidos.php" class="row align-items-end">
                            <div class="col-md-2 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos</option>
                                    <option value="pendiente" <?php echo ($filtro_estado === 'pendiente') ? 'selected' : ''; ?>>
                                        Pendiente
                                    </option>
                                    <option value="procesando" <?php echo ($filtro_estado === 'procesando') ? 'selected' : ''; ?>>
                                        Procesando
                                    </option>
                                    <option value="completado" <?php echo ($filtro_estado === 'completado') ? 'selected' : ''; ?>>
                                        Completado
                                    </option>
                                    <option value="cancelado" <?php echo ($filtro_estado === 'cancelado') ? 'selected' : ''; ?>>
                                        Cancelado
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" 
                                       value="<?php echo htmlspecialchars($filtro_fecha); ?>">
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="buscar" class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscar" name="buscar" 
                                       placeholder="ID pedido, nombre o email del cliente"
                                       value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <div class="col-md-3 mb-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="fas fa-search me-2"></i> Filtrar
                                </button>
                                <a href="pedidos.php" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tabla de pedidos -->
                <div class="card shadow">
                    <div class="card-body">
                        <?php if (count($pedidos) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                            <th>Productos</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pedidos as $pedido): ?>
                                            <?php
                                            // Contar productos del pedido
                                            $stmt_productos = $conn->prepare("SELECT COUNT(*) as total FROM detalles_pedidos WHERE pedido_id = ?");
                                            $stmt_productos->bind_param("i", $pedido['id']);
                                            $stmt_productos->execute();
                                            $resultado_productos = $stmt_productos->get_result();
                                            $total_productos = $resultado_productos->fetch_assoc()['total'];
                                            ?>
                                            <tr>
                                                <td>#<?php echo $pedido['id']; ?></td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($pedido['usuario_email']); ?></small>
                                                    </div>
                                                </td>
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
                                                <td><?php echo $pedido['fecha_pedido_formateada']; ?></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo $total_productos; ?> items</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-info" title="Ver detalles" 
                                                                onclick="verDetallesPedido(<?php echo $pedido['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                                    data-bs-toggle="dropdown" aria-expanded="false" title="Cambiar estado">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <?php if ($pedido['estado'] != 'pendiente'): ?>
                                                                    <li><a class="dropdown-item" href="#" onclick="cambiarEstadoPedido(<?php echo $pedido['id']; ?>, 'pendiente')">
                                                                        <i class="fas fa-clock text-warning me-2"></i>Pendiente
                                                                    </a></li>
                                                                <?php endif; ?>
                                                                <?php if ($pedido['estado'] != 'procesando'): ?>
                                                                    <li><a class="dropdown-item" href="#" onclick="cambiarEstadoPedido(<?php echo $pedido['id']; ?>, 'procesando')">
                                                                        <i class="fas fa-cog text-info me-2"></i>Procesando
                                                                    </a></li>
                                                                <?php endif; ?>
                                                                <?php if ($pedido['estado'] != 'completado'): ?>
                                                                    <li><a class="dropdown-item" href="#" onclick="cambiarEstadoPedido(<?php echo $pedido['id']; ?>, 'completado')">
                                                                        <i class="fas fa-check text-success me-2"></i>Completado
                                                                    </a></li>
                                                                <?php endif; ?>
                                                                <?php if ($pedido['estado'] != 'cancelado'): ?>
                                                                    <li><a class="dropdown-item" href="#" onclick="cambiarEstadoPedido(<?php echo $pedido['id']; ?>, 'cancelado')">
                                                                        <i class="fas fa-times text-danger me-2"></i>Cancelado
                                                                    </a></li>
                                                                <?php endif; ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Paginación -->
                            <?php if ($total_paginas > 1): ?>
                                <nav aria-label="Paginación de pedidos">
                                    <ul class="pagination justify-content-center mt-4">
                                        <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo ($pagina <= 1) ? '#' : 'pedidos.php?pagina='.($pagina-1).((!empty($filtro_estado)) ? '&estado='.$filtro_estado : '').((!empty($filtro_fecha)) ? '&fecha='.$filtro_fecha : '').((!empty($busqueda)) ? '&buscar='.urlencode($busqueda) : ''); ?>" 
                                               aria-label="Anterior">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        
                                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                            <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                                                <a class="page-link" href="pedidos.php?pagina=<?php echo $i; ?><?php echo (!empty($filtro_estado)) ? '&estado='.$filtro_estado : ''; ?><?php echo (!empty($filtro_fecha)) ? '&fecha='.$filtro_fecha : ''; ?><?php echo (!empty($busqueda)) ? '&buscar='.urlencode($busqueda) : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo ($pagina >= $total_paginas) ? '#' : 'pedidos.php?pagina='.($pagina+1).((!empty($filtro_estado)) ? '&estado='.$filtro_estado : '').((!empty($filtro_fecha)) ? '&fecha='.$filtro_fecha : '').((!empty($busqueda)) ? '&buscar='.urlencode($busqueda) : ''); ?>" 
                                               aria-label="Siguiente">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="text-center p-4">
                                <p class="text-muted mb-3">No se encontraron pedidos que coincidan con tu búsqueda.</p>
                                <a href="pedidos.php" class="btn btn-secondary">
                                    <i class="fas fa-redo me-2"></i> Limpiar Filtros
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para ver detalles de pedido -->
    <div class="modal fade" id="detallesPedidoModal" tabindex="-1" aria-labelledby="detallesPedidoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detallesPedidoModalLabel">Detalles del Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detallesPedidoBody">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando detalles...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para cambiar estado -->
    <div class="modal fade" id="cambiarEstadoPedidoModal" tabindex="-1" aria-labelledby="cambiarEstadoPedidoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cambiarEstadoPedidoModalLabel">Cambiar Estado del Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas cambiar el estado del pedido <strong id="pedidoIdEstado"></strong> a <span id="nuevoEstadoPedido"></span>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="confirmCambiarEstadoPedidoBtn" class="btn btn-primary">Cambiar Estado</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Admin JS -->
    <script src="js/admin.js"></script>
    <script>
        // Función para ver detalles del pedido
        function verDetallesPedido(id) {
            // Mostrar el modal
            var detallesModal = new bootstrap.Modal(document.getElementById('detallesPedidoModal'));
            detallesModal.show();
            
            // Realizar petición AJAX para obtener detalles
            fetch('obtener_pedido.php?id=' + id)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const pedido = data.pedido;
                        document.getElementById('detallesPedidoModalLabel').textContent = 'Detalles del Pedido #' + pedido.id;
                        
                        // Construir contenido HTML para mostrar detalles
                        let detallesHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Información del Pedido</h5>
                                    <p><strong>ID:</strong> #${pedido.id}</p>
                                    <p><strong>Fecha:</strong> ${pedido.fecha_pedido_formateada}</p>
                                    <p><strong>Estado:</strong> ${getEstadoBadge(pedido.estado)}</p>
                                    <p><strong>Total:</strong> S/${parseFloat(pedido.total).toFixed(2)}</p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Cliente</h5>
                                    <p><strong>Nombre:</strong> ${pedido.cliente_nombre}</p>
                                    <p><strong>Email:</strong> ${pedido.cliente_email}</p>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5>Productos del Pedido</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Cantidad</th>
                                                    <th>Precio Unit.</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${pedido.productos.map(producto => `
                                                    <tr>
                                                        <td>${producto.nombre}</td>
                                                        <td>${producto.cantidad}</td>
                                                        <td>S/${parseFloat(producto.precio).toFixed(2)}</td>
                                                        <td>S/${(parseFloat(producto.precio) * parseInt(producto.cantidad)).toFixed(2)}</td>
                                                    </tr>
                                                `).join('')}
                                                <tr class="table-primary">
                                                    <td colspan="3"><strong>Total</strong></td>
                                                    <td><strong>S/${parseFloat(pedido.total).toFixed(2)}</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        document.getElementById('detallesPedidoBody').innerHTML = detallesHTML;
                    } else {
                        document.getElementById('detallesPedidoBody').innerHTML = `
                            <div class="alert alert-danger" role="alert">
                                ${data.message || 'No se pudo cargar la información del pedido.'}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('detallesPedidoBody').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            Error al cargar los detalles del pedido.
                        </div>
                    `;
                });
        }
        
        // Función para cambiar estado del pedido
        function cambiarEstadoPedido(id, nuevoEstado) {
            document.getElementById('pedidoIdEstado').textContent = '#' + id;
            document.getElementById('nuevoEstadoPedido').textContent = nuevoEstado;
            document.getElementById('confirmCambiarEstadoPedidoBtn').href = 'procesar_pedido.php?action=cambiar_estado&id=' + id + '&estado=' + nuevoEstado;
            
            var modal = new bootstrap.Modal(document.getElementById('cambiarEstadoPedidoModal'));
            modal.show();
        }
        
        // Función auxiliar para mostrar badge de estado
        function getEstadoBadge(estado) {
            switch (estado) {
                case 'pendiente':
                    return '<span class="badge bg-warning">Pendiente</span>';
                case 'procesando':
                    return '<span class="badge bg-info">Procesando</span>';
                case 'completado':
                    return '<span class="badge bg-success">Completado</span>';
                case 'cancelado':
                    return '<span class="badge bg-danger">Cancelado</span>';
                default:
                    return '<span class="badge bg-secondary">' + estado + '</span>';
            }
        }
    </script>
</body>
</html>