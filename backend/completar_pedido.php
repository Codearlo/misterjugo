<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Conexión a la base de datos
require_once 'conexion.php';

// Obtener ID del pedido desde la URL
$pedido_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($pedido_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Actualizar el estado del pedido a "completado"
$stmt = $conn->prepare("UPDATE pedidos SET estado = 'completado' WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $pedido_id, $user_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Pedido marcado como completado',
        'pedido_id' => $pedido_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al completar el pedido']);
}

$stmt->close();
$conn->close();
?>