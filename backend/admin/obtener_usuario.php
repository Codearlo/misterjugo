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

// Obtener información del usuario
$stmt = $conn->prepare("SELECT *, DATE_FORMAT(fecha_registro, '%d/%m/%Y %H:%i') as fecha_registro_formateada FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit;
}

$usuario = $result->fetch_assoc();

// Obtener estadísticas del usuario
$stmt = $conn->prepare("SELECT COUNT(*) as total_pedidos, COALESCE(SUM(total), 0) as total_gastado, MAX(fecha_pedido) as ultimo_pedido FROM pedidos WHERE usuario_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();

$usuario['total_pedidos'] = $stats['total_pedidos'];
$usuario['total_gastado'] = $stats['total_gastado'];
$usuario['ultimo_pedido'] = $stats['ultimo_pedido'] ? date('d/m/Y H:i', strtotime($stats['ultimo_pedido'])) : null;

// Obtener direcciones del usuario
$stmt = $conn->prepare("SELECT * FROM direcciones WHERE usuario_id = ? ORDER BY es_predeterminada DESC, id DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$direcciones = [];

while ($row = $result->fetch_assoc()) {
    $direcciones[] = $row;
}

$usuario['direcciones'] = $direcciones;

// Devolver los datos en formato JSON
echo json_encode([
    'success' => true, 
    'usuario' => $usuario
]);