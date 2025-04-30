<?php
// Iniciar la sesión
session_start();

// Eliminar todas las variables de sesión
$_SESSION = array();

// Si se está usando una cookie de sesión, eliminarla también
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Si se está usando la cookie "remember_token", eliminarla
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
    
    // También eliminar el token de la base de datos si es necesario
    if (isset($_SESSION['user_id'])) {
        require_once 'conexion.php';
        
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("UPDATE usuarios SET remember_token = NULL WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
}

// Destruir la sesión
session_destroy();

// Responder con éxito (para la solicitud AJAX)
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>