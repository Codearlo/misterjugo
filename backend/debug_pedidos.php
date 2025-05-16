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

// Tabla detalle_pedidos
$sql = "DESCRIBE detalle_pedidos";
$result = $conn->query($sql);

if ($result) {
    echo "<h3>Tabla: detalle_pedidos</h3>";
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

// 3. Ver detalles de un pedido específico
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
        
        // Obtener detalles de los productos
        $sql = "SELECT dp.*, p.nombre as producto_nombre 
                FROM detalle_pedidos dp 
                LEFT JOIN productos p ON dp.producto_id = p.id 
                WHERE dp.pedido_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            echo "<h3>Productos del Pedido</h3>";
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
            echo "<p>No se encontraron productos asociados a este pedido.</p>";
        }
        
        echo "<p><a href='debug_pedidos.php'>Volver a la lista de pedidos</a></p>";
    } else {
        echo "<p style='color: red;'>No se encontró el pedido solicitado.</p>";
    }
}

// 4. Formulario para probar la inserción de pedidos
echo "<h2>4. Prueba de inserción manual de pedido</h2>";
echo "<p>Este formulario permite probar la inserción manual de un pedido para verificar la funcionalidad básica de la base de datos.</p>";

echo "<form method='post' action=''>";
echo "<table>";
echo "<tr><td>Usuario ID:</td><td><input type='number' name='usuario_id' required></td></tr>";
echo "<tr><td>Total:</td><td><input type='number' name='total' step='0.01' required></td></tr>";
echo "<tr><td>Estado:</td><td><select name='estado'><option value='pendiente'>pendiente</option><option value='procesando'>procesando</option><option value='completado'>completado</option><option value='cancelado'>cancelado</option></select></td></tr>";
echo "<tr><td colspan='2'><input type='submit' name='test_insert' value='Probar Inserción'></td></tr>";
echo "</table>";
echo "</form>";

// Procesar la prueba de inserción
if (isset($_POST['test_insert'])) {
    $usuario_id = intval($_POST['usuario_id']);
    $total = floatval($_POST['total']);
    $estado = $_POST['estado'];
    
    // Verificar si el usuario existe
    $stmt = $conn->prepare("SELECT id, nombre FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "<p style='color: red;'>Error: El usuario con ID $usuario_id no existe en la base de datos.</p>";
    } else {
        $usuario = $result->fetch_assoc();
        
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Insertar el pedido
            $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, total, estado, fecha_pedido) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("ids", $usuario_id, $total, $estado);
            $stmt->execute();
            
            $pedido_id = $conn->insert_id;
            
            // Insertar un detalle de prueba
            $stmt = $conn->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio) VALUES (?, 1, 1, ?)");
            $stmt->bind_param("id", $pedido_id, $total);
            $stmt->execute();
            
            // Confirmar transacción
            $conn->commit();
            
            echo "<p style='color: green;'>Prueba de inserción exitosa. Se ha creado el pedido #$pedido_id para el usuario " . htmlspecialchars($usuario['nombre']) . ".</p>";
            echo "<p><a href='?ver_pedido=$pedido_id'>Ver detalles del pedido de prueba</a></p>";
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $conn->rollback();
            echo "<p style='color: red;'>Error durante la prueba de inserción: " . $e->getMessage() . "</p>";
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

echo "<h3>Otras variables de sesión</h3>";
echo "<pre>";
foreach ($_SESSION as $key => $value) {
    if (!in_array($key, ['user_id', 'user_name', 'user_email', 'is_admin', 'carrito'])) {
        echo "$key: ";
        print_r($value);
        echo "\n";
    }
}
echo "</pre>";

// Botón para limpiar variables de depuración en sesión
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