<?php
session_start();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0 && isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $key => $item) {
        if ($item['id'] == $id) {
            unset($_SESSION['carrito'][$key]);
            $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar
            break;
        }
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => true]);