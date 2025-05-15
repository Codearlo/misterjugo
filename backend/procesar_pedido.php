<?php
// Iniciar sesión
session_start();

// Verificar si hay productos en el carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    $_SESSION['error_checkout'] = "El carrito está vacío.";
    header("Location: /productos");
    exit;
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_checkout'] = "Debes iniciar sesión para realizar un pedido.";
    header("Location: /login?redirect=/checkout");
    exit;
}

// Validar datos del formulario
if (!isset($_POST['nombre'], $_POST['telefono']) || 
    (!isset($_POST['direccion']) && !isset($_POST['direccion_id']))) {
    $_SESSION['error_checkout'] = "Faltan datos requeridos en el formulario.";
    header("Location: /checkout");
    exit;
}

// Obtener datos básicos y sanitizarlos
$nombre = htmlspecialchars(trim($_POST['nombre']));
$telefono = htmlspecialchars(trim($_POST['telefono']));
$instrucciones = isset($_POST['instrucciones_adicionales']) ? htmlspecialchars(trim($_POST['instrucciones_adicionales'])) : '';
$instrucciones_dir = isset($_POST['instrucciones']) ? htmlspecialchars(trim($_POST['instrucciones'])) : '';

// Combinar instrucciones si ambas están presentes
if (!empty($instrucciones) && !empty($instrucciones_dir)) {
    $instrucciones = $instrucciones_dir . "\n\nInstrucciones adicionales: " . $instrucciones;
} elseif (empty($instrucciones) && !empty($instrucciones_dir)) {
    $instrucciones = $instrucciones_dir;
}

// Obtener total del pedido desde el formulario
$total = isset($_POST['total']) ? floatval($_POST['total']) : 0;

// Obtener información de dirección
$direccion = "";
$direccion_id = isset($_POST['direccion_id']) ? intval($_POST['direccion_id']) : 0;

// Obtener ID del usuario
$user_id = $_SESSION['user_id'];

// Conexión a la base de datos
require_once 'conexion.php';

// Variable para la dirección completa
$direccion_completa = "";

// Si se proporcionó un ID de dirección, obtenerla de la base de datos
if ($direccion_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM direcciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $direccion_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $dir = $result->fetch_assoc();
        $direccion_completa = $dir['calle'] . ', ' . 
                $dir['ciudad'] . ', ' . 
                $dir['estado'] . ', CP: ' . 
                $dir['codigo_postal'];
        
        // Usar instrucciones de la dirección si no se proporcionaron otras
        if (empty($instrucciones) && !empty($dir['instrucciones'])) {
            $instrucciones = $dir['instrucciones'];
        }
    } else {
        $_SESSION['error_checkout'] = "La dirección seleccionada no es válida.";
        header("Location: /checkout");
        exit;
    }
} else {
    // Usar dirección directamente del formulario
    $direccion_completa = isset($_POST['direccion_completa']) ? htmlspecialchars(trim($_POST['direccion_completa'])) : '';
    
    if (empty($direccion_completa)) {
        $direccion_completa = isset($_POST['direccion']) ? htmlspecialchars(trim($_POST['direccion'])) : '';
    }
}

// Verificar que tenemos una dirección
if (empty($direccion_completa)) {
    $_SESSION['error_checkout'] = "No se proporcionó una dirección válida.";
    header("Location: /checkout");
    exit;
}

// Normalizar el carrito a un formato estándar
$carrito_normalizado = [];
$carrito = $_SESSION['carrito'];
$subtotal_calculado = 0;

