<?php
// Iniciar sesión
session_start();

// Verificar si hay productos en el carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    echo "El carrito está vacío.";
    echo "<a href='/productos'>Ver productos</a>";
    exit;
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_login'] = "Debes iniciar sesión para realizar un pedido.";
    header("Location: /login?redirect=/checkout");
    exit;
}

// Validar datos del formulario
if (!isset($_POST['nombre'], $_POST['telefono']) || 
    (!isset($_POST['direccion']) && !isset($_POST['direccion_id']))) {
    echo "Error: Faltan datos requeridos.";
    echo "<a href='/checkout'>Volver al checkout</a>";
    exit;
}

// Obtener el ID del usuario
$usuario_id = $_SESSION['user_id'];

// Obtener datos básicos
$nombre = htmlspecialchars(trim($_POST['nombre']));
$telefono = htmlspecialchars(trim($_POST['telefono']));
$instrucciones = isset($_POST['instrucciones_adicionales']) ? htmlspecialchars(trim($_POST['instrucciones_adicionales'])) : '';

// Obtener dirección - puede venir como texto directo o como ID de dirección guardada
$direccion = "";
$direccion_id = isset($_POST['direccion_id']) ? intval($_POST['direccion_id']) : 0;

// Conexión a la base de datos
require_once 'conexion.php';

// Si se proporcionó un ID de dirección, obtenerla de la base de datos
if ($direccion_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM direcciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $direccion_id, $usuario_id);
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

// Calcular total y preparar lista de productos
$total = 0;
$items = [];

// Procesar el carrito según su formato
if (isset(reset($_SESSION['carrito'])['id'])) {
    // Si el carrito es un array de objetos con id, nombre, precio, etc.
    foreach ($_SESSION['carrito'] as $item) {
        $cantidad = $item['cantidad'];
        $precio = $item['precio'];
        $importe = $precio * $cantidad;
        $total += $importe;
        
        $items[] = [
            'id' => $item['id'],
            'nombre' => $item['nombre'],
            'precio' => $precio,
            'cantidad' => $cantidad,
            'subtotal' => $importe
        ];
    }
} else {
    // Si el carrito es un array asociativo id => cantidad
    $ids = array_keys($_SESSION['carrito']);
    $ids_string = implode(',', array_map('intval', $ids));
    
    if (!empty($ids_string)) {
        $query = "SELECT id, nombre, precio FROM productos WHERE id IN ($ids_string)";
        $result = $conn->query($query);
        
        if ($result) {
            $productos = [];
            while ($row = $result->fetch_assoc()) {
                $productos[$row['id']] = $row;
            }
            
            foreach ($_SESSION['carrito'] as $id => $cantidad) {
                if (isset($productos[$id])) {
                    $producto = $productos[$id];
                    $precio = $producto['precio'];
                    $importe = $precio * $cantidad;
                    $total += $importe;
                    
                    $items[] = [
                        'id' => $id,
                        'nombre' => $producto['nombre'],
                        'precio' => $precio,
                        'cantidad' => $cantidad,
                        'subtotal' => $importe
                    ];
                }
            }
        }
    }
}

// Si llegamos aquí y no hay items, hay un problema con el carrito
if (empty($items)) {
    echo "Error: No se pudieron procesar los productos del carrito.";
    echo "<a href='/productos'>Ver productos</a>";
    exit;
}

// Verificar que el total calculado coincide con el total del formulario (si se proporciona)
$formulario_total = isset($_POST['total']) ? floatval($_POST['total']) : 0;
if ($formulario_total > 0 && abs($formulario_total - $total) < 0.01) {
    $total = $formulario_total; // Usar el total del formulario si es similar al calculado
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

foreach ($items as $item) {
    $mensaje .= "• {$item['nombre']} x {$item['cantidad']} = S/ " . number_format($item['subtotal'], 2) . "\n";
}

$mensaje .= "\n*TOTAL: S/ " . number_format($total, 2) . "*";
$mensaje .= "\n\nFecha y hora: " . date("d/m/Y H:i:s");

// Guardar el pedido en la base de datos
$pedido_id = 0;

// Iniciar transacción para asegurar integridad
$conn->begin_transaction();

try {
    // 1. Insertar el pedido en la tabla 'pedidos'
    $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, total, estado, fecha_pedido) VALUES (?, ?, 'pendiente', NOW())");
    $stmt->bind_param("id", $usuario_id, $total);
    $stmt->execute();
    
    $pedido_id = $conn->insert_id;
    
    // 2. Insertar los detalles del pedido en la tabla 'detalle_pedidos'
    $stmt = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)");
    
    foreach ($items as $item) {
        $producto_id = $item['id'];
        $cantidad = $item['cantidad'];
        $precio = $item['precio'];
        
        $stmt->bind_param("iiid", $pedido_id, $producto_id, $cantidad, $precio);
        $stmt->execute();
    }
    
    // Confirmar transacción
    $conn->commit();
    
    // Añadir ID del pedido al mensaje de WhatsApp
    $mensaje .= "\n\nPedido guardado en sistema con ID: #$pedido_id";
    
    // Guardar mensaje de éxito en sesión
    $_SESSION['exito_pedido'] = "Tu pedido #$pedido_id ha sido registrado correctamente.";
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    
    // Guardar mensaje de error en sesión
    $_SESSION['error_pedido'] = "Error al registrar el pedido: " . $e->getMessage();
    
    // Redireccionar a checkout
    header("Location: /checkout");
    exit;
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