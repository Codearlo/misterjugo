<?php
// Iniciar sesión
session_start();

// Verificar si hay productos en el carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    echo "El carrito está vacío.";
    echo "<a href='/productos'>Ver productos</a>";
    exit;
}

// Validar datos del formulario
if (!isset($_POST['nombre'], $_POST['telefono']) || 
    (!isset($_POST['direccion']) && !isset($_POST['direccion_id']))) {
    echo "Error: Faltan datos requeridos.";
    echo "<a href='/checkout'>Volver al checkout</a>";
    exit;
}

// Obtener datos básicos
$nombre = htmlspecialchars(trim($_POST['nombre']));
$telefono = htmlspecialchars(trim($_POST['telefono']));
$instrucciones = isset($_POST['instrucciones']) ? htmlspecialchars(trim($_POST['instrucciones'])) : '';
$total = floatval($_POST['total']);

// Obtener dirección - puede venir como texto directo o como ID de dirección guardada
$direccion = "";
$direccion_id = isset($_POST['direccion_id']) ? intval($_POST['direccion_id']) : 0;

// Conexión a la base de datos
require_once 'conexion.php';

// Si se proporcionó un ID de dirección, obtenerla de la base de datos
if ($direccion_id > 0 && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT * FROM direcciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $direccion_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $dir = $result->fetch_assoc();
        $direccion = $dir['calle'] . ', ' . 
                    $dir['ciudad'] . ', ' . 
                    $dir['estado'] . ', CP: ' . 
                    $dir['codigo_postal'];
        
        // Usar instrucciones de la dirección si no se proporcionaron otras
        if (empty($instrucciones) && !empty($dir['instrucciones'])) {
            $instrucciones = $dir['instrucciones'];
        }
    } else {
        // Si no se encuentra la dirección, intentar usar la dirección completa del formulario
        $direccion = isset($_POST['direccion_completa']) ? htmlspecialchars(trim($_POST['direccion_completa'])) : '';
    }
} else {
    // Usar dirección directamente del formulario
    $direccion = isset($_POST['direccion']) ? htmlspecialchars(trim($_POST['direccion'])) : '';
}

// Verificar que tenemos una dirección
if (empty($direccion)) {
    echo "Error: No se proporcionó una dirección válida.";
    echo "<a href='/checkout'>Volver al checkout</a>";
    exit;
}

// Construir mensaje para WhatsApp
$mensaje = "🛒 *NUEVO PEDIDO MISTERJUGO* 🛒\n\n";
$mensaje .= "*Datos del cliente:*\n";
$mensaje .= "👤 Nombre: $nombre\n";
$mensaje .= "📞 Teléfono: $telefono\n";
$mensaje .= "🏠 Dirección: $direccion\n";

if (!empty($instrucciones)) {
    $mensaje .= "📝 Instrucciones: $instrucciones\n";
}

$mensaje .= "\n*Productos:*\n";

// Obtener detalles de los productos
$carrito = $_SESSION['carrito'];
$subtotal = 0;

// Si el carrito es un array de objetos
if (isset(reset($carrito)['id'])) {
    foreach ($carrito as $item) {
        $cantidad = $item['cantidad'];
        $nombre_producto = $item['nombre'];
        $precio = $item['precio'];
        $importe = $precio * $cantidad;
        $subtotal += $importe;
        
        $mensaje .= "• $nombre_producto x $cantidad = S/ " . number_format($importe, 2) . "\n";
    }
}
// Si el carrito es un array asociativo id => cantidad
else {
    // Obtener IDs de los productos
    $ids = array_keys($carrito);
    $ids_string = implode(',', array_map('intval', $ids));
    
    if (!empty($ids_string)) {
        $query = "SELECT id, nombre, precio FROM productos WHERE id IN ($ids_string)";
        $result = $conn->query($query);
        
        if ($result) {
            $productos = [];
            while ($row = $result->fetch_assoc()) {
                $productos[$row['id']] = $row;
            }
            
            foreach ($carrito as $id => $cantidad) {
                if (isset($productos[$id])) {
                    $producto = $productos[$id];
                    $importe = $producto['precio'] * $cantidad;
                    $subtotal += $importe;
                    
                    $mensaje .= "• {$producto['nombre']} x $cantidad = S/ " . number_format($importe, 2) . "\n";
                }
            }
        }
    }
}

// Verificar el total
if (abs($subtotal - $total) > 0.01) {
    // Hay una discrepancia en el total, usar el calculado
    $total = $subtotal;
}

$mensaje .= "\n*TOTAL: S/ " . number_format($total, 2) . "*";
$mensaje .= "\n\nFecha y hora: " . date("d/m/Y H:i:s");

// Guardar el pedido en la base de datos (si el usuario está logueado)
$pedido_guardado = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Iniciar transacción para asegurar integridad
    $conn->begin_transaction();
    
    try {
        // Insertar el pedido
        $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, fecha_pedido, total, estado, direccion_id) VALUES (?, NOW(), ?, 'pendiente', ?)");
        $stmt->bind_param("idi", $user_id, $total, $direccion_id);
        $stmt->execute();
        
        $pedido_id = $conn->insert_id;
        
        // Insertar detalles del pedido
        if (isset(reset($carrito)['id'])) {
            // Para carrito como array de objetos
            foreach ($carrito as $item) {
                $producto_id = $item['id'];
                $precio = $item['precio'];
                $cantidad = $item['cantidad'];
                
                $stmt = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, precio, cantidad) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iidd", $pedido_id, $producto_id, $precio, $cantidad);
                $stmt->execute();
            }
        } else {
            // Para carrito como array asociativo
            foreach ($carrito as $id => $cantidad) {
                if (isset($productos[$id])) {
                    $producto_id = $id;
                    $precio = $productos[$id]['precio'];
                    
                    $stmt = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, precio, cantidad) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iidd", $pedido_id, $producto_id, $precio, $cantidad);
                    $stmt->execute();
                }
            }
        }
        
        // Confirmar transacción
        $conn->commit();
        $pedido_guardado = true;
        $mensaje .= "\n\nPedido guardado en sistema con ID: #$pedido_id";
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
    }
}

// Número de WhatsApp (formato internacional sin el +)
$numeroDestino = "51970846395"; 

// Limpiar el carrito
$_SESSION['carrito'] = [];

// Codificar mensaje y redirigir a WhatsApp
$whatsappUrl = "https://wa.me/$numeroDestino?text=" . rawurlencode($mensaje);
header("Location: $whatsappUrl");
exit;
?>