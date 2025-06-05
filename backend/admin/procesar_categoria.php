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

// Verificar si se han enviado datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    
    // Validar datos
    if (empty($nombre)) {
        $_SESSION['admin_error'] = "El nombre de la categoría es obligatorio";
        header("Location: categorias?action=new");
        exit;
    }
    
    // Verificar si estamos editando o creando una nueva categoría
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Editar categoría existente
        $id = (int)$_POST['id'];
        
        // Verificar que la categoría exista
        $stmt = $conn->prepare("SELECT id FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['admin_error'] = "La categoría que intentas editar no existe";
            header("Location: categorias");
            exit;
        }
        
        // Verificar que el nombre no esté en uso por otra categoría
        $stmt = $conn->prepare("SELECT id FROM categorias WHERE nombre = ? AND id != ?");
        $stmt->bind_param("si", $nombre, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['admin_error'] = "Ya existe una categoría con ese nombre";
            header("Location: categorias?action=edit&id=$id");
            exit;
        }
        
        // Actualizar categoría
        $stmt = $conn->prepare("UPDATE categorias SET nombre = ? WHERE id = ?");
        $stmt->bind_param("si", $nombre, $id);
        
        if ($stmt->execute()) {
            $_SESSION['admin_exito'] = "Categoría actualizada correctamente";
            header("Location: categorias");
            exit;
        } else {
            $_SESSION['admin_error'] = "Error al actualizar la categoría: " . $conn->error;
            header("Location: categorias?action=edit&id=$id");
            exit;
        }
    } else {
        // Crear nueva categoría
        
        // Verificar que el nombre no esté en uso
        $stmt = $conn->prepare("SELECT id FROM categorias WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['admin_error'] = "Ya existe una categoría con ese nombre";
            header("Location: categorias?action=new");
            exit;
        }
        
        // Insertar nueva categoría
        $stmt = $conn->prepare("INSERT INTO categorias (nombre) VALUES (?)");
        $stmt->bind_param("s", $nombre);
        
        if ($stmt->execute()) {
            $_SESSION['admin_exito'] = "Categoría creada correctamente";
            header("Location: categorias");
            exit;
        } else {
            $_SESSION['admin_error'] = "Error al crear la categoría: " . $conn->error;
            header("Location: categorias?action=new");
            exit;
        }
    }
} else {
    // Si no es POST, redirigir
    header("Location: categorias");
    exit;
}