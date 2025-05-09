<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    $_SESSION['admin_error'] = "Debes iniciar sesión como administrador para acceder";
    header("Location: index.php");
    exit;
}

// Incluir conexión a la base de datos
require_once '../conexion.php';

// Obtener la acción a realizar
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Actuar según la acción solicitada
switch ($action) {
    case 'update_status':
        actualizarEstadoPedido();
        break;
    
    default:
        // Acción no reconocida
        $_SESSION['admin_error'] = "Acción no válida";
        header("Location: pedidos.php");
        exit;
}

// Función para actualizar el estado de un pedido
function actualizarEstadoPedido() {
    global $conn;
    
    // Recoger ID del pedido y nuevo estado
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    
    // Validar datos
    if ($id <= 0) {
        $_SESSION['admin_error'] = "ID de pedido no válido";
        header("Location: pedidos.php");
        exit;
    }
    
    // Verificar que el estado sea válido
    $estados_validos = ['pendiente', 'procesando', 'completado', 'cancelado'];
    if (!in_array($status, $estados_validos)) {
        $_SESSION['admin_error'] = "Estado no válido";
        header("Location: ver_pedido.php?id=" . $id);
        exit;
    }
    
    // Verificar que el pedido existe
    $stmt = $conn->prepare("SELECT estado FROM pedidos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['admin_error'] = "El pedido no existe";
        header("Location: pedidos.php");
        exit;
    }
    
    $pedido = $result->fetch_assoc();
    $estado_actual = $pedido['estado'];
    
    // Validar la transición de estado
    $transicion_valida = false;
    switch ($estado_actual) {
        case 'pendiente':
            $transicion_valida = in_array($status, ['procesando', 'cancelado']);
            break;
        case 'procesando':
            $transicion_valida = in_array($status, ['completado', 'cancelado']);
            break;
        case 'completado':
        case 'cancelado':
            $transicion_valida = false; // Estados finales, no se pueden cambiar
            break;
    }
    
    if (!$transicion_valida) {
        $_SESSION['admin_error'] = "Transición de estado no válida";
        header("Location: ver_pedido.php?id=" . $id);
        exit;
    }
    
    // Si se está cancelando, ajustar inventario si es necesario
    if ($status === 'cancelado') {
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Actualizar estado del pedido
            $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
            
            // Restaurar inventario
            $stmt = $conn->prepare("SELECT producto_id, cantidad FROM detalle_pedidos WHERE pedido_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($item = $result->fetch_assoc()) {
                $stmt = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
                $stmt->bind_param("ii", $item['cantidad'], $item['producto_id']);
                $stmt->execute();
            }
            
            // Confirmar transacción
            $conn->commit();
            
            $_SESSION['admin_exito'] = "Pedido cancelado y stock restaurado correctamente";
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            $conn->rollback();
            
            $_SESSION['admin_error'] = "Error al cancelar el pedido: " . $e->getMessage();
            header("Location: ver_pedido.php?id=" . $id);
            exit;
        }
    } else {
        // Actualizar estado del pedido
        $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            $_SESSION['admin_exito'] = "Estado del pedido actualizado a '" . ucfirst($status) . "' correctamente";
        } else {
            $_SESSION['admin_error'] = "Error al actualizar el estado del pedido: " . $conn->error;
        }
    }
    
    // Redireccionar de vuelta a la página de detalles del pedido
    header("Location: ver_pedido.php?id=" . $id);
    exit;
}