<?php
// Incluir la conexión a la base de datos
require_once '../backend/conexion.php';

// Obtener los datos del formulario
$nombre = htmlspecialchars(trim($_POST['nombre']));
$telefono = htmlspecialchars(trim($_POST['telefono']));
$direccion = htmlspecialchars(trim($_POST['direccion']));
$totalPedido = isset($_POST['total']) ? floatval($_POST['total']) : 0;

// Obtener los productos del carrito
session_start();
$productosCarrito = $_SESSION['carrito'];

// Obtener detalles de los productos
function obtenerDetallesProductos($ids) {
    global $conexion;
    $idsStr = implode(',', $ids);
    $query = "SELECT id, nombre, precio FROM productos WHERE id IN ($idsStr)";
    $result = mysqli_query($conexion, $query);
    $productos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $productos[$row['id']] = $row;
    }
    return $productos;
}

$productosIds = array_keys($productosCarrito);
$detallesProductos = obtenerDetallesProductos($productosIds);

// Construir el mensaje de WhatsApp
$mensaje = "Nuevo Pedido:\n\n";
$mensaje .= "Nombre: $nombre\n";
$mensaje .= "Teléfono: $telefono\n";
$mensaje .= "Dirección: $direccion\n\n";

$mensaje .= "Detalles del Pedido:\n";
foreach ($productosCarrito as $id => $cantidad) {
    if (isset($detallesProductos[$id])) {
        $producto = $detallesProductos[$id];
        $precioUnitario = $producto['precio'];
        $totalProducto = $precioUnitario * $cantidad;
        $mensaje .= "- {$producto['nombre']} x $cantidad = $ {$totalProducto}\n";
    }
}
$mensaje .= "\nTotal: $ {$totalPedido}";

// Número de WhatsApp destino (sin espacios ni guiones)
$numeroDestino = "+51970846395"; // Reemplaza con tu número de WhatsApp real

// Generar la URL de WhatsApp
$whatsappUrl = "https://wa.me/ $numeroDestino?text=" . urlencode($mensaje);

// Redirigir al usuario a la URL de WhatsApp
header("Location: $whatsappUrl");
exit;
?>