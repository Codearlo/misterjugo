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

// Calcular total y preparar lista de productos
$totalPedido = 0;
$productosDetalles = [];

// Obtener carrito desde localStorage (simulación con SESSION)
$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

// Si el carrito viene de localStorage, procesarlo
if (isset($_POST['carrito_data'])) {
    $carrito = json_decode($_POST['carrito_data'], true);
    $_SESSION['carrito'] = $carrito;
}

// Procesar carrito con productos personalizados
if (!empty($carrito)) {
    foreach ($carrito as $item) {
        if (isset($item['producto_id'])) {
            // Producto personalizado (jugo)
            $totalPedido += $item['precio'] * $item['cantidad'];
            $productosDetalles[] = $item;
        } elseif (isset($item['id'])) {
            // Producto normal
            $totalPedido += $item['precio'] * $item['cantidad'];
            $productosDetalles[] = $item;
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
        
        <!-- Versión tabla para desktop -->
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
                <?php foreach ($productosDetalles as $item): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($item['nombre']); ?>
                            <?php if (isset($item['opciones']) && !empty($item['opciones'])): ?>
                                <div class="product-options">
                                    <?php 
                                    $opciones = $item['opciones'];
                                    if (isset($opciones['temperatura']) && $opciones['temperatura'] === 'helado') {
                                        echo '<span class="option-tag ice"><i class="fas fa-snowflake"></i> Helado</span>';
                                    }
                                    if (isset($opciones['azucar'])) {
                                        if ($opciones['azucar'] === 'sin_azucar') {
                                            echo '<span class="option-tag no-sugar"><i class="fas fa-times-circle"></i> Sin azúcar</span>';
                                        } elseif ($opciones['azucar'] === 'con_estevia') {
                                            echo '<span class="option-tag stevia"><i class="fas fa-leaf"></i> Con estevia</span>';
                                        }
                                    }
                                    if (isset($opciones['comentarios']) && !empty($opciones['comentarios'])) {
                                        echo '<div class="option-comments"><i class="fas fa-comment"></i> ' . htmlspecialchars($opciones['comentarios']) . '</div>';
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>S/<?php echo number_format($item['precio'], 2); ?></td>
                        <td><?php echo $item['cantidad']; ?></td>
                        <td>S/<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><strong>Total:</strong></td>
                    <td><strong>S/<?php echo number_format($totalPedido, 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
        
        <!-- Versión móvil con tarjetas -->
        <div class="mobile-order-summary">
            <?php foreach ($productosDetalles as $item): ?>
                <div class="order-item-card">
                    <div class="item-name">
                        <?php echo htmlspecialchars($item['nombre']); ?>
                        <?php if (isset($item['opciones']) && !empty($item['opciones'])): ?>
                            <div class="mobile-product-options">
                                <?php 
                                $opciones = $item['opciones'];
                                if (isset($opciones['temperatura']) && $opciones['temperatura'] === 'helado') {
                                    echo '<span class="option-tag ice"><i class="fas fa-snowflake"></i> Helado</span>';
                                }
                                if (isset($opciones['azucar'])) {
                                    if ($opciones['azucar'] === 'sin_azucar') {
                                        echo '<span class="option-tag no-sugar"><i class="fas fa-times-circle"></i> Sin azúcar</span>';
                                    } elseif ($opciones['azucar'] === 'con_estevia') {
                                        echo '<span class="option-tag stevia"><i class="fas fa-leaf"></i> Con estevia</span>';
                                    }
                                }
                                if (isset($opciones['comentarios']) && !empty($opciones['comentarios'])) {
                                    echo '<div class="option-comments"><i class="fas fa-comment"></i> ' . htmlspecialchars($opciones['comentarios']) . '</div>';
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="item-details">
                        <div class="item-price">Precio: S/<?php echo number_format($item['precio'], 2); ?></div>
                        <div class="item-quantity">Cantidad: <?php echo $item['cantidad']; ?></div>
                        <div class="item-total">S/<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="order-total-mobile">
                Total: S/<?php echo number_format($totalPedido, 2); ?>
            </div>
        </div>
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
                
                <!-- Comentarios del pedido para el restaurante -->
                <div class="form-group">
                    <label for="comentarios_pedido">Comentarios para el restaurante (opcional):</label>
                    <textarea id="comentarios_pedido" name="comentarios_pedido" placeholder="Ej: Que el jugo esté bien frío, sin cebolla en el sándwich, etc."></textarea>
                    <div class="field-note">Estos comentarios son para el equipo que prepara tu pedido</div>
                </div>
                
                <!-- Instrucciones adicionales para el repartidor -->
                <div class="form-group">
                    <label for="instrucciones_adicionales">Instrucciones de entrega para el repartidor (opcional):</label>
                    <textarea id="instrucciones_adicionales" name="instrucciones_adicionales" placeholder="Ej: Tocar el timbre, dejar con el portero, etc."></textarea>
                    <div class="field-note">Estas instrucciones son para el repartidor que lleva tu pedido</div>
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
                <input type="hidden" name="carrito_data" value="<?php echo htmlspecialchars(json_encode($carrito)); ?>">
                
                <!-- Botones de acción -->
                <div class="form-actions">
                    <a href="/productos" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Seguir comprando
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

<style>
/* Estilos para las opciones de productos personalizados */
.product-options, .mobile-product-options {
    margin-top: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}

.option-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    color: white;
}

.option-tag.ice {
    background-color: #2196F3;
}

.option-tag.no-sugar {
    background-color: #F44336;
}

.option-tag.stevia {
    background-color: #4CAF50;
}

.option-comments {
    margin-top: 4px;
    font-size: 0.8rem;
    color: #666;
    font-style: italic;
    display: flex;
    align-items: center;
    gap: 4px;
}

.field-note {
    font-size: 0.8rem;
    color: #666;
    margin-top: 4px;
    font-style: italic;
}

@media (max-width: 768px) {
    .mobile-product-options {
        margin-top: 8px;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .option-tag {
        font-size: 0.7rem;
    }
}
</style>

<?php
// Incluir el footer
include 'includes/footer.php';
?>