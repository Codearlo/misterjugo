<?php

session_start();

require_once '../backend/conexion.php';

// Verificar si hay productos en el carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    echo "<h2>El carrito está vacío.</h2>";
    exit;
}

function obtenerDetallesProductos($ids) {
    global $conexion;

    // Sanear IDs
    $idsStr = implode(',', array_map('intval', $ids));

    // Consulta SQL
    $query = "SELECT id, nombre, precio FROM productos WHERE id IN ($idsStr)";
    $result = mysqli_query($conexion, $query);

    $productos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $productos[$row['id']] = $row;
    }
    return $productos;
}

// Obtener IDs de los productos en el carrito
$productosIds = array_keys($_SESSION['carrito']);
$detallesProductos = obtenerDetallesProductos($productosIds);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Tu Tienda</title>
    <link rel="stylesheet" href="/css/checkout.css">
</head>
<body>

<div class="checkout-container">
    <h1>Resumen de tu Pedido</h1>

    <!-- Formulario -->
    <form action="../backend/procesar_pedido.php" method="POST">
        <!-- Tabla de productos -->
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio Unitario</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalPedido = 0;
                foreach ($_SESSION['carrito'] as $id => $cantidad):
                    if (isset($detallesProductos[$id])):
                        $producto = $detallesProductos[$id];
                        $precioUnitario = floatval($producto['precio']);
                        $totalProducto = $precioUnitario * $cantidad;
                        $totalPedido += $totalProducto;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                        <td>S/ <?= number_format($precioUnitario, 2) ?></td>
                        <td><?= $cantidad ?></td>
                        <td>S/ <?= number_format($totalProducto, 2) ?></td>
                    </tr>
                <?php
                    endif;
                endforeach;
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><strong>Total:</strong></td>
                    <td><strong>S/ <?= number_format($totalPedido, 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <!-- Campos del cliente -->
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required><br>

        <label for="telefono">Teléfono:</label>
        <input type="tel" id="telefono" name="telefono" required><br>

        <label for="direccion">Dirección:</label>
        <textarea id="direccion" name="direccion" required></textarea><br>

        <!-- Campo oculto con el total -->
        <input type="hidden" name="total" value="<?= $totalPedido ?>">

        <!-- Botón para enviar formulario -->
        <button type="submit">Confirmar Pedido</button>
    </form>
</div>

</body>
</html>