<?php
// Iniciar sesión para mantener mensajes de error/éxito
session_start();

// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Verificar si el formulario ha sido enviado por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recoger y sanitizar los datos del formulario
    $nombre = isset($_POST['nombre']) ? trim(htmlspecialchars($_POST['nombre'])) : '';
    $email = isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validar datos
    $errores = [];
    
    // Validar nombre (entre 3 y 100 caracteres)
    if (empty($nombre) || strlen($nombre) < 3 || strlen($nombre) > 100) {
        $errores[] = "El nombre debe tener entre 3 y 100 caracteres.";
    }
    
    // Validar email (formato válido y longitud máxima 100)
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $errores[] = "Por favor, proporciona un correo electrónico válido (máximo 100 caracteres).";
    }
    
    // Validar password (mínimo 6 caracteres, máximo 255)
    if (empty($password) || strlen($password) < 6 || strlen($password) > 255) {
        $errores[] = "La contraseña debe tener entre 6 y 255 caracteres.";
    }
    
    // Verificar si el correo ya existe en la base de datos
    if (empty($errores)) {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $errores[] = "Este correo electrónico ya está registrado. Por favor, utiliza otro o inicia sesión.";
        }
        $stmt->close();
    }
    
    // Si no hay errores, proceder con el registro
    if (empty($errores)) {
        // Hashear la contraseña para almacenamiento seguro
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Valor predeterminado para is_admin (0 = usuario normal)
        $is_admin = 0;
        
        // Preparar la consulta SQL para insertar el nuevo usuario
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, is_admin, fecha_registro) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssi", $nombre, $email, $password_hash, $is_admin);
        
        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Registro exitoso
            $_SESSION['registro_exitoso'] = true;
            $_SESSION['email'] = $email; // Guardamos el email para auto-rellenar el campo en login
            
            // Redirigir a la página de login con mensaje de éxito
            header("Location: /login?registro=exitoso");
            exit;
        } else {
            // Error al registrar
            $errores[] = "Error al registrar usuario: " . $conn->error;
        }
        
        $stmt->close();
    }
    
    // Si hay errores, almacenarlos en la sesión y redirigir de vuelta al formulario
    if (!empty($errores)) {
        $_SESSION['errores_registro'] = $errores;
        $_SESSION['datos_form'] = [
            'nombre' => $nombre,
            'email' => $email
        ];
        
        // Redirigir de vuelta al formulario
        header("Location: /registro");
        exit;
    }
} else {
    // Si alguien intenta acceder directamente a este script sin enviar el formulario
    header("Location: /registro");
    exit;
}

// Cerrar la conexión a la base de datos
$conn->close();
?>