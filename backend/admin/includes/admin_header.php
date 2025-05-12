<?php
// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Comprobar si el administrador está logueado
$isAdminLoggedIn = isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$adminName = $isAdminLoggedIn ? $_SESSION['admin_name'] : '';

// Si el administrador no está logueado, redirigir
if (!$isAdminLoggedIn) {
    $_SESSION['admin_error'] = "Acceso denegado. Debes iniciar sesión como administrador.";
    header("Location: /backend/admin/index.php");
    exit;
}

// Incluir conexión a la base de datos (asumiendo que existe en el directorio backend/)
require_once __DIR__ . '/../../conexion.php';

// Ahora, $isAdminLoggedIn y $adminName están disponibles para la plantilla HTML.
?>