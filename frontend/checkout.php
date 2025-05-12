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

// Función para obtener detalles de los productos desde la base de datos
function obtenerDetallesProductos($ids, $conn) {
    if (empty($ids)) {
        return [];
    }

    // Sanear IDs
    $idsStr = implode(',', array_map('intval', $ids));

    // Consulta SQL
    $query = "SELECT id, nombre, precio FROM productos WHERE id IN ($idsStr)";
    $result = $conn->query($query);

    if (!$result) {
        return [];
    }

    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[$row['id']] = $row;
    }
    return $productos;
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
    $productosDetalles = obtenerDetallesProductos($productosIds, $conn);
    
    // Calcular total
    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        if (isset($productosDetalles[$id])) {
            $totalPedido += $productosDetalles[$id]['precio'] * $cantidad;
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

    <!-- Formulario de datos de entrega -->
    <div class="customer-info">
        <h2>Datos de Entrega</h2>
        
        <?php if (!$userLoggedIn): ?>
            <div class="login-prompt">
                <p>¿Ya tienes una cuenta? <a href="/login?redirect=/checkout">Iniciar sesión</a> para usar tus direcciones guardadas.</p>
            </div>
        <?php endif; ?>
        
        <form action="/backend/procesar_pedido.php" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre completo:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($userName); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono de contacto:</label>
                <input type="tel" id="telefono" name="telefono" value="<?php echo $direccionPredeterminada ? htmlspecialchars($direccionPredeterminada['telefono']) : ''; ?>" required>
            </div>
            
            <?php if ($userLoggedIn && count($direcciones) > 0): ?>
                <div class="form-group">
                    <label for="direccion_id">Selecciona una dirección:</label>
                    <select id="direccion_id" name="direccion_id" class="address-select">
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
                </div>
                
                <div class="selected-address">
                    <div class="address-details">
                        <p id="address-display">
                            <?php if ($direccionPredeterminada): ?>
                                <?php echo htmlspecialchars($direccionPredeterminada['calle']); ?>, 
                                <?php echo htmlspecialchars($direccionPredeterminada['ciudad']); ?>, 
                                <?php echo htmlspecialchars($direccionPredeterminada['estado']); ?>, 
                                CP: <?php echo htmlspecialchars($direccionPredeterminada['codigo_postal']); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="instrucciones">Instrucciones para el repartidor:</label>
                    <textarea id="instrucciones" name="instrucciones"><?php echo $direccionPredeterminada ? htmlspecialchars($direccionPredeterminada['instrucciones']) : ''; ?></textarea>
                </div>
                
                <input type="hidden" name="direccion" id="direccion_completa" value="<?php 
                    echo $direccionPredeterminada ? 
                        htmlspecialchars($direccionPredeterminada['calle'] . ', ' . 
                                        $direccionPredeterminada['ciudad'] . ', ' . 
                                        $direccionPredeterminada['estado'] . ', CP: ' . 
                                        $direccionPredeterminada['codigo_postal']) : ''; 
                ?>">
            <?php else: ?>
                <div class="form-group">
                    <label for="direccion">Dirección de entrega:</label>
                    <textarea id="direccion" name="direccion" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="instrucciones">Instrucciones para el repartidor:</label>
                    <textarea id="instrucciones" name="instrucciones"></textarea>
                </div>
            <?php endif; ?>
            
            <input type="hidden" name="total" value="<?php echo $totalPedido; ?>">
            
            <div class="form-actions">
                <a href="/carrito" class="btn-back">Volver al carrito</a>
                <button type="submit" class="btn-submit">Realizar Pedido</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Solo inicializar si existe el selector de direcciones
    const direccionSelect = document.getElementById('direccion_id');
    const direccionCompleta = document.getElementById('direccion_completa');
    const addressDisplay = document.getElementById('address-display');
    const telefonoInput = document.getElementById('telefono');
    const instruccionesInput = document.getElementById('instrucciones');
    
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
            
            // Actualizar campo oculto con la dirección completa
            direccionCompleta.value = `${calle}, ${ciudad}, ${estado}, CP: ${codigo}`;
            
            // Actualizar teléfono e instrucciones
            telefonoInput.value = telefono;
            instruccionesInput.value = instrucciones || '';
        });
    }
});
</script>

<?php
// Incluir el footer
include 'includes/footer.php';
?>