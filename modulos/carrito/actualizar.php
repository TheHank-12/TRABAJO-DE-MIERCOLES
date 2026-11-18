<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_libro = intval($_POST['id_libro']);
    $nueva_cantidad = intval($_POST['cantidad']);

    if ($id_libro > 0 && isset($_SESSION['carrito'][$id_libro])) {
        if ($nueva_cantidad > 0) {
            // Consultar el stock real en la base de datos
            $res = mysqli_query($conexion, "SELECT stock, titulo FROM libros WHERE id_libro = $id_libro LIMIT 1");
            $libro = mysqli_fetch_assoc($res);

            if ($libro) {
                // Validar que la cantidad ingresada no supere el stock en BD
                if ($nueva_cantidad <= $libro['stock']) {
                    $_SESSION['carrito'][$id_libro] = $nueva_cantidad;
                    $_SESSION['mensaje'] = "Cantidad actualizada correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['carrito'][$id_libro] = $libro['stock'];
                    $_SESSION['mensaje'] = "Se ajustó al stock máximo disponible (" . $libro['stock'] . " unidades).";
                    $_SESSION['tipo_mensaje'] = "warning";
                }
            }
        } else {
            // Si colocan 0, se remueve el libro del carrito
            unset($_SESSION['carrito'][$id_libro]);
            $_SESSION['mensaje'] = "El libro fue removido del carrito.";
            $_SESSION['tipo_mensaje'] = "info";
        }
    }

    header("Location: " . BASE_URL . "modulos/carrito/index.php");
    exit();
}