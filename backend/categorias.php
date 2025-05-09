<?php
// Definir variables para el header
$titulo_pagina = "Gestión de Categorías";
$breadcrumbs = [
    ['texto' => 'Dashboard', 'url' => 'dashboard.php'],
    ['texto' => 'Categorías']
];

// Incluir el header
require_once 'includes/admin_header.php';

// Obtener mensajes de estado si existen
$exito = isset($_SESSION['admin_exito']) ? $_SESSION['admin_exito'] : '';
$error = isset($_SESSION['admin_error']) ? $_SESSION['admin_error'] : '';
unset($_SESSION['admin_exito'], $_SESSION['admin_error']);

// Obtener todas las categorías
$stmt = $conn->prepare("
    SELECT c.*, 
           (SELECT COUNT(*) FROM productos p WHERE p.categoria_id = c.id) as total_productos
    FROM categorias c 
    ORDER BY c.nombre ASC
");
$stmt->execute();
$result = $stmt->get_result();
$categorias = [];

while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}
?>

<!-- Mensajes de estado -->
<?php if (!empty($exito)): ?>
    <div class="notification success">
        <i class="fas fa-check-circle"></i>
        <span><?php echo $exito; ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="notification danger">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo $error; ?></span>
    </div>
<?php endif; ?>

<!-- Acciones -->
<div style="margin-bottom: 20px; display: flex; justify-content: flex-end;">
    <button type="button" class="btn btn-primary" id="btn-nueva-categoria">
        <i class="fas fa-plus"></i> Nueva Categoría
    </button>
</div>

<!-- Tabla de categorías -->
<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Productos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categorias)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 30px;">
                        <i class="fas fa-tags" style="font-size: 3rem; color: #ddd; margin-bottom: 10px;"></i>
                        <p style="margin: 0;">No hay categorías disponibles</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><?php echo $categoria['id']; ?></td>
                        <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                        <td>
                            <?php 
                            if (!empty($categoria['descripcion'])) {
                                echo (strlen($categoria['descripcion']) > 100) 
                                    ? htmlspecialchars(substr($categoria['descripcion'], 0, 100)) . '...' 
                                    : htmlspecialchars($categoria['descripcion']);
                            } else {
                                echo '<span style="color: #aaa; font-style: italic;">Sin descripción</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($categoria['total_productos'] > 0): ?>
                                <a href="productos.php?categoria=<?php echo $categoria['id']; ?>" class="badge primary" style="text-decoration: none;">
                                    <?php echo $categoria['total_productos']; ?> productos
                                </a>
                            <?php else: ?>
                                <span class="badge secondary">0 productos</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <button type="button" class="btn btn-primary btn-sm btn-icon btn-editar-categoria" data-id="<?php echo $categoria['id']; ?>" data-nombre="<?php echo htmlspecialchars($categoria['nombre']); ?>" data-descripcion="<?php echo htmlspecialchars($categoria['descripcion']); ?>" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <?php if ($categoria['total_productos'] == 0): ?>
                                    <button type="button" class="btn btn-danger btn-sm btn-icon btn-eliminar-categoria" data-id="<?php echo $categoria['id']; ?>" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-danger btn-sm btn-icon" disabled title="No se puede eliminar: tiene productos asociados">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para crear/editar categoría -->
<div class="modal" id="categoria-modal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 id="modal-title">Nueva Categoría</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="categoria-form" action="procesar_categoria.php" method="POST">
                <input type="hidden" name="action" id="form-action" value="create">
                <input type="hidden" name="id" id="categoria-id" value="">
                
                <div class="form-group">
                    <label for="nombre">Nombre de la categoría *</label>
                    <input type="text" id="categoria-nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="categoria-descripcion" name="descripcion" rows="4"></textarea>
                </div>
                
                <div class="form-actions" style="text-align: right; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary modal-close">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal" id="confirmacion-modal" style="display: none;">
    <div class="modal-overlay"></div>
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Confirmar eliminación</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>¿Estás seguro de que deseas eliminar esta categoría? Esta acción no se puede deshacer.</p>
            
            <div class="form-actions" style="text-align: right; margin-top: 20px;">
                <button type="button" class="btn btn-secondary modal-close">Cancelar</button>
                <a href="#" id="btn-confirmar-eliminar" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: none;
}

.modal.active {
    display: block;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #f1f1f1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.2rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #999;
}

.modal-close:hover {
    color: #333;
}

.modal-body {
    padding: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 2px rgba(255, 122, 0, 0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos DOM
    const categoriaModal = document.getElementById('categoria-modal');
    const confirmacionModal = document.getElementById('confirmacion-modal');
    const btnNuevaCategoria = document.getElementById('btn-nueva-categoria');
    const btnsEditarCategoria = document.querySelectorAll('.btn-editar-categoria');
    const btnsEliminarCategoria = document.querySelectorAll('.btn-eliminar-categoria');
    const btnConfirmarEliminar = document.getElementById('btn-confirmar-eliminar');
    const modalTitle = document.getElementById('modal-title');
    const categoriaForm = document.getElementById('categoria-form');
    const formAction = document.getElementById('form-action');
    const categoriaId = document.getElementById('categoria-id');
    const categoriaNombre = document.getElementById('categoria-nombre');
    const categoriaDescripcion = document.getElementById('categoria-descripcion');
    const modalCloseBtns = document.querySelectorAll('.modal-close');
    
    // Evento para abrir modal de nueva categoría
    btnNuevaCategoria.addEventListener('click', function() {
        modalTitle.textContent = 'Nueva Categoría';
        formAction.value = 'create';
        categoriaId.value = '';
        categoriaNombre.value = '';
        categoriaDescripcion.value = '';
        openModal(categoriaModal);
    });
    
    // Eventos para abrir modal de edición de categoría
    btnsEditarCategoria.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            const descripcion = this.getAttribute('data-descripcion') || '';
            
            modalTitle.textContent = 'Editar Categoría';
            formAction.value = 'update';
            categoriaId.value = id;
            categoriaNombre.value = nombre;
            categoriaDescripcion.value = descripcion;
            openModal(categoriaModal);
        });
    });
    
    // Eventos para abrir modal de confirmación de eliminación
    btnsEliminarCategoria.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            btnConfirmarEliminar.href = `procesar_categoria.php?action=delete&id=${id}`;
            openModal(confirmacionModal);
        });
    });
    
    // Eventos para cerrar modales
    modalCloseBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            closeModal(categoriaModal);
            closeModal(confirmacionModal);
        });
    });
    
    // Función para abrir modal
    function openModal(modal) {
        modal.classList.add('active');
        modal.style.display = 'block';
    }
    
    // Función para cerrar modal
    function closeModal(modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
    
    // Cerrar modal haciendo clic en overlay
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function() {
            closeModal(categoriaModal);
            closeModal(confirmacionModal);
        });
    });
});
</script>

<?php
// Incluir el footer
require_once 'includes/admin_footer.php';
?>