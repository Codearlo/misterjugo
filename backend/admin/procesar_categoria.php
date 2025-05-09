    if ($stmt->execute()) {
        $_SESSION['admin_exito'] = "Categoría creada correctamente";
    } else {
        $_SESSION['admin_error'] = "Error al crear la categoría: " . $conn->error;
    }
    
    header("Location: categorias.php");
    exit;
}

// Función para actualizar una categoría existente
function actualizarCategoria() {
    global $conn;
    
    // Obtener datos del formulario
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    
    // Validar datos
    if ($id <= 0) {
        $_SESSION['admin_error'] = "ID de categoría no válido";
        header("Location: categorias.php");
        exit;
    }
    
    if (empty($nombre)) {
        $_SESSION['admin_error'] = "El nombre de la categoría es obligatorio";
        header("Location: categorias.php");
        exit;
    }
    
    // Verificar si ya existe otra categoría con ese nombre
    $stmt = $conn->prepare("SELECT id FROM categorias WHERE nombre = ? AND id != ?");
    $stmt->bind_param("si", $nombre, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['admin_error'] = "Ya existe otra categoría con ese nombre";
        header("Location: categorias.php");
        exit;
    }
    
    // Verificar que la categoría existe
    $stmt = $conn->prepare("SELECT id FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['admin_error'] = "La categoría no existe";
        header("Location: categorias.php");
        exit;
    }
    
    // Actualizar categoría
    $stmt = $conn->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nombre, $descripcion, $id);
    
    if ($stmt->execute()) {
        $_SESSION['admin_exito'] = "Categoría actualizada correctamente";
    } else {
        $_SESSION['admin_error'] = "Error al actualizar la categoría: " . $conn->error;
    }
    
    header("Location: categorias.php");
    exit;
}

// Función para eliminar una categoría
function eliminarCategoria() {
    global $conn;
    
    // Obtener ID de la categoría
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // Validar ID
    if ($id <= 0) {
        $_SESSION['admin_error'] = "ID de categoría no válido";
        header("Location: categorias.php");
        exit;
    }
    
    // Verificar que la categoría existe
    $stmt = $conn->prepare("SELECT id FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['admin_error'] = "La categoría no existe";
        header("Location: categorias.php");
        exit;
    }
    
    // Verificar que no haya productos asociados
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM productos WHERE categoria_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_productos = $result->fetch_assoc()['total'];
    
    if ($total_productos > 0) {
        $_SESSION['admin_error'] = "No se puede eliminar la categoría porque tiene productos asociados";
        header("Location: categorias.php");
        exit;
    }
    
    // Eliminar categoría
    $stmt = $conn->prepare("DELETE FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['admin_exito'] = "Categoría eliminada correctamente";
    } else {
        $_SESSION['admin_error'] = "Error al eliminar la categoría: " . $conn->error;
    }
    
    header("Location: categorias.php");
    exit;
}