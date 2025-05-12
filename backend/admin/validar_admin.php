<?php
// Iniciar la sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos
require_once __DIR__ . '/../conexion.php';

// Verificar si se han enviado datos por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? ''; // Obtener la contraseña, usar '' si no está definida

    // Validar que los campos no estén vacíos
    if (empty($email) || empty($password)) {
        $_SESSION['admin_error'] = "Por favor, introduce tu email y contraseña.";
        header("Location: index.php");
        exit;
    }

    try {
        // Preparar la consulta para buscar al administrador por email
        $stmt = $conn->prepare("SELECT id, nombre, password, is_admin FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();

            // Verificar la contraseña
            if (password_verify($password, $admin['password']) && $admin['is_admin'] == 1) {
                // La contraseña es correcta y el usuario es administrador
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['nombre'];
                $_SESSION['is_admin'] = true;

                // Redirigir al panel de administración (dashboard)
                header("Location: dashboard.php");
                exit;
            } else {
                // Contraseña incorrecta o no es administrador
                $_SESSION['admin_error'] = "Credenciales incorrectas. Por favor, inténtalo de nuevo.";
                header("Location: index.php");
                exit;
            }
        } else {
            // No se encontró ningún usuario con ese email
            $_SESSION['admin_error'] = "Credenciales incorrectas. Por favor, inténtalo de nuevo.";
            header("Location: index.php");
            exit;
        }

        $stmt->close();
    } catch (Exception $e) {
        // Error al realizar la consulta
        $_SESSION['admin_error'] = "Ocurrió un error al intentar iniciar sesión. Por favor, inténtalo más tarde.";
        error_log("Error en validar_admin.php: " . $e->getMessage());
        header("Location: index.php");
        exit;
    }
} else {
    // Si se intenta acceder a este script por GET, redirigir al formulario de inicio de sesión
    header("Location: index.php");
    exit;
}

// Cerrar la conexión a la base de datos
$conn->close();
?>