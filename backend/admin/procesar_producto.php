<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    $_SESSION['admin_error'] = "Debes iniciar sesión como administrador para acceder";
    header("Location: index.php");
    exit;
}

// Incluir conexión a la base de datos
require_once '../conexion.php';

// Obtener la acción a realizar
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Actuar según la acción solicitada
switch ($action) {
    case 'create':
        crearProducto();
        break;
    
    case 'update':
        actualizarProducto();
        break;
    
    case 'delete':
        eliminarProducto();
        break;
    
    case 'toggle_status':
        cambiarEstadoProducto();
        break;
    
    case 'update_image':
        actualizarImagen();
        break;
    
    case 'delete_image':
        eliminarImagen();
        break;
    
    default:
        // Acción no reconocida
        $_SESSION['admin_error'] = "Acción no válida";
        header("Location: productos.php");
        exit;
}

// Función para crear un nuevo producto
function crearProducto() {
    global $conn;
    
    // Recoger y validar datos
    $errores = validarDatosProducto();
    
    if (!empty($errores)) {
        // Si hay errores, guardar datos y redireccionar
        $_SESSION['form_errores'] = $errores;
        $_SESSION['old_data'] = $_POST;
        header("Location: editar_producto.php");
        exit;
    }
    
    // Datos validados, proceder con la creación
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $stock = (int)$_POST['stock'];
    $categoria_id = (int)$_POST['categoria_id'];
    $disponible = 1; // Por defecto, activo
    
    // Insertar en la base de datos
    $stmt = $conn->prepare("
        INSERT INTO productos (nombre, descripcion, precio, stock, categoria_id, disponible, fecha_creacion) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("ssdiis", $nombre, $descripcion, $precio, $stock, $categoria_id, $disponible);
    
    if ($stmt->execute()) {
        $nuevo_id = $conn->insert_id;
        $_SESSION['admin_exito'] = "Producto creado con éxito";
        header("Location: editar_producto.php?id=" . $nuevo_id);
    } else {
        $_SESSION['admin_error'] = "Error al crear el producto: " . $conn->error;
        $_SESSION['old_data'] = $_POST;
        header("Location: editar_producto.php");
    }
    exit;
}

// Función para actualizar un producto existente
function actualizarProducto() {
    global $conn;
    
    // Recoger ID del producto
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id <= 0) {
        $_SESSION['admin_error'] = "ID de producto no válido";
        header("Location: productos.php");
        exit;
    }
    
    // Recoger y validar datos
    $errores = validarDatosProducto();
    
    if (!empty($errores)) {
        // Si hay errores, guardar datos y redireccionar
        $_SESSION['form_errores'] = $errores;
        $_SESSION['old_data'] = $_POST;
        header("Location: editar_producto.php?id=" . $id);
        exit;
    }
    
    // Datos validados, proceder con la actualización
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $stock = (int)$_POST['stock'];
    $categoria_id = (int)$_POST['categoria_id'];
    
    // Actualizar en la base de datos
    $stmt = $conn->prepare("
        UPDATE productos 
        SET nombre = ?, descripcion = ?, precio = ?, stock = ?, categoria_id = ?, fecha_actualizacion = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("ssdiis", $nombre, $descripcion, $precio, $stock, $categoria_id, $id);
    
    if ($stmt->execute()) {
        $_SESSION['admin_exito'] = "Producto actualizado con éxito";
        header("Location: editar_producto.php?id=" . $id);
    } else {
        $_SESSION['admin_error'] = "Error al actualizar el producto: " . $conn->error;
        $_SESSION['old_data'] = $_POST;
        header("Location: editar_producto.php?id=" . $id);
    }
    exit;
}

// Función para eliminar un producto
function eliminarProducto() {
    global $conn;
    
    // Recoger ID del producto
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        $_SESSION['admin_error'] = "ID de producto no válido";
        header("Location: productos.php");
        exit;
    }
    
    // Verificar si el producto existe
    $stmt = $conn->prepare("SELECT id FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['admin_error'] = "El producto no existe";
        header("Location: productos.php");
        exit;
    }
    
    // Verificar si el producto está en algún pedido (no eliminarlo en ese caso)
    $stmt = $conn->prepare("SELECT id FROM detalle_pedidos WHERE producto_id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Producto en uso, solo desactivar
        $stmt = $conn->prepare("UPDATE productos SET disponible = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['admin_exito'] = "El producto ha sido desactivado porque está asociado a pedidos existentes";
            header("Location: productos.php");
        } else {
            $_SESSION['admin_error'] = "Error al desactivar el producto: " . $conn->error;
            header("Location: productos.php");
        }
        exit;
    }
    
    // Eliminar el producto
    $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['admin_exito'] = "Producto eliminado con éxito";
        header("Location: productos.php");
    } else {
        $_SESSION['admin_error'] = "Error al eliminar el producto: " . $conn->error;
        header("Location: productos.php");
    }
    exit;
}

