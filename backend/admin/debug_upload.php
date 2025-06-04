<?php
session_start();

// Solo permitir acceso a administradores
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Acceso denegado");
}

echo "<h1>Debug de Subida de Imágenes</h1>";

// Información del servidor
echo "<h2>Configuración del Servidor</h2>";
echo "<p><strong>DOCUMENT_ROOT:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>max_file_uploads:</strong> " . ini_get('max_file_uploads') . "</p>";
echo "<p><strong>file_uploads:</strong> " . (ini_get('file_uploads') ? 'Habilitado' : 'Deshabilitado') . "</p>";

// Verificar directorio de imágenes
$imagen_dir = $_SERVER['DOCUMENT_ROOT'] . '/imagenes';
echo "<h2>Directorio de Imágenes</h2>";
echo "<p><strong>Ruta:</strong> $imagen_dir</p>";
echo "<p><strong>Existe:</strong> " . (is_dir($imagen_dir) ? 'Sí' : 'No') . "</p>";

if (!is_dir($imagen_dir)) {
    echo "<p><strong>Intentando crear directorio...</strong></p>";
    if (mkdir($imagen_dir, 0755, true)) {
        echo "<p style='color: green;'>Directorio creado exitosamente</p>";
    } else {
        echo "<p style='color: red;'>Error al crear directorio</p>";
    }
} else {
    echo "<p><strong>Permisos de escritura:</strong> " . (is_writable($imagen_dir) ? 'Sí' : 'No') . "</p>";
    
    if (!is_writable($imagen_dir)) {
        echo "<p style='color: red;'>¡El directorio no tiene permisos de escritura!</p>";
        echo "<p>Intenta ejecutar: <code>chmod 755 " . $imagen_dir . "</code></p>";
    }
}

// Mostrar archivos existentes
if (is_dir($imagen_dir)) {
    $files = scandir($imagen_dir);
    echo "<h3>Archivos en el directorio:</h3>";
    if (count($files) > 2) { // . y ..
        echo "<ul>";
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "<li>$file</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p>No hay archivos en el directorio</p>";
    }
}

// Formulario de prueba
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['test_image'])) {
    echo "<h2>Resultado de la Prueba</h2>";
    
    $file = $_FILES['test_image'];
    echo "<pre>";
    print_r($file);
    echo "</pre>";
    
    if ($file['error'] == 0) {
        $filename = 'test_' . time() . '.jpg';
        $upload_path = $imagen_dir . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            echo "<p style='color: green;'>¡Imagen subida exitosamente!</p>";
            echo "<p>Ruta: $upload_path</p>";
            echo "<p>URL: /imagenes/$filename</p>";
            echo "<img src='/imagenes/$filename' style='max-width: 200px;'>";
        } else {
            echo "<p style='color: red;'>Error al subir la imagen</p>";
        }
    } else {
        echo "<p style='color: red;'>Error en la subida: " . $file['error'] . "</p>";
    }
}
?>

<h2>Prueba de Subida</h2>
<form method="POST" enctype="multipart/form-data">
    <p>
        <label>Selecciona una imagen JPG:</label><br>
        <input type="file" name="test_image" accept=".jpg,.jpeg" required>
    </p>
    <p>
        <input type="submit" value="Probar Subida">
    </p>
</form>

<p><a href="productos.php">← Volver a Productos</a></p>