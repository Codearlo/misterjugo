<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias por tu pedido - Mi Jugo</title>
    <link rel="stylesheet" href="css/gracias.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="gracias-container">
        <h1>¡Gracias por tu pedido!</h1>
        <p>Tu pedido ha sido enviado correctamente.</p>
        <p>Pronto nos pondremos en contacto contigo.</p>
        <a href="index.php">Volver al inicio</a>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>