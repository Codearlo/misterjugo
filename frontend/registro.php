<!-- frontend/registro.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - MisterJuco</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .form-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Crear Cuenta</h2>
    <form id="registroForm" action="backend/procesar_registro.php" method="POST">
        <div class="form-group">
            <input type="text" name="nombre" placeholder="Nombre completo" required minlength="3">
        </div>
        <div class="form-group">
            <input type="email" name="email" placeholder="Correo electrónico" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Contraseña" required minlength="6">
        </div>
        <button type="submit">Registrarse</button>
    </form>
    <p>¿Ya tienes cuenta? <a href="login.php">Inicia Sesión</a></p>
</div>

<script src="js/auth.js"></script>
</body>
</html>