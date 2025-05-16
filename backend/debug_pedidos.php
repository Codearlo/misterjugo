<?php
// Este archivo se puede utilizar para depurar problemas con los pedidos
// Guárdalo como backend/debug_pedidos.php

// Habilitar mensajes de error para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Verificar acceso - solo para el administrador o desarrollador
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Acceso restringido. Solo administradores pueden acceder a esta página.");
}

// Incluir conexión a la base de datos
require_once 'conexion.php';

echo "<h1>Herramienta de Depuración de Pedidos</h1>";

// 1. Verificar estructura de las tablas
echo "<h2>1. Estructura de tablas</h2>";

// Tabla pedidos
$sql = "DESCRIBE pedidos";
$result = $conn->query($sql);

if ($result) {
    echo "<h3>Tabla: pedidos</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>NULL</th><th>Clave</th><th>Predeterminado</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error al consultar la estructura de la tabla pedidos: " . $conn->error . "</p>";
}

// Tabla detalles_pedidos (con 's')
$sql = "DESCRIBE detalles_pedidos";
$result = $conn->query($sql);

if ($result) {
    echo "<h3>Tabla: detalles_pedidos</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>NULL</th><th>Clave</th><th>Predeterminado</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error al consultar la estructura de la tabla detalles_pedidos: " . $conn->error . "</p>";
    
    // Intentar con el otro nombre (sin 's')
    $sql = "DESCRIBE detalle_pedidos";
    $result = $conn->query($sql);
    
    if ($result) {
        echo "<h3>Tabla: detalle_pedidos (sin 's')</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>NULL</th><th>Clave</th><th>Predeterminado</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color: red;'>Error al consultar la estructura de la tabla detalle_pedidos: " . $conn->error . "</p>";
    }
}

// Verificar si existen las tablas mediante información del esquema
echo "<h3>Comprobación de tablas existentes en la base de datos</h3>";
$tablas = [];
$sql = "SHOW TABLES";
$result = $conn->query($sql);

if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Nombre de la tabla</th></tr>";
    
    while ($row = $result->fetch_row()) {
        $tablas[] = $row[0];
        echo "<tr><td>" . $row[0] . "</td></tr>";
    }
    
    echo "</table>";
    
    // Comprobar si las tablas que necesitamos existen
    $tabla_pedidos = in_array('pedidos', $tablas);
    $tabla_detalles_pedidos = in_array('detalles_pedidos', $tablas);
    $tabla_detalle_pedidos = in_array('detalle_pedidos', $tablas);
    
    echo "<p><strong>Tabla 'pedidos' existe:</strong> " . ($tabla_pedidos ? 'Sí' : 'No') . "</p>";
    echo "<p><strong>Tabla 'detalles_pedidos' existe:</strong> " . ($tabla_detalles_pedidos ? 'Sí' : 'No') . "</p>";
    echo "<p><strong>Tabla 'detalle_pedidos' existe:</strong> " . ($tabla_detalle_pedidos ? 'Sí' : 'No') . "</p>";
    
    if (!$tabla_detalles_pedidos && !$tabla_detalle_pedidos) {
        echo "<p style='color: red;'><strong>ADVERTENCIA:</strong> No se encontró ninguna tabla para los detalles de pedidos. Es posible que necesites crearla.</p>";
    }
} else {
    echo "<p style='color: red;'>Error al consultar las tablas existentes: " . $conn->error . "</p>";
}

// 2. Últimos pedidos registrados
echo "<h2>2. Últimos pedidos registrados</h2>";

$sql = "SELECT p.*, u.nombre as usuario_nombre 
        FROM pedidos p 
        LEFT JOIN usuarios u ON p.usuario_id = u.id 
        ORDER BY p.fecha_pedido DESC 
        LIMIT 10";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Total</th><th>Estado</th><th>Fecha</th><th>Detalles</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['usuario_nombre'] ?? 'N/A') . " (ID: " . $row['usuario_id'] . ")</td>";
        echo "<td>S/ " . number_format($row['total'], 2) . "</td>";
        echo "<td>" . $row['estado'] . "</td>";
        echo "<td>" . $row['fecha_pedido'] . "</td>";
        echo "<td><a href='?ver_pedido=" . $row['id'] . "'>Ver detalles</a></td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No hay pedidos registrados en la base de datos.</p>";
}

