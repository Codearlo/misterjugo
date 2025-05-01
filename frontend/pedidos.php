<?php
// Verificar si el usuario está logueado
session_start();
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, redirigir al login con un mensaje
    $_SESSION['error_pedido'] = "Debes iniciar sesión para ver tus pedidos";
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

$stmt = $conn->prepare("SELECT * FROM pedidos WHERE USUARIO_ID = ? ORDER BY FECHA_PEDIDO DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Obtener detalles del pedido
        $detalles = [];
        $stmt_detalles = $conn->prepare("SELECT pd.*, p.NOMBRE FROM pedido_detalle pd JOIN productos p ON pd.PRODUCTO_ID = p.ID WHERE pd.PEDIDO_ID = ?");
        $stmt_detalles->bind_param("i", $row['ID']);
        $stmt_detalles->execute();
        $result_detalles = $stmt_detalles->get_result();
        
        while ($detalle = $result_detalles->fetch_assoc()) {
            $detalles[] = $detalle;
        }
        
        $row['detalles'] = $detalles;
        $pedidos[] = $row;
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
                <p class="page-subtitle">Revisa el historial y estado de tus pedidos</p>
            </div>
            
            <!-- Notificaciones -->
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
                    <div class="order-list">
                        <?php foreach ($pedidos as $pedido): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-id">
                                        <span>ID:</span> #<?php echo str_pad($pedido['ID'], 6, "0", STR_PAD_LEFT); ?>
                                    </div>
                                    <div class="order-date">
                                        <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($pedido['FECHA_PEDIDO'])); ?>
                                    </div>
                                    <div class="order-status status-<?php echo strtolower($pedido['ESTADO']); ?>">
                                        <?php echo $pedido['ESTADO']; ?>
                                    </div>
                                </div>
                                
                                <div class="order-summary">
                                    <div class="item-count">
                                        <?php echo count($pedido['detalles']); ?> ítem<?php echo count($pedido['detalles']) != 1 ? 's' : ''; ?>
                                    </div>
                                    <div class="order-total">
                                        Total: <span>$<?php echo number_format($pedido['TOTAL'], 2); ?></span>
                                    </div>
                                </div>
                                
                                <div class="order-actions">
                                    <button class="btn-view-details" data-order="<?php echo $pedido['ID']; ?>">
                                        <i class="fas fa-eye"></i> Ver detalles
                                    </button>
                                    
                                    <?php if ($pedido['ESTADO'] == 'Pendiente'): ?>
                                        <button class="btn-cancel" data-order="<?php echo $pedido['ID']; ?>">
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
                            <i class="fas fa-shopping-cart"></i>
                            <h3>No tienes pedidos registrados</h3>
                            <p>Realiza tu primer pedido y aparecerá aquí</p>
                            <a href="/menu" class="btn-primary">
                                <i class="fas fa-shopping-basket"></i> Ir al menú
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
                <h3 id="modal-order-title">Detalles del Pedido</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="order-info">
                    <div class="order-date">
                        <strong>Fecha:</strong> <span id="order-date"></span>
                    </div>
                    <div class="order-status">
                        <strong>Estado:</strong> <span id="order-status" class="status-tag"></span>
                    </div>
                    <div class="order-total">
                        <strong>Total:</strong> <span id="order-total"></span>
                    </div>
                </div>
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="order-items">
                        <!-- Los items se cargarán dinámicamente -->
                    </tbody>
                </table>
                
                <div class="modal-actions">
                    <button class="btn-cancel">Cerrar</button>
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
                <p id="order-to-cancel"></p>
            </div>
            <div class="confirmation-actions">
                <button type="button" class="btn-cancel-cancel">Cancelar</button>
                <button type="button" class="btn-confirm-cancel">Confirmar</button>
            </div>
        </div>
    </div>
</div>
<!-- Fin de la página de pedidos -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos DOM
    const btnViewDetails = document.querySelectorAll('.btn-view-details');
    const btnCancelOrder = document.querySelectorAll('.btn-cancel');
    const orderDetailsModal = document.getElementById('order-details-modal');
    const cancelConfirmationModal = document.getElementById('cancel-confirmation-modal');
    const closeModal = document.querySelector('.close-modal');
    const closeConfirmation = document.querySelector('.confirmation-close');
    const btnCancel = document.querySelector('.btn-cancel');
    const btnCancelCancel = document.querySelector('.btn-cancel-cancel');
    const btnConfirmCancel = document.querySelector('.btn-confirm-cancel');
    const orderIdToCancelText = document.getElementById('order-to-cancel');
    
    // Variable para almacenar el ID del pedido a cancelar
    let orderIdToCancel = null;
    
    // Función para abrir el modal de detalles
    function openOrderDetailsModal(orderId) {
        // Mostrar el modal
        orderDetailsModal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Limpiar contenido previo
        document.getElementById('order-items').innerHTML = '';
        document.getElementById('order-date').textContent = '';
        document.getElementById('order-status').textContent = '';
        document.getElementById('order-total').textContent = '';
        
        // Obtener datos del pedido
        fetch(`/backend/obtener_pedido.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar información del pedido
                    document.getElementById('modal-order-title').textContent = `Pedido #${orderId}`;
                    document.getElementById('order-date').textContent = data.pedido.fecha_pedido;
                    document.getElementById('order-status').textContent = data.pedido.estado;
                    document.getElementById('order-status').className = `status-tag status-${data.pedido.estado.toLowerCase()}`;
                    document.getElementById('order-total').textContent = `$${parseFloat(data.pedido.total).toFixed(2)}`;
                    
                    // Mostrar los items del pedido
                    const tbody = document.getElementById('order-items');
                    data.items.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.nombre}</td>
                            <td>${item.cantidad}</td>
                            <td>$${parseFloat(item.precio_unitario).toFixed(2)}</td>
                            <td>$${parseFloat(item.total).toFixed(2)}</td>
                        `;
                        tbody.appendChild(row);
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    
    // Función para abrir el modal de confirmación de cancelación
    function openCancelConfirmationModal(orderId) {
        orderIdToCancel = orderId;
        orderIdToCancelText.textContent = `Pedido #${orderId}`;
        cancelConfirmationModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    // Función para cerrar el modal
    function closeModalFunction() {
        orderDetailsModal.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Función para cerrar el modal de confirmación
    function closeConfirmationModal() {
        cancelConfirmationModal.classList.remove('active');
        document.body.style.overflow = '';
        orderIdToCancel = null;
    }
    
    // Eventos para abrir modal de detalles
    btnViewDetails.forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order');
            openOrderDetailsModal(orderId);
        });
    });
    
    // Eventos para mostrar confirmación de cancelación
    btnCancelOrder.forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order');
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
    closeModal.addEventListener('click', closeModalFunction);
    closeConfirmation.addEventListener('click', closeConfirmationModal);
    btnCancel.addEventListener('click', closeModalFunction);
    btnCancelCancel.addEventListener('click', closeConfirmationModal);
    
    // Cerrar modal haciendo clic fuera del contenido
    orderDetailsModal.addEventListener('click', function(e) {
        if (e.target === orderDetailsModal) {
            closeModalFunction();
        }
    });
    
    cancelConfirmationModal.addEventListener('click', function(e) {
        if (e.target === cancelConfirmationModal) {
            closeConfirmationModal();
        }
    });
    
    // Si hay notificaciones, mostrarlas y ocultarlas después de un tiempo
    const notifications = document.querySelectorAll('.notification');
    if (notifications.length > 0) {
        // Mostrar después de un pequeño retraso
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