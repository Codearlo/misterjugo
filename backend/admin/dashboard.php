<?php
// Incluir el header de administración
require_once '/includes/admin_header.phpp';
?>

    <h2>Dashboard Principal</h2>

    <div class="dashboard-stats">
        <?php
        try {
            // Ejemplo básico: contar el número de usuarios
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
            $stmt->execute();
            $usuarios_total = $stmt->get_result()->fetch_assoc()['total'];
            echo "<p>Total de usuarios: " . $usuarios_total . "</p>";

            // Ejemplo básico: contar el número de productos
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM productos");
            $stmt->execute();
            $productos_total = $stmt->get_result()->fetch_assoc()['total'];
            echo "<p>Total de productos: " . $productos_total . "</p>";

            // ... Aquí podrías añadir más estadísticas del dashboard
        } catch (Exception $e) {
            echo "<p>Error al obtener datos: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>

<?php
// Aquí podrías incluir un footer básico más adelante
?>
        </div>
    </div>
</body>
</html>