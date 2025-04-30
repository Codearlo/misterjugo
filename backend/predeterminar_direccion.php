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
    $stmt = $conn->prepare("SELECT id FROM direcciones WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $direccion_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        // Primero, desmarcar todas las direcciones predeterminadas del usuario
        $stmt = $conn->prepare("UPDATE direcciones SET es_predeterminada = 0 WHERE usuario_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Establecer la dirección seleccionada como predeterminada
        $stmt = $conn->prepare("UPDATE direcciones SET es_predeterminada = 1 WHERE id = ?");
        $stmt->bind_param("i", $direccion_id);
        
        if ($stmt->execute()) {
            $_SESSION['exito_direccion'] = "Dirección establecida como predeterminada correctamente";
        } else {
            $_SESSION['error_direccion'] = "Error al establecer la dirección como predeterminada: " . $conn->error;
        }
    } else {
        $_SESSION['error_direccion'] = "Dirección no encontrada o no tienes permiso para modificarla";
    }
} else {
    $_SESSION['error_direccion'] = "ID de dirección no válido";
}

// Redirigir a la página de direcciones
header("Location: /direcciones");
exit;