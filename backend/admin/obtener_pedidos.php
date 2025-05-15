<?php
session_start();

// Incluir conexión
include '../conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Consultar los pedidos del usuario
$query = "
    SELECT p.id AS pedido_id, p.total, p.estado, p.fecha_pedido,
           d.producto_id, d.cantidad, d.precio
    FROM pedidos p
    JOIN detalle_pedidos d ON p.id = d.pedido_id
    WHERE p.usuario_id = ?
    ORDER BY p.fecha_pedido DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$pedidos = [];

while ($row = $result->fetch_assoc()) {
    $pedido_id = $row['pedido_id'];

    // Agrupar productos por pedido
    if (!isset($pedidos[$pedido_id])) {
        $pedidos[$pedido_id] = [
            'pedido_id' => $pedido_id,
            'total' => $row['total'],
            'estado' => $row['estado'],
            'fecha_pedido' => $row['fecha_pedido'],
            'productos' => []
        ];
    }

    $pedidos[$pedido_id]['productos'][] = [
        'producto_id' => $row['producto_id'],
        'cantidad' => $row['cantidad'],
        'precio' => $row['precio']
    ];
}

// Convertir array asociativo a array numérico
$pedidos = array_values($pedidos);

echo json_encode($pedidos);
?>