.image-preview .no-image {
        color: #aaa;
        text-align: center;
    }
    
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }
    
    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    input:checked + .slider {
        background-color: var(--admin-primary);
    }
    
    input:focus + .slider {
        box-shadow: 0 0 1px var(--admin-primary);
    }
    
    input:checked + .slider:before {
        transform: translateX(26px);
    }
</style>

<!-- Mensajes de error -->
<?php if (!empty($errores)): ?>
    <div class="notification danger">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <?php foreach ($errores as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="form-container">
    <div class="form-main">
        <form action="procesar_producto.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $es_nuevo ? 'create' : 'update'; ?>">
            <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
            
            <div class="form-group">
                <label for="nombre">Nombre del producto *</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                <?php if (isset($errores['nombre'])): ?>
                    <div class="error"><?php echo $errores['nombre']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                <?php if (isset($errores['descripcion'])): ?>
                    <div class="error"><?php echo $errores['descripcion']; ?></div>
                <?php endif; ?>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="precio">Precio (S/) *</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>
                    <?php if (isset($errores['precio'])): ?>
                        <div class="error"><?php echo $errores['precio']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock *</label>
                    <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($producto['stock']); ?>" required>
                    <?php if (isset($errores['stock'])): ?>
                        <div class="error"><?php echo $errores['stock']; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="categoria_id">Categoría *</label>
                <select id="categoria_id" name="categoria_id" required>
                    <option value="">Seleccionar categoría</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['id']; ?>" <?php echo $producto['categoria_id'] == $categoria['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errores['categoria_id'])): ?>
                    <div class="error"><?php echo $errores['categoria_id']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-actions" style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="productos.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Producto
                </button>
            </div>
        </form>
    </div>
    
    <div class="form-sidebar">
        <div class="sidebar-card">
            <h3 style="margin-top: 0; margin-bottom: 15px;">Imagen del Producto</h3>
            
            <div class="image-preview" id="imagePreview">
                <?php if (!empty($producto['imagen'])): ?>
                    <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Vista previa">
                <?php else: ?>
                    <div class="no-image">
                        <i class="fas fa-image" style="font-size: 3rem; margin-bottom: 10px;"></i>
                        <p>No hay imagen</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <form action="procesar_producto.php" method="POST" enctype="multipart/form-data" id="imageForm">
                <input type="hidden" name="action" value="update_image">
                <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                
                <div class="form-group">
                    <label for="imagen">Seleccionar imagen</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*" style="width: 100%;">
                    <small style="display: block; margin-top: 5px; color: #666;">
                        Formatos permitidos: JPG, PNG, GIF. Máximo 2MB.
                    </small>
                    <?php if (isset($errores['imagen'])): ?>
                        <div class="error"><?php echo $errores['imagen']; ?></div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;" <?php echo $es_nuevo ? 'disabled' : ''; ?>>
                    <i class="fas fa-upload"></i> Subir Imagen
                </button>
                
                <?php if (!empty($producto['imagen']) && !$es_nuevo): ?>
                    <button type="button" class="btn btn-danger" style="width: 100%; margin-top: 10px;" onclick="eliminarImagen()">
                        <i class="fas fa-trash"></i> Eliminar Imagen
                    </button>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if (!$es_nuevo): ?>
            <div class="sidebar-card">
                <h3 style="margin-top: 0; margin-bottom: 15px;">Estado del Producto</h3>
                
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span>Disponible</span>
                    <label class="switch">
                        <input type="checkbox" id="toggleStatus" <?php echo $producto['disponible'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>
                
                <small style="display: block; margin-top: 10px; color: #666;">
                    <?php if ($producto['disponible']): ?>
                        El producto está activo y visible para los clientes.
                    <?php else: ?>
                        El producto está inactivo y no será visible para los clientes.
                    <?php endif; ?>
                </small>
            </div>
            
            <div class="sidebar-card">
                <h3 style="margin-top: 0; margin-bottom: 15px;">Información</h3>
                
                <p style="margin: 0 0 10px; font-size: 0.9rem;">
                    <strong>ID:</strong> <?php echo $producto['id']; ?>
                </p>
                
                <p style="margin: 0 0 10px; font-size: 0.9rem;">
                    <strong>Fecha de creación:</strong> 
                    <?php echo isset($producto['fecha_creacion']) ? date('d/m/Y', strtotime($producto['fecha_creacion'])) : 'N/A'; ?>
                </p>
                
                <p style="margin: 0; font-size: 0.9rem;">
                    <strong>Última actualización:</strong> 
                    <?php echo isset($producto['fecha_actualizacion']) ? date('d/m/Y', strtotime($producto['fecha_actualizacion'])) : 'N/A'; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Vista previa de imagen al seleccionar archivo
document.getElementById('imagen').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const imagePreview = document.getElementById('imagePreview');
            imagePreview.innerHTML = `<img src="${event.target.result}" alt="Vista previa">`;
        };
        reader.readAsDataURL(file);
    }
});

<?php if (!$es_nuevo): ?>
// Función para eliminar imagen
function eliminarImagen() {
    if (confirm('¿Estás seguro de que deseas eliminar la imagen? Esta acción no se puede deshacer.')) {
        window.location.href = `procesar_producto.php?action=delete_image&id=<?php echo $producto['id']; ?>`;
    }
}

// Toggle de estado del producto
document.getElementById('toggleStatus').addEventListener('change', function() {
    window.location.href = `procesar_producto.php?action=toggle_status&id=<?php echo $producto['id']; ?>`;
});
<?php endif; ?>
</script>

<?php
// Incluir el footer
require_once 'includes/admin_footer.php';
?>