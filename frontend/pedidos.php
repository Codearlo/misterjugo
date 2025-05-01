<?php
// Verificar si el usuario está logueado
session_start();
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, redirigir al login con un mensaje
    $_SESSION['error_login'] = "Debes iniciar sesión para acceder a tus pedidos";
    header("Location: /login");
    exit;
}

// Conexión a base de datos
require_once '../backend/conexion.php';

// Obtener el ID del usuario actual
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Verificar si hay mensajes de éxito o error
$exito_pedido = isset($_SESSION['exito_pedido']) ? $_SESSION['exito_pedido'] : '';
$error_pedido = isset($_SESSION['error_pedido']) ? $_SESSION['error_pedido'] : '';

// Limpiar mensajes de sesión
unset($_SESSION['exito_pedido']);
unset($_SESSION['error_pedido']);

// Obtener pedidos del usuario
$pedidos = [];

$stmt = $conn->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY fecha_pedido DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
}

// Función para obtener estado formateado
function getEstadoFormateado($estado) {
    switch ($estado) {
        case 'pendiente':
            return '<span class="status-badge pending">Pendiente</span>';
        case 'procesando':
            return '<span class="status-badge processing">Procesando</span>';
        case 'completado':
            return '<span class="status-badge completed">Completado</span>';
        case 'cancelado':
            return '<span class="status-badge cancelled">Cancelado</span>';
        default:
            return '<span class="status-badge">' . ucfirst($estado) . '</span>';
    }
}

// Incluir el archivo header
include 'includes/header.php';
?>

<link rel="stylesheet" href="/css/pedidos.css">

