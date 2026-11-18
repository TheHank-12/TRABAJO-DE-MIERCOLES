<?php
require_once __DIR__ . '/../../config/sesion.php';

// Limpiar todas las variables de sesión
session_unset();
session_destroy();

session_start();
$_SESSION['mensaje'] = "Ha cerrado su sesión de manera segura.";
$_SESSION['tipo_mensaje'] = "info";

header("Location: " . BASE_URL . "modulos/auth/login.php");
exit();
?>