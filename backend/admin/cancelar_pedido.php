<?php
session_start();
header('Content-Type: application/json');

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Conexión a la base de datos
require_once 'conexion.php';

// Set the timezone to Lima, Peru
date_default_timezone_set('America/Lima');

// Obtener ID del pedido desde la URL
$pedido_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($pedido_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Verificar que el pedido exista y pertenezca al usuario actual
$stmt = $conn->prepare("SELECT p.id, p.estado, p.total, u.nombre, u.email 
                       FROM pedidos p 
                       JOIN usuarios u ON p.usuario_id = u.id 
                       WHERE p.id = ? AND p.usuario_id = ?");
$stmt->bind_param("ii", $pedido_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado o sin permiso']);
    exit;
}

$pedido = $result->fetch_assoc();

// Verificar que el pedido esté en estado pendiente (solo se pueden cancelar pedidos pendientes)
if ($pedido['estado'] !== 'pendiente') {
    echo json_encode(['success' => false, 'message' => 'Solo se pueden cancelar pedidos en estado pendiente']);
    exit;
}

// Actualizar el estado del pedido a cancelado
$stmt = $conn->prepare("UPDATE pedidos SET estado = 'cancelado' WHERE id = ?");
$stmt->bind_param("i", $pedido_id);

if ($stmt->execute()) {
    // Construir mensaje para WhatsApp
    $mensaje = "🚫 *PEDIDO CANCELADO* 🚫\n\n";
    $mensaje .= "*Datos del cliente:*\n";
    $mensaje .= "👤 Nombre: " . $pedido['nombre'] . "\n";
    $mensaje .= "📧 Email: " . $pedido['email'] . "\n";
    $mensaje .= "🛒 Pedido #: " . $pedido_id . "\n";
    $mensaje .= "💰 Total: S/ " . number_format($pedido['total'], 2) . "\n\n";
    $mensaje .= "El cliente ha cancelado el pedido. Por favor, no procesar.";
    $mensaje .= "\n\nFecha y hora de cancelación: " . date("d/m/Y H:i:s");

    // WhatsApp URL
    $numeroDestino = "51970846395"; // Mismo número que en procesar_pedido.php
    $whatsappUrl = "https://wa.me/$numeroDestino?text=" . rawurlencode($mensaje);

    echo json_encode([
        'success' => true, 
        'message' => 'Pedido cancelado correctamente',
        'pedido_id' => $pedido_id,
        'whatsapp_url' => $whatsappUrl
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al cancelar el pedido: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>