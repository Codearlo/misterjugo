<?php
// Verificar si el usuario está logueado
session_start();
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, redirigir al login
    header("Location: /login");
    exit;
}

// Conexión a base de datos
require_once '../backend/conexion.php';

// Obtener el ID del usuario actual
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Obtener direcciones del usuario
$direcciones = [];
$tiene_direccion_predeterminada = false;

$stmt = $conn->prepare("SELECT * FROM direcciones WHERE usuario_id = ? ORDER BY es_predeterminada DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $direcciones[] = $row;
        if ($row['es_predeterminada'] == 1) {
            $tiene_direccion_predeterminada = true;
            $direccion_predeterminada = $row;
        }
    }
}

// Si no hay dirección predeterminada pero hay direcciones, usar la primera
if (!$tiene_direccion_predeterminada && count($direcciones) > 0) {
    $direccion_predeterminada = $direcciones[0];
}

// Incluir el archivo header
include 'includes/header.php';
?>

<link rel="stylesheet" href="/css/direcciones.css">

<div class="main-content">
    <div class="container">
        <div class="page-header">
            <h2 class="page-title">Mis Direcciones</h2>
            <p class="page-subtitle">Administra tus direcciones de entrega</p>
        </div>
        
        <div class="section-container">
            <?php if (count($direcciones) > 0): ?>
                <div class="card-list">
                    <?php foreach ($direcciones as $direccion): ?>
                        <div class="address-card">
                            <div class="address-header">
                                <h3 class="address-title"><?php echo $direccion['es_predeterminada'] ? 'Dirección Principal' : 'Dirección Alternativa'; ?></h3>
                                <?php if ($direccion['es_predeterminada']): ?>
                                    <span class="address-badge primary">Predeterminada</span>
                                <?php endif; ?>
                            </div>
                            <div class="address-content">
                                <p><i class="fas fa-user"></i> <?php echo $user_name; ?></p>
                                <p><i class="fas fa-map-marker-alt"></i> <?php echo $direccion['calle']; ?>, <?php echo $direccion['ciudad']; ?>, <?php echo $direccion['estado']; ?>, CP <?php echo $direccion['codigo_postal']; ?></p>
                                <p><i class="fas fa-phone"></i> <?php echo $direccion['telefono']; ?></p>
                                <?php if (!empty($direccion['instrucciones'])): ?>
                                    <p><i class="fas fa-info-circle"></i> <?php echo $direccion['instrucciones']; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="address-actions">
                                <button class="btn-edit" data-id="<?php echo $direccion['id']; ?>">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <?php if (count($direcciones) > 1 && !$direccion['es_predeterminada']): ?>
                                    <button class="btn-delete" data-id="<?php echo $direccion['id']; ?>">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                <?php endif; ?>
                                <?php if (!$direccion['es_predeterminada']): ?>
                                    <button class="btn-default" data-id="<?php echo $direccion['id']; ?>">
                                        <i class="fas fa-check-circle"></i> Predeterminar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="add-new-card">
                        <button class="btn-add" id="btn-add-address">
                            <i class="fas fa-plus"></i>
                            <span>Agregar nueva dirección</span>
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-addresses">
                    <div class="empty-state">
                        <i class="fas fa-map-marker-alt"></i>
                        <h3>No tienes direcciones guardadas</h3>
                        <p>Agrega una dirección para facilitar tus pedidos</p>
                        <button class="btn-primary" id="btn-add-first-address">
                            <i class="fas fa-plus"></i> Agregar dirección
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para editar/agregar dirección -->
<div class="modal" id="address-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Editar Dirección</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="address-form" action="../backend/procesar_direccion.php" method="POST">
                <input type="hidden" id="address_id" name="address_id" value="">
                <div class="form-group">
                    <label for="calle">Dirección</label>
                    <input type="text" id="calle" name="calle" placeholder="Calle, número, colonia" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="ciudad">Ciudad</label>
                        <input type="text" id="ciudad" name="ciudad" placeholder="Ciudad" required>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <input type="text" id="estado" name="estado" placeholder="Estado" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="codigo_postal">Código Postal</label>
                        <input type="text" id="codigo_postal" name="codigo_postal" placeholder="Código Postal" required pattern="[0-9]{5}">
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" placeholder="Teléfono de contacto" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="instrucciones">Instrucciones de entrega (opcional)</label>
                    <textarea id="instrucciones" name="instrucciones" placeholder="Instrucciones para el repartidor"></textarea>
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="es_predeterminada" name="es_predeterminada" value="1">
                    <label for="es_predeterminada">Establecer como dirección predeterminada</label>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel">Cancelar</button>
                    <button type="submit" class="btn-save">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos DOM
    const btnEditAddresses = document.querySelectorAll('.btn-edit');
    const btnDeleteAddresses = document.querySelectorAll('.btn-delete');
    const btnDefaultAddresses = document.querySelectorAll('.btn-default');
    const btnAddAddress = document.getElementById('btn-add-address');
    const btnAddFirstAddress = document.getElementById('btn-add-first-address');
    const addressModal = document.getElementById('address-modal');
    const closeModal = document.querySelector('.close-modal');
    const btnCancel = document.querySelector('.btn-cancel');
    const modalTitle = document.getElementById('modal-title');
    const addressForm = document.getElementById('address-form');
    
    // Función para abrir el modal
    function openModal(title, addressId = '') {
        modalTitle.textContent = title;
        document.getElementById('address_id').value = addressId;
        
        // Si hay un ID, cargar los datos para edición
        if (addressId) {
            // Aquí iría una petición AJAX para obtener los datos
            // Por ahora simulamos que los obtenemos de los atributos data
            const btnEdit = document.querySelector(`.btn-edit[data-id="${addressId}"]`);
            if (btnEdit) {
                const card = btnEdit.closest('.address-card');
                // Aquí cargaríamos los datos reales del servidor
            }
        } else {
            // Es un nuevo registro, limpiar el formulario
            addressForm.reset();
        }
        
        addressModal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Evitar scroll
    }
    
    // Función para cerrar el modal
    function closeModalFunction() {
        addressModal.classList.remove('active');
        document.body.style.overflow = ''; // Restaurar scroll
    }
    
    // Eventos para abrir modal
    btnEditAddresses.forEach(btn => {
        btn.addEventListener('click', function() {
            const addressId = this.getAttribute('data-id');
            openModal('Editar Dirección', addressId);
        });
    });
    
    if (btnAddAddress) {
        btnAddAddress.addEventListener('click', function() {
            openModal('Agregar Nueva Dirección');
        });
    }
    
    if (btnAddFirstAddress) {
        btnAddFirstAddress.addEventListener('click', function() {
            openModal('Agregar Nueva Dirección');
        });
    }
    
    // Eventos para eliminar dirección
    btnDeleteAddresses.forEach(btn => {
        btn.addEventListener('click', function() {
            const addressId = this.getAttribute('data-id');
            if (confirm('¿Estás seguro de que deseas eliminar esta dirección?')) {
                // Aquí iría el código para eliminar la dirección
                window.location.href = `../backend/eliminar_direccion.php?id=${addressId}`;
            }
        });
    });
    
    // Eventos para establecer dirección predeterminada
    btnDefaultAddresses.forEach(btn => {
        btn.addEventListener('click', function() {
            const addressId = this.getAttribute('data-id');
            window.location.href = `../backend/predeterminar_direccion.php?id=${addressId}`;
        });
    });
    
    // Eventos para cerrar modal
    closeModal.addEventListener('click', closeModalFunction);
    btnCancel.addEventListener('click', closeModalFunction);
    
    // Cerrar modal haciendo clic fuera del contenido
    addressModal.addEventListener('click', function(e) {
        if (e.target === addressModal) {
            closeModalFunction();
        }
    });
    
    // Manejar el envío del formulario
    addressForm.addEventListener('submit', function(e) {
        // La validación básica la hace HTML5 con los atributos required y pattern
        // Podríamos agregar validación adicional aquí si fuera necesario
    });
});
</script>

<?php
// Incluir el footer
include 'includes/footer.php';
?>