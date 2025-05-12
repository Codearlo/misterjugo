<?php
// Incluir la conexión a la base de datos
require_once '../backend/conexion.php';

// Obtener los productos del carrito (suponiendo que estén almacenados en la sesión)
session_start();
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: checkout");
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

        <!-- Botón para enviar al WhatsApp -->
        <button onclick="enviarWhatsApp()">Enviar Pedido a WhatsApp</button>

        <!-- Script para generar el enlace de WhatsApp -->
        <script>
            function enviarWhatsApp() {
                // Obtener datos del formulario
                const nombre = document.getElementById('nombre').value;
                const telefono = document.getElementById('telefono').value;
                const direccion = document.getElementById('direccion').value;

                // Datos del carrito
                const productos = <?php echo json_encode($carrito); ?>;
                const total = <?php echo $total; ?>;

                // Construir el mensaje
                let mensaje = "¡Nuevo pedido!\n\n";
                mensaje += `Cliente: ${nombre}\n`;
                mensaje += `Teléfono: ${telefono}\n`;
                mensaje += `Dirección: ${direccion}\n\n`;
                mensaje += "Productos:\n";

                productos.forEach(producto => {
                    mensaje += `- ${producto.nombre} x${producto.cantidad} (${producto.precio.toFixed(2)} c/u)\n`;
                });

                mensaje += `\nTotal: $${total.toFixed(2)}`;

                // Codificar el mensaje para el enlace
                const encodedMessage = encodeURIComponent(mensaje);

                // Generar el enlace de WhatsApp
                const whatsappNumber = '+51970846395'; // Reemplaza con tu número de WhatsApp
                const whatsappLink = `https://wa.me/ ${whatsappNumber}?text=${encodedMessage}`;

                // Abrir el enlace en una nueva ventana
                window.open(whatsappLink, '_blank');
            }
        </script>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>