<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

include '../conexion.php';

$pedido_id = intval($_GET['id']);
$usuario_id = $_SESSION['user_id'];

// Verificar que el pedido pertenece al usuario
$stmt = $conn->prepare("SELECT id FROM pedidos WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $pedido_id, $usuario_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado o no autorizado']);
    exit;
}

// Actualizar estado a "completado"
$stmt = $conn->prepare("UPDATE pedidos SET estado = 'completado' WHERE id = ?");
$stmt->bind_param("i", $pedido_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'pedido_id' => $pedido_id, 'estado' => 'completado']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al completar el pedido']);
}
?>