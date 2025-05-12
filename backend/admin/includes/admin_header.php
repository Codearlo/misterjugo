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

// Incluir conexión a la base de datos (asumiendo que existe y está configurada)
require_once __DIR__ . '/../conexion.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Panel de Administración</h1>
            <p>Bienvenido, Administrador <?php echo $_SESSION['admin_name'] ?? 'Desconocido'; ?></p>
            </header>

        <div class="admin-content">
            ```

**Opción 2: Dejar la etiqueta `<title>` pero sin contenido dinámico por ahora**

Podemos dejar la etiqueta `<title>` con un texto estático o vacío.

**Archivo: `backend/admin/includes/admin_header.php` (título estático):**

```php
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

// Incluir conexión a la base de datos (asumiendo que existe y está configurada)
require_once __DIR__ . '/../conexion.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin</title>
    </head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Panel de Administración</h1>
            <p>Bienvenido, Administrador <?php echo $_SESSION['admin_name'] ?? 'Desconocido'; ?></p>
            </header>

        <div class="admin-content">
            ```

**Opción 3: Dejar la etiqueta `<title>` vacía:**

```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    </head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Panel de Administración</h1>
            <p>Bienvenido, Administrador <?php echo $_SESSION['admin_name'] ?? 'Desconocido'; ?></p>
            </header>

        <div class="admin-content">
            ```
