<?php
session_start();

// Recibir producto desde JS
$producto = json_decode(file_get_contents('php://input'), true);

if (!$producto || !isset($producto['id'], $producto['nombre'], $producto['precio'])) {
    echo json_encode(['success' => false]);
    exit;
}

$id = intval($producto['id']);
$cantidad = isset($producto['cantidad']) ? intval($producto['cantidad']) : 1;

if ($id <= 0 || $cantidad <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

// Iniciar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Buscar si ya existe el producto
$found = false;
foreach ($_SESSION['carrito'] as &$item) {
    if ($item['id'] == $id) {
        $item['cantidad'] += $cantidad;
        $found = true;
        break;
    }
}

if (!$found) {
    $producto['cantidad'] = $cantidad;
    $_SESSION['carrito'][] = $producto;
}

echo json_encode(['success' => true]);