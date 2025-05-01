<?php
session_start();

// Verificar si el usuario está autenticado (ajusta según tu sistema de sesión)
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login"); // Ajusta la ruta según tu estructura
    exit();
}

// Incluir conexión a la base de datos
require_once '../backend/conexion.php'; // Ruta corregida

// Manejar actualización de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_estado'])) {
    $pedido_id = intval($_POST['pedido_id']);
    $nuevo_estado = mysqli_real_escape_string($conn, $_POST['estado']);
    
    $sql = "UPDATE pedidos SET ESTADO = '$nuevo_estado' WHERE ID = $pedido_id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['mensaje'] = "Estado actualizado correctamente";
    } else {
        $_SESSION['error'] = "Error al actualizar el estado: " . mysqli_error($conn);
    }
    header("Location: pedidos.php");
    exit();
}

// Obtener todos los pedidos con información del usuario
$sql = "SELECT p.*, u.NOMBRE AS nombre_usuario 
        FROM pedidos p
        JOIN usuarios u ON p.USUARIO_ID = u.ID
        ORDER BY p.FECHA_PEDIDO DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos - Restaurante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/pedidos.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Gestión de Pedidos</h2>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success"><?= $_SESSION['mensaje'] ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($pedido = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $pedido['ID'] ?></td>
                            <td><?= htmlspecialchars($pedido['nombre_usuario']) ?></td>
                            <td>$<?= number_format($pedido['TOTAL'], 2) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($pedido['FECHA_PEDIDO'])) ?></td>
                            <td><?= $pedido['ESTADO'] ?></td>
                            <td>
                                <form method="POST" class="d-flex">
                                    <input type="hidden" name="pedido_id" value="<?= $pedido['ID'] ?>">
                                    <select name="estado" class="form-select me-2" required>
                                        <option value="Pendiente" <?= $pedido['ESTADO'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="En preparación" <?= $pedido['ESTADO'] === 'En preparación' ? 'selected' : '' ?>>En preparación</option>
                                        <option value="Listo para entregar" <?= $pedido['ESTADO'] === 'Listo para entregar' ? 'selected' : '' ?>>Listo para entregar</option>
                                        <option value="Entregado" <?= $pedido['ESTADO'] === 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                                    </select>
                                    <button type="submit" name="actualizar_estado" class="btn btn-primary btn-sm">Actualizar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No hay pedidos registrados</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>