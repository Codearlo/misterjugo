<?php
session_start();

// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    $_SESSION['error_login'] = "Debes iniciar sesión como administrador para acceder";
    header("Location: /login");
    exit;
}

// Incluir conexión a la base de datos
require_once '../conexion.php';

// Obtener la acción a realizar
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Actuar según la acción solicitada
switch ($action) {
    case 'cambiar_estado':
        cambiarEstadoPedido();
        break;
    
    default:
        // Acción no reconocida
        $_SESSION['admin_error'] = "Acción no válida";
        header("Location: pedidos.php");
        exit;
}

// Función para cambiar el estado de un pedido
function cambiarEstadoPedido() {
    global $conn;
    
    // Recoger ID del pedido y nuevo estado
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $nuevo_estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
    
    if ($id <= 0) {
        $_SESSION['admin_error'] = "ID de pedido no válido";
        header("Location: pedidos.php");
        exit;
    }
    
    // Validar que el estado sea válido
    $estados_validos = ['pendiente', 'procesando', 'completado', 'cancelado'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        $_SESSION['admin_error'] = "Estado no válido";
        header("Location: pedidos.php");
        exit;
    }
    
    // Verificar que el pedido existe
    $stmt = $conn->prepare("SELECT id, estado, total, usuario_id FROM pedidos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['admin_error'] = "Pedido no encontrado";
        header("Location: pedidos.php");
        exit;
    }
    
    $pedido = $result->fetch_assoc();
    
    // Verificar si ya tiene ese estado
    if ($pedido['estado'] == $nuevo_estado) {
        $_SESSION['admin_error'] = "El pedido ya tiene ese estado";
        header("Location: pedidos.php");
        exit;
    }
    
    // Actualizar estado
    $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $id);
    
    if ($stmt->execute()) {
        // Obtener información del cliente para notificación opcional
        $stmt_cliente = $conn->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
        $stmt_cliente->bind_param("i", $pedido['usuario_id']);
        $stmt_cliente->execute();
        $result_cliente = $stmt_cliente->get_result();
        $cliente = $result_cliente->fetch_assoc();
        
        $_SESSION['admin_exito'] = "Estado del pedido #$id actualizado a '$nuevo_estado' correctamente";
        
        // Aquí se podría enviar una notificación al cliente por email o WhatsApp
        // Si el estado es "completado" o "cancelado", se puede notificar automáticamente
        
    } else {
        $_SESSION['admin_error'] = "Error al actualizar el estado del pedido: " . $conn->error;
    }
    
    header("Location: pedidos.php");
    exit;
}