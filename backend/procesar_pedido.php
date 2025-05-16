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
if (!isset($_POST['nombre'], $_POST['direccion_id'])) {
    echo "Error: Faltan datos requeridos. Nombre o dirección no proporcionados.";
    echo "<a href='/checkout'>Volver al checkout</a>";
    exit;
}

// Obtener el ID del usuario
$usuario_id = $_SESSION['user_id'];

// Obtener datos básicos
$nombre = htmlspecialchars(trim($_POST['nombre']));
$telefono = isset($_POST['telefono_hidden']) ? htmlspecialchars(trim($_POST['telefono_hidden'])) : '';
$instrucciones = isset($_POST['instrucciones_adicionales']) ? htmlspecialchars(trim($_POST['instrucciones_adicionales'])) : '';
$instrucciones_dir = isset($_POST['instrucciones_hidden']) ? htmlspecialchars(trim($_POST['instrucciones_hidden'])) : '';

// Combinar instrucciones si ambas están presentes
if (!empty($instrucciones) && !empty($instrucciones_dir)) {
    $instrucciones = $instrucciones_dir . "\n\nInstrucciones adicionales: " . $instrucciones;
} elseif (empty($instrucciones) && !empty($instrucciones_dir)) {
    $instrucciones = $instrucciones_dir;
}

// Obtener dirección
$direccion_completa = isset($_POST['direccion_completa']) ? htmlspecialchars(trim($_POST['direccion_completa'])) : '';
$direccion_id = isset($_POST['direccion_id']) ? intval($_POST['direccion_id']) : 0;

// Obtener el total desde el formulario
$total_formulario = isset($_POST['total']) ? floatval($_POST['total']) : 0;

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
        // Si no tenemos ya la dirección completa del formulario
        if (empty($direccion_completa)) {
            $direccion_completa = $dir['calle'] . ', ' . 
                                $dir['ciudad'] . ', ' . 
                                $dir['estado'] . ', CP: ' . 
                                $dir['codigo_postal'];
        }
        
        // Si no tenemos teléfono, usar el de la dirección
        if (empty($telefono) && !empty($dir['telefono'])) {
            $telefono = $dir['telefono'];
        }
        
        // Usar instrucciones de la dirección si no se proporcionaron otras
        if (empty($instrucciones) && !empty($dir['instrucciones'])) {
            $instrucciones = $dir['instrucciones'];
        }
    } else {
        echo "Error: La dirección seleccionada no es válida.";
        echo "<a href='/checkout'>Volver al checkout</a>";
        exit;
    }
}

// Verificar que tenemos una dirección
if (empty($direccion_completa)) {
    echo "Error: No se proporcionó una dirección válida.";
    echo "<a href='/checkout'>Volver al checkout</a>";
    exit;
}

// Calcular total y preparar lista de productos
$total_calculado = 0;
$items = [];

// Procesar el carrito según su formato
if (isset(reset($_SESSION['carrito'])['id'])) {
    // Si el carrito es un array de objetos con id, nombre, precio, etc.
    foreach ($_SESSION['carrito'] as $item) {
        $cantidad = isset($item['cantidad']) ? $item['cantidad'] : 1;
        $precio = floatval($item['precio']);
        $importe = $precio * $cantidad;
        $total_calculado += $importe;
        
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
    
    if (!empty($ids)) {
        $ids_param = implode(',', array_map('intval', $ids));
        $query = "SELECT id, nombre, precio FROM productos WHERE id IN ($ids_param)";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $id = intval($row['id']);
                if (isset($_SESSION['carrito'][$id])) {
                    $cantidad = intval($_SESSION['carrito'][$id]);
                    $precio = floatval($row['precio']);
                    $importe = $precio * $cantidad;
                    $total_calculado += $importe;
                    
                    $items[] = [
                        'id' => $id,
                        'nombre' => $row['nombre'],
                        'precio' => $precio,
                        'cantidad' => $cantidad,
                        'subtotal' => $importe
                    ];
                }
            }
        } else {
            echo "Error: No se pudieron obtener los productos del carrito.";
            echo "<a href='/productos'>Ver productos</a>";
            exit;
        }
    }
}

// Si llegamos aquí y no hay items, hay un problema con el carrito
if (empty($items)) {
    echo "Error: No se pudieron procesar los productos del carrito.";
    echo "<a href='/productos'>Ver productos</a>";
    exit;
}

// Verificar el total
$total = $total_calculado;
if ($total_formulario > 0 && abs($total_formulario - $total_calculado) < 0.01) {
    $total = $total_formulario; // Usar el total del formulario si es similar
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
    
    if (!$stmt->execute()) {
        throw new Exception("Error al insertar pedido: " . $stmt->error);
    }
    
    $pedido_id = $conn->insert_id;
    
    if ($pedido_id <= 0) {
        throw new Exception("No se pudo obtener el ID del pedido insertado");
    }
    
    // 2. Insertar los detalles del pedido en la tabla 'detalle_pedidos'
    $stmt = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)");
    
    foreach ($items as $item) {
        $producto_id = $item['id'];
        $cantidad = $item['cantidad'];
        $precio = $item['precio'];
        
        $stmt->bind_param("iiid", $pedido_id, $producto_id, $cantidad, $precio);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar detalle: producto_id=$producto_id, " . $stmt->error);
        }
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
    
    // Mensaje de error
    echo "Error al registrar el pedido: " . $e->getMessage();
    echo "<br><a href='/checkout'>Volver al checkout</a>";
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