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

// Definir un título básico para la página
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

**Archivo: `backend/admin/dashboard.php`**

```php
<?php
// Incluir el header de administración
require_once 'includes/admin_header.php';
?>

    <h2>Dashboard Principal</h2>

    <div class="dashboard-stats">
        <?php
        try {
            // Ejemplo básico: contar el número de usuarios
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
            $stmt->execute();
            $usuarios_total = $stmt->get_result()->fetch_assoc()['total'];
            echo "<p>Total de usuarios: " . $usuarios_total . "</p>";

            // Ejemplo básico: contar el número de productos
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM productos");
            $stmt->execute();
            $productos_total = $stmt->get_result()->fetch_assoc()['total'];
            echo "<p>Total de productos: " . $productos_total . "</p>";

            // ... Aquí podrías añadir más estadísticas del dashboard
        } catch (Exception $e) {
            echo "<p>Error al obtener datos: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>

<?php
// Aquí podrías incluir un footer básico más adelante
?>
        </div> </div> </body>
</html>