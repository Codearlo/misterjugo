<?php

session_start();

// Incluir manualmente las clases básicas de Google API
require_once __DIR__ . '/../google-api/src/Client.php';
require_once __DIR__ . '/../google-api/src/Http/Request.php';
require_once __DIR__ . '/../google-api/src/Http/Curl.php';
require_once __DIR__ . '/../google-api/src/Config.php';
require_once __DIR__ . '/../google-api/src/Service/Resource.php';
require_once __DIR__ . '/../google-api/src/Service/ServiceResource.php';
require_once __DIR__ . '/../google-api/src/Service/Oauth2.php';

// Incluir archivo de conexión a la base de datos
require_once __DIR__ . '/conexion.php';

// Iniciar conexión a la base de datos
$conn = conectarDB();

// Configuración de Google
$client = new Google_Client();
$client->setClientId('TU_CLIENT_ID_AQUÍ'); // Reemplaza con tu Client ID
$client->setClientSecret('TU_CLIENT_SECRET_AQUÍ'); // Reemplaza con tu Client Secret
$client->setRedirectUri('https://misterjuco.com/backend/google_callback.php');
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    // Obtener token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    // Obtener información del usuario
    $oauth = new Google_Service_Oauth2($client);
    $userInfo = $oauth->userinfo->get();

    // Guardar en sesión
    $_SESSION['nombre'] = $userInfo->name;
    $_SESSION['email'] = $userInfo->email;

    // Verificar si el usuario ya existe en la BD
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userInfo->email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // El usuario no existe, registrarlo
        $insertSql = "INSERT INTO usuarios (nombre, email) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ss", $userInfo->name, $userInfo->email);
        $insertStmt->execute();

        // Obtener ID del nuevo usuario
        $userId = $insertStmt->insert_id;

        $insertStmt->close();
    } else {
        // Usuario existente, obtener su ID
        $row = $result->fetch_assoc();
        $userId = $row['id'];
    }

    // Guardar ID del usuario en sesión
    $_SESSION['usuario_id'] = $userId;

    // Cerrar conexiones
    $stmt->close();

    // Redirigir al inicio
    header('Location: ../frontend/index.php');
    exit();
}

// Si no hay código, redirigimos a Google para autenticar
$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit();