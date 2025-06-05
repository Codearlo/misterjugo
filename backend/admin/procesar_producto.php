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
    $categoria_id = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : 0;
    $precio = isset($_POST['precio']) ? (float)$_POST['precio'] : 0;
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $disponible = isset($_POST['disponible']) ? (int)$_POST['disponible'] : 1;
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    // Validar datos básicos
    $errores = [];

    if (empty($nombre)) {
        $errores[] = "El nombre del producto es obligatorio";
    }

    if ($categoria_id <= 0) {
        $errores[] = "Debes seleccionar una categoría válida";
    }

    if ($precio <= 0) {
        $errores[] = "El precio debe ser mayor que 0";
    }

    // Obtener imagen actual si estamos editando
    $imagen_actual = '';
    if ($id > 0) {
        $stmt = $conn->prepare("SELECT imagen FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $imagen_actual = $result->fetch_assoc()['imagen'];
        }
    }

    // Procesar imagen
    $imagen_path = $imagen_actual; // Por defecto, mantener la actual

    // Si se subió una nueva imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['imagen']['tmp_name'];
        $file_name = $_FILES['imagen']['name'];
        $file_size = $_FILES['imagen']['size'];
        
        // Validar tamaño (2MB máximo)
        if ($file_size > 2097152) {
            $errores[] = "La imagen debe ser menor a 2MB";
        }
        
        // Validar tipo de archivo
        $file_info = getimagesize($file_tmp);
        if ($file_info === false || !in_array($file_info['mime'], ['image/jpeg', 'image/jpg'])) {
            $errores[] = "Solo se permiten imágenes JPG";
        }
        
        if (empty($errores)) {
            // Crear directorio si no existe
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/imagenes';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generar nombre único
            $extension = 'jpg';
            $nuevo_nombre = uniqid('img_') . '.' . $extension;
            $ruta_completa = $upload_dir . '/' . $nuevo_nombre;
            
            // Mover archivo
            if (move_uploaded_file($file_tmp, $ruta_completa)) {
                // Eliminar imagen anterior si existe
                if (!empty($imagen_actual) && file_exists($_SERVER['DOCUMENT_ROOT'] . $imagen_actual)) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . $imagen_actual);
                }
                
                $imagen_path = '/imagenes/' . $nuevo_nombre;
            } else {
                $errores[] = "Error al subir la imagen";
            }
        }
    } else if ($id == 0) {
        // Nuevo producto necesita imagen
        $errores[] = "La imagen es obligatoria para nuevos productos";
    }

    // Si hay errores, redirigir
    if (!empty($errores)) {
        $_SESSION['admin_error'] = implode("<br>", $errores);
        if ($id > 0) {
            header("Location: productos?action=edit&id=$id");
        } else {
            header("Location: productos?action=new");
        }
        exit;
    }

    // Guardar en base de datos
    if ($id > 0) {
        // Actualizar producto existente
        $stmt = $conn->prepare("UPDATE productos SET nombre = ?, categoria_id = ?, precio = ?, descripcion = ?, imagen = ?, disponible = ? WHERE id = ?");
        $stmt->bind_param("sisssii", $nombre, $categoria_id, $precio, $descripcion, $imagen_path, $disponible, $id);
    } else {
        // Crear nuevo producto
        $stmt = $conn->prepare("INSERT INTO productos (nombre, categoria_id, precio, descripcion, imagen, disponible) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidssi", $nombre, $categoria_id, $precio, $descripcion, $imagen_path, $disponible);
    }

    if ($stmt->execute()) {
        $mensaje = ($id > 0) ? "Producto actualizado correctamente" : "Producto creado correctamente";
        $_SESSION['admin_exito'] = $mensaje;
        header("Location: productos");
    } else {
        $_SESSION['admin_error'] = "Error en la base de datos: " . $conn->error;
        if ($id > 0) {
            header("Location: productos?action=edit&id=$id");
        } else {
            header("Location: productos?action=new");
        }
    }
    exit;
}

// Si no es POST, redirigir
header("Location: productos");
exit;
?>