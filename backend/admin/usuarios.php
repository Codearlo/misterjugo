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
$filtro_admin = isset($_GET['admin']) ? $_GET['admin'] : '';
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

// Construir consulta base
$consulta_base = "FROM usuarios WHERE 1=1";
$params = [];
$tipos = "";

// Aplicar filtros
if ($filtro_admin !== '') {
    $consulta_base .= " AND is_admin = ?";
    $params[] = (int)$filtro_admin;
    $tipos .= "i";
}

if (!empty($busqueda)) {
    $busqueda_param = "%$busqueda%";
    $consulta_base .= " AND (nombre LIKE ? OR email LIKE ?)";
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

// Obtener usuarios
$consulta_usuarios = "SELECT *, DATE_FORMAT(fecha_registro, '%d/%m/%Y %H:%i') as fecha_registro_formateada " . $consulta_base . " ORDER BY fecha_registro DESC LIMIT ?, ?";
$stmt = $conn->prepare($consulta_usuarios);

$tipos .= "ii";
$params[] = $inicio;
$params[] = $registros_por_pagina;

if (!empty($params)) {
    $stmt->bind_param($tipos, ...$params);
}

$stmt->execute();
$usuarios = [];
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

// Obtener mensajes de sesión
$admin_exito = isset($_SESSION['admin_exito']) ? $_SESSION['admin_exito'] : '';
$admin_error = isset($_SESSION['admin_error']) ? $_SESSION['admin_error'] : '';

// Limpiar mensajes de sesión
unset($_SESSION['admin_exito']);
unset($_SESSION['admin_error']);

$titulo_pagina = "Gestión de Usuarios - MisterJugo";
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
                            <a class="nav-link" href="pedidos.php">
                                <i class="fas fa-shopping-cart me-2"></i> Pedidos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="usuarios.php">
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
                    <h1 class="h2">Gestión de Usuarios</h1>
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-3">Total: <?php echo $total_registros; ?> usuarios</span>
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

                <!-- Filtros y buscador -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form method="GET" action="usuarios.php" class="row align-items-end">
                            <div class="col-md-3 mb-3">
                                <label for="admin" class="form-label">Tipo de Usuario</label>
                                <select class="form-select" id="admin" name="admin">
                                    <option value="">Todos</option>
                                    <option value="0" <?php echo ($filtro_admin === '0') ? 'selected' : ''; ?>>
                                        Clientes
                                    </option>
                                    <option value="1" <?php echo ($filtro_admin === '1') ? 'selected' : ''; ?>>
                                        Administradores
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="buscar" class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="buscar" name="buscar" 
                                       placeholder="Nombre o email del usuario"
                                       value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                            <div class="col-md-3 mb-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="fas fa-search me-2"></i> Filtrar
                                </button>
                                <a href="usuarios.php" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tabla de usuarios -->
                <div class="card shadow">
                    <div class="card-body">
                        <?php if (count($usuarios) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Tipo</th>
                                            <th>Fecha Registro</th>
                                            <th>Pedidos</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <?php
                                            // Contar pedidos del usuario
                                            $stmt_pedidos = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ?");
                                            $stmt_pedidos->bind_param("i", $usuario['id']);
                                            $stmt_pedidos->execute();
                                            $resultado_pedidos = $stmt_pedidos->get_result();
                                            $total_pedidos = $resultado_pedidos->fetch_assoc()['total'];
                                            ?>
                                            <tr>
                                                <td><?php echo $usuario['id']; ?></td>
                                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                                <td>
                                                    <?php if ($usuario['is_admin']): ?>
                                                        <span class="badge bg-primary">Administrador</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Cliente</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $usuario['fecha_registro_formateada']; ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $total_pedidos; ?></span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-info" title="Ver detalles" 
                                                                onclick="verDetallesUsuario(<?php echo $usuario['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if (!$usuario['is_admin']): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Restablecer contraseña"
                                                                    onclick="restablecerPassword(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombre']); ?>')">
                                                                <i class="fas fa-key"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Paginación -->
                            <?php if ($total_paginas > 1): ?>
                                <nav aria-label="Paginación de usuarios">
                                    <ul class="pagination justify-content-center mt-4">
                                        <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo ($pagina <= 1) ? '#' : 'usuarios.php?pagina='.($pagina-1).((!empty($filtro_admin)) ? '&admin='.$filtro_admin : '').((!empty($busqueda)) ? '&buscar='.urlencode($busqueda) : ''); ?>" 
                                               aria-label="Anterior">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        
                                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                            <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                                                <a class="page-link" href="usuarios.php?pagina=<?php echo $i; ?><?php echo (!empty($filtro_admin)) ? '&admin='.$filtro_admin : ''; ?><?php echo (!empty($busqueda)) ? '&buscar='.urlencode($busqueda) : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo ($pagina >= $total_paginas) ? '#' : 'usuarios.php?pagina='.($pagina+1).((!empty($filtro_admin)) ? '&admin='.$filtro_admin : '').((!empty($busqueda)) ? '&buscar='.urlencode($busqueda) : ''); ?>" 
                                               aria-label="Siguiente">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="text-center p-4">
                                <p class="text-muted mb-3">No se encontraron usuarios que coincidan con tu búsqueda.</p>
                                <a href="usuarios.php" class="btn btn-secondary">
                                    <i class="fas fa-redo me-2"></i> Limpiar Filtros
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para ver detalles de usuario -->
    <div class="modal fade" id="detallesUsuarioModal" tabindex="-1" aria-labelledby="detallesUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detallesUsuarioModalLabel">Detalles del Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detallesUsuarioBody">
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

    <!-- Modal de confirmación para restablecer contraseña -->
    <div class="modal fade" id="restablecerPasswordModal" tabindex="-1" aria-labelledby="restablecerPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restablecerPasswordModalLabel">Restablecer Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas restablecer la contraseña del usuario <strong id="nombreUsuarioPassword"></strong>?</p>
                    <p class="text-warning">Se generará una nueva contraseña aleatoria.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="confirmRestablecerPasswordBtn" class="btn btn-danger">Restablecer</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Admin JS -->
    <script src="js/admin.js"></script>
    <script>
        // Función para ver detalles del usuario
        function verDetallesUsuario(id) {
            // Mostrar el modal
            var detallesModal = new bootstrap.Modal(document.getElementById('detallesUsuarioModal'));
            detallesModal.show();
            
            // Realizar petición AJAX para obtener detalles
            fetch('obtener_usuario.php?id=' + id)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const usuario = data.usuario;
                        document.getElementById('detallesUsuarioModalLabel').textContent = 'Detalles: ' + usuario.nombre;
                        
                        // Construir contenido HTML para mostrar detalles
                        let detallesHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Información Personal</h5>
                                    <p><strong>Nombre:</strong> ${usuario.nombre}</p>
                                    <p><strong>Email:</strong> ${usuario.email}</p>
                                    <p><strong>Tipo:</strong> ${usuario.is_admin == 1 ? 
                                        '<span class="badge bg-primary">Administrador</span>' : 
                                        '<span class="badge bg-secondary">Cliente</span>'
                                    }</p>
                                    <p><strong>Fecha de Registro:</strong> ${usuario.fecha_registro_formateada}</p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Estadísticas</h5>
                                    <p><strong>Total de Pedidos:</strong> ${usuario.total_pedidos}</p>
                                    <p><strong>Total Gastado:</strong> S/${parseFloat(usuario.total_gastado || 0).toFixed(2)}</p>
                                    <p><strong>Último Pedido:</strong> ${usuario.ultimo_pedido || 'Nunca'}</p>
                                </div>
                            </div>
                            ${usuario.direcciones && usuario.direcciones.length > 0 ? `
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5>Direcciones Registradas</h5>
                                        ${usuario.direcciones.map(dir => `
                                            <div class="card mb-2">
                                                <div class="card-body">
                                                    <p class="mb-1"><strong>${dir.calle}</strong></p>
                                                    <p class="mb-1">${dir.ciudad}, ${dir.estado} - CP: ${dir.codigo_postal}</p>
                                                    <p class="mb-1">Teléfono: ${dir.telefono}</p>
                                                    ${dir.es_predeterminada == 1 ? '<span class="badge bg-primary">Predeterminada</span>' : ''}
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                        `;
                        
                        document.getElementById('detallesUsuarioBody').innerHTML = detallesHTML;
                    } else {
                        document.getElementById('detallesUsuarioBody').innerHTML = `
                            <div class="alert alert-danger" role="alert">
                                ${data.message || 'No se pudo cargar la información del usuario.'}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('detallesUsuarioBody').innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            Error al cargar los detalles del usuario.
                        </div>
                    `;
                });
        }
        
        // Función para restablecer contraseña
        function restablecerPassword(id, nombre) {
            document.getElementById('nombreUsuarioPassword').textContent = nombre;
            document.getElementById('confirmRestablecerPasswordBtn').href = '../procesar_usuario.php?action=reset_password&id=' + id;
            
            var modal = new bootstrap.Modal(document.getElementById('restablecerPasswordModal'));
            modal.show();
        }
    </script>
</body>
</html>