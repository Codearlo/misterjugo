<?php
// Definir variables para el header
$titulo_pagina = "Gestión de Pedidos";
$breadcrumbs = [
    ['texto' => 'Dashboard', 'url' => 'dashboard.php'],
    ['texto' => 'Pedidos']
];

// Incluir el header
require_once 'includes/admin_header.php';

// Obtener mensajes de estado si existen
$exito = isset($_SESSION['admin_exito']) ? $_SESSION['admin_exito'] : '';
$error = isset($_SESSION['admin_error']) ? $_SESSION['admin_error'] : '';
unset($_SESSION['admin_exito'], $_SESSION['admin_error']);

// Configuración de paginación
$por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_actual - 1) * $por_pagina;

// Filtros
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';

// Construir la consulta SQL base
$sql_count = "SELECT COUNT(*) as total FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id WHERE 1=1";
$sql = "SELECT p.*, u.nombre as usuario_nombre, u.email as usuario_email FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id WHERE 1=1";
$params = [];
$types = "";

// Aplicar filtros
if (!empty($busqueda)) {
    $sql_count .= " AND (p.id LIKE ? OR u.nombre LIKE ? OR u.email LIKE ?)";
    $sql .= " AND (p.id LIKE ? OR u.nombre LIKE ? OR u.email LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params[] = &$busqueda_param;
    $params[] = &$busqueda_param;
    $params[] = &$busqueda_param;
    $types .= "sss";
}

if (!empty($estado)) {
    $sql_count .= " AND p.estado = ?";
    $sql .= " AND p.estado = ?";
    $params[] = &$estado;
    $types .= "s";
}

if (!empty($fecha_desde)) {
    $fecha_desde_formateada = date('Y-m-d', strtotime($fecha_desde)) . ' 00:00:00';
    $sql_count .= " AND p.fecha_pedido >= ?";
    $sql .= " AND p.fecha_pedido >= ?";
    $params[] = &$fecha_desde_formateada;
    $types .= "s";
}

if (!empty($fecha_hasta)) {
    $fecha_hasta_formateada = date('Y-m-d', strtotime($fecha_hasta)) . ' 23:59:59';
    $sql_count .= " AND p.fecha_pedido <= ?";
    $sql .= " AND p.fecha_pedido <= ?";
    $params[] = &$fecha_hasta_formateada;
    $types .= "s";
}

// Obtener total de registros para paginación
$stmt_count = $conn->prepare($sql_count);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_pedidos = $stmt_count->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_pedidos / $por_pagina);

// Añadir límites para paginación
$sql .= " ORDER BY p.fecha_pedido DESC LIMIT ?, ?";
$params[] = &$inicio;
$params[] = &$por_pagina;
$types .= "ii";

// Ejecutar la consulta principal
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$pedidos = [];

while ($row = $result->fetch_assoc()) {
    $pedidos[] = $row;
}

