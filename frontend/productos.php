<?php
// Incluir el archivo header
include 'includes/header.php';

// Conectar a la base de datos
require_once '../backend/conexion.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Variables para filtrar productos
$categoria_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'nombre_asc';

// Obtener todas las categorías para el filtro
$categorias = [];
$sql_categorias = "SELECT * FROM categorias ORDER BY nombre";
$result_categorias = $conn->query($sql_categorias);

if ($result_categorias->num_rows > 0) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Construir consulta SQL base
$sql_productos = "SELECT p.*, c.nombre as categoria_nombre 
                 FROM productos p 
                 LEFT JOIN categorias c ON p.categoria_id = c.id 
                 WHERE p.disponible = 1";

// Añadir condiciones según filtros
if ($categoria_id > 0) {
    $sql_productos .= " AND p.categoria_id = $categoria_id";
}

if (!empty($busqueda)) {
    $busqueda = $conn->real_escape_string($busqueda);
    $sql_productos .= " AND (p.nombre LIKE '%$busqueda%' OR p.descripcion LIKE '%$busqueda%')";
}

// Ordenar resultados
switch ($orden) {
    case 'precio_asc':
        $sql_productos .= " ORDER BY p.precio ASC";
        break;
    case 'precio_desc':
        $sql_productos .= " ORDER BY p.precio DESC";
        break;
    case 'nombre_desc':
        $sql_productos .= " ORDER BY p.nombre DESC";
        break;
    default:
        $sql_productos .= " ORDER BY p.nombre ASC";
}

// Ejecutar consulta
$result_productos = $conn->query($sql_productos);
$productos = [];

if ($result_productos && $result_productos->num_rows > 0) {
    while ($row = $result_productos->fetch_assoc()) {
        $productos[] = $row;
    }
}
?>

<link rel="stylesheet" href="/css/productos.css">

