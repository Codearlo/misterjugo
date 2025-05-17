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

if ($result_categorias && $result_categorias->num_rows > 0) {
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
    <!-- Barra superior de navegación y filtros -->
    <div class="top-nav-bar">
        <div class="container">
            <div class="nav-controls">
                <!-- Control de ordenamiento a la izquierda -->
                <div class="order-control">
                    <form action="" method="GET" class="order-form">
                        <?php if($categoria_id > 0): ?>
                            <input type="hidden" name="categoria" value="<?php echo $categoria_id; ?>">
                        <?php endif; ?>
                        <?php if(!empty($busqueda)): ?>
                            <input type="hidden" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>">
                        <?php endif; ?>
                        
                        <label for="orden">Ordenar:</label>
                        <select name="orden" id="orden" onchange="this.form.submit()">
                            <option value="nombre_asc" <?php echo ($orden == 'nombre_asc') ? 'selected' : ''; ?>>Nombre (A-Z)</option>
                            <option value="nombre_desc" <?php echo ($orden == 'nombre_desc') ? 'selected' : ''; ?>>Nombre (Z-A)</option>
                            <option value="precio_asc" <?php echo ($orden == 'precio_asc') ? 'selected' : ''; ?>>Precio (menor a mayor)</option>
                            <option value="precio_desc" <?php echo ($orden == 'precio_desc') ? 'selected' : ''; ?>>Precio (mayor a menor)</option>
                        </select>
                    </form>
                </div>
                
                <!-- Categorías en el centro -->
                <div class="categories-nav">
                    <button class="nav-arrow prev" id="cat-prev">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="categories-scroll" id="categories-scroll">
                        <a href="/productos" class="category-btn <?php echo ($categoria_id == 0) ? 'active' : ''; ?>">Todos</a>
                        <?php foreach($categorias as $cat): ?>
                            <a href="/productos?categoria=<?php echo $cat['id']; ?>" class="category-btn <?php echo ($categoria_id == $cat['id']) ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <button class="nav-arrow next" id="cat-next">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <!-- Búsqueda a la derecha -->
                <div class="search-control">
                    <form action="" method="GET" class="search-form">
                        <?php if($categoria_id > 0): ?>
                            <input type="hidden" name="categoria" value="<?php echo $categoria_id; ?>">
                        <?php endif; ?>
                        
                        <div class="search-input-group">
                            <input type="text" name="buscar" placeholder="Buscar..." value="<?php echo htmlspecialchars($busqueda); ?>">
                            <button type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    
                    <?php if(!empty($busqueda)): ?>
                        <a href="<?php echo $categoria_id > 0 ? '/productos?categoria='.$categoria_id : '/productos'; ?>" class="clear-search">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <!-- Sección de productos -->
            <div class="productos-section">
                <?php if(count($productos) > 0): ?>
                    <div class="productos-grid">
                        <?php foreach($productos as $producto): ?>
                            <div class="producto-card">
                                <div class="categoria-badge"><?php echo htmlspecialchars($producto['categoria_nombre']); ?></div>
                                <div class="producto-image">
                                    <?php if(!empty($producto['imagen'])): ?>
                                        <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                    <?php else: ?>
                                        <img src="/images/producto-default.jpg" alt="Imagen no disponible">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="producto-info">
                                    <h3 class="producto-name"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                    <div class="producto-price">S/<?php echo number_format($producto['precio'], 2); ?></div>
                                </div>
                                
                                <div class="producto-actions">
                                    <button class="btn-add-cart" data-id="<?php echo $producto['id']; ?>" data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>" data-precio="<?php echo $producto['precio']; ?>" data-imagen="<?php echo htmlspecialchars($producto['imagen'] ?: '/images/producto-default.jpg'); ?>">
                                        <i class="fas fa-cart-plus"></i> Añadir
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
    
    <!-- Carrito lateral -->
    <div class="cart-sidebar" id="cart-sidebar">
        <div class="cart-header">
            <h3><i class="fas fa-shopping-cart"></i> Mi Carrito</h3>
            <button class="btn-close-cart" id="btn-close-cart">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="cart-items" id="cart-items">
            <!-- Aquí se cargarán los items del carrito con JS -->
            <div class="empty-cart">
                <i class="fas fa-shopping-basket"></i>
                <p>Tu carrito está vacío</p>
            </div>
        </div>
        
        <div class="cart-summary">
            <div class="cart-total">
                <span>Total:</span>
                <span id="cart-total-amount">S/0.00</span>
            </div>
            <div class="cart-actions">
                <a href="/carrito" class="btn-view-cart">
                    <i class="fas fa-shopping-bag"></i> Ver carrito
                </a>
                <a href="/checkout" class="btn-checkout">
                    <i class="fas fa-check"></i> Finalizar compra
                </a>
            </div>
        </div>
    </div>
    
    <div class="cart-overlay" id="cart-overlay"></div>
    
    <!-- Botón flotante del carrito para móviles -->
    <button class="cart-float-btn" id="cart-float-btn">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count">0</span>
    </button>
    
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
document.addEventListener('DOMContentLoaded', function () {
    // Elementos del DOM
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    const btnCloseCart = document.getElementById('btn-close-cart');
    const cartFloatBtn = document.getElementById('cart-float-btn');

    // Función para agregar al carrito (envía datos al servidor)
    function addToCart(id, nombre, precio, imagen, cantidad = 1) {
        if (!id || !nombre || !precio) {
            console.error('Datos incompletos para añadir al carrito:', { id, nombre, precio });
            return;
        }

        const producto = {
            id: id,
            nombre: nombre,
            precio: parseFloat(precio),
            imagen: imagen || '/images/producto-default.jpg',
            cantidad: parseInt(cantidad)
        };

        fetch('/backend/agregar_al_carrito.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(producto)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion(`${nombre} agregado al carrito`);
                actualizarCarritoUI(); // Actualizar UI desde sesión
                openCartSidebar(); // Abrir carrito automáticamente
            } else {
                alert('Error al agregar el producto al carrito.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al agregar al carrito.');
        });
    }

    // Configurar botones "Añadir al carrito"
    function setupProductButtons() {
        const btnAddCart = document.querySelectorAll('.btn-add-cart');
        btnAddCart.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const precio = this.getAttribute('data-precio');
                const imagen = this.getAttribute('data-imagen');
                addToCart(id, nombre, precio, imagen);
            });
        });

        // Botón "Añadir desde modal"
        const btnAddCartModal = document.querySelector('.btn-add-cart-modal');
        if (btnAddCartModal) {
            btnAddCartModal.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const precio = this.getAttribute('data-precio');
                const imagen = this.getAttribute('data-imagen');
                const cantidad = document.getElementById('modal-quantity')?.value || 1;
                addToCart(id, nombre, precio, imagen, cantidad);
            });
        }
    }

    // Cargar el carrito desde el servidor
    function actualizarCarritoUI() {
        fetch('/backend/obtener_carrito.php')
            .then(response => response.json())
            .then(carrito => {
                const cartItems = document.getElementById('cart-items');
                const cartTotalAmount = document.getElementById('cart-total-amount');
                const cartCount = document.querySelector('.cart-count');

                if (!carrito || carrito.length === 0) {
                    cartItems.innerHTML = `
                        <div class="empty-cart">
                            <i class="fas fa-shopping-basket"></i>
                            <p>Tu carrito está vacío</p>
                        </div>`;
                    cartTotalAmount.textContent = 'S/0.00';
                    cartCount.textContent = '0';

                    if (cartFloatBtn) cartFloatBtn.classList.remove('active');
                } else {
                    let html = '';
                    let total = 0;

                    carrito.forEach(item => {
                        const subtotal = item.precio * item.cantidad;
                        total += subtotal;

                        html += `
                            <div class="cart-item" data-id="${item.id}">
                                <div class="cart-item-image">
                                    <img src="${item.imagen}" alt="${item.nombre}">
                                </div>
                                <div class="cart-item-details">
                                    <h4>${item.nombre}</h4>
                                    <div>S/${parseFloat(item.precio).toFixed(2)}</div>
                                    <div>Cant: ${item.cantidad}</div>
                                </div>
                                <div class="cart-item-subtotal">
                                    S/${subtotal.toFixed(2)}
                                    <button class="cart-item-remove" data-id="${item.id}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>`;
                    });

                    cartItems.innerHTML = html;
                    cartTotalAmount.textContent = `S/${total.toFixed(2)}`;
                    cartCount.textContent = carrito.reduce((sum, item) => sum + item.cantidad, 0);

                    if (cartFloatBtn && cartCount.textContent > 0) {
                        cartFloatBtn.classList.add('active');
                    }

                    // Eliminar items
                    const removeButtons = document.querySelectorAll('.cart-item-remove');
                    removeButtons.forEach(btn => {
                        btn.addEventListener('click', function () {
                            const id = this.getAttribute('data-id');
                            eliminarDelCarrito(id);
                        });
                    });
                }
            });
    }

    // Eliminar del carrito
    function eliminarDelCarrito(id) {
        fetch(`/backend/eliminar_del_carrito.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(() => {
            actualizarCarritoUI();
        });
    }

    // Mostrar notificación
    function mostrarNotificacion(mensaje) {
        const notificacion = document.createElement('div');
        notificacion.className = 'notification success-notification';
        notificacion.innerHTML = `<i class="fas fa-check-circle"></i><p>${mensaje}</p>`;
        document.body.appendChild(notificacion);
        setTimeout(() => notificacion.classList.add('show'), 100);
        setTimeout(() => {
            notificacion.classList.remove('show');
            setTimeout(() => document.body.removeChild(notificacion), 300);
        }, 3000);
    }

    // Abrir carrito lateral
    function openCartSidebar() {
        if (!cartSidebar || !cartOverlay) return;
        cartSidebar.classList.add('active');
        cartOverlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Evitar scroll
    }

    // Cerrar carrito lateral
    function closeCartSidebar() {
        if (!cartSidebar || !cartOverlay) return;
        cartSidebar.classList.remove('active');
        cartOverlay.classList.remove('active');
        document.body.style.overflow = ''; // Restaurar scroll
    }

    // Eventos del carrito
    if (cartFloatBtn) {
        cartFloatBtn.addEventListener('click', openCartSidebar);
    }

    if (btnCloseCart) {
        btnCloseCart.addEventListener('click', closeCartSidebar);
    }

    if (cartOverlay) {
        cartOverlay.addEventListener('click', closeCartSidebar);
    }

    // Inicialización
    setupProductButtons();
    actualizarCarritoUI();
});

// Función para desplazar las categorías
function setupCategoriasScroll() {{
    const scrollContainer = document.getElementById('categories-scroll');
    const btnPrev = document.getElementById('cat-prev');
    const btnNext = document.getElementById('cat-next');

    if (!scrollContainer || !btnPrev || !btnNext) return;

    const scrollAmount = 200; //// Ajusta este valor según lo que se desplace cada vez

    btnPrev.addEventListener('click', ()() => {{
        scrollContainer.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });});
    });});

    btnNext.addEventListener('click', ()() => {{
        scrollContainer.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });});
    });});

    //// Opcional: Mostrar/ocultar flechas si hay scroll
    function toggleArrows() {{
        const {{ scrollLeft, scrollWidth, clientWidth }} = scrollContainer;
        btnPrev.style.display = scrollLeft <= 0 ?? 'none' :: 'block';
        btnNext.style.display = scrollLeft + clientWidth >= scrollWidth ?? 'none' :: 'block';
    }}

    scrollContainer.addEventListener('scroll', toggleArrows);
    window.addEventListener('load', toggleArrows);
}}

//// Llama a esta función al final del DOMContentLoaded
setupCategoriasScroll();
</script>