// Si el carrito es un array de objetos con id, nombre, precio, etc.
if (isset(reset($carrito)['id'])) {
    foreach ($carrito as $item) {
        $id = intval($item['id']);
        $cantidad = intval($item['cantidad']);
        $precio = floatval($item['precio']);
        $nombre_producto = $item['nombre'];
        $subtotal_item = $precio * $cantidad;
        $subtotal_calculado += $subtotal_item;
        
        $carrito_normalizado[] = [
            'id' => $id,
            'cantidad' => $cantidad,
            'precio' => $precio,
            'nombre' => $nombre_producto,
            'subtotal' => $subtotal_item
        ];
    }
}
// Si el carrito es un array asociativo id => cantidad
else {
    // Obtener IDs de los productos
    $ids = array_keys($carrito);
    
    if (!empty($ids)) {
        $ids_param = implode(',', array_map('intval', $ids));
        $query = "SELECT id, nombre, precio, imagen FROM productos WHERE id IN ($ids_param)";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($producto = $result->fetch_assoc()) {
                $id = intval($producto['id']);
                if (isset($carrito[$id])) {
                    $cantidad = intval($carrito[$id]);
                    $precio = floatval($producto['precio']);
                    $subtotal_item = $precio * $cantidad;
                    $subtotal_calculado += $subtotal_item;
                    
                    $carrito_normalizado[] = [
                        'id' => $id,
                        'cantidad' => $cantidad,
                        'precio' => $precio,
                        'nombre' => $producto['nombre'],
                        'subtotal' => $subtotal_item
                    ];
                }
            }
        }
    }
}

// Si no hay productos después de normalizar, redirigir a productos
if (empty($carrito_normalizado)) {
    $_SESSION['error_checkout'] = "No se pudieron procesar los productos del carrito.";
    header("Location: /productos");
    exit;
}

// Verificar el total
if (abs($subtotal_calculado - $total) > 0.01) {
    // Hay una discrepancia en el total, usar el calculado
    $total = $subtotal_calculado;
}

// Construir mensaje para WhatsApp
$mensaje = "🛒 *NUEVO PEDIDO MISTERJUGO* 🛒\n\n";
$mensaje .= "*Datos del cliente:*\n";
$mensaje .= "👤 Nombre: $nombre\n";
$mensaje .= "📞 Teléfono: $telefono\n";
$mensaje .= "🏠 Dirección: $direccion_completa\n";

if (!empty($instrucciones)) {
    $mensaje .= "📝 Instrucciones: $instrucciones\n";
}

$mensaje .= "\n*Productos:*\n";

// Agregar productos al mensaje
foreach ($carrito_normalizado as $item) {
    $mensaje .= "• {$item['nombre']} x {$item['cantidad']} = S/ " . number_format($item['subtotal'], 2) . "\n";
}

$mensaje .= "\n*TOTAL: S/ " . number_format($total, 2) . "*";
$mensaje .= "\n\nFecha y hora: " . date("d/m/Y H:i:s");

// Guardar el pedido en la base de datos
$pedido_guardado = false;
$pedido_id = 0;

// Iniciar transacción para asegurar integridad
$conn->begin_transaction();

try {
    // Insertar el pedido
    $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, fecha_pedido, total, estado, direccion_id) VALUES (?, NOW(), ?, 'pendiente', ?)");
    $stmt->bind_param("idi", $user_id, $total, $direccion_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al guardar el pedido: " . $stmt->error);
    }
    
    $pedido_id = $conn->insert_id;
    
    // Insertar detalles del pedido
    foreach ($carrito_normalizado as $item) {
        $producto_id = $item['id'];
        $precio = $item['precio'];
        $cantidad = $item['cantidad'];
        
        $stmt = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $pedido_id, $producto_id, $cantidad, $precio);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al guardar el detalle del pedido: " . $stmt->error);
        }
    }
    
    // Confirmar transacción
    $conn->commit();
    $pedido_guardado = true;
    
    // Agregar ID del pedido al mensaje de WhatsApp
    $mensaje .= "\n\nPedido guardado en sistema con ID: #$pedido_id";
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    
    // Guardar mensaje de error y redirigir
    $_SESSION['error_checkout'] = "Error al procesar el pedido: " . $e->getMessage();
    header("Location: /checkout");
    exit;
}

// Limpiar el carrito
$_SESSION['carrito'] = [];

// Guardar mensaje de éxito en sesión
$_SESSION['exito_pedido'] = "Tu pedido #$pedido_id ha sido registrado correctamente.";

// Número de WhatsApp (formato internacional sin el +)
$numeroDestino = "51970846395"; 

// Codificar mensaje y redirigir a WhatsApp
$whatsappUrl = "https://wa.me/$numeroDestino?text=" . rawurlencode($mensaje);
header("Location: $whatsappUrl");
exit;
?>