// Función para cambiar el estado de un producto (activo/inactivo)
function cambiarEstadoProducto() {
    global $conn;
    
    // Recoger ID del producto
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        $_SESSION['admin_error'] = "ID de producto no válido";
        header("Location: productos.php");
        exit;
    }
    
    // Obtener estado actual
    $stmt = $conn->prepare("SELECT disponible FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['admin_error'] = "El producto no existe";
        header("Location: productos.php");
        exit;
    }
    
    $row = $result->fetch_assoc();
    $nuevo_estado = $row['disponible'] ? 0 : 1;
    
    // Cambiar estado
    $stmt = $conn->prepare("UPDATE productos SET disponible = ? WHERE id = ?");
    $stmt->bind_param("ii", $nuevo_estado, $id);
    
    if ($stmt->execute()) {
        $_SESSION['admin_exito'] = $nuevo_estado ? "Producto activado con éxito" : "Producto desactivado con éxito";
        
        // Determinar hacia dónde redireccionar (productos.php o editar_producto.php)
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if (strpos($referer, 'editar_producto.php') !== false) {
            header("Location: editar_producto.php?id=" . $id);
        } else {
            header("Location: productos.php");
        }
    } else {
        $_SESSION['admin_error'] = "Error al cambiar el estado del producto: " . $conn->error;
        header("Location: productos.php");
    }
    exit;
}

// Función para actualizar la imagen de un producto
function actualizarImagen() {
    global $conn;
    
    // Recoger ID del producto
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id <= 0) {
        $_SESSION['admin_error'] = "ID de producto no válido";
        header("Location: productos.php");
        exit;
    }
    
    // Verificar si se ha subido un archivo
    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
        $_SESSION['admin_error'] = "No se ha seleccionado ninguna imagen";
        header("Location: editar_producto.php?id=" . $id);
        exit;
    }
    
    // Validar el archivo
    $errores = validarImagen($_FILES['imagen']);
    
    if (!empty($errores)) {
        $_SESSION['form_errores'] = ['imagen' => $errores];
        header("Location: editar_producto.php?id=" . $id);
        exit;
    }
    
    // Directorio para guardar las imágenes
    $directorio_destino = '../../images/productos/';
    
    // Crear el directorio si no existe
    if (!file_exists($directorio_destino)) {
        mkdir($directorio_destino, 0755, true);
    }
    
    // Generar un nombre único para la imagen
    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = 'producto_' . $id . '_' . uniqid() . '.' . $extension;
    $ruta_archivo = $directorio_destino . $nombre_archivo;
    
    // Mover el archivo subido al directorio de destino
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_archivo)) {
        // Obtener la imagen anterior (si existe)
        $stmt = $conn->prepare("SELECT imagen FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $imagen_anterior = $stmt->get_result()->fetch_assoc()['imagen'];
        
        // Ruta relativa para guardar en la base de datos
        $ruta_relativa = '/images/productos/' . $nombre_archivo;
        
        // Actualizar la imagen en la base de datos
        $stmt = $conn->prepare("UPDATE productos SET imagen = ? WHERE id = ?");
        $stmt->bind_param("si", $ruta_relativa, $id);
        
        if ($stmt->execute()) {
            // Eliminar imagen anterior si existe y no es la misma
            if (!empty($imagen_anterior) && file_exists('../../' . ltrim($imagen_anterior, '/'))) {
                unlink('../../' . ltrim($imagen_anterior, '/'));
            }
            
            $_SESSION['admin_exito'] = "Imagen actualizada con éxito";
        } else {
            $_SESSION['admin_error'] = "Error al actualizar la imagen en la base de datos: " . $conn->error;
        }
    } else {
        $_SESSION['admin_error'] = "Error al subir la imagen";
    }
    
    header("Location: editar_producto.php?id=" . $id);
    exit;
}

