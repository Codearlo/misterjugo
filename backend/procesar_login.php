<?php
// Iniciar sesión para guardar información del usuario
session_start();

// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Verificar si el formulario ha sido enviado por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recoger y sanitizar los datos del formulario
    $email = isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    // Ignoramos completamente la variable recordar para evitar que cause problemas
    
    // Validar datos básicos
    if (empty($email) || empty($password)) {
        $_SESSION['error_login'] = "Por favor, completa todos los campos.";
        header("Location: /login");
        exit;
    }
    
    // Buscar el usuario en la base de datos
    $stmt = $conn->prepare("SELECT id, nombre, email, password, is_admin FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 1) {
        // Usuario encontrado, verificar la contraseña
        $usuario = $resultado->fetch_assoc();
        
        if (password_verify($password, $usuario['password'])) {
            // Contraseña correcta, iniciar sesión
            
            // Guardar datos del usuario en la sesión
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_name'] = $usuario['nombre'];
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['is_admin'] = $usuario['is_admin'];
            
            // Establecer cookie para todos los usuarios, sin importar si marcaron "recordar"
            // Esto resuelve el problema eliminando la condicionalidad
            $token = bin2hex(random_bytes(32)); // Generar token seguro
            $expira = time() + (30 * 24 * 60 * 60); // 30 días
            
            // Guardar token en la base de datos
            $stmt_token = $conn->prepare("UPDATE usuarios SET remember_token = ? WHERE id = ?");
            $stmt_token->bind_param("si", $token, $usuario['id']);
            $stmt_token->execute();
            $stmt_token->close();
            
            // Establecer cookie con parámetros simplificados
            setcookie('remember_token', $token, $expira, '/');
            
            // Redirigir al usuario basado en su rol
            // Usar siempre la misma URL para cada tipo de usuario
            if ($usuario['is_admin']) {
                header("Location: /backend/admin.php");
                exit;
            } else {
                // Para usuarios normales, usar redirección absoluta como último recurso
                header("Location: https://misterjugo.codearlo.com/");
                exit;
            }
        } else {
            // Contraseña incorrecta
            $_SESSION['error_login'] = "Correo electrónico o contraseña incorrectos.";
        }
    } else {
        // Usuario no encontrado
        $_SESSION['error_login'] = "Correo electrónico o contraseña incorrectos.";
    }
    
    $stmt->close();
    
    // Redirigir de vuelta al formulario de login con el error
    header("Location: /login");
    exit;
} else {
    // Si alguien intenta acceder directamente a este script sin enviar el formulario
    header("Location: /login");
    exit;
}

// Cerrar la conexión a la base de datos
$conn->close();
?>