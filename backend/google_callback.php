
<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

session_start();

// Define la ruta base para evitar errores de inclusión
$baseDir = $_SERVER['DOCUMENT_ROOT'] . '/misterjugo/backend';

// Incluir manualmente las clases básicas de Google API
require_once $baseDir . '/google-api/src/Client.php';
require_once $baseDir . '/google-api/src/Http/Request.php';
require_once $baseDir . '/google-api/src/Http/Curl.php';
require_once $baseDir . '/google-api/src/Config.php';
require_once $baseDir . '/google-api/src/Service/Resource.php';
require_once $baseDir . '/google-api/src/Service/ServiceResource.php';
require_once $baseDir . '/google-api/src/Service/Oauth2.php';

// Incluir conexión a la base de datos
require_once $baseDir . '/conexion.php';

// Continúa con tu lógica de Google Sign-In
$client = new Google_Client();
$client->setClientId('TU_CLIENT_ID_AQUÍ');
$client->setClientSecret('TU_CLIENT_SECRET_AQUÍ');
$client->setRedirectUri('https://misterjugo.codearlo.com/backend/google_callback.php');
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    // Obtener token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    // Obtener información del usuario
    $oauth = new Google_Service_Oauth2($client);
    $userInfo = $oauth->userinfo->get();

    // Guardar info básica en sesión
    $_SESSION['nombre'] = $userInfo->name;
    $_SESSION['email'] = $userInfo->email;

    // Conectar a la base de datos
    $conn = conectarDB();

    // Verificar si el usuario ya existe
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
    $conn->close();

    // Redirigir al inicio
    header('Location: ' . $_SERVER['DOCUMENT_ROOT'] . '/misterjugo/frontend/index.php');
    exit();
}

// Si no hay código, redirigimos a Google para autenticar
$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit();