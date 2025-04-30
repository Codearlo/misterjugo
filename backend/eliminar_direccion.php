<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit;
}

// Incluir conexión a la base de datos
require_once 'conexion.php';

// Verificar si se recibió un ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $direccion_id = $_GET['id'];
    $user_id =