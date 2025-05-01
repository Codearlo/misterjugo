<?php
// Script para cancelar un pedido
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_pedido'] = "Debes iniciar sesión para realizar esta acción";
    header("Location: /login");
    exit;
}

// Conexión a base de datos
require_once 'conexion.php';

// Obtener el ID del usuario actual y del pedido
$user_id = $_SESSION['user_id'];
$pedido_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verificar que el pedido exista y pertenezca al usuario
$stmt = $conn->prepare("SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $pedido_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_pedido'] = "El pedido no existe o no tienes permiso para cancelarlo";
    header("Location: /pedidos");
    exit;
}

$pedido = $result->fetch_assoc();

// Verificar que el pedido esté en estado 'pendiente'
if ($pedido['estado'] !== 'pendiente') {
    $_SESSION['error_pedido'] = "Solo se pueden cancelar pedidos pendientes";
    header("Location: /pedidos");
    exit;
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // Actualizar estado del pedido
    $stmt = $conn->prepare("UPDATE pedidos SET estado = 'cancelado' WHERE id = ?");
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    
    // Opcional: Restaurar inventario de productos
    $stmt = $conn->prepare("SELECT producto_id, cantidad FROM detalle_pedidos WHERE pedido_id = ?");
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $producto_id = $row['producto_id'];
        $cantidad = $row['cantidad'];
        
        // Aumentar stock del producto
        $stmt_update = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
        $stmt_update->bind_param("ii", $cantidad, $producto_id);
        $stmt_update->execute();
    }
    
    // Confirmar transacción
    $conn->commit();
    
    $_SESSION['exito_pedido'] = "El pedido #$pedido_id ha sido cancelado correctamente";
    
} catch (Exception $e) {
    // Revertir cambios en caso de error
    $conn->rollback();
    $_SESSION['error_pedido'] = "Error al cancelar el pedido: " . $e->getMessage();
}

// Redireccionar a la página de pedidos
header("Location: /pedidos");
exit;
?>