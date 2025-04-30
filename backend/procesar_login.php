<?php
// Iniciar sesión para guardar información del usuario
session_start();

// Crear un archivo de log para debug
$log_file = __DIR__ . '/login_debug.log';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Inicio del proceso de login\n", FILE_APPEND);

// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Conexión incluida\n", FILE_APPEND);

// Log de todas las variables POST
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Variables POST: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Verificar si el formulario ha sido enviado por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Método POST detectado\n", FILE_APPEND);
    
    // Recoger y sanitizar los datos del formulario
    $email = isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $recordar = isset($_POST['recordar']) ? true : false;
    
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Email: $email\n", FILE_APPEND);
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Recordar: " . ($recordar ? 'SÍ' : 'NO') . "\n", FILE_APPEND);
    
    // Validar datos básicos
    if (empty($email) || empty($password)) {
        $_SESSION['error_login'] = "Por favor, completa todos los campos.";
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Campos vacíos\n", FILE_APPEND);
        header("Location: /login");
        exit;
    }
    
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Buscando usuario en DB\n", FILE_APPEND);
    
    // Buscar el usuario en la base de datos
    $stmt = $conn->prepare("SELECT id, nombre, email, password, is_admin FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Filas encontradas: " . $resultado->num_rows . "\n", FILE_APPEND);
    
    if ($resultado->num_rows === 1) {
        // Usuario encontrado, verificar la contraseña
        $usuario = $resultado->fetch_assoc();
        
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Verificando contraseña\n", FILE_APPEND);
        
        if (password_verify($password, $usuario['password'])) {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Contraseña correcta\n", FILE_APPEND);
            
            // Contraseña correcta, iniciar sesión
            // Guardar datos del usuario en la sesión
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_name'] = $usuario['nombre'];
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['is_admin'] = $usuario['is_admin'];
            
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Sesión iniciada para usuario ID: " . $usuario['id'] . "\n", FILE_APPEND);
            
            // Si se marcó "recordar", establecer una cookie
            if ($recordar) {
                file_put_contents($log_file, date('Y-m-d H:i:s') . " - Procesando 'recordar cuenta'\n", FILE_APPEND);
                
                $token = bin2hex(random_bytes(32)); // Generar token seguro
                $expira = time() + (30 * 24 * 60 * 60); // 30 días
                
                // Guardar token en la base de datos
                $stmt_token = $conn->prepare("UPDATE usuarios SET remember_token = ? WHERE id = ?");
                $stmt_token->bind_param("si", $token, $usuario['id']);
                $result_token = $stmt_token->execute();
                
                file_put_contents($log_file, date('Y-m-d H:i:s') . " - Token guardado en DB: " . ($result_token ? 'SÍ' : 'NO') . "\n", FILE_APPEND);
                
                $stmt_token->close();
                
                // Establecer cookie
                setcookie('remember_token', $token, $expira, '/', '', false, true);
                file_put_contents($log_file, date('Y-m-d H:i:s') . " - Cookie establecida\n", FILE_APPEND);
            }
            
            // MODIFICACIÓN: Almacenar la URL de redirección anterior 
            // para poder examinarla en el log
            $redirect_url = $usuario['is_admin'] ? "/backend/admin.php" : "/";
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - URL de redirección: " . $redirect_url . "\n", FILE_APPEND);
            
            // PRUEBA 1: Redirección utilizando ruta relativa
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Redirigiendo a: " . $redirect_url . "\n", FILE_APPEND);
            header("Location: " . $redirect_url);
            exit;
            
        } else {
            // Contraseña incorrecta
            $_SESSION['error_login'] = "Correo electrónico o contraseña incorrectos.";
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Contraseña incorrecta\n", FILE_APPEND);
        }
    } else {
        // Usuario no encontrado
        $_SESSION['error_login'] = "Correo electrónico o contraseña incorrectos.";
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Usuario no encontrado\n", FILE_APPEND);
    }
    
    $stmt->close();
    
    // Redirigir de vuelta al formulario de login con el error
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Redirigiendo a login con error\n", FILE_APPEND);
    header("Location: /login");
    exit;
} else {
    // Si alguien intenta acceder directamente a este script sin enviar el formulario
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Acceso directo al script sin POST\n", FILE_APPEND);
    header("Location: /login");
    exit;
}

// Cerrar la conexión a la base de datos
$conn->close();
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Finalizado\n", FILE_APPEND);
?>