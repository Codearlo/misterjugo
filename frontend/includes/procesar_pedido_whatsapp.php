<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Error: Debes iniciar sesión para realizar un pedido.");
}

include '../../backend/conexion.php';

$usuario_id = $_SESSION['user_id'];
$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

if (empty($carrito)) {
    die("El carrito está vacío.");
}

$total_pedido = 0;

// Primero insertamos el pedido principal
$stmt_pedido = $conn->prepare("INSERT INTO pedidos (usuario_id, total, estado) VALUES (?, 0, 'pendiente')");
$stmt_pedido->bind_param("i", $usuario_id);
$stmt_pedido->execute();
$pedido_id = $stmt_pedido->insert_id;
$stmt_pedido->close();

// Luego insertamos cada producto del carrito
$stmt_detalle = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, nombre_producto, cantidad, precio) VALUES (?, ?, ?, ?, ?)");

foreach ($carrito as $producto) {
    $producto_id = $producto['id'];
    $nombre_producto = $producto['nombre'];
    $cantidad = $producto['cantidad'];
    $precio = $producto['precio'];
    $total_pedido += $cantidad * $precio;

    $stmt_detalle->bind_param("iissd", $pedido_id, $producto_id, $nombre_producto, $cantidad, $precio);
    $stmt_detalle->execute();
}

// Actualizar el total del pedido
$stmt_actualizar_total = $conn->prepare("UPDATE pedidos SET total = ? WHERE id = ?");
$stmt_actualizar_total->bind_param("di", $total_pedido, $pedido_id);
$stmt_actualizar_total->execute();
$stmt_actualizar_total->close();

// Limpiar el carrito después del pedido
unset($_SESSION['carrito']);

// Generar mensaje de WhatsApp
$mensaje = "Hola MJUGO, quiero hacer el siguiente pedido:\n\n";
foreach ($carrito as $item) {
    $mensaje .= "- {$item['nombre']} x {$item['cantidad']} - $$item['precio'] c/u\n";
}
$mensaje .= "\nTotal: $" . number_format($total_pedido, 2);
$mensaje = urlencode($mensaje);

// Redirigir a WhatsApp
header("Location: https://wa.me/1234567890?text= $mensaje");
exit;
?>