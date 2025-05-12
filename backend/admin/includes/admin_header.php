<?php
// Iniciar la sesión si no está activa
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

// Incluir la conexión a la base de datos (asumiendo que existe en el directorio padre)
require_once __DIR__ . '/../conexion.php';

// Definir un título básico para la página (puedes modificar esto en cada página si lo prefieres)
$titulo_pagina = "Panel de Administración";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?></title>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1><?php echo $titulo_pagina; ?></h1>
            <p>Bienvenido, Administrador <?php echo $_SESSION['admin_name'] ?? 'Desconocido'; ?></p>
        </header>

        <div class="admin-content">
            ```

**Explicación de este header básico:**

1.  **`<?php`**: Abre el bloque de código PHP.
2.  **`if (session_status() == PHP_SESSION_NONE) { session_start(); }`**: Inicia la sesión de PHP si aún no está activa. Esto es crucial para acceder a las variables de sesión (`$_SESSION`).
3.  **Verificación de administrador**:
    * `!isset($_SESSION['admin_id'])`: Comprueba si la variable `admin_id` no está definida en la sesión.
    * `!isset($_SESSION['is_admin'])`: Comprueba si la variable `is_admin` no está definida.
    * `$_SESSION['is_admin'] !== true`: Comprueba si la variable `is_admin` no es igual a `true`.
    * Si alguna de estas condiciones es verdadera, significa que el usuario no está logueado como administrador.
    * `$_SESSION['admin_error'] = ...`: Establece un mensaje de error en la sesión.
    * `header("Location: /backend/admin/index.php");`: Redirige al usuario a la página de inicio de sesión del administrador. **Asegúrate de que esta ruta sea correcta para tu proyecto.**
    * `exit;`: Detiene la ejecución del script para asegurar que la página actual no se siga cargando.
4.  **`require_once __DIR__ . '/../conexion.php';`**: Incluye el archivo que contiene la conexión a tu base de datos. **Asumo que tienes un archivo `conexion.php` en el directorio padre (`backend/admin/`) que establece la conexión y define la variable `$conn`.**
5.  **`$titulo_pagina = "Panel de Administración";`**: Define una variable para el título de la página. Puedes modificarla en archivos específicos si lo deseas.
6.  **`<!DOCTYPE html>` y la estructura HTML básica**: Comienza la estructura HTML con un título básico que utiliza la variable `$titulo_pagina`.
7.  **`<div class="admin-container">`**: Un contenedor principal para el contenido del panel de administración.
8.  **`<header class="admin-header">`**: Un header para la parte superior del panel, mostrando el título y un saludo al administrador (si el nombre está en la sesión).
9.  **`<div class="admin-content">`**: Un div donde se insertará el contenido específico de cada página (como el dashboard, la lista de pedidos, etc.). El cierre de este `div` y del `body` y `html` se realizaría en un archivo de footer si lo tuvieras, o directamente en cada página después de su contenido.

**Para usar este header:**

1.  Guarda este código como `backend/admin/includes/admin_header.php`.
2.  Asegúrate de tener un archivo `backend/admin/conexion.php` con tu conexión a la base de datos.
3.  En tu archivo `backend/admin/dashboard.php` (y otras páginas del panel), incluye este header al principio:

    ```php
    <?php
    require_once 'includes/admin_header.php';
    ?>

    <h2>Contenido del Dashboard</h2>
    <p>...</p>

    <?php
    // Si tienes un footer, inclúyelo aquí
    ?>
        </div> </div> </body>
</html>
    ```

Este es un header funcional básico con la verificación de sesión. Si al incluir este header en `dashboard.php` sigues teniendo el error 500, el problema podría estar en el archivo `conexion.php` o en una configuración más fundamental del servidor. Avísame si esto resuelve el problema o si el error persiste.