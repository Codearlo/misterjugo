<?php
// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Si no es administrador, redirigir a la página de inicio de sesión
    $_SESSION['admin_error'] = "Acceso denegado. Debes iniciar sesión como administrador.";
    header("Location: /backend/admin/index.php");
    exit;
}

// Incluir conexión a la base de datos (asumiendo que existe en el directorio backend/)
require_once __DIR__ . '/../../conexion.php';

// Ahora, cualquier página que incluya este header sabrá que la sesión de administrador es válida.
// La estructura HTML del header se manejará en otro archivo (por ejemplo, admin_template.php).
?>