<!-- Inicio de la página de pedidos con la clase específica -->
<div class="pedidos-page">
    <div class="main-content">
        <div class="container">
            <div class="page-header">
                <h2 class="page-title">Mis Pedidos</h2>
                <div class="title-underline"></div>
                <p class="page-subtitle">Historial de tus pedidos y su estado</p>
            </div>
            
            <!-- Notificaciones debajo del título -->
            <?php if (!empty($exito_pedido)): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <p><?php echo $exito_pedido; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_pedido)): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <p><?php echo $error_pedido; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="section-container">
                <?php if (count($pedidos) > 0): ?>
                    <div class="card-list">
                        <?php foreach ($pedidos as $pedido): ?>
                            <div class="order-card" data-id="<?php echo $pedido['id']; ?>">
                                <div class="order-header">
                                    <h3 class="order-title">Pedido #<?php echo $pedido['id']; ?></h3>
                                    <div class="order-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?>
                                    </div>
                                </div>
                                <div class="order-content">
                                    <div class="order-info">
                                        <div class="order-status">
                                            <span class="info-label">Estado:</span>
                                            <?php echo getEstadoFormateado($pedido['estado']); ?>
                                        </div>
                                        <div class="order-total">
                                            <span class="info-label">Total:</span>
                                            <span class="total-amount">$<?php echo number_format($pedido['total'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-actions">
                                    <button class="btn-view-details" data-id="<?php echo $pedido['id']; ?>">
                                        <i class="fas fa-eye"></i> Ver detalles
                                    </button>
                                    <?php if ($pedido['estado'] == 'pendiente'): ?>
                                        <button class="btn-cancel-order" data-id="<?php echo $pedido['id']; ?>">
                                            <i class="fas fa-times"></i> Cancelar pedido
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-orders">
                        <div class="empty-state">
                            <i class="fas fa-shopping-basket"></i>
                            <h3>No tienes pedidos</h3>
                            <p>Aquí aparecerán los pedidos que realices</p>
                            <a href="/productos" class="btn-primary">
                                <i class="fas fa-shopping-cart"></i> Ir a la tienda
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles del pedido -->
    <div class="modal" id="order-details-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Detalles del Pedido</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="order-details-content">
                    <!-- Aquí se cargarán los detalles del pedido mediante AJAX -->
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Cargando detalles...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmación para cancelar pedido -->
    <div class="confirmation-modal" id="cancel-confirmation-modal">
        <div class="confirmation-content">
            <div class="confirmation-header">
                <h3>Cancelar Pedido</h3>
                <button class="confirmation-close">&times;</button>
            </div>
            <div class="confirmation-body">
                <p>¿Estás seguro de que deseas cancelar este pedido?</p>
                <p>Esta acción no se puede deshacer.</p>
            </div>
            <div class="confirmation-actions">
                <button type="button" class="btn-cancel-modal">No, mantener pedido</button>
                <button type="button" class="btn-confirm-cancel">Sí, cancelar pedido</button>
            </div>
        </div>
    </div>
</div>
<!-- Fin de la página de pedidos -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos DOM
    const btnViewDetails = document.querySelectorAll('.btn-view-details');
    const btnCancelOrders = document.querySelectorAll('.btn-cancel-order');
    const orderDetailsModal = document.getElementById('order-details-modal');
    const cancelConfirmationModal = document.getElementById('cancel-confirmation-modal');
    const closeModal = document.querySelector('.close-modal');
    const closeConfirmation = document.querySelector('.confirmation-close');
    const btnCancelModal = document.querySelector('.btn-cancel-modal');
    const btnConfirmCancel = document.querySelector('.btn-confirm-cancel');
    const modalTitle = document.getElementById('modal-title');
    const orderDetailsContent = document.getElementById('order-details-content');
    
    // Variable para almacenar el ID del pedido a cancelar
    let orderIdToCancel = null;
    
    // Detectar si es móvil y añadir clase
    if (window.innerWidth <= 480) {
        document.querySelector('.pedidos-page').classList.add('mobile-view');
    }
    
    // Función para abrir el modal de detalles
    function openDetailsModal(orderId) {
        modalTitle.textContent = `Detalles del Pedido #${orderId}`;
        orderDetailsContent.innerHTML = `
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Cargando detalles...</p>
            </div>
        `;
        
        // Mostrar el modal
        orderDetailsModal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Evitar scroll
        
        // Cargar los detalles del pedido mediante AJAX
        fetch(`/backend/obtener_detalles_pedido.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Construir el HTML para los detalles del pedido
                    let html = `
                        <div class="order-summary">
                            <div class="order-info-detail">
                                <div class="info-group">
                                    <span class="info-label">Fecha:</span>
                                    <span class="info-value">${data.pedido.fecha_formateada}</span>
                                </div>
                                <div class="info-group">
                                    <span class="info-label">Estado:</span>
                                    <span class="info-value status-text ${data.pedido.estado}">${data.pedido.estado_texto}</span>
                                </div>
                                <div class="info-group">
                                    <span class="info-label">Dirección de entrega:</span>
                                    <span class="info-value">${data.pedido.direccion}</span>
                                </div>
                            </div>
                        </div>
                        <div class="order-items">
                            <h4>Productos del pedido</h4>
                            <div class="items-list">
                    `;
                    
                    // Agregar cada item del pedido
                    data.items.forEach(item => {
                        html += `
                            <div class="order-item">
                                <div class="item-image">
                                    <img src="${item.imagen}" alt="${item.nombre}">
                                </div>
                                <div class="item-details">
                                    <h5 class="item-name">${item.nombre}</h5>
                                    <div class="item-meta">
                                        <span class="item-quantity">Cantidad: ${item.cantidad}</span>
                                        <span class="item-price">$${Number(item.precio).toFixed(2)} c/u</span>
                                    </div>
                                </div>
                                <div class="item-total">
                                    $${Number(item.subtotal).toFixed(2)}
                                </div>
                            </div>
                        `;
                    });
                    
                    // Agregar resumen de precios
                    html += `
                            </div>
                        </div>
                        <div class="order-totals">
                            <div class="total-line subtotal">
                                <span class="total-label">Subtotal:</span>
                                <span class="total-value">$${Number(data.totales.subtotal).toFixed(2)}</span>
                            </div>
                            <div class="total-line shipping">
                                <span class="total-label">Envío:</span>
                                <span class="total-value">$${Number(data.totales.envio).toFixed(2)}</span>
                            </div>
                            <div class="total-line discount">
                                <span class="total-label">Descuento:</span>
                                <span class="total-value">-$${Number(data.totales.descuento).toFixed(2)}</span>
                            </div>
                            <div class="total-line grand-total">
                                <span class="total-label">Total:</span>
                                <span class="total-value">$${Number(data.totales.total).toFixed(2)}</span>
                            </div>
                        </div>
                    `;
                    
                    orderDetailsContent.innerHTML = html;
                } else {
                    orderDetailsContent.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>No se pudieron cargar los detalles del pedido</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                orderDetailsContent.innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Ocurrió un error al cargar los detalles</p>
                    </div>
                `;
            });
    }
    
    // Función para abrir el modal de confirmación de cancelación
    function openCancelConfirmationModal(orderId) {
        orderIdToCancel = orderId;
        cancelConfirmationModal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Evitar scroll
    }
    
    // Función para cerrar el modal de detalles
    function closeDetailsModal() {
        orderDetailsModal.classList.remove('active');
        document.body.style.overflow = ''; // Restaurar scroll
    }
    
    // Función para cerrar el modal de confirmación
    function closeCancelConfirmationModal() {
        cancelConfirmationModal.classList.remove('active');
        document.body.style.overflow = ''; // Restaurar scroll
        orderIdToCancel = null;
    }
    
    // Eventos para abrir modal de detalles
    btnViewDetails.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const orderId = this.getAttribute('data-id');
            openDetailsModal(orderId);
        });
    });
    
    // También permitir que se abra al hacer clic en la tarjeta completa
    document.querySelectorAll('.order-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Solo abrir si el clic no fue en un botón
            if (!e.target.closest('button')) {
                const orderId = this.getAttribute('data-id');
                openDetailsModal(orderId);
            }
        });
    });
    
    // Eventos para mostrar confirmación de cancelación
    btnCancelOrders.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation(); // Evitar que se abra el modal de detalles
            const orderId = this.getAttribute('data-id');
            openCancelConfirmationModal(orderId);
        });
    });
    
    // Evento para confirmar cancelación
    btnConfirmCancel.addEventListener('click', function() {
        if (orderIdToCancel) {
            window.location.href = `/backend/cancelar_pedido.php?id=${orderIdToCancel}`;
        }
    });
    
    // Eventos para cerrar modales
    closeModal.addEventListener('click', closeDetailsModal);
    closeConfirmation.addEventListener('click', closeCancelConfirmationModal);
    btnCancelModal.addEventListener('click', closeCancelConfirmationModal);
    
    // Cerrar modal haciendo clic fuera del contenido
    orderDetailsModal.addEventListener('click', function(e) {
        if (e.target === orderDetailsModal) {
            closeDetailsModal();
        }
    });
    
    cancelConfirmationModal.addEventListener('click', function(e) {
        if (e.target === cancelConfirmationModal) {
            closeCancelConfirmationModal();
        }
    });
    
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