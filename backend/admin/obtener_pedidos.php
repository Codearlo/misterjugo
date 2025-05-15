<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Incluir conexión a la base de datos
include '../conexion.php';

// Consulta para obtener los pedidos del usuario y sus detalles
$query = "
    SELECT 
        p.id AS pedido_id,
        p.usuario_id,
        p.fecha_pedido,
        p.estado,
        p.total,
        d.producto_id,
        d.cantidad,
        d.precio,
        d.nombre_producto
    FROM 
        pedidos p
    JOIN 
        detalle_pedidos d ON p.id = d.pedido_id
    WHERE 
        p.usuario_id = ?
    ORDER BY 
        p.fecha_pedido DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Error al ejecutar la consulta: ' . $stmt->error]);
    exit;
}

$result = $stmt->get_result();

$pedidos = [];

while ($row = $result->fetch_assoc()) {
    $pedido_id = $row['pedido_id'];

    // Si es el primer producto del pedido, inicializamos el array
    if (!isset($pedidos[$pedido_id])) {
        $pedidos[$pedido_id] = [
            'pedido_id' => $pedido_id,
            'usuario_id' => $row['usuario_id'],
            'fecha_pedido' => $row['fecha_pedido'],
            'estado' => $row['estado'],
            'total' => $row['total'],
            'productos' => []
        ];
    }

    // Agregar el producto al pedido actual
    $pedidos[$pedido_id]['productos'][] = [
        'producto_id' => $row['producto_id'],
        'nombre_producto' => $row['nombre_producto'],
        'cantidad' => $row['cantidad'],
        'precio' => $row['precio']
    ];
}

// Convertir el array asociativo en un array numérico
$pedidos = array_values($pedidos);

// Devolver los pedidos en formato JSON
echo json_encode($pedidos);
?>