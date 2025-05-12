<?php
session_start();

// Validar datos del formulario
if (!isset($_POST['nombre'], $_POST['telefono'], $_POST['direccion'], $_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    echo "Error: Datos incompletos o carrito vacío.";
    echo "<a href='/checkout'>Volver al checkout</a>";
    exit;
}

$nombre = htmlspecialchars(trim($_POST['nombre']));
$telefono = htmlspecialchars(trim($_POST['telefono']));
$direccion = htmlspecialchars(trim($_POST['direccion']));
$total = floatval($_POST['total']);

// Conexión a la base de datos
require_once 'conexion.php';

// Obtener detalles de los productos
$carrito = $_SESSION['carrito'];
$productos = [];
$mensaje = "Nuevo Pedido:\n\n";
$mensaje .= "Nombre: $nombre\n";
$mensaje .= "Teléfono: $telefono\n";
$mensaje .= "Dirección: $direccion\n\n";
$mensaje .= "Productos:\n";

// Versión para carrito como array de objetos
if (is_array($carrito) && isset(reset($carrito)['id'])) {
    foreach ($carrito as $item) {
        $id = $item['id'];
        $cantidad = $item['cantidad'];
        $nombre_producto = $item['nombre'];
        $precio = $item['precio'];
        $subtotal = $precio * $cantidad;
        
        $mensaje .= "- {$nombre_producto} x {$cantidad} = S/ " . number_format($subtotal, 2) . "\n";
    }
}
// Versión para carrito como array asociativo de cantidades
else if (is_array($carrito)) {
    // Necesitamos obtener los detalles de los productos de la base de datos
    $ids = array_keys($carrito);
    $ids_string = implode(',', array_map('intval', $ids));
    
    // Verificar que hay IDs
    if (empty($ids_string)) {
        echo "Error: No hay productos en el carrito.";
        echo "<a href='/productos'>Ver productos</a>";
        exit;
    }
    
    $query = "SELECT id, nombre, precio FROM productos WHERE id IN ({$ids_string})";
    $result = $conn->query($query);
    
    if (!$result) {
        echo "Error en la consulta: " . $conn->error;
        exit;
    }
    
    while ($row = $result->fetch_assoc()) {
        $productos[$row['id']] = $row;
    }
    
    foreach ($carrito as $id => $cantidad) {
        if (isset($productos[$id])) {
            $producto = $productos[$id];
            $subtotal = $producto['precio'] * $cantidad;
            $mensaje .= "- {$producto['nombre']} x {$cantidad} = S/ " . number_format($subtotal, 2) . "\n";
        }
    }
}

$mensaje .= "\nTotal: S/ " . number_format($total, 2);

// Número de WhatsApp destino
$numeroDestino = "51970846395"; // Quitar el + para la URL

// Codificar mensaje y redirigir a WhatsApp
$whatsappUrl = "https://wa.me/$numeroDestino?text=" . rawurlencode($mensaje);

// Limpiar el carrito
$_SESSION['carrito'] = [];

// Redirigir a WhatsApp
header("Location: $whatsappUrl");
exit;
?>