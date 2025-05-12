<?php
// Prevenir caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Iniciar sesión
session_start();

// Incluir el archivo de conexión a la base de datos
require_once '../conexion.php';

// Verificar si es una solicitud POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validar datos
    if (empty($usuario) || empty($password)) {
        $_SESSION['admin_error'] = "Por favor, completa todos los campos";
        header("Location: index.php");
        exit;
    }
    
    try {
        // Buscar el usuario en la base de datos
        // Primero intentamos en tabla "administradores"
        $tabla_admin_existe = false;
        try {
            $stmt = $conn->prepare("SHOW TABLES LIKE 'administradores'");
            $stmt->execute();
            $tabla_admin_existe = $stmt->get_result()->num_rows > 0;
        } catch (Exception $e) {
            $tabla_admin_existe = false;
        }
        
        $usuario_encontrado = false;
        $id = 0;
        $nombre = '';
        $password_hash = '';
        
        if ($tabla_admin_existe) {
            // Buscar en tabla administradores
            $stmt = $conn->prepare("SELECT id, nombre, usuario, password FROM administradores WHERE usuario = ?");
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $usuario_encontrado = true;
                $id = $row['id'];
                $nombre = $row['nombre'];
                $password_hash = $row['password'];
                $usuario_admin = $row['usuario'];
            }
        }
        
        // Si no se encuentra en la tabla de administradores, buscar en la tabla de usuarios
        if (!$usuario_encontrado) {
            $stmt = $conn->prepare("SELECT id, nombre, email, password FROM usuarios WHERE email = ? AND is_admin = 1");
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $usuario_encontrado = true;
                $id = $row['id'];
                $nombre = $row['nombre'];
                $password_hash = $row['password'];
                $usuario_admin = $row['email'];
            }
        }
        
        // Verificar si se encontró el usuario y si la contraseña es correcta
        if ($usuario_encontrado && password_verify($password, $password_hash)) {
            // Establecer datos de sesión
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_name'] = $nombre;
            $_SESSION['admin_user'] = $usuario_admin ?? $usuario;
            $_SESSION['is_admin'] = true;
            
            // Redirigir al dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            if (!$usuario_encontrado) {
                $_SESSION['admin_error'] = "El usuario no existe o no tiene permisos de administrador";
            } else {
                $_SESSION['admin_error'] = "Contraseña incorrecta";
            }
            header("Location: index.php");
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['admin_error'] = "Error del sistema: " . $e->getMessage();
        header("Location: index.php");
        exit;
    }
} else {
    // Si no es POST, redirigir a la página de login
    header("Location: index.php");
    exit;
}
?>