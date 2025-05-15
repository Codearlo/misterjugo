<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_pedido'] = "Debes iniciar sesión para realizar un pedido.";
    header("Location: /frontend/includes/pedidos.php");
    exit;
}

// Conectar a la base de datos
include '../conexion.php';

// Obtener carrito
if (empty($_SESSION['carrito'])) {
    $_SESSION['error_pedido'] = "El carrito está vacío.";
    header("Location: /frontend/includes/pedidos.php");
    exit;
}

$carrito = $_SESSION['carrito'];
$usuario_id = $_SESSION['user_id'];
$fecha_pedido = date("Y-m-d H:i:s");
$total_pedido = 0;

// Calcular total del pedido
foreach ($carrito as $producto) {
    $total_pedido += $producto['precio'] * $producto['cantidad'];
}

// Insertar en la tabla 'pedidos'
$stmt_pedido = $conn->prepare("INSERT INTO pedidos (usuario_id, fecha_pedido, total, estado) VALUES (?, ?, ?, ?)");
$stmt_pedido->bind_param("isds", $usuario_id, $fecha_pedido, $total_pedido, "pendiente");
$stmt_pedido->execute();
$pedido_id = $stmt_pedido->insert_id; // ID del nuevo pedido
$stmt_pedido->close();

// Insertar cada producto en 'detalle_pedidos'
$stmt_detalle = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, nombre_producto, cantidad, precio) VALUES (?, ?, ?, ?, ?)");

foreach ($carrito as $producto) {
    $producto_id = $producto['id'];
    $nombre_producto = $producto['nombre'];
    $cantidad = $producto['cantidad'];
    $precio = $producto['precio'];

    $stmt_detalle->bind_param("iissd", $pedido_id, $producto_id, $nombre_producto, $cantidad, $precio);
    $stmt_detalle->execute();
}
$stmt_detalle->close();

// Limpiar carrito después del pedido
unset($_SESSION['carrito']);

// Generar mensaje de WhatsApp
$mensaje = "Hola MJUGO, quiero hacer el siguiente pedido:\n\n";
foreach ($carrito as $item) {
    $mensaje .= "- {$item['nombre']} x {$item['cantidad']} - $$item['precio'] c/u\n";
}
$mensaje .= "\nTotal: $" . number_format($total_pedido, 2);
$mensaje = urlencode($mensaje);

// Redirigir a WhatsApp
header("Location: https://wa.me/?text= $mensaje"); // Reemplaza con tu número
exit;
?>