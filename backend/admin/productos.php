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

// Verificar si se ha solicitado una acción
$action = isset($_GET['action']) ? $_GET['action'] : null;
$producto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener producto si se está editando
$producto = null;
if ($action == 'edit' && $producto_id > 0) {
    $stmt = $conn->prepare("SELECT p.*, c.nombre as categoria_nombre 
                           FROM productos p 
                           LEFT JOIN categorias c ON p.categoria_id = c.id 
                           WHERE p.id = ?");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $producto = $result->fetch_assoc();
    } else {
        // Si no se encuentra el producto, redirigir a la lista
        $_SESSION['admin_error'] = "Producto no encontrado";
        header("Location: productos.php");
        exit;
    }
}

// Obtener todas las categorías para el selector
$categorias = [];
$result = $conn->query("SELECT * FROM categorias ORDER BY nombre");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Si no hay categorías y estamos creando un producto, redirigir para crear una
if (empty($categorias) && ($action == 'new' || $action == 'edit')) {
    $_SESSION['admin_error'] = "Debes crear al menos una categoría antes de gestionar productos";
    header("Location: categorias.php?action=new");
    exit;
}

// Paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registros_por_pagina = 10;
$inicio = ($pagina - 1) * $registros_por_pagina;

// Filtros
$filtro_categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$filtro_disponible = isset($_GET['disponible']) ? $_GET['disponible'] : '';
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

// Construir consulta base
$consulta_base = "FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE 1=1";
$params = [];
$tipos = "";

// Aplicar filtros
if ($filtro_categoria > 0) {
    $consulta_base .= " AND p.categoria_id = ?";
    $params[] = $filtro_categoria;
    $tipos .= "i";
}

if ($filtro_disponible !== '') {
    $consulta_base .= " AND p.disponible = ?";
    $params[] = (int)$filtro_disponible;
    $tipos .= "i";
}

if (!empty($busqueda)) {
    $busqueda_param = "%$busqueda%";
    $consulta_base .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $tipos .= "ss";
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

// Obtener productos
$consulta_productos = "SELECT p.*, c.nombre as categoria_nombre " . $consulta_base . " ORDER BY p.id DESC LIMIT ?, ?";
$stmt = $conn->prepare($consulta_productos);

$tipos .= "ii";
$params[] = $inicio;
$params[] = $registros_por_pagina;

if (!empty($params)) {
    $stmt->bind_param($tipos, ...$params);
}

$stmt->execute();
$productos = [];
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}

// Obtener mensajes de sesión
$admin_exito = isset($_SESSION['admin_exito']) ? $_SESSION['admin_exito'] : '';
$admin_error = isset($_SESSION['admin_error']) ? $_SESSION['admin_error'] : '';

// Limpiar mensajes de sesión
unset($_SESSION['admin_exito']);
unset($_SESSION['admin_error']);

