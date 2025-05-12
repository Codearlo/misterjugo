<form action="procesar_pedido.php" method="POST">
    <table>
        <!-- Aquí va tu tabla de productos -->
        <!-- Ejemplo de fila -->
        <?php foreach ($_SESSION['carrito'] as $id => $cantidad): ?>
            <?php if (isset($detallesProductos[$id])): 
                $producto = $detallesProductos[$id];
                $precioUnitario = $producto['precio'];
                $totalProducto = $precioUnitario * $cantidad;
                $totalPedido += $totalProducto;
            ?>
            <tr>
                <td><?= htmlspecialchars($producto['nombre']) ?></td>
                <td>$ <?= number_format($precioUnitario, 2) ?></td>
                <td><?= $cantidad ?></td>
                <td>$ <?= number_format($totalProducto, 2) ?></td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>

    <!-- Campos del cliente -->
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" required><br>

    <label for="telefono">Teléfono:</label>
    <input type="tel" name="telefono" required><br>

    <label for="direccion">Dirección:</label>
    <textarea name="direccion" required></textarea><br>

    <input type="hidden" name="total" value="<?= $totalPedido ?>">

    <button type="submit">Enviar Pedido por WhatsApp</button>
</form>