// 3. Ver detalles de un pedido específico (con soporte para ambos nombres de tabla)
if (isset($_GET['ver_pedido'])) {
    $pedido_id = intval($_GET['ver_pedido']);
    
    // Obtener detalles del pedido
    $sql = "SELECT p.*, u.nombre as usuario_nombre 
            FROM pedidos p 
            LEFT JOIN usuarios u ON p.usuario_id = u.id 
            WHERE p.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $pedido = $result->fetch_assoc();
        
        echo "<h2>Detalles del Pedido #" . $pedido_id . "</h2>";
        
        echo "<h3>Información General</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        
        foreach ($pedido as $key => $value) {
            if ($key == 'usuario_nombre') continue;
            echo "<tr>";
            echo "<td>" . $key . "</td>";
            echo "<td>" . ($value === null ? 'NULL' : htmlspecialchars($value)) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Intentar primero con 'detalles_pedidos' (con 's')
        $sql = "SELECT dp.*, p.nombre as producto_nombre 
                FROM detalles_pedidos dp 
                LEFT JOIN productos p ON dp.producto_id = p.id 
                WHERE dp.pedido_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            echo "<h3>Productos del Pedido (de tabla 'detalles_pedidos')</h3>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>";
            
            $total = 0;
            while ($row = $result->fetch_assoc()) {
                $subtotal = $row['cantidad'] * $row['precio'];
                $total += $subtotal;
                
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['producto_nombre'] ?? 'Producto #' . $row['producto_id']) . "</td>";
                echo "<td>" . $row['cantidad'] . "</td>";
                echo "<td>S/ " . number_format($row['precio'], 2) . "</td>";
                echo "<td>S/ " . number_format($subtotal, 2) . "</td>";
                echo "</tr>";
            }
            
            echo "<tr><td colspan='4' align='right'><strong>Total</strong></td><td><strong>S/ " . number_format($total, 2) . "</strong></td></tr>";
            echo "</table>";
            
            // Verificar si el total calculado coincide con el total guardado
            if (abs($total - $pedido['total']) > 0.01) {
                echo "<p style='color: red;'><strong>¡ADVERTENCIA!</strong> El total calculado (S/ " . number_format($total, 2) . ") no coincide con el total guardado (S/ " . number_format($pedido['total'], 2) . "). Esto podría indicar un problema en el cálculo o en la inserción de datos.</p>";
            }
        } else {
            // Intentar con 'detalle_pedidos' (sin 's')
            $sql = "SELECT dp.*, p.nombre as producto_nombre 
                    FROM detalle_pedidos dp 
                    LEFT JOIN productos p ON dp.producto_id = p.id 
                    WHERE dp.pedido_id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $pedido_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                echo "<h3>Productos del Pedido (de tabla 'detalle_pedidos')</h3>";
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>ID</th><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>";
                
                $total = 0;
                while ($row = $result->fetch_assoc()) {
                    $subtotal = $row['cantidad'] * $row['precio'];
                    $total += $subtotal;
                    
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['producto_nombre'] ?? 'Producto #' . $row['producto_id']) . "</td>";
                    echo "<td>" . $row['cantidad'] . "</td>";
                    echo "<td>S/ " . number_format($row['precio'], 2) . "</td>";
                    echo "<td>S/ " . number_format($subtotal, 2) . "</td>";
                    echo "</tr>";
                }
                
                echo "<tr><td colspan='4' align='right'><strong>Total</strong></td><td><strong>S/ " . number_format($total, 2) . "</strong></td></tr>";
                echo "</table>";
                
                // Verificar si el total calculado coincide con el total guardado
                if (abs($total - $pedido['total']) > 0.01) {
                    echo "<p style='color: red;'><strong>¡ADVERTENCIA!</strong> El total calculado (S/ " . number_format($total, 2) . ") no coincide con el total guardado (S/ " . number_format($pedido['total'], 2) . "). Esto podría indicar un problema en el cálculo o en la inserción de datos.</p>";
                }
            } else {
                echo "<p>No se encontraron productos asociados a este pedido en ninguna de las tablas (detalles_pedidos o detalle_pedidos).</p>";
            }
        }
        
        echo "<p><a href='debug_pedidos.php'>Volver a la lista de pedidos</a></p>";
    } else {
        echo "<p style='color: red;'>No se encontró el pedido solicitado.</p>";
    }
}

