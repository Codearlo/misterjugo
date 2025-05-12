<?php
session_start();

if (!isset($_POST['nombre'], $_POST['telefono'], $_POST['direccion'], $_SESSION['carrito'])) {
    die("Datos incompletos.");
}

$nombre = htmlspecialchars($_POST['nombre']);
$telefono = htmlspecialchars($_POST['telefono']);
$direccion = htmlspecialchars($_POST['direccion']);
$total = floatval($_POST['total']);

// Obtener detalles de los productos desde la base de datos
require_once '../backend/conexion.php';

$productosIds = array_keys($_SESSION['carrito']);
$idsStr = implode(',', array_map('intval', $productosIds));
$query = "SELECT id, nombre, precio FROM productos WHERE id IN ($idsStr)";
$result = mysqli_query($conexion, $query);

$productos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $productos[$row['id']] = $row;
}

// Construir mensaje
$mensaje = "Nuevo Pedido:\n\n";
$mensaje .= "Nombre: $nombre\n";
$mensaje .= "Teléfono: $telefono\n";
$mensaje .= "Dirección: $direccion\n\n";
$mensaje .= "Productos:\n";

foreach ($_SESSION['carrito'] as $id => $cantidad) {
    if (isset($productos[$id])) {
        $producto = $productos[$id];
        $subtotal = $producto['precio'] * $cantidad;
        $mensaje .= "- {$producto['nombre']} x $cantidad = S/ " . number_format($subtotal, 2) . "\n";
    }
}
$mensaje .= "\nTotal: S/ " . number_format($total, 2);

// Número de WhatsApp (formato internacional)
$numero = "+51970846395"; // Reemplaza con tu número

// Codificar mensaje para WhatsApp
$mensajeCodificado = rawurlencode($mensaje);
$whatsappUrl = "https://wa.me/ $numero?text=$mensajeCodificado";

// Redirigir a WhatsApp
header("Location: $whatsappUrl");
exit;