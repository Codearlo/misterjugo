<!-- frontend/login.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - MisterJuco</title>
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
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Iniciar Sesión</h2>
    <form action="backend/iniciar_sesion.php" method="POST">
        <div class="form-group">
            <input type="email" name="email" placeholder="Correo electrónico" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Contraseña" required>
        </div>
        <button type="submit">Entrar</button>
    </form>
    <p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>

    <!-- Botón para iniciar con Google -->
    <br><hr><br>
    <a href="backend/google_callback.php">
        <button type="button" style="background-color:#db4437; color:white;">
            🔐 Iniciar con Google
        </button>
    </a>
</div>

</body>
</html>