<?php
// Iniciar sesión
session_start();

// Incluir el archivo de conexión a la base de datos
require_once '../conexion.php';

// Verificar si es una solicitud POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtener datos del formulario
    $usuario = isset($_POST['usuario']) ? $_POST['usuario'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validar datos
    if (empty($usuario) || empty($password)) {
        $_SESSION['admin_error'] = "Por favor, completa todos los campos";
        header("Location: index.php");
        exit;
    }
    
    // Buscar el usuario en la base de datos (tabla de administradores)
    // IMPORTANTE: Primero verifica si existe una tabla 'administradores'. Si no, puedes usar la tabla 'usuarios' con is_admin = 1
    
    // Si existe tabla administradores
    if ($conn->query("SHOW TABLES LIKE 'administradores'")->num_rows > 0) {
        $stmt = $conn->prepare("SELECT id, nombre, usuario, password FROM administradores WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
    } else {
        // Usar tabla usuarios con campo is_admin
        $stmt = $conn->prepare("SELECT id, nombre, email, password FROM usuarios WHERE email = ? AND is_admin = 1");
        $stmt->bind_param("s", $usuario);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        // Verificar contraseña (asumiendo hash bcrypt)
        if (password_verify($password, $admin['password'])) {
            // Establecer datos de sesión
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['nombre'];
            $_SESSION['admin_user'] = $admin['usuario'] ?? $admin['email'];
            $_SESSION['is_admin'] = true;
            
            // Redirigir al dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $_SESSION['admin_error'] = "Contraseña incorrecta";
        }
    } else {
        $_SESSION['admin_error'] = "Usuario no encontrado o no tienes permisos de administrador";
    }
    
    header("Location: index.php");
    exit;
}

// Si no es POST, redirigir a la página de login
header("Location: index.php");
exit;