<?php
// Este archivo debe estar en la carpeta backend
header('Content-Type: application/json');

// Iniciar sesión
session_start();

// Incluir conexión
require_once 'conexion.php';

// Verificar que se recibió un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de producto no válido'
    ]);
    exit;
}

$producto_id = intval($_GET['id']);

// Obtener datos del producto
$stmt = $conn->prepare("
    SELECT 
        p.*,
        c.nombre as categoria_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    WHERE p.id = ? AND p.disponible = 1
");

$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Producto no encontrado o no disponible'
    ]);
    exit;
}

$producto = $result->fetch_assoc();

// Si la imagen está vacía, asignar una imagen por defecto
if (empty($producto['imagen'])) {
    $producto['imagen'] = '/images/producto-default.jpg';
}

// Devolver la respuesta
echo json_encode([
    'success' => true,
    'producto' => $producto
]);
?>