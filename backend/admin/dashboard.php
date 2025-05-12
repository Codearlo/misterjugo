<?php
// Definir variables para el header
$titulo_pagina = "Dashboard";
$breadcrumbs = [
    ['texto' => 'Dashboard']
];

// Incluir el header
require_once 'includes/admin_header.php';

// Obtener mensajes de estado si existen
$exito = isset($_SESSION['admin_exito']) ? $_SESSION['admin_exito'] : '';
$error = isset($_SESSION['admin_error']) ? $_SESSION['admin_error'] : '';
unset($_SESSION['admin_exito'], $_SESSION['admin_error']);

// Código para obtener estadísticas
try {
    // Ejemplo básico: contar el número de usuarios
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
    $stmt->execute();
    $usuarios_total = $stmt->get_result()->fetch_assoc()['total'];
    
    // Ejemplo básico: contar el número de productos
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM productos");
    $stmt->execute();
    $productos_total = $stmt->get_result()->fetch_assoc()['total'];
    
    // Obtener conteo de pedidos por estado
    $estados_conteo = [];
    $sql_conteo = "SELECT estado, COUNT(*) as total FROM pedidos GROUP BY estado";
    $result_conteo = $conn->query($sql_conteo);
    while ($row = $result_conteo->fetch_assoc()) {
        $estados_conteo[$row['estado']] = $row['total'];
    }
    
    // Agregar conteo total de pedidos
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pedidos");
    $stmt->execute();
    $pedidos_total = $stmt->get_result()->fetch_assoc()['total'];
    
} catch (Exception $e) {
    echo "<div class='notification danger'><i class='fas fa-exclamation-circle'></i><span>Error al obtener datos: " . $e->getMessage() . "</span></div>";
}
?>

<!-- Notificaciones -->
<?php if (!empty($exito)): ?>
    <div class="notification success">
        <i class="fas fa-check-circle"></i>
        <span><?php echo $exito; ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="notification danger">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo $error; ?></span>
    </div>
<?php endif; ?>

<!-- Tarjetas de estadísticas -->
<div class="card-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo isset($usuarios_total) ? $usuarios_total : 0; ?></div>
            <div class="stat-label">Usuarios</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo isset($productos_total) ? $productos_total : 0; ?></div>
            <div class="stat-label">Productos</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?php echo isset($pedidos_total) ? $pedidos_total : 0; ?></div>
            <div class="stat-label">Pedidos Totales</div>
        </div>
    </div>
</div>

<!-- Contenido adicional del dashboard -->
<!-- Aquí pueden ir gráficos, tablas de resumen, etc. -->

<?php
// Incluir el footer
require_once 'includes/admin_footer.php';
?>