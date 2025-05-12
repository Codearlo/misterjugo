<?php
// Prevenir caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Mostrar errores (quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Registrar actividad para depuración
error_log("Validar Admin - Inicio del script");

// Incluir el archivo de conexión a la base de datos con ruta absoluta
try {
    require_once __DIR__ . '/../conexion.php';
    error_log("Conexión incluida correctamente");
} catch (Exception $e) {
    error_log("Error al incluir conexión: " . $e->getMessage());
    die("Error al incluir el archivo de conexión. Contacte al administrador.");
}

// Verificar conexión a la base de datos
if (!isset($conn) || $conn->connect_error) {
    error_log("Error de conexión a la base de datos: " . (isset($conn) ? $conn->connect_error : "No definida"));
    die("Error de conexión a la base de datos. Contacte al administrador.");
}

// Verificar si es una solicitud POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("Método POST detectado");
    error_log("POST data: " . print_r($_POST, true));
    
    // Obtener datos del formulario
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validar datos
    if (empty($usuario) || empty($password)) {
        error_log("Datos incompletos: usuario o contraseña vacíos");
        $_SESSION['admin_error'] = "Por favor, completa todos los campos";
        header("Location: index.php");
        exit;
    }
    
    try {
        // Buscar el usuario en la base de datos
        error_log("Buscando usuario: " . $usuario);
        
        // Comprobar si existe la tabla administradores
        $tabla_admin_existe = false;
        try {
            $tabla_admin_existe = $conn->query("SHOW TABLES LIKE 'administradores'")->num_rows > 0;
            error_log("Tabla administradores existe: " . ($tabla_admin_existe ? "Sí" : "No"));
        } catch (Exception $e) {
            error_log("Error al verificar tabla administradores: " . $e->getMessage());
            $tabla_admin_existe = false;
        }
        
        if ($tabla_admin_existe) {
            error_log("Usando tabla administradores");
            $stmt = $conn->prepare("SELECT id, nombre, usuario, password FROM administradores WHERE usuario = ?");
            $stmt->bind_param("s", $usuario);
        } else {
            error_log("Usando tabla usuarios con is_admin=1");
            $stmt = $conn->prepare("SELECT id, nombre, email, password FROM usuarios WHERE email = ? AND is_admin = 1");
            $stmt->bind_param("s", $usuario);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        error_log("Número de resultados: " . $result->num_rows);
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            error_log("Usuario encontrado: " . $admin['nombre']);
            
            // Verificar contraseña
            if (password_verify($password, $admin['password'])) {
                error_log("Contraseña correcta para usuario: " . $usuario);
                // Establecer datos de sesión
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['nombre'];
                $_SESSION['admin_user'] = $admin['usuario'] ?? $admin['email'];
                $_SESSION['is_admin'] = true;
                
                // Redirigir al dashboard
                error_log("Redirigiendo a dashboard.php");
                header("Location: dashboard.php");
                exit;
            } else {
                error_log("Contraseña incorrecta para usuario: " . $usuario);
                $_SESSION['admin_error'] = "Contraseña incorrecta";
            }
        } else {
            error_log("Usuario no encontrado o no es administrador: " . $usuario);
            $_SESSION['admin_error'] = "Usuario no encontrado o no tienes permisos de administrador";
        }
    } catch (Exception $e) {
        error_log("Error durante la autenticación: " . $e->getMessage());
        $_SESSION['admin_error'] = "Error del sistema: " . $e->getMessage();
    }
    
    error_log("Redirigiendo a index.php por error o fallo de autenticación");
    header("Location: index.php");
    exit;
}

// Si no es POST, redirigir a la página de login
error_log("Método no POST, redirigiendo a index.php");
header("Location: index.php");
exit;