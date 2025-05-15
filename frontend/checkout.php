<?php
// Iniciar sesión
session_start();

// Verificar si hay productos en el carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    echo "<h2>El carrito está vacío.</h2>";
    echo "<a href='/productos'>Ver productos</a>";
    exit;
}

// Verificar si el usuario está logueado
$userLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userId = $userLoggedIn ? $_SESSION['user_id'] : null;
$userName = $userLoggedIn ? $_SESSION['user_name'] : '';
$userEmail = $userLoggedIn ? $_SESSION['user_email'] : '';

// Conexión a la base de datos
require_once '../backend/conexion.php';

// Obtener direcciones del usuario si está logueado
$direcciones = [];
$direccionPredeterminada = null;

if ($userLoggedIn) {
    $stmt = $conn->prepare("SELECT * FROM direcciones WHERE usuario_id = ? ORDER BY es_predeterminada DESC, fecha_creacion DESC");
    $stmt->bind_param("i", $userId);
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

    <?php if (!$userLoggedIn): ?>
        <!-- Mensaje para usuarios no logueados -->
        <div class="login-required-message">
            <div class="message-icon"><i class="fas fa-user-lock"></i></div>
            <div class="message-content">
                <h3>Inicia sesión para continuar</h3>
                <p>Para realizar un pedido, debes iniciar sesión en tu cuenta primero.</p>
                <div class="message-actions">
                    <a href="/login?redirect=/checkout" class="btn-login">Iniciar sesión</a>
                    <a href="/registro?redirect=/checkout" class="btn-register">Registrarse</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Formulario para usuarios logueados -->
        <div class="customer-info">
            <h2>Datos de Entrega</h2>
            
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
                <form action="/backend/procesar_pedido.php" method="POST">
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
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($userName); ?>" required>
                        <div class="field-note">Nombre que aparecerá en tu pedido</div>
                    </div>
                    
                    <!-- Instrucciones adicionales -->
                    <div class="form-group">
                        <label for="instrucciones_adicionales">Instrucciones adicionales para este pedido (opcional):</label>
                        <textarea id="instrucciones_adicionales" name="instrucciones_adicionales" placeholder="Ej: Tocar el timbre, dejar con el portero, etc."></textarea>
                    </div>
                    
                    <!-- Campos ocultos con la información de la dirección -->
                    <input type="hidden" name="telefono" id="telefono_hidden" value="<?php echo $direccionPredeterminada ? htmlspecialchars($direccionPredeterminada['telefono']) : ''; ?>">
                    <input type="hidden" name="direccion_completa" id="direccion_completa" value="<?php 
                        echo $direccionPredeterminada ? 
                            htmlspecialchars($direccionPredeterminada['calle'] . ', ' . 
                                            $direccionPredeterminada['ciudad'] . ', ' . 
                                            $direccionPredeterminada['estado'] . ', CP: ' . 
                                            $direccionPredeterminada['codigo_postal']) : ''; 
                    ?>">
                    <input type="hidden" name="instrucciones" id="instrucciones_hidden" value="<?php echo $direccionPredeterminada ? htmlspecialchars($direccionPredeterminada['instrucciones']) : ''; ?>">
                    <input type="hidden" name="total" value="<?php echo $totalPedido; ?>">
                    
                    <!-- Botones de acción -->
                    <div class="form-actions">
                        <a href="/carrito" class="btn-back">
                            <i class="fas fa-arrow-left"></i> Volver al carrito
                        </a>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-check"></i> Realizar Pedido
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Solo inicializar si existe el selector de direcciones
    const direccionSelect = document.getElementById('direccion_id');
    const addressDisplay = document.getElementById('address-display');
    const phoneDisplay = document.getElementById('phone-display');
    const instructionsDisplay = document.getElementById('instructions-display');
    const instructionsRow = document.getElementById('instructions-row');
    
    // Campos ocultos
    const telefonoHidden = document.getElementById('telefono_hidden');
    const direccionCompleta = document.getElementById('direccion_completa');
    const instruccionesHidden = document.getElementById('instrucciones_hidden');
    
    if (direccionSelect) {
        direccionSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const calle = selectedOption.dataset.calle;
            const ciudad = selectedOption.dataset.ciudad;
            const estado = selectedOption.dataset.estado;
            const codigo = selectedOption.dataset.codigo;
            const telefono = selectedOption.dataset.telefono;
            const instrucciones = selectedOption.dataset.instrucciones;
            
            // Actualizar la dirección mostrada
            addressDisplay.textContent = `${calle}, ${ciudad}, ${estado}, CP: ${codigo}`;
            phoneDisplay.textContent = telefono;
            
            // Actualizar campos ocultos
            telefonoHidden.value = telefono;
            direccionCompleta.value = `${calle}, ${ciudad}, ${estado}, CP: ${codigo}`;
            instruccionesHidden.value = instrucciones || '';
            
            // Mostrar/ocultar instrucciones según si existen o no
            if (instrucciones && instrucciones.trim() !== '') {
                instructionsDisplay.textContent = instrucciones;
                instructionsRow.style.display = 'flex';
            } else {
                instructionsRow.style.display = 'none';
            }
        });
    }
});
</script>

<?php
// Incluir el footer
include 'includes/footer.php';
?>