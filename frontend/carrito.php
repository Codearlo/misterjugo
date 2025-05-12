<?php
// Iniciar sesión (siempre al inicio)
session_start();

// Verificar si el carrito existe, si no, lo inicializamos vacío
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Manejar acciones como eliminar producto o actualizar cantidad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $id_producto = intval($_POST['producto_id']);

        switch ($_POST['action']) {
            case 'eliminar':
                unset($_SESSION['carrito'][$id_producto]);
                break;

            case 'aumentar':
                if (isset($_SESSION['carrito'][$id_producto])) {
                    $_SESSION['carrito'][$id_producto]++;
                }
                break;

            case 'disminuir':
                if (isset($_SESSION['carrito'][$id_producto]) && $_SESSION['carrito'][$id_producto] > 1) {
                    $_SESSION['carrito'][$id_producto]--;
                } else {
                    unset($_SESSION['carrito'][$id_producto]); // Eliminar si es 0
                }
                break;
        }

        // Redirigir para evitar reenvío del formulario
        header("Location: carrito.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tu Carrito</title>
    <link rel="stylesheet" href="carrito.css"> <!-- Vinculamos el CSS -->
</head>
<body>

<h1>🛒 Tu Carrito de Compras</h1>

<?php if (empty($_SESSION['carrito'])): ?>
    <p class="empty-cart">Tu carrito está vacío.</p>
<?php else: 
    // Simular productos desde una base de datos o array
    $productos = [
        1 => ['nombre' => 'Hamburguesa', 'precio' => 120],
        2 => ['nombre' => 'Pizza', 'precio' => 150],
        3 => ['nombre' => 'Ensalada', 'precio' => 80],
        4 => ['nombre' => 'Refresco', 'precio' => 30],
    ];

    $total = 0;
?>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($_SESSION['carrito'] as $id => $cantidad): 
                if (isset($productos[$id])) {
                    $producto = $productos[$id];
                    $subtotal = $producto['precio'] * $cantidad;
                    $total += $subtotal;
            ?>
            <tr>
                <td><?= htmlspecialchars($producto['nombre']) ?></td>
                <td>$<?= number_format($producto['precio'], 2) ?></td>
                <td><?= $cantidad ?></td>
                <td>$<?= number_format($subtotal, 2) ?></td>
                <td class="actions">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="producto_id" value="<?= $id ?>">
                        <input type="hidden" name="action" value="aumentar">
                        <button type="submit">+</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="producto_id" value="<?= $id ?>">
                        <input type="hidden" name="action" value="disminuir">
                        <button type="submit">−</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="producto_id" value="<?= $id ?>">
                        <input type="hidden" name="action" value="eliminar">
                        <button type="submit" onclick="return confirm('¿Eliminar este producto?')">🗑️</button>
                    </form>
                </td>
            </tr>
            <?php } endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="total">Total del Pedido:</td>
                <td class="total">$<?= number_format($total, 2) ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Botón para ir al Checkout -->
    <a href="checkout.php" class="btn-checkout">Ir al Checkout</a>

<?php endif; ?>

</body>
</html>