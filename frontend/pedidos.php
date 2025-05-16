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
<!-- Inicio de la página de pedidos -->
<div class="pedidos-page">
    <div class="main-content">
        <div class="container">
            <div class="page-header">
                <h2 class="page-title">Mis Pedidos</h2>
                <div class="title-underline"></div>
                <p class="page-subtitle">Historial de tus pedidos y su estado</p>
            </div>

            <!-- Notificaciones -->
            <?php if (!empty($exito_pedido)): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i>
                    <div><p><?php echo $exito_pedido; ?></p></div>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_pedido)): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div><p><?php echo $error_pedido; ?></p></div>
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
                                            <span class="total-amount">S/<?php echo number_format($pedido['total'], 2); ?></span>
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
                            <a href="/productos" class="btn-order-now">
                                <svg class="cart-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                <span>ordena ahora</span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal: Detalles del pedido -->
    <div class="modal" id="order-details-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Detalles del Pedido</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="order-details-content">
                    <div class="loading"><i class="fas fa-spinner fa-spin"></i><p>Cargando detalles...</p></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Confirmación de cancelación -->
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

<!-- Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
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

    let orderIdToCancel = null;

    function openDetailsModal(orderId) {
        modalTitle.textContent = `Detalles del Pedido #${orderId}`;
        orderDetailsContent.innerHTML = `<div class="loading"><i class="fas fa-spinner fa-spin"></i><p>Cargando detalles...</p></div>`;
        orderDetailsModal.classList.add('active');
        document.body.style.overflow = 'hidden';

        fetch(`/backend/obtener_detalles_pedido.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
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
                            </div>
                        </div>
                        <div class="order-actions">
                            ${data.pedido.estado === 'pendiente' ? `
                                <button class="btn-cancel-order-modal" data-id="${data.pedido.id}">
                                    <i class="fas fa-times"></i> Cancelar Pedido
                                </button>
                            ` : ''}
                        </div>
                        <div class="order-items">
                            <h4>Productos del pedido</h4>
                            <div class="items-list">`;

                    data.items.forEach(item => {
                        html += `
                            <div class="order-item">
                                <div class="item-image"><img src="${item.imagen}" alt="${item.nombre}"></div>
                                <div class="item-details">
                                    <h5>${item.nombre}</h5>
                                    <div>Cantidad: ${item.cantidad}</div>
                                    <div>Precio: S/${Number(item.precio).toFixed(2)}</div>
                                </div>
                                <div class="item-total">S/${Number(item.subtotal).toFixed(2)}</div>
                            </div>`;
                    });

                    html += `</div></div><div class="order-totals"><div class="total-line grand-total">
                                <span class="total-label">Total:</span>
                                <span class="total-value">S/${Number(data.totales.total).toFixed(2)}</span>
                            </div></div>`;

                    orderDetailsContent.innerHTML = html;

                    // Evento: Cancelar pedido desde modal
                    document.querySelector('.btn-cancel-order-modal')?.addEventListener('click', function () {
                        const idPedido = this.getAttribute('data-id');
                        openCancelConfirmationModal(idPedido);
                    });
                } else {
                    orderDetailsContent.innerHTML = `<p>Error al cargar los detalles: ${data.message || 'No se pudieron cargar los detalles.'}</p>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                orderDetailsContent.innerHTML = `<p>Error al cargar los detalles.</p>`;
            });
    }

    function openCancelConfirmationModal(orderId) {
        orderIdToCancel = orderId;
        cancelConfirmationModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeDetailsModal() {
        orderDetailsModal.classList.remove('active');
        document.body.style.overflow = '';
    }

    function closeCancelConfirmationModal() {
        cancelConfirmationModal.classList.remove('active');
        document.body.style.overflow = '';
        orderIdToCancel = null;
    }

    function mostrarNotificacion(mensaje, tipo) {
        const contenedor = document.querySelector('.main-content');
        const div = document.createElement('div');
        div.className = `notification ${tipo}`;
        div.innerHTML = `<i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i><div><p>${mensaje}</p></div>`;
        div.style.opacity = '0';
        contenedor.prepend(div);

        setTimeout(() => {
            div.style.opacity = '1';
        }, 100);
        
        setTimeout(() => {
            div.style.opacity = '0';
            setTimeout(() => div.remove(), 500);
        }, 4000);
    }

    // Eventos para abrir detalles
    btnViewDetails.forEach(btn => {
        btn.addEventListener('click', function () {
            const orderId = this.getAttribute('data-id');
            openDetailsModal(orderId);
        });
    });

    // Abrir detalles al hacer clic en tarjeta
    document.querySelectorAll('.order-card').forEach(card => {
        card.addEventListener('click', function (e) {
            if (!e.target.closest('button')) {
                const orderId = this.getAttribute('data-id');
                openDetailsModal(orderId);
            }
        });
    });

    // Evento para botones de cancelación
    btnCancelOrders.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation(); // Evita que se abra el modal de detalles
            const orderId = this.getAttribute('data-id');
            openCancelConfirmationModal(orderId);
        });
    });

    // Cerrar modales
    closeModal.addEventListener('click', closeDetailsModal);
    closeConfirmation.addEventListener('click', closeCancelConfirmationModal);
    btnCancelModal.addEventListener('click', closeCancelConfirmationModal);

    // Botón de confirmar cancelación
    btnConfirmCancel.addEventListener('click', function () {
        if (orderIdToCancel) {
            // Mostrar indicador de carga
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            this.disabled = true;
            
            fetch(`/backend/cancelar_pedido.php?id=${orderIdToCancel}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeCancelConfirmationModal();
                        closeDetailsModal();
                        mostrarNotificacion("El pedido ha sido cancelado exitosamente", "success");
                        
                        // Actualizar la UI para reflejar el cambio
                        const statusElement = document.querySelector(`.order-card[data-id="${data.pedido_id}"] .status-badge`);
                        if (statusElement) {
                            statusElement.textContent = "Cancelado";
                            statusElement.className = "status-badge cancelled";
                        }
                        
                        // Eliminar el botón de cancelar de ese pedido
                        const cancelBtn = document.querySelector(`.order-card[data-id="${data.pedido_id}"] .btn-cancel-order`);
                        if (cancelBtn) {
                            cancelBtn.remove();
                        }
                    } else {
                        alert("Error: " + (data.message || "No se pudo cancelar el pedido"));
                    }
                    
                    // Restaurar botón
                    this.innerHTML = '<i class="fas fa-times"></i> Sí, cancelar pedido';
                    this.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Ocurrió un error al procesar la solicitud");
                    
                    // Restaurar botón
                    this.innerHTML = '<i class="fas fa-times"></i> Sí, cancelar pedido';
                    this.disabled = false;
                });
        }
    });

    // Cerrar modales al hacer clic fuera
    orderDetailsModal.addEventListener('click', function (e) {
        if (e.target === orderDetailsModal) closeDetailsModal();
    });
    cancelConfirmationModal.addEventListener('click', function (e) {
        if (e.target === cancelConfirmationModal) closeCancelConfirmationModal();
    });
});
</script>

<?php include 'includes/footer.php'; ?>