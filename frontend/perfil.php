<?php
// Verificar si el usuario está logueado
session_start();
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, redirigir al login con un mensaje
    $_SESSION['error_login'] = "Debes iniciar sesión para acceder a tu perfil";
    header("Location: /login");
    exit;
}

// Conexión a base de datos
require_once '../backend/conexion.php';

// Obtener el ID del usuario actual
$user_id = $_SESSION['user_id'];

// Verificar si hay mensajes de éxito o error
$exito_perfil = isset($_SESSION['exito_perfil']) ? $_SESSION['exito_perfil'] : '';
$error_perfil = isset($_SESSION['error_perfil']) ? $_SESSION['error_perfil'] : '';

// Limpiar mensajes de sesión
unset($_SESSION['exito_perfil']);
unset($_SESSION['error_perfil']);

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Obtener direcciones del usuario
$direcciones = [];
$stmt = $conn->prepare("SELECT * FROM direcciones WHERE usuario_id = ? ORDER BY es_predeterminada DESC, fecha_creacion DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $direcciones[] = $row;
    }
}

// Obtener estadísticas de pedidos
$stats = [
    'total' => 0,
    'pendientes' => 0,
    'completados' => 0,
    'cancelados' => 0
];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['total'] = $row['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ? AND estado = 'pendiente'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['pendientes'] = $row['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ? AND estado = 'completado'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['completados'] = $row['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ? AND estado = 'cancelado'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stats['cancelados'] = $row['total'];

// Incluir el archivo header
include 'includes/header.php';
?>

<link rel="stylesheet" href="/css/perfil.css">

<!-- Inicio de la página de perfil con la clase específica -->
<div class="perfil-page">
    <div class="main-content">
        <div class="container">
            <div class="page-header">
                <h2 class="page-title">Mi Perfil</h2>
                <div class="title-underline"></div>
                <p class="page-subtitle">Gestiona tu información personal y direcciones</p>
            </div>
            
            <!-- Notificaciones debajo del título -->
            <?php if (!empty($exito_perfil)): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <p><?php echo $exito_perfil; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_perfil)): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <p><?php echo $error_perfil; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="profile-container">
                <!-- SECCIÓN DE INFORMACIÓN PERSONAL -->
                <div class="profile-section personal-info">
                    <div class="section-header">
                        <h3><i class="fas fa-user-circle"></i> Información Personal</h3>
                        <button class="btn-edit-info" id="btn-edit-personal">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </div>
                    
                    <div class="section-content" id="personal-info-view">
                        <div class="info-group">
                            <span class="info-label">Nombre:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                        </div>
                        <div class="info-group">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['email']); ?></span>
                        </div>
                        <div class="info-group">
                            <span class="info-label">Fecha de registro:</span>
                            <span class="info-value"><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="section-content hidden" id="personal-info-edit">
                        <form id="form-personal-info" action="/backend/actualizar_perfil.php" method="POST">
                            <div class="form-group">
                                <label for="nombre">Nombre</label>
                                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-cancel" id="btn-cancel-personal">Cancelar</button>
                                <button type="submit" class="btn-save">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- SECCIÓN DE CAMBIO DE CONTRASEÑA -->
                <div class="profile-section password-section">
                    <div class="section-header">
                        <h3><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
                        <button class="btn-edit-info" id="btn-edit-password">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </div>
                    
                    <div class="section-content hidden" id="password-edit">
                        <form id="form-change-password" action="/backend/cambiar_password.php" method="POST">
                            <div class="form-group">
                                <label for="current_password">Contraseña Actual</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Nueva Contraseña</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirmar Contraseña</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-cancel" id="btn-cancel-password">Cancelar</button>
                                <button type="submit" class="btn-save">Actualizar Contraseña</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="section-content" id="password-info-view">
                        <p class="password-placeholder">••••••••••</p>
                        <p class="password-hint">Para cambiar tu contraseña, haz clic en el botón editar.</p>
                    </div>
                </div>
                
                <!-- SECCIÓN DE ESTADÍSTICAS DE PEDIDOS -->
                <div class="profile-section stats-section">
                    <div class="section-header">
                        <h3><i class="fas fa-chart-pie"></i> Mis Estadísticas</h3>
                    </div>
                    
                    <div class="section-content">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="stat-data">
                                    <span class="stat-value"><?php echo $stats['total']; ?></span>
                                    <span class="stat-label">Pedidos Totales</span>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon pending">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-data">
                                    <span class="stat-value"><?php echo $stats['pendientes']; ?></span>
                                    <span class="stat-label">Pendientes</span>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon completed">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-data">
                                    <span class="stat-value"><?php echo $stats['completados']; ?></span>
                                    <span class="stat-label">Completados</span>
                                </div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon cancelled">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="stat-data">
                                    <span class="stat-value"><?php echo $stats['cancelados']; ?></span>
                                    <span class="stat-label">Cancelados</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stats-action">
                            <a href="/pedidos" class="btn-view-orders">
                                <i class="fas fa-list"></i> Ver historial de pedidos
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- SECCIÓN DE DIRECCIONES -->
                <div class="profile-section addresses-section">
                    <div class="section-header">
                        <h3><i class="fas fa-map-marker-alt"></i> Mis Direcciones</h3>
                        <button class="btn-add-address" id="btn-add-address">
                            <i class="fas fa-plus"></i> Añadir Nueva
                        </button>
                    </div>
                    
                    <div class="section-content">
                        <?php if (count($direcciones) > 0): ?>
                            <div class="address-list">
                                <?php foreach ($direcciones as $direccion): ?>
                                    <div class="address-card <?php echo $direccion['es_predeterminada'] ? 'default' : ''; ?>">
                                        <?php if ($direccion['es_predeterminada']): ?>
                                            <div class="default-badge">
                                                <i class="fas fa-star"></i> Predeterminada
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="address-content">
                                            <p class="address-line">
                                                <i class="fas fa-home"></i> 
                                                <?php echo htmlspecialchars($direccion['calle']); ?>
                                            </p>
                                            <p class="address-line">
                                                <i class="fas fa-map"></i>
                                                <?php echo htmlspecialchars($direccion['ciudad']); ?>, 
                                                <?php echo htmlspecialchars($direccion['estado']); ?>, 
                                                CP: <?php echo htmlspecialchars($direccion['codigo_postal']); ?>
                                            </p>
                                            <p class="address-line">
                                                <i class="fas fa-phone"></i>
                                                <?php echo htmlspecialchars($direccion['telefono']); ?>
                                            </p>
                                            <?php if (!empty($direccion['instrucciones'])): ?>
                                                <p class="address-instructions">
                                                    <i class="fas fa-info-circle"></i>
                                                    <?php echo htmlspecialchars($direccion['instrucciones']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="address-actions">
                                            <button class="btn-edit-address" data-id="<?php echo $direccion['id']; ?>">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            
                                            <?php if (!$direccion['es_predeterminada']): ?>
                                                <button class="btn-set-default" data-id="<?php echo $direccion['id']; ?>">
                                                    <i class="fas fa-star"></i> Establecer como predeterminada
                                                </button>
                                                
                                                <button class="btn-delete-address" data-id="<?php echo $direccion['id']; ?>">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-addresses">
                                <div class="empty-state">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <h3>No tienes direcciones guardadas</h3>
                                    <p>Añade una dirección para facilitar tus pedidos</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para añadir/editar dirección -->
    <div class="modal" id="address-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="address-modal-title">Añadir Nueva Dirección</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-address" action="/backend/guardar_direccion.php" method="POST">
                    <input type="hidden" id="direccion_id" name="direccion_id" value="">
                    
                    <div class="form-group">
                        <label for="calle">Dirección Completa</label>
                        <input type="text" id="calle" name="calle" required placeholder="Calle, número, colonia">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ciudad">Ciudad</label>
                            <input type="text" id="ciudad" name="ciudad" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <input type="text" id="estado" name="estado" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="codigo_postal">Código Postal</label>
                            <input type="text" id="codigo_postal" name="codigo_postal" required maxlength="10">
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono">Teléfono de Contacto</label>
                            <input type="tel" id="telefono" name="telefono" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="instrucciones">Instrucciones de entrega (opcional)</label>
                        <textarea id="instrucciones" name="instrucciones" rows="3" placeholder="Indicaciones para el repartidor"></textarea>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="es_predeterminada" name="es_predeterminada" value="1">
                        <label for="es_predeterminada">Establecer como dirección predeterminada</label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-cancel-modal">Cancelar</button>
                        <button type="submit" class="btn-save-modal">Guardar Dirección</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmación para eliminar dirección -->
    <div class="confirmation-modal" id="delete-address-modal">
        <div class="confirmation-content">
            <div class="confirmation-header">
                <h3>Eliminar Dirección</h3>
                <button class="confirmation-close">&times;</button>
            </div>
            <div class="confirmation-body">
                <p>¿Estás seguro de que deseas eliminar esta dirección?</p>
                <p>Esta acción no se puede deshacer.</p>
            </div>
            <div class="confirmation-actions">
                <button type="button" class="btn-cancel-modal">No, mantener</button>
                <button type="button" class="btn-confirm-delete">Sí, eliminar</button>
            </div>
        </div>
    </div>
</div>
<!-- Fin de la página de perfil -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos DOM para información personal
    const btnEditPersonal = document.getElementById('btn-edit-personal');
    const btnCancelPersonal = document.getElementById('btn-cancel-personal');
    const personalInfoView = document.getElementById('personal-info-view');
    const personalInfoEdit = document.getElementById('personal-info-edit');
    
    // Referencias a elementos DOM para cambio de contraseña
    const btnEditPassword = document.getElementById('btn-edit-password');
    const btnCancelPassword = document.getElementById('btn-cancel-password');
    const passwordInfoView = document.getElementById('password-info-view');
    const passwordEdit = document.getElementById('password-edit');
    
    // Referencias a elementos DOM para modal de dirección
    const btnAddAddress = document.getElementById('btn-add-address');
    const addressModal = document.getElementById('address-modal');
    const closeAddressModal = addressModal.querySelector('.close-modal');
    const btnCancelAddressModal = addressModal.querySelector('.btn-cancel-modal');
    const addressModalTitle = document.getElementById('address-modal-title');
    const addressForm = document.getElementById('form-address');
    
    // Referencias a elementos DOM para edición de direcciones
    const btnEditAddresses = document.querySelectorAll('.btn-edit-address');
    const btnSetDefault = document.querySelectorAll('.btn-set-default');
    const btnDeleteAddresses = document.querySelectorAll('.btn-delete-address');
    
    // Referencias a elementos DOM para modal de confirmación
    const deleteAddressModal = document.getElementById('delete-address-modal');
    const closeDeleteModal = deleteAddressModal.querySelector('.confirmation-close');
    const btnCancelDelete = deleteAddressModal.querySelector('.btn-cancel-modal');
    const btnConfirmDelete = document.querySelector('.btn-confirm-delete');
    
    // Variable para almacenar el ID de la dirección a eliminar
    let addressIdToDelete = null;
    
    // Función para alternar la edición de información personal
    function togglePersonalInfoEdit() {
        personalInfoView.classList.toggle('hidden');
        personalInfoEdit.classList.toggle('hidden');
    }
    
    // Función para alternar la edición de contraseña
    function togglePasswordEdit() {
        passwordInfoView.classList.toggle('hidden');
        passwordEdit.classList.toggle('hidden');
    }
    
    // Función para abrir el modal de dirección para añadir
    function openAddAddressModal() {
        addressModalTitle.textContent = 'Añadir Nueva Dirección';
        addressForm.reset();
        document.getElementById('direccion_id').value = '';
        addressModal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Evitar scroll
    }
    
    // Función para abrir el modal de dirección para editar
    function openEditAddressModal(addressId) {
        addressModalTitle.textContent = 'Editar Dirección';
        
        // Cargar los datos de la dirección mediante AJAX
        fetch(`/backend/obtener_direccion.php?id=${addressId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('direccion_id').value = data.direccion.id;
                    document.getElementById('calle').value = data.direccion.calle;
                    document.getElementById('ciudad').value = data.direccion.ciudad;
                    document.getElementById('estado').value = data.direccion.estado;
                    document.getElementById('codigo_postal').value = data.direccion.codigo_postal;
                    document.getElementById('telefono').value = data.direccion.telefono;
                    document.getElementById('instrucciones').value = data.direccion.instrucciones || '';
                    document.getElementById('es_predeterminada').checked = data.direccion.es_predeterminada == 1;
                    
                    addressModal.classList.add('active');
                    document.body.style.overflow = 'hidden'; // Evitar scroll
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('No se pudo cargar la información de la dirección');
            });
    }
    
    // Función para cerrar el modal de dirección
    function closeAddressModal() {
        addressModal.classList.remove('active');
        document.body.style.overflow = ''; // Restaurar scroll
    }
    
    // Función para abrir el modal de confirmación de eliminación
    function openDeleteConfirmationModal(addressId) {
        addressIdToDelete = addressId;
        deleteAddressModal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Evitar scroll
    }
    
    // Función para cerrar el modal de confirmación
    function closeDeleteConfirmationModal() {
        deleteAddressModal.classList.remove('active');
        document.body.style.overflow = ''; // Restaurar scroll
        addressIdToDelete = null;
    }
    
    // Evento para editar información personal
    btnEditPersonal.addEventListener('click', togglePersonalInfoEdit);
    btnCancelPersonal.addEventListener('click', togglePersonalInfoEdit);
    
    // Evento para editar contraseña
    btnEditPassword.addEventListener('click', togglePasswordEdit);
    btnCancelPassword.addEventListener('click', togglePasswordEdit);
    
    // Evento para añadir dirección
    btnAddAddress.addEventListener('click', openAddAddressModal);
    
    // Eventos para cerrar modal de dirección
    closeAddressModal.addEventListener('click', closeAddressModal);
    btnCancelAddressModal.addEventListener('click', closeAddressModal);
    
    // Eventos para editar direcciones
    btnEditAddresses.forEach(btn => {
        btn.addEventListener('click', function() {
            const addressId = this.getAttribute('data-id');
            openEditAddressModal(addressId);
        });
    });
    
    // Eventos para establecer dirección predeterminada
    btnSetDefault.forEach(btn => {
        btn.addEventListener('click', function() {
            const addressId = this.getAttribute('data-id');
            window.location.href = `/backend/establecer_direccion_predeterminada.php?id=${addressId}`;
        });
    });
    
    // Eventos para eliminar direcciones
    btnDeleteAddresses.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const addressId = this.getAttribute('data-id');
            openDeleteConfirmationModal(addressId);
        });
    });
    
    // Evento para confirmar eliminación
    btnConfirmDelete.addEventListener('click', function() {
        if (addressIdToDelete) {
            window.location.href = `/backend/eliminar_direccion.php?id=${addressIdToDelete}`;
        }
    });
    
    // Eventos para cerrar modal de confirmación
    closeDeleteModal.addEventListener('click', closeDeleteConfirmationModal);
    btnCancelDelete.addEventListener('click', closeDeleteConfirmationModal);
    
    // Cerrar modales haciendo clic fuera del contenido
    addressModal.addEventListener('click', function(e) {
        if (e.target === addressModal) {
            closeAddressModal();
        }
    });
    
    deleteAddressModal.addEventListener('click', function(e) {
        if (e.target === deleteAddressModal) {
            closeDeleteConfirmationModal();
        }
    });
    
    // Validación del formulario de cambio de contraseña
    const formChangePassword = document.getElementById('form-change-password');
    
    if (formChangePassword) {
        formChangePassword.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden. Por favor, inténtalo de nuevo.');
            }
        });
    }
    
    // Si hay notificaciones, mostrarlas y ocultarlas después de un tiempo
    const notifications = document.querySelectorAll('.notification');
    if (notifications.length > 0) {
        // Mostrar después de un pequeño retraso para evitar problemas de layout
        setTimeout(function() {
            notifications.forEach(notification => {
                notification.style.opacity = '1';
            });
            
            // Ocultar después de 5 segundos
            setTimeout(function() {
                notifications.forEach(notification => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 500);
                });
            }, 5000);
        }, 300);
    }
});
</script>

<?php
// Incluir el footer
include 'includes/footer.php';
?>