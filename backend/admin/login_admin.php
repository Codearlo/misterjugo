<!-- backend/admin/login_admin.php -->
<?php
session_start();

if (isset($_SESSION['admin'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <h1>Iniciar Sesión - Administrador</h1>
    <form action="validar_admin.php" method="POST">
        <label for="usuario">Usuario:</label>
        <input type="text" name="usuario" required><br><br>

        <label for="password">Contraseña:</label>
        <input type="password" name="password" required><br><br>

        <button type="submit">Iniciar Sesión</button>
    </form>
</body>
</html>