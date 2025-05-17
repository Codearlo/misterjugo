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
$categoria_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener categoría si se está editando
$categoria = null;
if ($action == 'edit' && $categoria_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $categoria_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $categoria = $result->fetch_assoc();
    } else {
        // Si no se encuentra la categoría, redirigir a la lista
        $_SESSION['admin_error'] = "Categoría no encontrada";
        header("Location: categorias.php");
        exit;
    }
}

// Obtener todas las categorías para la tabla
$categorias = [];
$result = $conn->query("SELECT * FROM categorias ORDER BY nombre");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Obtener mensajes de sesión
$admin_exito = isset($_SESSION['admin_exito']) ? $_SESSION['admin_exito'] : '';
$admin_error = isset($_SESSION['admin_error']) ? $_SESSION['admin_error'] : '';

// Limpiar mensajes de sesión
unset($_SESSION['admin_exito']);
unset($_SESSION['admin_error']);

$titulo_pagina = ($action == 'new' ? "Nueva Categoría" : ($action == 'edit' ? "Editar Categoría" : "Gestión de Categorías")) . " - MisterJugo";
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
                            <a class="nav-link active" href="categorias.php">
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
                            echo "Nueva Categoría";
                        } elseif ($action == 'edit') {
                            echo "Editar Categoría";
                        } else {
                            echo "Gestión de Categorías";
                        }
                        ?>
                    </h1>
                    <?php if (!$action): ?>
                        <a href="categorias.php?action=new" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Nueva Categoría
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
                    <!-- Formulario para crear o editar categoría -->
                    <div class="card shadow">
                        <div class="card-body">
                            <form action="procesar_categoria.php" method="POST">
                                <?php if ($action == 'edit'): ?>
                                    <input type="hidden" name="id" value="<?php echo $categoria['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre de la Categoría</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?php echo ($action == 'edit' && $categoria) ? htmlspecialchars($categoria['nombre']) : ''; ?>"
                                           required maxlength="100">
                                    <div class="form-text">Ingrese un nombre descriptivo para la categoría.</div>
                                </div>
                                
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="categorias.php" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        <?php echo ($action == 'edit') ? 'Actualizar Categoría' : 'Crear Categoría'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Tabla de categorías -->
                    <div class="card shadow">
                        <div class="card-body">
                            <?php if (count($categorias) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Productos</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categorias as $cat): ?>
                                                <?php
                                                // Contar productos en esta categoría
                                                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM productos WHERE categoria_id = ?");
                                                $stmt->bind_param("i", $cat['id']);
                                                $stmt->execute();
                                                $resultado = $stmt->get_result();
                                                $total_productos = $resultado->fetch_assoc()['total'];
                                                ?>
                                                <tr>
                                                    <td><?php echo $cat['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                                                    <td><?php echo $total_productos; ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="categorias.php?action=edit&id=<?php echo $cat['id']; ?>" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                    <?php echo ($total_productos > 0) ? 'disabled' : ''; ?>
                                                                    onclick="confirmarEliminacion(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['nombre']); ?>')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center p-4">
                                    <p class="text-muted mb-3">No hay categorías registradas aún.</p>
                                    <a href="categorias.php?action=new" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i> Crear Primera Categoría
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
                    <p>¿Estás seguro de que deseas eliminar la categoría <strong id="deleteItemName"></strong>?</p>
                    <p class="text-danger">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Eliminar</a>
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
            document.getElementById('confirmDeleteBtn').href = 'eliminar_categoria.php?id=' + id;
            
            // Mostrar el modal usando Bootstrap
            var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
    </script>
</body>
</html>