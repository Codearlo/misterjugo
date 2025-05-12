<?php
// Incluir la conexión a la base de datos
require_once 'conexion.php';

// Obtener los datos del formulario
$productos = isset($_POST['productos']) ? json_decode($_POST['productos'], true) : [];
$nombre = $_POST['nombre'];
$telefono = $_POST['telefono'];
$direccion = $_POST['direccion'];
$total = $_POST['total'];

// Validar que los datos no estén vacíos
if (empty($productos) || empty($nombre) || empty($telefono) || empty($direccion) || empty($total)) {
    die("Error: Datos incompletos.");
}

// Guardar el pedido en la base de datos (opcional)
try {
    // Preparar la consulta SQL para insertar el pedido
    $stmt = $conn->prepare("INSERT INTO pedidos (nombre, telefono, direccion, total, productos) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $telefono, $direccion, $total, json_encode($productos)]);

    // Enviar los datos a WhatsApp
    $mensaje_whatsapp = "Nuevo pedido:\n\n";
    $mensaje_whatsapp .= "Nombre: $nombre\n";
    $mensaje_whatsapp .= "Teléfono: $telefono\n";
    $mensaje_whatsapp .= "Dirección: $direccion\n\n";
    $mensaje_whatsapp .= "Productos:\n";

    foreach ($productos as $producto) {
        $mensaje_whatsapp .= "- {$producto['nombre']} x {$producto['cantidad']} ({$producto['precio']} x {$producto['cantidad']} = ${$producto['precio'] * $producto['cantidad']})\n";
    }

    $mensaje_whatsapp .= "\nTotal: $${$total}";

    // Usar una API de WhatsApp Business para enviar el mensaje
    // Ejemplo usando Twilio (requiere configuración previa)
    require 'vendor/autoload.php';
    use Twilio\Rest\Client;

    $sid = 'TU_TWILIO_ACCOUNT_SID';
    $token = 'TU_TWILIO_AUTH_TOKEN';
    $twilio_number = 'TU_NUMERO_TWILIO';
    $whatsapp_number = 'NUMERO_WHATSAPP_DESTINO';

    $client = new Client($sid, $token);
    $client->messages->create(
        "whatsapp:$whatsapp_number",
        [
            'from' => "whatsapp:$twilio_number",
            'body' => $mensaje_whatsapp
        ]
    );

    // Redirigir al usuario después de procesar el pedido
    header("Location: ../frontend/gracias.php?pedido=exitoso");
    exit();
} catch (Exception $e) {
    die("Error al procesar el pedido: " . $e->getMessage());
}
?>