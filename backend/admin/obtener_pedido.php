<?php
session_start();

// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Incluir conexión a la base de datos
require_once '../conexion.php';

// Verificar si se ha pasado un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$id = (int)$_GET['id'];

// Obtener información del pedido
$stmt = $conn->prepare("SELECT p.*, u.nombre as cliente_nombre, u.email as cliente_email, 
                               DATE_FORMAT(p.fecha_pedido, '%d/%m/%Y %H:%i') as fecha_pedido_formateada 
                        FROM pedidos p 
                        LEFT JOIN usuarios u ON p.usuario_id = u.id 
                        WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
    exit;
}

$pedido = $result->fetch_assoc();

// Obtener productos del pedido
$stmt = $conn->prepare("SELECT dp.*, pr.nombre 
                        FROM detalles_pedidos dp 
                        LEFT JOIN productos pr ON dp.producto_id = pr.id 
                        WHERE dp.pedido_id = ? 
                        ORDER BY dp.id");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$productos = [];

while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}

$pedido['productos'] = $productos;

// Devolver los datos en formato JSON
echo json_encode([
    'success' => true, 
    'pedido' => $pedido
]);