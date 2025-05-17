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

// Obtener información del producto
$stmt = $conn->prepare("SELECT p.*, c.nombre as categoria_nombre 
                       FROM productos p 
                       LEFT JOIN categorias c ON p.categoria_id = c.id 
                       WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    exit;
}

$producto = $result->fetch_assoc();

// Devolver los datos en formato JSON
echo json_encode([
    'success' => true, 
    'producto' => $producto
]);