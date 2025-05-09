<?php
// Iniciar sesión
session_start();

// Eliminar variables de sesión de administrador
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_user']);
unset($_SESSION['is_admin']);

// Redirigir a la página de login con mensaje
session_regenerate_id(true);
$_SESSION['admin_error'] = "Has cerrado sesión correctamente";
header("Location: index.php");
exit;