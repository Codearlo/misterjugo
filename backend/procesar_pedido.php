<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Verificar que se haya enviado el formulario
if (!isset($_POST['nombre'], $_POST['telefono'], $_POST['direccion'], $_SESSION['carrito'])) {
    die("Datos incompletos.");
}

// Recibir datos del formulario
$nombre = htmlspecialchars(trim($_POST['nombre']));
$telefono = htmlspecialchars(trim($_POST['telefono']));
$direccion = htmlspecialchars(trim($_POST['direccion']));
$total = floatval($_POST['total']);

// Conexión a la base de datos
require_once 'conexion.php';

// Obtener detalles de los productos
$productosIds = array_keys($_SESSION['carrito']);
$idsStr = implode(',', array_map('intval', $productosIds));
$query = "SELECT id, nombre, precio FROM productos WHERE id IN ($idsStr)";
$result = mysqli_query($conexion, $query);

$productos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $productos[$row['id']] = $row;
}

// Construir mensaje para WhatsApp
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
$numeroDestino = "+51970846395"; // Reemplaza con tu número real

// Codificar mensaje y redirigir a WhatsApp
$whatsappUrl = "https://wa.me/ $numeroDestino?text=" . rawurlencode($mensaje);
header("Location: $whatsappUrl");
exit;
?>