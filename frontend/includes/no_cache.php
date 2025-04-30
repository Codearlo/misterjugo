<?php
// Establecer encabezados para prevenir el caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Establecer un parámetro aleatorio para forzar recarga
$cache_buster = "?v=" . time();
?>