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
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad de navegación de categorías
    const categoriesScroll = document.getElementById('categories-scroll');
    const catPrevBtn = document.getElementById('cat-prev');
    const catNextBtn = document.getElementById('cat-next');
    
    if (categoriesScroll && catPrevBtn && catNextBtn) {
        catPrevBtn.addEventListener('click', function() {
            categoriesScroll.scrollBy({ left: -200, behavior: 'smooth' });
        });
        
        catNextBtn.addEventListener('click', function() {
            categoriesScroll.scrollBy({ left: 200, behavior: 'smooth' });
        });
    }
    
    // Referencias a elementos DOM
    const productoDetailsModal = document.getElementById('producto-details-modal');
    const modalTitle = document.getElementById('modal-title');
    const productoDetailsContent = document.getElementById('producto-details-content');
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    const btnCloseCart = document.getElementById('btn-close-cart');
    const cartFloatBtn = document.getElementById('cart-float-btn');
    const cartItems = document.getElementById('cart-items');
    const cartTotalAmount = document.getElementById('cart-total-amount');
    const cartCount = document.querySelector('.cart-count');
    
    // Agregar eventos a los botones "Ver detalles" y "Añadir al carrito"
    function setupProductButtons() {
        // Botones "Ver detalles"
        const btnViewDetails = document.querySelectorAll('.btn-view-details');
        btnViewDetails.forEach(btn => {
            btn.addEventListener('click', function() {
                const productoId = this.getAttribute('data-id');
                openDetailsModal(productoId);
            });
        });
        
        // Botones "Añadir al carrito"
        const btnAddCart = document.querySelectorAll('.btn-add-cart');
        btnAddCart.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const precio = this.getAttribute('data-precio');
                const imagen = this.getAttribute('data-imagen');
                
                addToCart(id, nombre, precio, imagen);
                openCartSidebar(); // Abre el carrito lateral al añadir un producto
            });
        });
    }
    
    // Llamar a la función para configurar los botones
    setupProductButtons();
    
    // Eventos para el carrito lateral
    if (cartFloatBtn) {
        cartFloatBtn.addEventListener('click', openCartSidebar);
    }
    if (btnCloseCart) {
        btnCloseCart.addEventListener('click', closeCartSidebar);
    }
    if (cartOverlay) {
        cartOverlay.addEventListener('click', closeCartSidebar);
    }
    
    // Evento para cerrar el modal
    const closeModal = document.querySelector('.close-modal');
    if (closeModal) {
        closeModal.addEventListener('click', closeDetailsModal);
    }
    if (productoDetailsModal) {
        productoDetailsModal.addEventListener('click', function(e) {
            if (e.target === productoDetailsModal) {
                closeDetailsModal();
            }
        });
    }
    
    // Función para abrir el modal de detalles
    function openDetailsModal(productoId) {
        if (!productoDetailsModal || !modalTitle || !productoDetailsContent) return;
        
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
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
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
                                <div class="producto-modal-category">
                                    <h4>Categoría:</h4>
                                    <p>${data.producto.categoria_nombre}</p>
                                </div>
                                <div class="producto-modal-actions">
                                    <div class="quantity-selector">
                                        <button class="quantity-btn minus" data-action="decrease">-</button>
                                        <input type="number" class="quantity-input" id="modal-quantity" value="1" min="1" max="10">
                                        <button class="quantity-btn plus" data-action="increase">+</button>
                                    </div>
                                    <button class="btn-add-cart-modal" data-id="${data.producto.id}" data-nombre="${data.producto.nombre}" data-precio="${data.producto.precio}" data-imagen="${data.producto.imagen || '/images/producto-default.jpg'}">
                                        <i class="fas fa-cart-plus"></i> Añadir al carrito
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
                    if (btnAddCartModal) {
                        btnAddCartModal.addEventListener('click', function() {
                            const id = this.getAttribute('data-id');
                            const nombre = this.getAttribute('data-nombre');
                            const precio = this.getAttribute('data-precio');
                            const imagen = this.getAttribute('data-imagen');
                            const cantidad = parseInt(quantityInput.value);
                            
                            addToCart(id, nombre, precio, imagen, cantidad);
                            closeDetailsModal();
                            openCartSidebar();
                        });
                    }
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
    function addToCart(id, nombre, precio, imagen, cantidad = 1) {
        if (!id || !nombre || !precio) {
            console.error('Datos incompletos para añadir al carrito:', { id, nombre, precio, imagen });
            return;
        }
        
        // Crear objeto de producto
        const producto = {
            id: id,
            nombre: nombre,
            precio: parseFloat(precio),
            imagen: imagen || '/images/producto-default.jpg',
            cantidad: cantidad
        };
        
        // Obtener carrito actual o inicializar uno nuevo
        let carrito = [];
        try {
            const carritoGuardado = localStorage.getItem('mjCarrito');
            carrito = carritoGuardado ? JSON.parse(carritoGuardado) : [];
        } catch (e) {
            console.error('Error al obtener carrito:', e);
            carrito = [];
        }
        
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
        
        // Actualizar UI del carrito
        actualizarCarritoUI();
    }
    
    // Función para actualizar la UI del carrito
    function actualizarCarritoUI() {
        if (!cartItems || !cartTotalAmount || !cartCount) return;
        
        // Obtener carrito actual o inicializar uno nuevo
        let carrito = [];
        try {
            const carritoGuardado = localStorage.getItem('mjCarrito');
            carrito = carritoGuardado ? JSON.parse(carritoGuardado) : [];
        } catch (e) {
            console.error('Error al obtener carrito:', e);
            carrito = [];
        }
        
        // Actualizar contador
        const cantidadTotal = carrito.reduce((total, item) => total + item.cantidad, 0);
        cartCount.textContent = cantidadTotal;
        
        // Mostrar u ocultar el botón flotante según si hay items
        if (cantidadTotal > 0 && cartFloatBtn) {
            cartFloatBtn.classList.add('active');
        } else if (cartFloatBtn) {
            cartFloatBtn.classList.remove('active');
        }
        
        // Actualizar contenido del carrito
        if (carrito.length === 0) {
            cartItems.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-basket"></i>
                    <p>Tu carrito está vacío</p>
                </div>
            `;
            cartTotalAmount.textContent = 'S/0.00';
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
                            <h4 class="cart-item-name">${item.nombre}</h4>
                            <div class="cart-item-price">S/${item.precio.toFixed(2)}</div>
                            <div class="cart-item-quantity">
                                <button class="cart-quantity-btn minus" data-id="${item.id}">-</button>
                                <span>${item.cantidad}</span>
                                <button class="cart-quantity-btn plus" data-id="${item.id}">+</button>
                            </div>
                        </div>
                        <div class="cart-item-subtotal">
                            <span>S/${subtotal.toFixed(2)}</span>
                            <button class="cart-item-remove" data-id="${item.id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            
            cartItems.innerHTML = html;
            cartTotalAmount.textContent = `S/${total.toFixed(2)}`;
            
            // Agregar eventos a los botones de cantidad del carrito
            setupCartButtons();
        }
    }
    
    // Configurar botones del carrito
    function setupCartButtons() {
        const btnMinus = cartItems.querySelectorAll('.cart-quantity-btn.minus');
        const btnPlus = cartItems.querySelectorAll('.cart-quantity-btn.plus');
        const btnRemove = cartItems.querySelectorAll('.cart-item-remove');
        
        btnMinus.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                updateCartItemQuantity(id, 'decrease');
            });
        });
        
        btnPlus.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                updateCartItemQuantity(id, 'increase');
            });
        });
        
        btnRemove.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                removeCartItem(id);
            });
        });
    }
    
    // Función para actualizar cantidad de un item en el carrito
    function updateCartItemQuantity(id, action) {
        let carrito = [];
        try {
            const carritoGuardado = localStorage.getItem('mjCarrito');
            carrito = carritoGuardado ? JSON.parse(carritoGuardado) : [];
        } catch (e) {
            console.error('Error al obtener carrito:', e);
            return;
        }
        
        const index = carrito.findIndex(item => item.id === id);
        
        if (index !== -1) {
            if (action === 'increase') {
                carrito[index].cantidad += 1;
            } else if (action === 'decrease') {
                if (carrito[index].cantidad > 1) {
                    carrito[index].cantidad -= 1;
                } else {
                    removeCartItem(id);
                    return;
                }
            }
            
            localStorage.setItem('mjCarrito', JSON.stringify(carrito));
            actualizarCarritoUI();
        }
    }
    
    // Función para eliminar un item del carrito
    function removeCartItem(id) {
        let carrito = [];
        try {
            const carritoGuardado = localStorage.getItem('mjCarrito');
            carrito = carritoGuardado ? JSON.parse(carritoGuardado) : [];
        } catch (e) {
            console.error('Error al obtener carrito:', e);
            return;
        }
        
        carrito = carrito.filter(item => item.id !== id);
        localStorage.setItem('mjCarrito', JSON.stringify(carrito));
        actualizarCarritoUI();
    }
    
    // Función para abrir el carrito lateral
    function openCartSidebar() {
        if (!cartSidebar || !cartOverlay) return;
        
        cartSidebar.classList.add('active');
        cartOverlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Evitar scroll
    }
    
    // Función para cerrar el carrito lateral
    function closeCartSidebar() {
        if (!cartSidebar || !cartOverlay) return;
        
        cartSidebar.classList.remove('active');
        cartOverlay.classList.remove('active');
        document.body.style.overflow = ''; // Restaurar scroll
    }
    
    // Función para cerrar el modal
    function closeDetailsModal() {
        if (!productoDetailsModal) return;
        
        productoDetailsModal.classList.remove('active');
        document.body.style.overflow = ''; // Restaurar scroll
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
    
    // Inicializar UI del carrito al cargar la página
    actualizarCarritoUI();
    
    // Funcionalidad de actualización dinámica - agregar event listeners a los nuevos elementos
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                // Verificar si se han agregado nuevos botones
                setupProductButtons();
            }
        });
    });
    
    // Iniciar la observación de cambios en el DOM
    const containerToObserve = document.querySelector('.productos-section');
    if (containerToObserve) {
        observer.observe(containerToObserve, { childList: true, subtree: true });
    }
});

document.querySelector('.btn-checkout').addEventListener('click', function(e) {
    e.preventDefault(); // Evitar redirección inmediata

    const carrito = JSON.parse(localStorage.getItem('mjCarrito')) || [];

    if (carrito.length === 0) {
        alert("Tu carrito está vacío.");
        return;
    }

    fetch('/backend/guardar_carrito.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(carrito)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/checkout'; // Redirige al checkout solo si se guardó
        } else {
            alert("Hubo un error al procesar tu carrito.");
        }
    });
});
</script>