// Función para formatear fecha
function formatearFecha($fecha) {
    return date('d/m/Y H:i', strtotime($fecha));
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

// Opciones de estado para filtro
$estados_disponibles = [
    'pendiente' => 'Pendiente',
    'procesando' => 'Procesando',
    'completado' => 'Completado',
    'cancelado' => 'Cancelado'
];
?>

<!-- Mensajes de estado -->
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

<!-- Filtros y acciones -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form action="" method="GET" class="filter-form" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
            <div style="flex: 1; min-width: 200px;">
                <label for="buscar" style="display: block; margin-bottom: 5px; font-size: 0.9rem;">Buscar</label>
                <input type="text" id="buscar" name="buscar" placeholder="ID, Cliente o Email..." value="<?php echo htmlspecialchars($busqueda); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label for="estado" style="display: block; margin-bottom: 5px; font-size: 0.9rem;">Estado</label>
                <select id="estado" name="estado" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Todos</option>
                    <?php foreach ($estados_disponibles as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo $estado === $key ? 'selected' : ''; ?>><?php echo $value; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label for="fecha_desde" style="display: block; margin-bottom: 5px; font-size: 0.9rem;">Desde</label>
                <input type="date" id="fecha_desde" name="fecha_desde" value="<?php echo htmlspecialchars($fecha_desde); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label for="fecha_hasta" style="display: block; margin-bottom: 5px; font-size: 0.9rem;">Hasta</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta" value="<?php echo htmlspecialchars($fecha_hasta); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                
                <?php if (!empty($busqueda) || !empty($estado) || !empty($fecha_desde) || !empty($fecha_hasta)): ?>
                    <a href="pedidos.php" class="btn btn-secondary" style="white-space: nowrap;">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Resumen de pedidos -->
<div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
    <?php
    // Obtener conteo de pedidos por estado
    $estados_conteo = [];
    $sql_conteo = "SELECT estado, COUNT(*) as total FROM pedidos GROUP BY estado";
    $result_conteo = $conn->query($sql_conteo);
    while ($row = $result_conteo->fetch_assoc()) {
        $estados_conteo[$row['estado']] = $row['total'];
    }
    
    // Agregar conteo total
    $estados_conteo['total'] = $total_pedidos;
    ?>
    
    <div class="card" style="flex: 1; min-width: 120px; text-align: center; margin: 0;">
        <div style="padding: 15px;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--admin-dark);">
                <?php echo $estados_conteo['total'] ?? 0; ?>
            </div>
            <div style="color: var(--admin-medium); font-size: 0.9rem;">Total</div>
        </div>
    </div>
    
    <div class="card" style="flex: 1; min-width: 120px; text-align: center; margin: 0; border-left: 3px solid var(--admin-warning);">
        <div style="padding: 15px;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--admin-warning);">
                <?php echo $estados_conteo['pendiente'] ?? 0; ?>
            </div>
            <div style="color: var(--admin-medium); font-size: 0.9rem;">Pendientes</div>
        </div>
    </div>
    
    <div class="card" style="flex: 1; min-width: 120px; text-align: center; margin: 0; border-left: 3px solid var(--admin-info);">
        <div style="padding: 15px;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--admin-info);">
                <?php echo $estados_conteo['procesando'] ?? 0; ?>
            </div>
            <div style="color: var(--admin-medium); font-size: 0.9rem;">Procesando</div>
        </div>
    </div>
    
    <div class="card" style="flex: 1; min-width: 120px; text-align: center; margin: 0; border-left: 3px solid var(--admin-success);">
        <div style="padding: 15px;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--admin-success);">
                <?php echo $estados_conteo['completado'] ?? 0; ?>
            </div>
            <div style="color: var(--admin-medium); font-size: 0.9rem;">Completados</div>
        </div>
    </div>
    
    <div class="card" style="flex: 1; min-width: 120px; text-align: center; margin: 0; border-left: 3px solid var(--admin-danger);">
        <div style="padding: 15px;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--admin-danger);">
                <?php echo $estados_conteo['cancelado'] ?? 0; ?>
            </div>
            <div style="color: var(--admin-medium); font-size: 0.9rem;">Cancelados</div>
        </div>
    </div>
</div>

<!-- Tabla de pedidos -->
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
            <?php if (empty($pedidos)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px;">
                        <i class="fas fa-shopping-basket" style="font-size: 3rem; color: #ddd; margin-bottom: 10px;"></i>
                        <p style="margin: 0;">No hay pedidos disponibles</p>
                        <?php if (!empty($busqueda) || !empty($estado) || !empty($fecha_desde) || !empty($fecha_hasta)): ?>
                            <p style="margin: 10px 0 0;">Prueba a cambiar los filtros de búsqueda</p>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td>#<?php echo $pedido['id']; ?></td>
                        <td>
                            <div><?php echo htmlspecialchars($pedido['usuario_nombre']); ?></div>
                            <div style="font-size: 0.85rem; color: #777;"><?php echo htmlspecialchars($pedido['usuario_email']); ?></div>
                        </td>
                        <td><?php echo formatearFecha($pedido['fecha_pedido']); ?></td>
                        <td>S/<?php echo number_format($pedido['total'], 2); ?></td>
                        <td>
                            <span class="badge <?php echo getEstadoClass($pedido['estado']); ?>">
                                <?php echo ucfirst($pedido['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="ver_pedido.php?id=<?php echo $pedido['id']; ?>" class="btn btn-primary btn-sm btn-icon" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($pedido['estado'] === 'pendiente'): ?>
                                    <a href="procesar_pedido.php?action=update_status&id=<?php echo $pedido['id']; ?>&status=procesando" class="btn btn-info btn-sm btn-icon" title="Marcar como En Proceso">
                                        <i class="fas fa-cog"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($pedido['estado'] === 'procesando'): ?>
                                    <a href="procesar_pedido.php?action=update_status&id=<?php echo $pedido['id']; ?>&status=completado" class="btn btn-success btn-sm btn-icon" title="Marcar como Completado">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($pedido['estado'] === 'pendiente' || $pedido['estado'] === 'procesando'): ?>
                                    <a href="procesar_pedido.php?action=update_status&id=<?php echo $pedido['id']; ?>&status=cancelado" class="btn btn-danger btn-sm btn-icon delete-confirm" title="Cancelar Pedido">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginación -->
<?php if ($total_paginas > 1): ?>
    <div class="pagination">
        <?php if ($pagina_actual > 1): ?>
            <a href="?pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($busqueda) ? '&buscar=' . urlencode($busqueda) : ''; ?><?php echo !empty($estado) ? '&estado=' . $estado : ''; ?><?php echo !empty($fecha_desde) ? '&fecha_desde=' . $fecha_desde : ''; ?><?php echo !empty($fecha_hasta) ? '&fecha_hasta=' . $fecha_hasta : ''; ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
        <?php else: ?>
            <span class="disabled"><i class="fas fa-chevron-left"></i></span>
        <?php endif; ?>
        
        <?php
        // Calcular rango de páginas a mostrar
        $rango = 2; // Número de páginas antes y después de la actual
        $inicio_rango = max(1, $pagina_actual - $rango);
        $fin_rango = min($total_paginas, $pagina_actual + $rango);
        
        // Mostrar enlace a la primera página si no está incluida en el rango
        if ($inicio_rango > 1) {
            echo '<a href="?pagina=1' . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . (!empty($estado) ? '&estado=' . $estado : '') . (!empty($fecha_desde) ? '&fecha_desde=' . $fecha_desde : '') . (!empty($fecha_hasta) ? '&fecha_hasta=' . $fecha_hasta : '') . '">1</a>';
            if ($inicio_rango > 2) {
                echo '<span class="ellipsis">...</span>';
            }
        }
        
        // Mostrar páginas en el rango
        for ($i = $inicio_rango; $i <= $fin_rango; $i++) {
            if ($i == $pagina_actual) {
                echo '<span class="current">' . $i . '</span>';
            } else {
                echo '<a href="?pagina=' . $i . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . (!empty($estado) ? '&estado=' . $estado : '') . (!empty($fecha_desde) ? '&fecha_desde=' . $fecha_desde : '') . (!empty($fecha_hasta) ? '&fecha_hasta=' . $fecha_hasta : '') . '">' . $i . '</a>';
            }
        }
        
        // Mostrar enlace a la última página si no está incluida en el rango
        if ($fin_rango < $total_paginas) {
            if ($fin_rango < $total_paginas - 1) {
                echo '<span class="ellipsis">...</span>';
            }
            echo '<a href="?pagina=' . $total_paginas . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . (!empty($estado) ? '&estado=' . $estado : '') . (!empty($fecha_desde) ? '&fecha_desde=' . $fecha_desde : '') . (!empty($fecha_hasta) ? '&fecha_hasta=' . $fecha_hasta : '') . '">' . $total_paginas . '</a>';
        }
        ?>
        
        <?php if ($pagina_actual < $total_paginas): ?>
            <a href="?pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($busqueda) ? '&buscar=' . urlencode($busqueda) : ''; ?><?php echo !empty($estado) ? '&estado=' . $estado : ''; ?><?php echo !empty($fecha_desde) ? '&fecha_desde=' . $fecha_desde : ''; ?><?php echo !empty($fecha_hasta) ? '&fecha_hasta=' . $fecha_hasta : ''; ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php else: ?>
            <span class="disabled"><i class="fas fa-chevron-right"></i></span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<style>
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
    gap: 5px;
}

.pagination a, .pagination span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 35px;
    height: 35px;
    padding: 0 10px;
    border-radius: 4px;
    text-decoration: none;
    color: var(--admin-dark);
    background-color: #fff;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.pagination a:hover {
    background-color: #f1f1f1;
}

.pagination .current {
    background-color: var(--admin-primary);
    color: white;
    font-weight: 500;
}

.pagination .disabled {
    color: #ccc;
    pointer-events: none;
}

.pagination .ellipsis {
    background: none;
}
</style>

<?php
// Incluir el footer
require_once 'includes/admin_footer.php';
?>