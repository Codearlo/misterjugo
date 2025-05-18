<!-- Barra de navegación flotante para productos -->
<link rel="stylesheet" href="/css/navbar.css">
<div class="top-nav-bar" id="floating-navbar">
    <div class="container">
        <div class="nav-controls">
            <!-- Control de ordenamiento a la izquierda con icono que cambia -->
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
            
            <!-- Categorías en el centro -->
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
            
            <!-- Búsqueda a la derecha -->
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
        </div>
    </div>
</div>