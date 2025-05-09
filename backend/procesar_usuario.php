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
    case 'toggle_status':
        cambiarEstadoUsuario();
        break;
    
    case 'reset_password':
        restablecerContraseña();
        break;
    
    default:
        // Acción no reconocida
        $_SESSION['admin_error'] = "Acción no válida";
        header("Location: usuarios.php");
        exit;
}

// Función para cambiar el estado de un usuario (activo/inactivo)
function cambiarEstadoUsuario() {
    global $conn;
    
    // Recoger ID del usuario
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        $_SESSION['admin_error'] = "ID de usuario no válido";
        header("Location: usuarios.php");
        exit;
    }
    
    // No permitir cambiar el estado de un administrador
    $stmt = $conn->prepare("SELECT is_admin FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['admin_error'] = "Usuario no encontrado";
        header("Location: usuarios.php");
        exit;
    }
    
    $usuario = $result->fetch_assoc();
    
    if ($usuario['is_admin'] == 1) {
        $_SESSION['admin_error'] = "No se puede cambiar el estado de un administrador";
        header("Location: usuarios.php");
        exit;
    }
    
    // No permitir que un administrador cambie su propio estado
    if ($id == $_SESSION['admin_id']) {
        $_SESSION['admin_error'] = "No puedes cambiar tu propio estado";
        header("Location: usuarios.php");
        exit;
    }
    
    // Obtener estado actual y cambiarlo
    $stmt = $conn->prepare("SELECT activo FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $nuevo_estado = $usuario['activo'] ? 0 : 1;
    
    // Actualizar estado
    $stmt = $conn->prepare("UPDATE usuarios SET activo = ? WHERE id = ?");
    $stmt->bind_param("ii", $nuevo_estado, $id);
    
    if ($stmt->execute()) {
        $_SESSION['admin_exito'] = "Estado del usuario actualizado correctamente";
    } else {
        $_SESSION['admin_error'] = "Error al actualizar el estado del usuario: " . $conn->error;
    }
    
    header("Location: usuarios.php");
    exit;
}

// Función para restablecer la contraseña de un usuario
function restablecerContraseña() {
    global $conn;
    
    // Recoger ID del usuario
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        $_SESSION['admin_error'] = "ID de usuario no válido";
        header("Location: usuarios.php");
        exit;
    }
    
    // No permitir que un administrador restablezca su propia contraseña desde aquí
    if ($id == $_SESSION['admin_id']) {
        $_SESSION['admin_error'] = "No puedes restablecer tu propia contraseña desde aquí. Usa la opción de cambiar contraseña en tu perfil.";
        header("Location: usuarios.php");
        exit;
    }
    
    // Verificar que el usuario existe
    $stmt = $conn->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['admin_error'] = "Usuario no encontrado";
        header("Location: usuarios.php");
        exit;
    }
    
    $usuario = $result->fetch_assoc();
    
    // Generar nueva contraseña aleatoria
    $nueva_contraseña = generarContraseñaAleatoria();
    
    // Hashear la nueva contraseña
    $hash = password_hash($nueva_contraseña, PASSWORD_DEFAULT);
    
    // Actualizar la contraseña en la base de datos
    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hash, $id);
    
    if ($stmt->execute()) {
        // En un entorno real, se enviaría un email al usuario con la nueva contraseña
        // Sin embargo, para este ejemplo, mostraremos la contraseña en el mensaje de éxito
        $_SESSION['admin_exito'] = "Contraseña restablecida correctamente para {$usuario['nombre']} ({$usuario['email']}). Nueva contraseña: $nueva_contraseña";
    } else {
        $_SESSION['admin_error'] = "Error al restablecer la contraseña: " . $conn->error;
    }
    
    header("Location: usuarios.php");
    exit;
}

// Función para generar una contraseña aleatoria
function generarContraseñaAleatoria($longitud = 10) {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%&*?';
    $contraseña = '';
    $max = strlen($caracteres) - 1;
    
    for ($i = 0; $i < $longitud; $i++) {
        $contraseña .= $caracteres[random_int(0, $max)];
    }
    
    return $contraseña;
}