<!-- Inicio de la página de productos -->
<div class="productos-page">
    <div class="main-content">
        <div class="container">
            <!-- Banner principal -->
            <div class="productos-banner">
                <h1 class="page-title">Nuestros Productos</h1>
                <div class="title-underline"></div>
                <p class="page-subtitle">Descubre nuestras deliciosas opciones de jugos y bebidas saludables</p>
            </div>
            
            <!-- Sección de filtros y búsqueda -->
            <div class="filter-section">
                <form class="filter-form" action="" method="GET">
                    <div class="filter-group">
                        <label for="categoria">Categoría:</label>
                        <select name="categoria" id="categoria">
                            <option value="0">Todas las categorías</option>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($categoria_id == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="orden">Ordenar por:</label>
                        <select name="orden" id="orden">
                            <option value="nombre_asc" <?php echo ($orden == 'nombre_asc') ? 'selected' : ''; ?>>Nombre (A-Z)</option>
                            <option value="nombre_desc" <?php echo ($orden == 'nombre_desc') ? 'selected' : ''; ?>>Nombre (Z-A)</option>
                            <option value="precio_asc" <?php echo ($orden == 'precio_asc') ? 'selected' : ''; ?>>Precio (menor a mayor)</option>
                            <option value="precio_desc" <?php echo ($orden == 'precio_desc') ? 'selected' : ''; ?>>Precio (mayor a menor)</option>
                        </select>
                    </div>
                    
                    <div class="search-group">
                        <input type="text" name="buscar" id="buscar" placeholder="Buscar productos..." value="<?php echo htmlspecialchars($busqueda); ?>">
                        <button type="submit" class="btn-search">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <?php if($categoria_id > 0 || !empty($busqueda) || $orden != 'nombre_asc'): ?>
                        <a href="/productos" class="btn-clear-filters">Limpiar filtros</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Sección de productos -->
            <div class="productos-section">
                <?php if(count($productos) > 0): ?>
                    <div class="productos-grid">
                        <?php foreach($productos as $producto): ?>
                            <div class="producto-card">
                                <div class="producto-image">
                                    <?php if(!empty($producto['imagen'])): ?>
                                        <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                    <?php else: ?>
                                        <img src="/images/producto-default.jpg" alt="Imagen no disponible">
                                    <?php endif; ?>
                                    <div class="categoria-badge"><?php echo htmlspecialchars($producto['categoria_nombre']); ?></div>
                                </div>
                                
                                <div class="producto-info">
                                    <h3 class="producto-name"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                    <p class="producto-description"><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 100)) . (strlen($producto['descripcion']) > 100 ? '...' : ''); ?></p>
                                    <div class="producto-price">S/<?php echo number_format($producto['precio'], 2); ?></div>
                                </div>
                                
                                <div class="producto-actions">
                                    <button class="btn-add-cart" data-id="<?php echo $producto['id']; ?>" data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>" data-precio="<?php echo $producto['precio']; ?>">
                                        <i class="fas fa-shopping-cart"></i> Añadir al carrito
                                    </button>
                                    <button class="btn-view-details" data-id="<?php echo $producto['id']; ?>">
                                        <i class="fas fa-eye"></i> Ver detalles
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-products">
                        <div class="empty-state">
                            <i class="fas fa-mug-hot"></i>
                            <h3>No hay productos disponibles</h3>
                            <?php if(!empty($busqueda) || $categoria_id > 0): ?>
                                <p>No se encontraron productos con los filtros seleccionados.</p>
                                <a href="/productos" class="btn-try-again">
                                    <i class="fas fa-sync"></i> Ver todos los productos
                                </a>
                            <?php else: ?>
                                <p>Muy pronto tendremos deliciosas opciones para ti.</p>
                                <a href="/" class="btn-home">
                                    <i class="fas fa-home"></i> Volver al inicio
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal de detalles de producto -->
    <div class="modal" id="producto-details-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Detalles del Producto</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="producto-details-content">
                    <!-- Aquí se cargarán los detalles del producto mediante AJAX -->
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Cargando detalles...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos DOM
    const productoDetailsModal = document.getElementById('producto-details-modal');
    const closeModal = document.querySelector('.close-modal');
    const modalTitle = document.getElementById('modal-title');
    const productoDetailsContent = document.getElementById('producto-details-content');
    const btnViewDetails = document.querySelectorAll('.btn-view-details');
    const btnAddCart = document.querySelectorAll('.btn-add-cart');
    
    // Función para abrir el modal de detalles
    function openDetailsModal(productoId) {
        modalTitle.textContent = 'Cargando detalles...';
        productoDetailsContent.innerHTML = `
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Cargando detalles...</p>
            </div>
        `;
        
        // Mostrar el modal
        productoDetailsModal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Evitar scroll
        
        // Cargar los detalles del producto mediante AJAX
        fetch(`/backend/obtener_detalles_producto.php?id=${productoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalTitle.textContent = data.producto.nombre;
                    
                    // Construir el HTML para los detalles del producto
                    let html = `
                        <div class="producto-modal-content">
                            <div class="producto-modal-image">
                                <img src="${data.producto.imagen || '/images/producto-default.jpg'}" alt="${data.producto.nombre}">
                                <div class="categoria-badge">${data.producto.categoria_nombre}</div>
                            </div>
                            <div class="producto-modal-info">
                                <div class="producto-modal-price">
                                    <span class="price-label">Precio:</span>
                                    <span class="price-value">S/${parseFloat(data.producto.precio).toFixed(2)}</span>
                                </div>
                                <div class="producto-modal-description">
                                    <h4>Descripción:</h4>
                                    <p>${data.producto.descripcion}</p>
                                </div>
                                <div class="producto-modal-actions">
                                    <div class="quantity-selector">
                                        <button class="quantity-btn minus" data-action="decrease">-</button>
                                        <input type="number" class="quantity-input" value="1" min="1" max="10">
                                        <button class="quantity-btn plus" data-action="increase">+</button>
                                    </div>
                                    <button class="btn-add-cart-modal" data-id="${data.producto.id}" data-nombre="${data.producto.nombre}" data-precio="${data.producto.precio}">
                                        <i class="fas fa-shopping-cart"></i> Añadir al carrito
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    productoDetailsContent.innerHTML = html;
                    
                    // Agregar eventos a los botones de cantidad
                    const quantityBtns = productoDetailsContent.querySelectorAll('.quantity-btn');
                    const quantityInput = productoDetailsContent.querySelector('.quantity-input');
                    
                    quantityBtns.forEach(btn => {
                        btn.addEventListener('click', function() {
                            const action = this.getAttribute('data-action');
                            let currentValue = parseInt(quantityInput.value);
                            
                            if (action === 'increase') {
                                if (currentValue < 10) {
                                    quantityInput.value = currentValue + 1;
                                }
                            } else if (action === 'decrease') {
                                if (currentValue > 1) {
                                    quantityInput.value = currentValue - 1;
                                }
                            }
                        });
                    });
                    
                    // Evento para añadir al carrito desde el modal
                    const btnAddCartModal = productoDetailsContent.querySelector('.btn-add-cart-modal');
                    btnAddCartModal.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const nombre = this.getAttribute('data-nombre');
                        const precio = this.getAttribute('data-precio');
                        const cantidad = parseInt(quantityInput.value);
                        
                        addToCart(id, nombre, precio, cantidad);
                    });
                } else {
                    productoDetailsContent.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>No se pudieron cargar los detalles del producto</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                productoDetailsContent.innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Ocurrió un error al cargar los detalles</p>
                    </div>
                `;
            });
    }
    
    // Función para añadir producto al carrito
    function addToCart(id, nombre, precio, cantidad = 1) {
        // Crear objeto de producto
        const producto = {
            id: id,
            nombre: nombre,
            precio: parseFloat(precio),
            cantidad: cantidad
        };
        
        // Obtener carrito actual o inicializar uno nuevo
        let carrito = JSON.parse(localStorage.getItem('mjCarrito')) || [];
        
        // Verificar si el producto ya está en el carrito
        const productoExistente = carrito.findIndex(item => item.id === id);
        
        if (productoExistente !== -1) {
            // Actualizar cantidad si ya existe
            carrito[productoExistente].cantidad += cantidad;
        } else {
            // Agregar nuevo producto
            carrito.push(producto);
        }
        
        // Guardar carrito actualizado
        localStorage.setItem('mjCarrito', JSON.stringify(carrito));
        
        // Mostrar notificación
        mostrarNotificacion(`${nombre} agregado al carrito`);
        
        // Actualizar contador del carrito
        actualizarContadorCarrito();
    }
    
    // Función para mostrar notificación
    function mostrarNotificacion(mensaje) {
        // Crear elemento de notificación
        const notificacion = document.createElement('div');
        notificacion.className = 'notification success-notification';
        notificacion.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <p>${mensaje}</p>
        `;
        
        // Agregar al DOM
        document.body.appendChild(notificacion);
        
        // Mostrar con animación
        setTimeout(() => {
            notificacion.classList.add('show');
        }, 100);
        
        // Ocultar después de 3 segundos
        setTimeout(() => {
            notificacion.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notificacion);
            }, 300);
        }, 3000);
    }
    
    // Función para actualizar contador del carrito
    function actualizarContadorCarrito() {
        const carrito = JSON.parse(localStorage.getItem('mjCarrito')) || [];
        const contador = document.querySelector('.cart-count');
        
        if (contador) {
            const totalItems = carrito.reduce((total, item) => total + item.cantidad, 0);
            contador.textContent = totalItems;
            
            if (totalItems > 0) {
                contador.classList.add('active');
            } else {
                contador.classList.remove('active');
            }
        }
    }
    
    // Función para cerrar el modal
    function closeDetailsModal() {
        productoDetailsModal.classList.remove('active');
        document.body.style.overflow = ''; // Restaurar scroll
    }
    
    // Eventos para abrir modal de detalles
    btnViewDetails.forEach(btn => {
        btn.addEventListener('click', function() {
            const productoId = this.getAttribute('data-id');
            openDetailsModal(productoId);
        });
    });
    
    // Eventos para añadir al carrito
    btnAddCart.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            const precio = this.getAttribute('data-precio');
            
            addToCart(id, nombre, precio);
        });
    });
    
    // Eventos para cerrar el modal
    closeModal.addEventListener('click', closeDetailsModal);
    
    productoDetailsModal.addEventListener('click', function(e) {
        if (e.target === productoDetailsModal) {
            closeDetailsModal();
        }
    });
    
    // Actualizar contador del carrito al cargar la página
    actualizarContadorCarrito();
    
    // Eventos de filtros
    const categoriaSelect = document.getElementById('categoria');
    const ordenSelect = document.getElementById('orden');
    
    if (categoriaSelect) {
        categoriaSelect.addEventListener('change', function() {
            document.querySelector('.filter-form').submit();
        });
    }
    
    if (ordenSelect) {
        ordenSelect.addEventListener('change', function() {
            document.querySelector('.filter-form').submit();
        });
    }
});
</script>

<?php
// Incluir el footer
include 'includes/footer.php';
?>