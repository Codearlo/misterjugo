<?php
session_start();

// Validar datos del formulario
if (!isset($_POST['nombre'], $_POST['telefono'], $_POST['direccion'], $_SESSION['carrito'])) {
    die("Datos incompletos.");
}

$nombre = htmlspecialchars(trim($_POST['nombre']));
$telefono = htmlspecialchars(trim($_POST['telefono']));
$direccion = htmlspecialchars(trim($_POST['direccion']));
$total = floatval($_POST['total']);

// Conexión a la base de datos
require_once 'conexion.php'; // Asegúrate de que este archivo exista en /backend

// Obtener detalles de los productos
$productosIds = array_keys($_SESSION['carrito']);
$idsStr = implode(',', array_map('intval', $productosIds));
$query = "SELECT id, nombre, precio FROM productos WHERE id IN ($idsStr)";
$result = mysqli_query($conexion, $query);

$productos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $productos[$row['id']] = $row;
}

// Construir mensaje de WhatsApp
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

// Número de WhatsApp destino
$numeroDestino = "+51970846395"; // Reemplaza con tu número real

// Codificar mensaje y redirigir a WhatsApp
$whatsappUrl = "https://wa.me/ $numeroDestino?text=" . rawurlencode($mensaje);
header("Location: $whatsappUrl");
exit;
?>