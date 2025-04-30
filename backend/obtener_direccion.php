<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Incluir conexión a la base de datos
require_once 'conexion.php';

// Verificar si se recibió un ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $direccion_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    // Verificar que la dirección pertenezca al usuario
    $stmt = $conn->prepare("SELECT * FROM direcciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $direccion_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $direccion = $result->fetch_assoc();
        echo json_encode(['success' => true, 'direccion' => $direccion]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Dirección no encontrada o sin permiso']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'ID de dirección no válido']);
}