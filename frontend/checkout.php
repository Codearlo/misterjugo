<?php
// Iniciar sesión
session_start();

// Verificar si hay productos en el carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    $_SESSION['error_checkout'] = "El carrito está vacío.";
    header("Location: /productos");
    exit;
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, redirigir al login con un mensaje
    $_SESSION['error_login'] = "Debes iniciar sesión para realizar un pedido";
    header("Location: /login");
    exit;
}

// Conexión a base de datos
require_once '../backend/conexion.php';

// Obtener el ID del usuario actual
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];

// Verificar si hay mensajes de éxito o error
$exito_checkout = isset($_SESSION['exito_checkout']) ? $_SESSION['exito_checkout'] : '';
$error_checkout = isset($_SESSION['error_checkout']) ? $_SESSION['error_checkout'] : '';

// Limpiar mensajes de sesión
unset($_SESSION['exito_checkout']);
unset($_SESSION['error_checkout']);

// Obtener direcciones del usuario si está logueado
$direcciones = [];
$direccionPredeterminada = null;

$stmt = $conn->prepare("SELECT * FROM direcciones WHERE usuario_id = ? ORDER BY es_predeterminada DESC, fecha_creacion DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $direcciones[] = $row;
        if ($row['es_predeterminada'] == 1 && !$direccionPredeterminada) {
            $direccionPredeterminada = $row;
        }
    }
    
    // Si no hay dirección predeterminada pero hay direcciones, usar la primera
    if (!$direccionPredeterminada && count($direcciones) > 0) {
        $direccionPredeterminada = $direcciones[0];
    }
}

// Calcular total
$totalPedido = 0;
$productosDetalles = [];

// Si el carrito es un array de objetos
if (isset(reset($_SESSION['carrito'])['id'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $totalPedido += $item['precio'] * $item['cantidad'];
        $productosDetalles[$item['id']] = $item;
    }
}
// Si el carrito es un array asociativo id => cantidad
else {
    // Obtener IDs de los productos en el carrito
    $productosIds = array_keys($_SESSION['carrito']);
    
    // Consulta SQL para obtener detalles de productos
    if (!empty($productosIds)) {
        $idsStr = implode(',', array_map('intval', $productosIds));
        $query = "SELECT id, nombre, precio FROM productos WHERE id IN ($idsStr)";
        $result = $conn->query($query);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $productosDetalles[$row['id']] = $row;
            }
            
            // Calcular total
            foreach ($_SESSION['carrito'] as $id => $cantidad) {
                if (isset($productosDetalles[$id])) {
                    $totalPedido += $productosDetalles[$id]['precio'] * $cantidad;
                }
            }
        }
    }
}

// Incluir el archivo header
include 'includes/header.php';
?>

<link rel="stylesheet" href="/css/checkout.css">

