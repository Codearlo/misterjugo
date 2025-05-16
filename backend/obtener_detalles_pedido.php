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

// Obtener información del pedido
$stmt_pedido = $conn->prepare("SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?");
$stmt_pedido->bind_param("ii", $pedido_id, $user_id);
$stmt_pedido->execute();
$result_pedido = $stmt_pedido->get_result();

if ($result_pedido->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
    exit;
}

$pedido = $result_pedido->fetch_assoc();

// Obtener los productos del pedido
$stmt_detalle = $conn->prepare("SELECT pd.*, p.nombre, p.imagen 
                                FROM detalles_pedidos pd
                                JOIN productos p ON pd.producto_id = p.id
                                WHERE pd.pedido_id = ?");
$stmt_detalle->bind_param("i", $pedido_id);
$stmt_detalle->execute();
$result_detalle = $stmt_detalle->get_result();

$items = [];
while ($row = $result_detalle->fetch_assoc()) {
    $items[] = [
        'nombre' => $row['nombre'],
        'cantidad' => $row['cantidad'],
        'precio' => $row['precio'],
        'subtotal' => $row['precio'] * $row['cantidad'],
        'imagen' => $row['imagen']
    ];
}

$response = [
    'success' => true,
    'pedido' => [
        'id' => $pedido['id'],
        'fecha_pedido' => $pedido['fecha_pedido'],
        'fecha_formateada' => date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])),
        'total' => $pedido['total'],
        'estado' => $pedido['estado'],
        'estado_texto' => ucfirst($pedido['estado'])
    ],
    'items' => $items,
    'totales' => [
        'total' => array_sum(array_column($items, 'subtotal'))
    ]
];

echo json_encode($response);

$stmt_pedido->close();
$stmt_detalle->close();
$conn->close();
?>