// Función para eliminar la imagen de un producto
function eliminarImagen() {
    global $conn;
    
    // Recoger ID del producto
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        $_SESSION['admin_error'] = "ID de producto no válido";
        header("Location: productos.php");
        exit;
    }
    
    // Obtener la imagen actual
    $stmt = $conn->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['admin_error'] = "El producto no existe";
        header("Location: productos.php");
        exit;
    }
    
    $imagen = $result->fetch_assoc()['imagen'];
    
    // Eliminar la imagen del sistema de archivos
    if (!empty($imagen) && file_exists('../../' . ltrim($imagen, '/'))) {
        unlink('../../' . ltrim($imagen, '/'));
    }
    
    // Actualizar la base de datos para quitar referencia a la imagen
    $stmt = $conn->prepare("UPDATE productos SET imagen = NULL WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['admin_exito'] = "Imagen eliminada con éxito";
    } else {
        $_SESSION['admin_error'] = "Error al eliminar la referencia a la imagen: " . $conn->error;
    }
    
    header("Location: editar_producto.php?id=" . $id);
    exit;
}

// Función para validar los datos del producto
function validarDatosProducto() {
    $errores = [];
    
    // Validar nombre (obligatorio, longitud máxima 100)
    if (empty($_POST['nombre'])) {
        $errores['nombre'] = "El nombre del producto es obligatorio";
    } elseif (strlen($_POST['nombre']) > 100) {
        $errores['nombre'] = "El nombre no puede exceder los 100 caracteres";
    }
    
    // Validar descripción (opcional, pero longitud máxima 1000)
    if (!empty($_POST['descripcion']) && strlen($_POST['descripcion']) > 1000) {
        $errores['descripcion'] = "La descripción no puede exceder los 1000 caracteres";
    }
    
    // Validar precio (obligatorio, mayor que 0)
    if (!isset($_POST['precio']) || $_POST['precio'] === '') {
        $errores['precio'] = "El precio es obligatorio";
    } elseif ((float)$_POST['precio'] <= 0) {
        $errores['precio'] = "El precio debe ser mayor que 0";
    }
    
    // Validar stock (obligatorio, mayor o igual a 0)
    if (!isset($_POST['stock']) || $_POST['stock'] === '') {
        $errores['stock'] = "El stock es obligatorio";
    } elseif ((int)$_POST['stock'] < 0) {
        $errores['stock'] = "El stock no puede ser negativo";
    }
    
    // Validar categoría (obligatorio)
    if (empty($_POST['categoria_id'])) {
        $errores['categoria_id'] = "Debe seleccionar una categoría";
    }
    
    return $errores;
}

// Función para validar la imagen
function validarImagen($file) {
    // Verificar si hay errores en la subida
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return "El archivo es demasiado grande";
            case UPLOAD_ERR_PARTIAL:
                return "El archivo fue subido parcialmente";
            case UPLOAD_ERR_NO_FILE:
                return "No se seleccionó ningún archivo";
            default:
                return "Error al subir el archivo";
        }
    }
    
    // Verificar el tipo de archivo (solo permitir imágenes)
    $mime_types = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $mime_types)) {
        return "El archivo debe ser una imagen (JPG, PNG o GIF)";
    }
    
    // Verificar tamaño (máximo 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        return "La imagen no puede superar los 2MB";
    }
    
    return "";
}