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
    header("Location: categorias.php");
    exit;
}

$id = (int)$_GET['id'];

// Verificar que la categoría exista
$stmt = $conn->prepare("SELECT id FROM categorias WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['admin_error'] = "La categoría no existe";
    header("Location: categorias.php");
    exit;
}

// Verificar si hay productos asociados a esta categoría
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM productos WHERE categoria_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$total_productos = $result->fetch_assoc()['total'];

if ($total_productos > 0) {
    $_SESSION['admin_error'] = "No se puede eliminar la categoría porque tiene productos asociados. Elimina primero los productos o asígnalos a otra categoría.";
    header("Location: categorias.php");
    exit;
}

// Eliminar la categoría
$stmt = $conn->prepare("DELETE FROM categorias WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['admin_exito'] = "Categoría eliminada correctamente";
} else {
    $_SESSION['admin_error'] = "Error al eliminar la categoría: " . $conn->error;
}

header("Location: categorias.php");
exit;