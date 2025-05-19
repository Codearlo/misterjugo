<?php
// No incluir el header principal
session_start();

// Conectar a la base de datos
require_once '../backend/conexion.php';

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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - MisterJugo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/productos.css">
    <link rel="stylesheet" href="/css/navbar.css">
</head>
<body>
    <!-- Botón volver -->
    <a href="javascript:history.back()" class="back-button">
        <i class="fas fa-arrow-left"></i> Volver
    </a>

    <!-- Inicio de la página de productos -->
    <div class="productos-page">
        <!-- Incluir la barra de navegación -->
        <?php include 'includes/navbar.php'; ?>

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

    <!-- Incluir carrito desde archivo separado -->
    <?php include 'carrito.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Funcionalidad para la barra de navegación flotante
        const navbar = document.getElementById('floating-navbar');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 10) {
                navbar.classList.add('floating');
            } else {
                navbar.classList.remove('floating');
            }
        });
        
        // Funcionalidad para el botón de ordenamiento
        const sortToggle = document.getElementById('sort-toggle');
        const ordenSelect = document.getElementById('orden');
        const sortIcon = document.getElementById('sort-icon');
        
        if (sortToggle && ordenSelect) {
            sortToggle.addEventListener('click', function() {
                // Determinar el orden actual y cambiarlo
                const currentOrder = ordenSelect.value;
                let newOrder;
                
                switch (currentOrder) {
                    case 'nombre_asc':
                        newOrder = 'nombre_desc';
                        sortIcon.className = 'fas fa-sort-alpha-up';
                        break;
                    case 'nombre_desc':
                        newOrder = 'precio_asc';
                        sortIcon.className = 'fas fa-sort-numeric-down';
                        break;
                    case 'precio_asc':
                        newOrder = 'precio_desc';
                        sortIcon.className = 'fas fa-sort-numeric-up';
                        break;
                    case 'precio_desc':
                        newOrder = 'nombre_asc';
                        sortIcon.className = 'fas fa-sort-alpha-down';
                        break;
                    default:
                        newOrder = 'nombre_asc';
                        sortIcon.className = 'fas fa-sort-alpha-down';
                }
                
                // Actualizar el valor del select
                ordenSelect.value = newOrder;
                
                // Enviar el formulario
                ordenSelect.form.submit();
            });
        }
        
        // Inicializar icono de ordenamiento según el orden actual
        if (sortIcon) {
            const currentOrder = ordenSelect.value;
            switch (currentOrder) {
                case 'nombre_asc':
                    sortIcon.className = 'fas fa-sort-alpha-down';
                    break;
                case 'nombre_desc':
                    sortIcon.className = 'fas fa-sort-alpha-up';
                    break;
                case 'precio_asc':
                    sortIcon.className = 'fas fa-sort-numeric-down';
                    break;
                case 'precio_desc':
                    sortIcon.className = 'fas fa-sort-numeric-up';
                    break;
            }
        }
        
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
        
        // Botones "Ver detalles"
        const btnViewDetails = document.querySelectorAll('.btn-view-details');
        const productoDetailsModal = document.getElementById('producto-details-modal');
        const modalTitle = document.getElementById('modal-title');
        const productoDetailsContent = document.getElementById('producto-details-content');
        const closeModal = document.querySelector('.close-modal');
        
        btnViewDetails.forEach(btn => {
            btn.addEventListener('click', function() {
                const productoId = this.getAttribute('data-id');
                
                // Mostrar el modal
                if (productoDetailsModal) {
                    productoDetailsModal.classList.add('active');
                    document.body.style.overflow = 'hidden'; // Evitar scroll
                }
                
                // Actualizar contenido
                if (modalTitle) modalTitle.textContent = 'Cargando detalles...';
                if (productoDetailsContent) {
                    productoDetailsContent.innerHTML = `
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Cargando detalles...</p>
                        </div>
                    `;
                }
                
                // Cargar detalles via AJAX
                fetch(`/backend/obtener_detalles_producto.php?id=${productoId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            modalTitle.textContent = data.producto.nombre;
                            
                            // Construir HTML con detalles
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
                            
                            // Eventos para botones de cantidad
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
                            
                            // Evento para añadir al carrito
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
            });
        });
        
        // Cerrar modal
        if (closeModal && productoDetailsModal) {
            closeModal.addEventListener('click', closeDetailsModal);
            productoDetailsModal.addEventListener('click', function(e) {
                if (e.target === productoDetailsModal) {
                    closeDetailsModal();
                }
            });
        }
        
        function closeDetailsModal() {
            if (productoDetailsModal) {
                productoDetailsModal.classList.remove('active');
                document.body.style.overflow = ''; // Restaurar scroll
            }
        }
        
        // Botones "Añadir al carrito"
        const btnAddCart = document.querySelectorAll('.btn-add-cart');
        btnAddCart.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const precio = this.getAttribute('data-precio');
                const imagen = this.getAttribute('data-imagen');
                
                // Usar la función global para añadir al carrito
                addToCart(id, nombre, precio, imagen);
            });
        });
    });
    </script>
</body>
</html>