<?php
session_start();

$carrito = json_decode(file_get_contents('php://input'), true);

if (!$carrito || !is_array($carrito)) {
    echo json_encode(['success' => false]);
    exit;
}

// Validar y limpiar los datos del carrito
$carritoLimpio = [];
foreach ($carrito as $item) {
    if (isset($item['id'], $item['nombre'], $item['precio'], $item['cantidad'])) {
        $carritoLimpio[] = [
            'id' => $item['id'],
            'nombre' => $item['nombre'],
            'precio' => floatval($item['precio']),
            'imagen' => $item['imagen'] ?? '/images/producto-default.jpg',
            'cantidad' => intval($item['cantidad'])
        ];
    }
}

$_SESSION['carrito'] = $carritoLimpio;

echo json_encode(['success' => true]);