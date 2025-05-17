<?php
session_start();

// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    $_SESSION['error_login'] = "Debes iniciar sesión como administrador para acceder";
    header("Location: /login");
    exit;
}

// Incluir conexión a la base de datos
require_once '../conexion.php';

// Verificar si se ha pasado un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['admin_error'] = "ID no proporcionado";
    header("Location: productos.php");
    exit;
}

$id = (int)$_GET['id'];

// Verificar que el producto exista y obtener su información
$stmt = $conn->prepare("SELECT id, imagen FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['admin_error'] = "El producto no existe";
    header("Location: productos.php");
    exit;
}

$producto = $result->fetch_assoc();

// Eliminar imagen física del servidor si existe
if (!empty($producto['imagen'])) {
    $imagen_path = $_SERVER['DOCUMENT_ROOT'] . $producto['imagen'];
    if (file_exists($imagen_path)) {
        unlink($imagen_path);
    }
}

// Eliminar el producto
$stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['admin_exito'] = "Producto eliminado correctamente";
} else {
    $_SESSION['admin_error'] = "Error al eliminar el producto: " . $conn->error;
}

header("Location: productos.php");
exit;