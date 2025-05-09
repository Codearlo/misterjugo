<?php
// Definir variables para el header
$titulo_pagina = "Gestión de Productos";
$breadcrumbs = [
    ['texto' => 'Dashboard', 'url' => 'dashboard.php'],
    ['texto' => 'Productos']
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
$categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;

// Obtener total de productos con filtros
$sql_count = "SELECT COUNT(*) as total FROM productos p WHERE 1=1";
$params_count = [];
$types_count = "";

if (!empty($busqueda)) {
    $sql_count .= " AND p.nombre LIKE ?";
    $busqueda_param = "%$busqueda%";
    $params_count[] = &$busqueda_param;
    $types_count .= "s";
}

if ($categoria > 0) {
    $sql_count .= " AND p.categoria_id = ?";
    $params_count[] = &$categoria;
    $types_count .= "i";
}

$stmt_count = $conn->prepare($sql_count);
if (!empty($params_count)) {
    $stmt_count->bind_param($types_count, ...$params_count);
}
$stmt_count->execute();
$total_productos = $stmt_count->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_productos / $por_pagina);

// Ajustar página actual si es necesario
if ($pagina_actual > $total_paginas && $total_paginas > 0) {
    $pagina_actual = $total_paginas;
    $inicio = ($pagina_actual - 1) * $por_pagina;
}

// Obtener productos con paginación y filtros
$sql = "SELECT p.*, c.nombre as categoria_nombre 
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE 1=1";
$params = [];
$types = "";

if (!empty($busqueda)) {
    $sql .= " AND p.nombre LIKE ?";
    $busqueda_param = "%$busqueda%";
    $params[] = &$busqueda_param;
    $types .= "s";
}

if ($categoria > 0) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = &$categoria;
    $types .= "i";
}

$sql .= " ORDER BY p.id DESC LIMIT ?, ?";
$params[] = &$inicio;
$params[] = &$por_pagina;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$productos = [];

while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}

// Obtener categorías para el filtro
$categorias = [];
$stmt = $conn->prepare("SELECT id, nombre FROM categorias ORDER BY nombre");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}
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
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
    <form action="" method="GET" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <input type="text" name="buscar" placeholder="Buscar productos..." value="<?php echo htmlspecialchars($busqueda); ?>" style="padding: 8px 15px; border: 1px solid #ddd; border-radius: 4px; min-width: 200px;">
            
            <select name="categoria" style="padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="0">Todas las categorías</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categoria == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-search"></i> Filtrar
            </button>
            
            <?php if (!empty($busqueda) || $categoria > 0): ?>
                <a href="productos.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            <?php endif; ?>
        </div>
    </form>
    
    <a href="editar_producto.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Producto
    </a>
</div>

<!-- Tabla de productos -->
<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px;">
                        <i class="fas fa-box-open" style="font-size: 3rem; color: #ddd; margin-bottom: 10px;"></i>
                        <p style="margin: 0;">No hay productos disponibles</p>
                        <?php if (!empty($busqueda) || $categoria > 0): ?>
                            <p style="margin: 10px 0 0;">Prueba a cambiar los filtros de búsqueda</p>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?php echo $producto['id']; ?></td>
                        <td>
                            <?php if (!empty($producto['imagen'])): ?>
                                <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background-color: #f1f1f1; border-radius: 4px;">
                                    <i class="fas fa-image" style="color: #aaa;"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                        <td>S/<?php echo number_format($producto['precio'], 2); ?></td>
                        <td><?php echo $producto['stock']; ?></td>
                        <td>
                            <?php if ($producto['disponible']): ?>
                                <span class="badge success">Activo</span>
                            <?php else: ?>
                                <span class="badge danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-primary btn-sm btn-icon" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="procesar_producto.php?action=toggle_status&id=<?php echo $producto['id']; ?>" class="btn btn-secondary btn-sm btn-icon" title="<?php echo $producto['disponible'] ? 'Desactivar' : 'Activar'; ?>">
                                    <i class="fas fa-<?php echo $producto['disponible'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                </a>
                                <a href="procesar_producto.php?action=delete&id=<?php echo $producto['id']; ?>" class="btn btn-danger btn-sm btn-icon delete-confirm" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
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
            <a href="?pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($busqueda) ? '&buscar=' . urlencode($busqueda) : ''; ?><?php echo $categoria > 0 ? '&categoria=' . $categoria : ''; ?>">
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
            echo '<a href="?pagina=1' . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . ($categoria > 0 ? '&categoria=' . $categoria : '') . '">1</a>';
            if ($inicio_rango > 2) {
                echo '<span class="ellipsis">...</span>';
            }
        }
        
        // Mostrar páginas en el rango
        for ($i = $inicio_rango; $i <= $fin_rango; $i++) {
            if ($i == $pagina_actual) {
                echo '<span class="current">' . $i . '</span>';
            } else {
                echo '<a href="?pagina=' . $i . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . ($categoria > 0 ? '&categoria=' . $categoria : '') . '">' . $i . '</a>';
            }
        }
        
        // Mostrar enlace a la última página si no está incluida en el rango
        if ($fin_rango < $total_paginas) {
            if ($fin_rango < $total_paginas - 1) {
                echo '<span class="ellipsis">...</span>';
            }
            echo '<a href="?pagina=' . $total_paginas . (!empty($busqueda) ? '&buscar=' . urlencode($busqueda) : '') . ($categoria > 0 ? '&categoria=' . $categoria : '') . '">' . $total_paginas . '</a>';
        }
        ?>
        
        <?php if ($pagina_actual < $total_paginas): ?>
            <a href="?pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($busqueda) ? '&buscar=' . urlencode($busqueda) : ''; ?><?php echo $categoria > 0 ? '&categoria=' . $categoria : ''; ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php else: ?>
            <span class="disabled"><i class="fas fa-chevron-right"></i></span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
// Incluir el footer
require_once 'includes/admin_footer.php';
?>