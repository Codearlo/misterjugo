<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit;
}

// Incluir conexión a la base de datos
require_once 'conexion.php';

// Verificar si se recibió un ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $direccion_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    // Verificar que la dirección pertenezca al usuario
    $stmt = $conn->prepare("SELECT id, es_predeterminada FROM direcciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $direccion_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $direccion = $result->fetch_assoc();
        
        // Verificar si es la única dirección del usuario
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM direcciones WHERE usuario_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $count_result = $stmt->get_result();
        $count = $count_result->fetch_assoc()['total'];
        
        if ($count <= 1) {
            $_SESSION['error_direccion'] = "No puedes eliminar tu única dirección. Debes tener al menos una dirección registrada.";
            header("Location: /direcciones");
            exit;
        }
        
        // Si es la dirección predeterminada, establecer otra como predeterminada
        if ($direccion['es_predeterminada']) {
            $stmt = $conn->prepare("UPDATE direcciones SET es_predeterminada = 1 WHERE usuario_id = ? AND id != ? LIMIT 1");
            $stmt->bind_param("ii", $user_id, $direccion_id);
            $stmt->execute();
        }
        
        // Eliminar la dirección
        $stmt = $conn->prepare("DELETE FROM direcciones WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $direccion_id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['exito_direccion'] = "Dirección eliminada correctamente";
        } else {
            $_SESSION['error_direccion'] = "Error al eliminar la dirección: " . $conn->error;
        }
    } else {
        $_SESSION['error_direccion'] = "Dirección no encontrada o no tienes permiso para eliminarla";
    }
} else {
    $_SESSION['error_direccion'] = "ID de dirección no válido";
}

// Redirigir a la página de direcciones
header("Location: /direcciones");
exit;