<div class="checkout-container">
    <h1>Finalizar Compra</h1>

    <!-- Notificaciones -->
    <?php if (!empty($exito_checkout)): ?>
        <div class="notification success">
            <i class="fas fa-check-circle"></i>
            <div><p><?php echo $exito_checkout; ?></p></div>
        </div>
    <?php endif; ?>
    <?php if (!empty($error_checkout)): ?>
        <div class="notification error">
            <i class="fas fa-exclamation-circle"></i>
            <div><p><?php echo $error_checkout; ?></p></div>
        </div>
    <?php endif; ?>

    <!-- Resumen del pedido -->
    <div class="order-summary">
        <h2>Resumen de tu Pedido</h2>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset(reset($_SESSION['carrito'])['id'])): ?>
                    <?php foreach ($_SESSION['carrito'] as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                            <td>S/<?php echo number_format($item['precio'], 2); ?></td>
                            <td><?php echo $item['cantidad']; ?></td>
                            <td>S/<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ($_SESSION['carrito'] as $id => $cantidad): ?>
                        <?php if (isset($productosDetalles[$id])): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($productosDetalles[$id]['nombre']); ?></td>
                                <td>S/<?php echo number_format($productosDetalles[$id]['precio'], 2); ?></td>
                                <td><?php echo $cantidad; ?></td>
                                <td>S/<?php echo number_format($productosDetalles[$id]['precio'] * $cantidad, 2); ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><strong>Total:</strong></td>
                    <td><strong>S/<?php echo number_format($totalPedido, 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <?php if (empty($direcciones)): ?>
        <!-- Mensaje para usuarios sin direcciones -->
        <div class="address-required-message">
            <div class="message-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="message-content">
                <h3>No tienes direcciones guardadas</h3>
                <p>Para continuar con tu pedido, primero debes agregar una dirección de entrega.</p>
                <div class="message-actions">
                    <a href="/direcciones" class="btn-add-address">
                        <i class="fas fa-plus"></i> Agregar dirección
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Formulario para usuarios con direcciones -->
        <div class="customer-info">
            <h2>Datos de Entrega</h2>
            
            <form id="checkout-form" action="/backend/procesar_pedido.php" method="POST">
                <!-- Selector de direcciones (primero) -->
                <div class="form-group address-selector">
                    <label for="direccion_id">Selecciona una dirección de entrega:</label>
                    <select id="direccion_id" name="direccion_id" class="address-select" required>
                        <?php foreach ($direcciones as $dir): ?>
                            <option value="<?php echo $dir['id']; ?>" 
                                <?php echo ($direccionPredeterminada && $direccionPredeterminada['id'] == $dir['id']) ? 'selected' : ''; ?>
                                data-calle="<?php echo htmlspecialchars($dir['calle']); ?>"
                                data-ciudad="<?php echo htmlspecialchars($dir['ciudad']); ?>"
                                data-estado="<?php echo htmlspecialchars($dir['estado']); ?>"
                                data-codigo="<?php echo htmlspecialchars($dir['codigo_postal']); ?>"
                                data-telefono="<?php echo htmlspecialchars($dir['telefono']); ?>"
                                data-instrucciones="<?php echo htmlspecialchars($dir['instrucciones']); ?>">
                                <?php echo htmlspecialchars($dir['calle']); ?> 
                                (<?php echo $dir['es_predeterminada'] ? 'Predeterminada' : 'Alternativa'; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div class="address-actions">
                        <a href="/direcciones" class="btn-manage-addresses">
                            <i class="fas fa-cog"></i> Gestionar direcciones
                        </a>
                    </div>
                </div>
                
                <!-- Detalles de la dirección seleccionada (no modificables) -->
                <div class="selected-address">
                    <h3>Detalles de entrega</h3>
                    <div class="address-details">
                        <div class="detail-row">
                            <div class="detail-label"><i class="fas fa-map-marker-alt"></i> Dirección:</div>
                            <div class="detail-value" id="address-display">
                                <?php if ($direccionPredeterminada): ?>
                                    <?php echo htmlspecialchars($direccionPredeterminada['calle']); ?>, 
                                    <?php echo htmlspecialchars($direccionPredeterminada['ciudad']); ?>, 
                                    <?php echo htmlspecialchars($direccionPredeterminada['estado']); ?>, 
                                    CP: <?php echo htmlspecialchars($direccionPredeterminada['codigo_postal']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label"><i class="fas fa-phone"></i> Teléfono:</div>
                            <div class="detail-value" id="phone-display">
                                <?php echo $direccionPredeterminada ? htmlspecialchars($direccionPredeterminada['telefono']) : ''; ?>
                            </div>
                        </div>
                        
                        <div class="detail-row" id="instructions-row" style="<?php echo empty($direccionPredeterminada['instrucciones']) ? 'display:none;' : ''; ?>">
                            <div class="detail-label"><i class="fas fa-info-circle"></i> Instrucciones:</div>
                            <div class="detail-value" id="instructions-display">
                                <?php echo $direccionPredeterminada && !empty($direccionPredeterminada['instrucciones']) ? htmlspecialchars($direccionPredeterminada['instrucciones']) : ''; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Nombre del cliente -->
                <div class="form-group">
                    <label for="nombre">Nombre para el pedido:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user_name); ?>" required>
                    <div class="field-note">Nombre que aparecerá en tu pedido</div>
                </div>
                
                <!-- Instrucciones adicionales -->
                <div class="form-group">
                    <label for="instrucciones_adicionales">Instrucciones adicionales para este pedido (opcional):</label>
                    <textarea id="instrucciones_adicionales" name="instrucciones_adicionales" placeholder="Ej: Tocar el timbre, dejar con el portero, etc."></textarea>
                </div>
                
                <!-- Campos ocultos con la información de la dirección -->
                <input type="hidden" name="telefono_hidden" id="telefono_hidden" value="<?php echo $direccionPredeterminada ? htmlspecialchars($direccionPredeterminada['telefono']) : ''; ?>">
                <input type="hidden" name="direccion_completa" id="direccion_completa" value="<?php 
                    echo $direccionPredeterminada ? 
                        htmlspecialchars($direccionPredeterminada['calle'] . ', ' . 
                                        $direccionPredeterminada['ciudad'] . ', ' . 
                                        $direccionPredeterminada['estado'] . ', CP: ' . 
                                        $direccionPredeterminada['codigo_postal']) : ''; 
                ?>">
                <input type="hidden" name="instrucciones_hidden" id="instrucciones_hidden" value="<?php echo $direccionPredeterminada ? htmlspecialchars($direccionPredeterminada['instrucciones']) : ''; ?>">
                <input type="hidden" name="total" value="<?php echo $totalPedido; ?>">
                
                <!-- Botones de acción -->
                <div class="form-actions">
                    <a href="/carrito" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Volver al carrito
                    </a>
                    <button type="submit" id="btn-realizar-pedido" class="btn-submit">
                        <i class="fas fa-check"></i> Realizar Pedido
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<!-- Script mejorado para manejar el pedido -->
<script src="/js/checkout.js"></script>

<?php
// Incluir el footer
include 'includes/footer.php';
?>