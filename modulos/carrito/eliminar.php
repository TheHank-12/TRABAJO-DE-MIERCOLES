<?php
require_once __DIR__ . '/../../config/sesion.php';

inicializar_carrito();

// Opción para vaciar todo el carrito
if (isset($_GET['vaciar']) && $_GET['vaciar'] == '1') {
    $_SESSION['carrito'] = [];
    $_SESSION['mensaje'] = "Se ha vaciado todo el carrito de compras.";
    $_SESSION['tipo_mensaje'] = "info";
    header("Location: " . BASE_URL . "modulos/carrito/index.php");
    exit();
}

// Eliminar un producto específico
$id_libro = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_libro > 0 && isset($_SESSION['carrito'][$id_libro])) {
    $titulo = $_SESSION['carrito'][$id_libro]['titulo'];
    unset($_SESSION['carrito'][$id_libro]);
    $_SESSION['mensaje'] = "Se quitó '" . $titulo . "' del carrito.";
    $_SESSION['tipo_mensaje'] = "info";
}

header("Location: " . BASE_URL . "modulos/carrito/index.php");
exit();
?>