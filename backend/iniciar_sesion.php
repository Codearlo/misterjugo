<?php
// Este archivo debe incluirse al inicio de cada página para verificar sesiones
session_start();

// Incluir conexión a la base de datos
require_once 'conexion.php';

// Función para verificar si el usuario tiene una sesión activa
function verificarSesion() {
    global $conn;
    
    // Si ya hay una sesión activa, no necesitamos verificar cookies
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return true;
    }
    
    // Verificar si existe la cookie de recordar
    if (isset($_COOKIE['remember_token']) && !empty($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        
        // Buscar el token en la base de datos
        $stmt = $conn->prepare("SELECT id, nombre, email, is_admin FROM usuarios WHERE remember_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            // Token válido, iniciar sesión automáticamente
            $usuario = $resultado->fetch_assoc();
            
            // Regenerar ID de sesión para seguridad
            session_regenerate_id(true);
            
            // Establecer variables de sesión
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_name'] = $usuario['nombre'];
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['is_admin'] = $usuario['is_admin'];
            
            // Generar un nuevo token para mayor seguridad
            $nuevo_token = bin2hex(random_bytes(32));
            $expira = time() + (30 * 24 * 60 * 60); // 30 días
            
            // Actualizar token en la base de datos
            $stmt_update = $conn->prepare("UPDATE usuarios SET remember_token = ? WHERE id = ?");
            $stmt_update->bind_param("si", $nuevo_token, $usuario['id']);
            $stmt_update->execute();
            $stmt_update->close();
            
            // Actualizar cookie
            setcookie('remember_token', $nuevo_token, $expira, '/');
            
            return true;
        }
        
        $stmt->close();
    }
    
    return false;
}

// Función para requerir inicio de sesión
function requerirSesion() {
    if (!verificarSesion()) {
        // Guardar la URL actual para redirigir después del login
        $_SESSION['redir_despues_login'] = $_SERVER['REQUEST_URI'];
        
        // Redirigir al login
        header("Location: /login");
        exit;
    }
}

// Función para verificar si es administrador
function esAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Función para requerir ser administrador
function requerirAdmin() {
    if (!verificarSesion() || !esAdmin()) {
        header("Location: /");
        exit;
    }
}
?>