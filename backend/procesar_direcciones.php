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

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $user_id = $_SESSION['user_id'];
    $address_id = isset($_POST['address_id']) ? trim($_POST['address_id']) : '';
    $calle = isset($_POST['calle']) ? trim($_POST['calle']) : '';
    $ciudad = isset($_POST['ciudad']) ? trim($_POST['ciudad']) : '';
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';
    $codigo_postal = isset($_POST['codigo_postal']) ? trim($_POST['codigo_postal']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $instrucciones = isset($_POST['instrucciones']) ? trim($_POST['instrucciones']) : '';
    $es_predeterminada = isset($_POST['es_predeterminada']) ? 1 : 0;

    // Validación básica
    $errores = [];

    if (empty($calle)) {
        $errores[] = "La dirección es obligatoria";
    }

    if (empty($ciudad)) {
        $errores[] = "La ciudad es obligatoria";
    }

    if (empty($estado)) {
        $errores[] = "El estado es obligatorio";
    }

    if (empty($codigo_postal) || !preg_match('/^\d{5}$/', $codigo_postal)) {
        $errores[] = "El código postal debe contener 5 dígitos";
    }

    if (empty($telefono)) {
        $errores[] = "El teléfono es obligatorio";
    }

    // Si hay errores, redirigir de vuelta con mensajes
    if (!empty($errores)) {
        $_SESSION['errores_direccion'] = $errores;
        $_SESSION['datos_direccion'] = [
            'calle' => $calle,
            'ciudad' => $ciudad,
            'estado' => $estado,
            'codigo_postal' => $codigo_postal,
            'telefono' => $telefono,
            'instrucciones' => $instrucciones,
            'es_predeterminada' => $es_predeterminada
        ];
        header("Location: /direcciones");
        exit;
    }

    // Si es predeterminada, desmarcar cualquier otra dirección predeterminada
    if ($es_predeterminada) {
        $stmt = $conn->prepare("UPDATE direcciones SET es_predeterminada = 0 WHERE usuario_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }

    // Si tiene un ID, actualizar la dirección existente
    if (!empty($address_id)) {
        // Verificar que la dirección pertenezca al usuario
        $stmt = $conn->prepare("SELECT id FROM direcciones WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $address_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['error_direccion'] = "No tienes permiso para modificar esta dirección";
            header("Location: /direcciones");
            exit;
        }
        
        // Actualizar la dirección
        $stmt = $conn->prepare("UPDATE direcciones SET calle = ?, ciudad = ?, estado = ?, codigo_postal = ?, telefono = ?, instrucciones = ?, es_predeterminada = ? WHERE id = ?");
        $stmt->bind_param("ssssssii", $calle, $ciudad, $estado, $codigo_postal, $telefono, $instrucciones, $es_predeterminada, $address_id);
        
        if ($stmt->execute()) {
            $_SESSION['exito_direccion'] = "Dirección actualizada correctamente";
        } else {
            $_SESSION['error_direccion'] = "Error al actualizar la dirección: " . $conn->error;
        }
    } else {
        // Es una nueva dirección, insertar
        $stmt = $conn->prepare("INSERT INTO direcciones (usuario_id, calle, ciudad, estado, codigo_postal, telefono, instrucciones, es_predeterminada) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssi", $user_id, $calle, $ciudad, $estado, $codigo_postal, $telefono, $instrucciones, $es_predeterminada);
        
        if ($stmt->execute()) {
            $_SESSION['exito_direccion'] = "Dirección agregada correctamente";
        } else {
            $_SESSION['error_direccion'] = "Error al agregar la dirección: " . $conn->error;
        }
    }
    
    // Redirigir a la página de direcciones
    header("Location: /direcciones");
    exit;
} else {
    // Si no es POST, redirigir a la página de direcciones
    header("Location: /direcciones");
    exit;
}