// 4. Formulario para crear la tabla si no existe
echo "<h2>4. Crear tabla de detalles si no existe</h2>";

echo "<form method='post' action=''>";
echo "<p>Si la tabla de detalles no existe, puedes crearla aquí:</p>";
echo "<div style='margin-bottom: 10px;'>";
echo "<input type='radio' id='create_con_s' name='table_name' value='detalles_pedidos' checked>";
echo "<label for='create_con_s'> Crear tabla 'detalles_pedidos' (con 's')</label>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<input type='radio' id='create_sin_s' name='table_name' value='detalle_pedidos'>";
echo "<label for='create_sin_s'> Crear tabla 'detalle_pedidos' (sin 's')</label>";
echo "</div>";
echo "<input type='submit' name='create_table' value='Crear Tabla'>";
echo "</form>";

// Procesar la creación de la tabla
if (isset($_POST['create_table']) && isset($_POST['table_name'])) {
    $table_name = $_POST['table_name'];
    
    // Verificar si la tabla ya existe
    $sql = "SHOW TABLES LIKE '$table_name'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: red;'>La tabla '$table_name' ya existe.</p>";
    } else {
        // SQL para crear la tabla
        $sql = "CREATE TABLE `$table_name` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `pedido_id` int(11) NOT NULL,
                  `producto_id` int(11) NOT NULL,
                  `cantidad` int(11) NOT NULL,
                  `precio` decimal(10,2) NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `pedido_id` (`pedido_id`),
                  KEY `producto_id` (`producto_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>La tabla '$table_name' ha sido creada correctamente.</p>";
            echo "<meta http-equiv='refresh' content='2;url=debug_pedidos.php'>";
        } else {
            echo "<p style='color: red;'>Error al crear la tabla: " . $conn->error . "</p>";
        }
    }
}

// 5. Variables en sesión relacionadas con pedidos
echo "<h2>5. Variables en sesión</h2>";

echo "<h3>Carrito actual</h3>";
echo "<pre>";
if (isset($_SESSION['carrito'])) {
    if (empty($_SESSION['carrito'])) {
        echo "El carrito está vacío.";
    } else {
        print_r($_SESSION['carrito']);
    }
} else {
    echo "No existe la variable de sesión 'carrito'.";
}
echo "</pre>";

echo "<h3>Usuario en sesión</h3>";
echo "<pre>";
echo "user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'No definido') . "\n";
echo "user_name: " . (isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'No definido') . "\n";
echo "user_email: " . (isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : 'No definido') . "\n";
echo "is_admin: " . (isset($_SESSION['is_admin']) ? ($_SESSION['is_admin'] ? 'Sí' : 'No') : 'No definido') . "\n";
echo "</pre>";

// Botón para limpiar variables de sesión
echo "<form method='post'>";
echo "<input type='submit' name='limpiar_sesion' value='Limpiar variables de sesión (excepto usuario)'>";
echo "</form>";

if (isset($_POST['limpiar_sesion'])) {
    // Guardar variables de usuario
    $user_id = $_SESSION['user_id'] ?? null;
    $user_name = $_SESSION['user_name'] ?? null;
    $user_email = $_SESSION['user_email'] ?? null;
    $is_admin = $_SESSION['is_admin'] ?? null;
    
    // Limpiar sesión
    session_unset();
    
    // Restaurar variables de usuario
    if ($user_id !== null) $_SESSION['user_id'] = $user_id;
    if ($user_name !== null) $_SESSION['user_name'] = $user_name;
    if ($user_email !== null) $_SESSION['user_email'] = $user_email;
    if ($is_admin !== null) $_SESSION['is_admin'] = $is_admin;
    
    echo "<p style='color: green;'>Se han limpiado las variables de sesión, excepto las de usuario.</p>";
    echo "<meta http-equiv='refresh' content='1;url=debug_pedidos.php'>";
}
?>