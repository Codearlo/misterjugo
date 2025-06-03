<?php
// Configurar headers para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Iniciar sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para realizar un pedido'
    ]);
    exit;
}

// Verificar si hay productos en el carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    echo json_encode([
        'success' => false,
        'message' => 'El carrito está vacío'
    ]);
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

try {
    // Conexión a base de datos
    require_once 'conexion.php';
    
    // Obtener datos del formulario
    $user_id = $_SESSION['user_id'];
    $direccion_id = isset($_POST['direccion_id']) ? intval($_POST['direccion_id']) : 0;
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $telefono = isset($_POST['telefono_hidden']) ? trim($_POST['telefono_hidden']) : '';
    $direccion_completa = isset($_POST['direccion_completa']) ? trim($_POST['direccion_completa']) : '';
    $instrucciones = isset($_POST['instrucciones_adicionales']) ? trim($_POST['instrucciones_adicionales']) : '';
    $instrucciones_direccion = isset($_POST['instrucciones_hidden']) ? trim($_POST['instrucciones_hidden']) : '';
    $total = isset($_POST['total']) ? floatval($_POST['total']) : 0;
    
    // Validaciones
    if ($direccion_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Debes seleccionar una dirección de entrega'
        ]);
        exit;
    }
    
    if (empty($nombre)) {
        echo json_encode([
            'success' => false,
            'message' => 'El nombre es requerido'
        ]);
        exit;
    }
    
    if ($total <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El total del pedido no es válido'
        ]);
        exit;
    }
    
    // Verificar que la dirección pertenezca al usuario y obtener el teléfono
    $stmt = $conn->prepare("SELECT telefono, calle, ciudad, estado, codigo_postal, instrucciones FROM direcciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $direccion_id, $user_id);
    $stmt->execute();
    $direccion_result = $stmt->get_result();
    
    if ($direccion_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'La dirección seleccionada no es válida'
        ]);
        exit;
    }
    
    $direccion_data = $direccion_result->fetch_assoc();
    
    // Usar el teléfono de la dirección
    $telefono_direccion = $direccion_data['telefono'];
    $direccion_completa_real = $direccion_data['calle'] . ', ' . $direccion_data['ciudad'] . ', ' . $direccion_data['estado'] . ', CP: ' . $direccion_data['codigo_postal'];
    $instrucciones_direccion_real = $direccion_data['instrucciones'] ?? '';
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    // Insertar el pedido
    $fecha_pedido = date('Y-m-d H:i:s');
    $estado = 'pendiente';
    
    // Combinar instrucciones
    $instrucciones_completas = '';
    if (!empty($instrucciones_direccion_real)) {
        $instrucciones_completas .= "Instrucciones de dirección: " . $instrucciones_direccion_real;
    }
    if (!empty($instrucciones)) {
        if (!empty($instrucciones_completas)) {
            $instrucciones_completas .= "\n";
        }
        $instrucciones_completas .= "Instrucciones adicionales: " . $instrucciones;
    }
    
    $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, direccion, instrucciones, total, estado, fecha_pedido) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdss", $user_id, $direccion_completa_real, $instrucciones_completas, $total, $estado, $fecha_pedido);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al crear el pedido');
    }
    
    $pedido_id = $conn->insert_id;
    
    // Insertar detalles del pedido
    $total_calculado = 0;
    
    foreach ($_SESSION['carrito'] as $item) {
        if (isset($item['id'], $item['nombre'], $item['precio'], $item['cantidad'])) {
            $producto_id = intval($item['id']);
            $producto_nombre = $item['nombre'];
            $precio = floatval($item['precio']);
            $cantidad = intval($item['cantidad']);
            $subtotal = $precio * $cantidad;
            
            // Verificar que el producto existe y está disponible
            $stmt_producto = $conn->prepare("SELECT id, nombre, precio, disponible FROM productos WHERE id = ? AND disponible = 1");
            $stmt_producto->bind_param("i", $producto_id);
            $stmt_producto->execute();
            $producto_result = $stmt_producto->get_result();
            
            if ($producto_result->num_rows === 0) {
                throw new Exception("El producto '$producto_nombre' ya no está disponible");
            }
            
            $producto_db = $producto_result->fetch_assoc();
            
            // Usar precio de la base de datos por seguridad
            $precio_real = floatval($producto_db['precio']);
            $subtotal_real = $precio_real * $cantidad;
            
            // Insertar detalle del pedido
            $stmt_detalle = $conn->prepare("INSERT INTO detalles_pedido (pedido_id, producto_id, nombre_producto, precio, cantidad, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_detalle->bind_param("iisdid", $pedido_id, $producto_id, $producto_db['nombre'], $precio_real, $cantidad, $subtotal_real);
            
            if (!$stmt_detalle->execute()) {
                throw new Exception('Error al guardar los detalles del pedido');
            }
            
            $total_calculado += $subtotal_real;
        }
    }
    
    // Actualizar el total del pedido con el precio real
    $stmt_update = $conn->prepare("UPDATE pedidos SET total = ? WHERE id = ?");
    $stmt_update->bind_param("di", $total_calculado, $pedido_id);
    $stmt_update->execute();
    
    // Confirmar transacción
    $conn->commit();
    
    // Preparar mensaje para WhatsApp ANTES de limpiar el carrito
    $nombre_usuario = $_SESSION['user_name'] ?? $nombre;
    $mensaje_whatsapp = "🍹 *NUEVO PEDIDO - MisterJugo* 🍹\n\n";
    $mensaje_whatsapp .= "📋 *Pedido #$pedido_id*\n";
    $mensaje_whatsapp .= "👤 *Cliente:* $nombre_usuario\n";
    $mensaje_whatsapp .= "📱 *Teléfono:* $telefono_direccion\n";
    $mensaje_whatsapp .= "📍 *Dirección:* $direccion_completa_real\n";
    
    if (!empty($instrucciones_completas)) {
        $mensaje_whatsapp .= "📝 *Instrucciones:* $instrucciones_completas\n";
    }
    
    $mensaje_whatsapp .= "\n🛒 *PRODUCTOS:*\n";
    
    // Obtener productos del pedido recién creado
    $stmt_productos = $conn->prepare("SELECT nombre_producto, precio, cantidad, subtotal FROM detalles_pedido WHERE pedido_id = ?");
    $stmt_productos->bind_param("i", $pedido_id);
    $stmt_productos->execute();
    $productos_result = $stmt_productos->get_result();
    
    while ($producto = $productos_result->fetch_assoc()) {
        $mensaje_whatsapp .= "• {$producto['cantidad']}x {$producto['nombre_producto']} - S/" . number_format($producto['subtotal'], 2) . "\n";
    }
    
    // Limpiar el carrito DESPUÉS de usar los datos
    unset($_SESSION['carrito']);
    
    $mensaje_whatsapp .= "\n💰 *TOTAL: S/" . number_format($total_calculado, 2) . "*\n";
    $mensaje_whatsapp .= "\n⏰ *Fecha:* " . date('d/m/Y H:i') . "\n";
    $mensaje_whatsapp .= "\n✅ *Estado:* Pendiente de confirmación";
    
    // Número de WhatsApp del restaurante
    $whatsapp_numero = "+51970846395";
    $whatsapp_url = "https://wa.me/" . str_replace('+', '', $whatsapp_numero) . "?text=" . urlencode($mensaje_whatsapp);
    
    // Establecer mensaje de éxito en sesión para mostrar en la página de pedidos
    $_SESSION['exito_pedido'] = "¡Pedido realizado con éxito! Pedido #$pedido_id";
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Pedido realizado con éxito',
        'pedido_id' => $pedido_id,
        'total' => $total_calculado,
        'whatsapp_url' => $whatsapp_url
    ]);
    
} catch (Exception $e) {
    // Rollback en caso de error
    if (isset($conn)) {
        $conn->rollback();
    }
    
    // Log del error (opcional)
    error_log("Error en procesar_pedido.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Cerrar conexión
    if (isset($conn)) {
        $conn->close();
    }
}
?>