<?php
// Script para obtener los detalles de un pedido mediante AJAX
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Conexión a base de datos
require_once 'conexion.php';

// Obtener el ID del usuario actual y del pedido
$user_id = $_SESSION['user_id'];
$pedido_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verificar que el pedido exista y pertenezca al usuario
$stmt = $conn->prepare("SELECT p.*, d.calle, d.ciudad, d.estado, d.codigo_postal, d.instrucciones 
                        FROM pedidos p 
                        LEFT JOIN direcciones d ON p.direccion_id = d.id 
                        WHERE p.id = ? AND p.usuario_id = ?");
$stmt->bind_param("ii", $pedido_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
    exit;
}

$pedido = $result->fetch_assoc();

// Formatear fecha
$fecha_formateada = date('d/m/Y H:i', strtotime($pedido['fecha_pedido']));

// Formatear estado
$estado_texto = '';
switch ($pedido['estado']) {
    case 'pendiente':
        $estado_texto = 'Pendiente';
        break;
    case 'procesando':
        $estado_texto = 'En procesamiento';
        break;
    case 'completado':
        $estado_texto = 'Completado';
        break;
    case 'cancelado':
        $estado_texto = 'Cancelado';
        break;
    default:
        $estado_texto = ucfirst($pedido['estado']);
}

// Formatear dirección
$direccion = $pedido['calle'] . ', ' . $pedido['ciudad'] . ', ' . $pedido['estado'] . ', CP ' . $pedido['codigo_postal'];
if (!empty($pedido['instrucciones'])) {
    $direccion .= ' (Instrucciones: ' . $pedido['instrucciones'] . ')';
}

// Obtener items del pedido
$items = [];
$stmt = $conn->prepare("SELECT dp.*, p.nombre, p.imagen 
                        FROM detalle_pedidos dp 
                        JOIN productos p ON dp.producto_id = p.id 
                        WHERE dp.pedido_id = ?");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();

$subtotal = 0;
while ($row = $result->fetch_assoc()) {
    $row['subtotal'] = $row['cantidad'] * $row['precio'];
    $subtotal += $row['subtotal'];
    $items[] = $row;
}

// Calcular totales
$envio = $pedido['costo_envio'] ?? 0;
$descuento = $pedido['descuento'] ?? 0;
$total = $pedido['total'];

// Preparar respuesta
$respuesta = [
    'success' => true,
    'pedido' => [
        'id' => $pedido['id'],
        'fecha_pedido' => $pedido['fecha_pedido'],
        'fecha_formateada' => $fecha_formateada,
        'estado' => $pedido['estado'],
        'estado_texto' => $estado_texto,
        'direccion' => $direccion
    ],
    'items' => $items,
    'totales' => [
        'subtotal' => $subtotal,
        'envio' => $envio,
        'descuento' => $descuento,
        'total' => $total
    ]
];

// Devolver respuesta como JSON
header('Content-Type: application/json');
echo json_encode($respuesta);
?>