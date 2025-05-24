<!-- Barra de navegación flotante para productos - Versión móvil mejorada -->
<script src="js/loader.js"></script>
<script src="js/cache_control.js"></script>
<link rel="stylesheet" href="/css/navbar.css">
<div class="top-nav-bar" id="floating-navbar">
    <div class="container">
        <div class="nav-controls">
            <!-- Botón volver - siempre visible -->
            <div class="back-control">
                <a href="javascript:history.back()" class="back-button-nav">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver</span>
                </a>
            </div>
            
            <!-- Búsqueda -->
            <div class="search-control">
                <form action="" method="GET" class="search-form">
                    <?php if($categoria_id > 0): ?>
                        <input type="hidden" name="categoria" value="<?php echo $categoria_id; ?>">
                    <?php endif; ?>
                    <?php if($orden != 'nombre_asc'): ?>
                        <input type="hidden" name="orden" value="<?php echo $orden; ?>">
                    <?php endif; ?>
                    
                    <div class="search-input-group">
                        <input type="text" name="buscar" placeholder="Buscar..." value="<?php echo htmlspecialchars($busqueda); ?>">
                        <button type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <?php if(!empty($busqueda)): ?>
                    <a href="<?php echo $categoria_id > 0 ? '/productos?categoria='.$categoria_id : '/productos'; ?><?php echo ($orden != 'nombre_asc') ? '&orden='.$orden : ''; ?>" class="clear-search">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Control de ordenamiento con icono que cambia -->
            <div class="order-control">
                <form action="" method="GET" class="order-form">
                    <?php if($categoria_id > 0): ?>
                        <input type="hidden" name="categoria" value="<?php echo $categoria_id; ?>">
                    <?php endif; ?>
                    <?php if(!empty($busqueda)): ?>
                        <input type="hidden" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>">
                    <?php endif; ?>
                    
                    <button type="button" id="sort-toggle" class="sort-toggle">
                        <i class="fas fa-sort-alpha-down" id="sort-icon"></i>
                    </button>
                    
                    <select name="orden" id="orden" style="display: none;">
                        <option value="nombre_asc" <?php echo ($orden == 'nombre_asc') ? 'selected' : ''; ?>>Nombre (A-Z)</option>
                        <option value="nombre_desc" <?php echo ($orden == 'nombre_desc') ? 'selected' : ''; ?>>Nombre (Z-A)</option>
                        <option value="precio_asc" <?php echo ($orden == 'precio_asc') ? 'selected' : ''; ?>>Precio (menor a mayor)</option>
                        <option value="precio_desc" <?php echo ($orden == 'precio_desc') ? 'selected' : ''; ?>>Precio (mayor a menor)</option>
                    </select>
                </form>
            </div>
            
            <!-- Categorías -->
            <div class="categories-nav">
                <button class="nav-arrow prev" id="cat-prev">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="categories-scroll" id="categories-scroll">
                    <a href="/productos" class="category-btn <?php echo ($categoria_id == 0) ? 'active' : ''; ?>">Todos</a>
                    <?php foreach($categorias as $cat): ?>
                        <a href="/productos?categoria=<?php echo $cat['id']; ?><?php echo (!empty($busqueda)) ? '&buscar='.urlencode($busqueda) : ''; ?><?php echo ($orden != 'nombre_asc') ? '&orden='.$orden : ''; ?>" 
                           class="category-btn <?php echo ($categoria_id == $cat['id']) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <button class="nav-arrow next" id="cat-next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad para la barra de navegación flotante
    const navbar = document.getElementById('floating-navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 10) {
                navbar.classList.add('floating');
            } else {
                navbar.classList.remove('floating');
            }
        });
    }
    
    // Funcionalidad para el botón de ordenamiento
    const sortToggle = document.getElementById('sort-toggle');
    const ordenSelect = document.getElementById('orden');
    const sortIcon = document.getElementById('sort-icon');
    
    if (sortToggle && ordenSelect && sortIcon) {
        // Inicializar icono según orden actual
        updateSortIcon(ordenSelect.value);
        
        sortToggle.addEventListener('click', function() {
            // Determinar el orden actual y cambiarlo
            const currentOrder = ordenSelect.value;
            let newOrder;
            
            switch (currentOrder) {
                case 'nombre_asc':
                    newOrder = 'nombre_desc';
                    break;
                case 'nombre_desc':
                    newOrder = 'precio_asc';
                    break;
                case 'precio_asc':
                    newOrder = 'precio_desc';
                    break;
                case 'precio_desc':
                    newOrder = 'nombre_asc';
                    break;
                default:
                    newOrder = 'nombre_asc';
            }
            
            // Actualizar el valor del select
            ordenSelect.value = newOrder;
            updateSortIcon(newOrder);
            
            // Enviar el formulario
            ordenSelect.form.submit();
        });
        
        function updateSortIcon(order) {
            switch (order) {
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
                default:
                    sortIcon.className = 'fas fa-sort-alpha-down';
            }
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
        
        // Mostrar/ocultar flechas según el scroll
        function updateArrows() {
            const scrollLeft = categoriesScroll.scrollLeft;
            const scrollWidth = categoriesScroll.scrollWidth;
            const clientWidth = categoriesScroll.clientWidth;
            
            catPrevBtn.style.opacity = scrollLeft <= 0 ? '0.5' : '1';
            catNextBtn.style.opacity = scrollLeft >= scrollWidth - clientWidth ? '0.5' : '1';
        }
        
        categoriesScroll.addEventListener('scroll', updateArrows);
        updateArrows(); // Inicializar
    }
    
    // Ajuste de margin-top para el contenido principal en móvil
    function adjustMainContentMargin() {
        const navbar = document.getElementById('floating-navbar');
        const mainContent = document.querySelector('.productos-page .main-content');
        
        if (navbar && mainContent && window.innerWidth <= 480) {
            const navbarHeight = navbar.offsetHeight;
            mainContent.style.marginTop = (navbarHeight + 10) + 'px';
        } else if (mainContent) {
            mainContent.style.marginTop = '';
        }
    }
    
    // Ajustar al cargar y al redimensionar
    adjustMainContentMargin();
    window.addEventListener('resize', adjustMainContentMargin);
});
</script>