$titulo_pagina = ($action == 'new' ? "Nuevo Producto" : ($action == 'edit' ? "Editar Producto" : "Gestión de Productos")) . " - MisterJugo";
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
                            <a class="nav-link active" href="productos.php">
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
                    <h1 class="h2">
                        <?php 
                        if ($action == 'new') {
                            echo "Nuevo Producto";
                        } elseif ($action == 'edit') {
                            echo "Editar Producto";
                        } else {
                            echo "Gestión de Productos";
                        }
                        ?>
                    </h1>
                    <?php if (!$action): ?>
                        <a href="productos.php?action=new" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Nuevo Producto
                        </a>
                    <?php endif; ?>
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

                <?php if ($action == 'new' || $action == 'edit'): ?>
                    <!-- Formulario para crear o editar producto -->
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="procesar_producto.php" method="POST" enctype="multipart/form-data">
                                <?php if ($action == 'edit'): ?>
                                    <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                                <?php endif; ?>
                                
                                <!-- Primera fila: Nombre y Categoría -->
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label for="nombre" class="form-label">Nombre del Producto</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?php echo ($action == 'edit' && $producto) ? htmlspecialchars($producto['nombre']) : ''; ?>"
                                               required maxlength="100">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="categoria_id" class="form-label">Categoría</label>
                                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                                            <option value="">-- Seleccionar Categoría --</option>
                                            <?php foreach ($categorias as $categoria): ?>
                                                <option value="<?php echo $categoria['id']; ?>"
                                                    <?php if (($action == 'edit' && $producto['categoria_id'] == $categoria['id'])): ?>
                                                        selected
                                                    <?php endif; ?>>
                                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Segunda fila: Precio y Disponibilidad -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="precio" class="form-label">Precio (S/)</label>
                                        <input type="number" class="form-control" id="precio" name="precio" 
                                               value="<?php echo ($action == 'edit' && $producto) ? $producto['precio'] : ''; ?>"
                                               step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="disponible" class="form-label">Disponibilidad</label>
                                        <select class="form-select" id="disponible" name="disponible">
                                            <option value="1" <?php echo ($action == 'edit' && !$producto['disponible']) ? '' : 'selected'; ?>>
                                                Disponible
                                            </option>
                                            <option value="0" <?php echo ($action == 'edit' && !$producto['disponible']) ? 'selected' : ''; ?>>
                                                No Disponible
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="imagen" class="form-label">Imagen (JPG)</label>
                                        <input type="file" class="form-control" id="imagen" name="imagen" 
                                               accept=".jpg, .jpeg" <?php echo ($action == 'new') ? 'required' : ''; ?>>
                                        <?php if ($action == 'edit' && !empty($producto['imagen'])): ?>
                                            <div class="form-text">
                                                Imagen actual: <a href="<?php echo htmlspecialchars($producto['imagen']); ?>" target="_blank">
                                                    <?php echo htmlspecialchars($producto['imagen']); ?>
                                                </a>
                                            </div>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="mantener_imagen" name="mantener_imagen" value="1" checked>
                                                <label class="form-check-label" for="mantener_imagen">
                                                    Mantener imagen actual si no se sube una nueva
                                                </label>
                                            </div>
                                        <?php endif; ?>
                                        <div class="form-text">Solo se permiten archivos JPG. Tamaño máximo: 2MB.</div>
                                    </div>
                                </div>
                                
                                <!-- Descripción -->
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4"><?php echo ($action == 'edit' && $producto) ? htmlspecialchars($producto['descripcion']) : ''; ?></textarea>
                                </div>
                                
                                <!-- Vista previa de imagen -->
                                <?php if ($action == 'edit' && !empty($producto['imagen'])): ?>
                                    <div class="mb-3">
                                        <label class="form-label">Vista previa de la imagen actual</label>
                                        <div class="product-image-preview">
                                            <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                                                 class="img-thumbnail" style="max-height: 200px;">
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Vista previa de la nueva imagen -->
                                <div class="mb-3" id="imagePreviewContainer" style="display: none;">
                                    <label class="form-label">Vista previa de la nueva imagen</label>
                                    <div class="product-image-preview">
                                        <img id="imagePreview" src="#" alt="Vista previa" class="img-thumbnail" style="max-height: 200px;">
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="productos.php" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        <?php echo ($action == 'edit') ? 'Actualizar Producto' : 'Crear Producto'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Filtros y buscador -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="GET" action="productos.php" class="row align-items-end">
                                <div class="col-md-3 mb-3">
                                    <label for="categoria" class="form-label">Filtrar por Categoría</label>
                                    <select class="form-select" id="categoria" name="categoria">
                                        <option value="0">Todas las categorías</option>
                                        <?php foreach ($categorias as $categoria): ?>
                                            <option value="<?php echo $categoria['id']; ?>" 
                                                <?php echo ($filtro_categoria == $categoria['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="disponible" class="form-label">Filtrar por Estado</label>
                                    <select class="form-select" id="disponible" name="disponible">
                                        <option value="">Todos</option>
                                        <option value="1" <?php echo ($filtro_disponible === '1') ? 'selected' : ''; ?>>
                                            Disponibles
                                        </option>
                                        <option value="0" <?php echo ($filtro_disponible === '0') ? 'selected' : ''; ?>>
                                            No Disponibles
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="buscar" class="form-label">Buscar</label>
                                    <input type="text" class="form-control" id="buscar" name="buscar" 
                                           placeholder="Nombre o descripción del producto"
                                           value="<?php echo htmlspecialchars($busqueda); ?>">
                                </div>
                                <div class="col-md-3 mb-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="fas fa-search me-2"></i> Filtrar
                                    </button>
                                    <a href="productos.php" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tabla de productos -->
                    <div class="card shadow">
                        <div class="card-body">
                            <?php if (count($productos) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Imagen</th>
                                                <th>Nombre</th>
                                                <th>Categoría</th>
                                                <th>Precio</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($productos as $prod): ?>
                                                <tr>
                                                    <td><?php echo $prod['id']; ?></td>
                                                    <td>
                                                        <?php if (!empty($prod['imagen'])): ?>
                                                            <img src="<?php echo htmlspecialchars($prod['imagen']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($prod['nombre']); ?>" 
                                                                 class="img-thumbnail product-thumb">
                                                        <?php else: ?>
                                                            <div class="no-image-placeholder">
                                                                <i class="fas fa-image"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                                                    <td><?php echo htmlspecialchars($prod['categoria_nombre']); ?></td>
                                                    <td>S/<?php echo number_format($prod['precio'], 2); ?></td>
                                                    <td>
                                                        <?php if ($prod['disponible']): ?>
                                                            <span class="badge bg-success">Disponible</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">No Disponible</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="productos.php?action=edit&id=<?php echo $prod['id']; ?>" 
                                                               class="btn btn-sm btn-outline-primary" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                                                    onclick="confirmarEliminacion(<?php echo $prod['id']; ?>, '<?php echo htmlspecialchars($prod['nombre']); ?>')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-info" title="Ver detalles" 
                                                                    onclick="verDetalles(<?php echo $prod['id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Paginación -->
                                <?php if ($total_paginas > 1): ?>
                                    <nav aria-label="Paginación de productos">
                                        <ul class="pagination justify-content-center mt-4">
                                            <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="<?php echo ($pagina <= 1) ? '#' : 'productos.php?pagina='.($pagina-1).((!empty($filtro_categoria)) ? '&categoria='.$filtro_categoria : '').((!empty($filtro_disponible)) ? '&disponible='.$filtro_disponible : '').((!empty($busqueda)) ? '&buscar='.urlencode($busqueda) : ''); ?>" 
                                                   aria-label="Anterior">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                            
                                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                                <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                                                    <a class="page-link" href="productos.php?pagina=<?php echo $i; ?><?php echo (!empty($filtro_categoria)) ? '&categoria='.$filtro_categoria : ''; ?><?php echo (!empty($filtro_disponible)) ? '&disponible='.$filtro_disponible : ''; ?><?php echo (!empty($busqueda)) ? '&buscar='.urlencode($busqueda) : ''; ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="<?php echo ($pagina >= $total_paginas) ? '#' : 'productos.php?pagina='.($pagina+1).((!empty($filtro_categoria)) ? '&categoria='.$filtro_categoria : '').((!empty($filtro_disponible)) ? '&disponible='.$filtro_disponible : '').((!empty($busqueda)) ? '&buscar='.urlencode($busqueda) : ''); ?>" 
                                                   aria-label="Siguiente">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
                                
                            <?php else: ?>
                                <div class="text-center p-4">
                                    <p class="text-muted mb-3">No se encontraron productos que coincidan con tu búsqueda.</p>
                                    <a href="productos.php" class="btn btn-secondary me-2">
                                        <i class="fas fa-redo me-2"></i> Limpiar Filtros
                                    </a>
                                    <a href="productos.php?action=new" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i> Crear Nuevo Producto
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar el producto <strong id="deleteItemName"></strong>?</p>
                    <p class="text-danger">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Eliminar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles de producto -->
    <div class="modal fade" id="detallesModal" tabindex="-1" aria-labelledby="detallesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detallesModalLabel">Detalles del Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detallesProductoBody">
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

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Admin JS -->
    <script src="js/admin.js"></script>
    <script>
        // Función para mostrar el modal de confirmación
        function confirmarEliminacion(id, nombre) {
            document.getElementById('deleteItemName').textContent = nombre;
            document.getElementById('confirmDeleteBtn').href = 'eliminar_producto.php?id=' + id;
            
            // Mostrar el modal usando Bootstrap
            var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
        
        // Función para ver detalles del producto
        function verDetalles(id) {
            // Mostrar el modal
            var detallesModal = new bootstrap.Modal(document.getElementById('detallesModal'));
            detallesModal.show();
            
            // Realizar petición AJAX para obtener detalles
            fetch('obtener_producto.php?id=' + id)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const producto = data.producto;
                        document.getElementById('detallesModalLabel').textContent = 'Detalles: ' + producto.nombre;
                        
                        // Construir contenido HTML para mostrar detalles
                        let detallesHTML = `
                            <div class="row">
                                <div class="col-md-5 text-center">
                                    ${producto.imagen ? 
                                        `<img src="${producto.imagen}" alt="${producto.nombre}" class="img-fluid img-thumbnail mb-3" style="max-height: 250px;">` : 
                                        `<div class="no-image-placeholder" style="height: 200px;"><i class="fas fa-image"></i></div>`
                                    }
                                </div>
                                <div class="col-md-7">
                                    <h4>${producto.nombre}</h4>
                                    <p><strong>Categoría:</strong> ${producto.categoria_nombre}</p>
                                    <p><strong>Precio:</strong> S/${parseFloat(producto.precio).toFixed(2)}</p>
                                    <p><strong>Estado:</strong> 
                                        ${producto.disponible == 1 ? 
                                            '<span class="badge bg-success">Disponible</span>' : 
                                            '<span class="badge bg-secondary">No Disponible</span>'
                                        }
                                    </p>
                                    ${producto.descripcion ? `<p><strong>Descripción:</strong> ${producto.descripcion}</p>` : ''}
                                    <div class="mt-3">
                                        <a href="productos.php?action=edit&id=${producto.id}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit me-1"></i> Editar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        document.getElementById('detallesProductoBody').innerHTML = detallesHTML;
                    } else {
                        document.getElementById('detallesProductoBody').innerHTML = `
                            <div class="alert alert-danger" role="alert">
                                ${data.message || 'No se pudo cargar la información del producto.'}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('detallesProductoBody').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            Error al cargar los detalles del producto.
                        </div>
                    `;
                });
        }
        
        // Vista previa de imagen al seleccionar un archivo
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('imagen');
            const imagePreview = document.getElementById('imagePreview');
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            
            if (imageInput && imagePreview && imagePreviewContainer) {
                imageInput.onchange = function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.src = e.target.result;
                            imagePreviewContainer.style.display = 'block';
                        }
                        reader.readAsDataURL(file);
                    } else {
                        imagePreviewContainer.style.display = 'none';
                    }
                };
            }
        });
    </script>
</body>
</html>