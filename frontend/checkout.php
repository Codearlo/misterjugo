<?php
// Incluir la conexión a la base de datos
require_once '../backend/conexion.php';

// Obtener los productos del carrito (suponiendo que estén almacenados en la sesión)
session_start();
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: index.php");
    exit();
}

$carrito = $_SESSION['carrito'];
$total = 0;

// Calcular el total del carrito
foreach ($carrito as $producto) {
    $total += $producto['precio'] * $producto['cantidad'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Mi Jugo</title>
    <link rel="stylesheet" href="css/checkout.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="checkout-container">
        <h1>Resumen de tu pedido</h1>
        <form action="procesar_pedido.php" method="POST" id="checkout-form">
            <input type="hidden" name="productos" value="<?php echo json_encode($carrito); ?>">
            <input type="hidden" name="total" value="<?php echo $total; ?>">

            <!-- Mostrar los productos del carrito -->
            <div class="productos-carrito">
                <?php foreach ($carrito as $producto): ?>
                    <div class="producto">
                        <img src="images/<?php echo $producto['imagen']; ?>" alt="<?php echo $producto['nombre']; ?>" width="50">
                        <div class="detalle-producto">
                            <p><?php echo $producto['nombre']; ?></p>
                            <p>Cantidad: <?php echo $producto['cantidad']; ?></p>
                            <p>Precio unitario: $<?php echo $producto['precio']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Información del cliente -->
            <h2>Datos del cliente</h2>
            <div class="cliente-info">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>

                <label for="telefono">Teléfono:</label>
                <input type="tel" id="telefono" name="telefono" required>

                <label for="direccion">Dirección:</label>
                <textarea id="direccion" name="direccion" required></textarea>
            </div>

            <!-- Botón de envío -->
            <button type="submit">Confirmar pedido</button>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>