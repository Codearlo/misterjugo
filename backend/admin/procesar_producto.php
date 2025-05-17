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
    $mantener_imagen = isset($_POST['mantener_imagen']) ? (int)$_POST['mantener_imagen'] : 0;

    // Validar datos
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

    // Verificar si la categoría existe
    $stmt = $conn->prepare("SELECT id FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $categoria_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $errores[] = "La categoría seleccionada no existe";
    }

    // Procesar imagen (solo JPG permitido)
    $imagen_path = '';
    $imagen_actual = '';

    // Si estamos editando, obtener la imagen actual
    if ($id > 0) {
        $stmt = $conn->prepare("SELECT imagen FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $imagen_actual = $result->fetch_assoc()['imagen'];
        }
    }

    // Verificar si se subió una nueva imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $file_name = $_FILES['imagen']['name'];
        $file_tmp = $_FILES['imagen']['tmp_name'];
        $file_size = $_FILES['imagen']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Verificar extensión permitida (solo JPG)
        if ($file_ext != "jpg" && $file_ext != "jpeg") {
            $errores[] = "Solo se permiten archivos JPG/JPEG";
        }

        // Verificar tamaño máximo (2MB)
        if ($file_size > 2097152) {
            $errores[] = "El tamaño máximo de la imagen es 2MB";
        }

        // Generar nuevo nombre para la imagen
        $new_file_name = 'imagenes/' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', basename($file_name, ".$file_ext")) . '.jpg';
        $upload_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $new_file_name;

        // Crear directorio si no existe
        $dir = dirname($upload_path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Subir la imagen si no hay errores
        if (empty($errores)) {
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $imagen_path = '/' . $new_file_name; // URL para guardar en la base de datos
            } else {
                $errores[] = "Error al subir la imagen. Verifica los permisos de la carpeta.";
            }
        }
    } else {
        // Si no se subió una nueva imagen y estamos editando
        if ($id > 0 && $mantener_imagen && !empty($imagen_actual)) {
            $imagen_path = $imagen_actual;
        } elseif ($id == 0) { // Crear nuevo producto sin imagen
            $errores[] = "La imagen es obligatoria para nuevos productos";
        }
    }

    // Si hay errores, redirigir con mensajes
    if (!empty($errores)) {
        $_SESSION['admin_error'] = implode("<br>", $errores);
        if ($id > 0) {
            header("Location: productos.php?action=edit&id=$id");
        } else {
            header("Location: productos.php?action=new");
        }
        exit;
    }

    // Verificar si estamos editando o creando un nuevo producto
    if ($id > 0) {
        // Editar producto existente
        $stmt = $conn->prepare("UPDATE productos SET nombre = ?, categoria_id = ?, precio = ?, descripcion = ?, imagen = ?, disponible = ? WHERE id = ?");
        $stmt->bind_param("sidsiii", $nombre, $categoria_id, $precio, $descripcion, $imagen_path, $disponible, $id);
        
        if ($stmt->execute()) {
            $_SESSION['admin_exito'] = "Producto actualizado correctamente";
            header("Location: productos.php");
            exit;
        } else {
            $_SESSION['admin_error'] = "Error al actualizar el producto: " . $conn->error;
            header("Location: productos.php?action=edit&id=$id");
            exit;
        }
    } else {
        // Crear nuevo producto
        $stmt = $conn->prepare("INSERT INTO productos (nombre, categoria_id, precio, descripcion, imagen, disponible) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidisi", $nombre, $categoria_id, $precio, $descripcion, $imagen_path, $disponible);
        
        if ($stmt->execute()) {
            $_SESSION['admin_exito'] = "Producto creado correctamente";
            header("Location: productos.php");
            exit;
        } else {
            $_SESSION['admin_error'] = "Error al crear el producto: " . $conn->error;
            header("Location: productos.php?action=new");
            exit;
        }
    }
} else {
    // Si no es POST, redirigir
    header("Location: productos.php");
    exit;
}