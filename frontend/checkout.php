<?php
// Iniciar sesión (siempre al inicio)
session_start();

// Verificar si hay productos en el carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    echo "<h2>El carrito está vacío.</h2>";
    echo "<a href='/productos'>Ver productos</a>";
    exit;
}

// Conexión a la base de datos
require_once '../backend/conexion.php';

// Incluir el archivo header
include 'includes/header.php';

// Función para obtener detalles de los productos desde la base de datos
function obtenerDetallesProductos($ids) {
    global $conn;

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
    $productosDetalles = obtenerDetallesProductos($productosIds);
    
    // Calcular total
    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        if (isset($productosDetalles[$id])) {
            $totalPedido += $productosDetalles[$id]['precio'] * $cantidad;
        }
    }
}
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
        <form action="/backend/procesar_pedido.php" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre completo:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono de contacto:</label>
                <input type="tel" id="telefono" name="telefono" required>
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección de entrega:</label>
                <textarea id="direccion" name="direccion" required></textarea>
            </div>
            
            <input type="hidden" name="total" value="<?php echo $totalPedido; ?>">
            
            <div class="form-actions">
                <a href="/carrito" class="btn-back">Volver al carrito</a>
                <button type="submit" class="btn-submit">Realizar Pedido</button>
            </div>
        </form>
    </div>
</div>

<?php
// Incluir el footer
include 'includes/footer.php';
?>