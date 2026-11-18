<?php

$host = "localhost";
$usuario = "root";
$password = "usbw";
$base_datos = "bd_libreria";
$puerto = 3306;

$conexion = mysqli_connect($host, $usuario, $password, $base_datos, $puerto);

if (!$conexion) {
    die("<div style='font-family:sans-serif; padding:20px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:8px;'>
            <h3>Error de Conexión a la Base de Datos</h3>
            <p>No se pudo conectar a MySQL procedimental: " . mysqli_connect_error() . "</p>
         </div>");
}

mysqli_set_charset($conexion, "utf8mb4");
date_default_timezone_set("America/Lima");
?>