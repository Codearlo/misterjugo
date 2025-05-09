<?php
// Iniciar sesión
session_start();

// Redirigir si ya está logueado
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Verificar si hay mensaje de error
$error_message = isset($_SESSION['admin_error']) ? $_SESSION['admin_error'] : '';
unset($_SESSION['admin_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Admin MisterJugo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-styles.css">
    <style>
        /* Estilos específicos para la página de login */
        body {
            background-color: var(--admin-light);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            font-family: var(--admin-font-normal);
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            background-color: var(--admin-white);
            border-radius: var(--admin-radius);
            box-shadow: var(--admin-shadow-md);
            padding: 30px;
            animation: fadeIn 0.5s ease;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header img {
            height: 70px;
            margin-bottom: 15px;
        }
        
        .login-header h1 {
            margin: 0 0 10px;
            color: var(--admin-dark);
            font-size: 1.6rem;
        }
        
        .login-header p {
            margin: 0;
            color: var(--admin-medium);
            font-size: 0.95rem;
        }
        
        .login-form .form-group {
            margin-bottom: 20px;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 8px;
            color: var(--admin-dark);
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--admin-medium);
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #e0e0e0;
            border-radius: var(--admin-radius);
            font-size: 1rem;
            transition: all var(--admin-transition-fast);
        }
        
        .input-group input:focus {
            outline: none;
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px rgba(255, 122, 0, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: var(--admin-primary);
            color: white;
            border: none;
            border-radius: var(--admin-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--admin-transition-fast);
        }
        
        .btn-login:hover {
            background-color: var(--admin-primary-dark);
        }
        
        .error-message {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--admin-danger);
            padding: 12px 15px;
            border-radius: var(--admin-radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: var(--admin-medium);
            font-size: 0.9rem;
        }
        
        .login-footer a {
            color: var(--admin-primary);
            text-decoration: none;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="../../images/logo_mrjugo.png" alt="MisterJugo Logo">
            <h1>Panel de Administración</h1>
            <p>Ingresa tus credenciales para acceder</p>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>
        
        <form class="login-form" action="validar_admin.php" method="POST">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="usuario" name="usuario" required autocomplete="username">
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
            </div>
            
            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>
        
        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> MisterJugo - Panel de Administración</p>
            <p><a href="/">Volver al sitio principal</a></p>
        </div>
    </div>
</body>
</html>