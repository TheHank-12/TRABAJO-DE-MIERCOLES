<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

inicializar_carrito();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_libro = isset($_POST['id_libro']) ? intval($_POST['id_libro']) : 0;
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;

    if ($id_libro > 0 && $cantidad > 0) {
        $res = mysqli_query($conexion, "SELECT id_libro, titulo, isbn, precio_venta, stock, portada FROM libros WHERE id_libro = $id_libro AND estado = 'DISPONIBLE' LIMIT 1");

        if ($res && mysqli_num_rows($res) === 1) {
            $libro = mysqli_fetch_assoc($res);

            // Verificar si el producto ya está en el carrito
            $cant_actual_carrito = isset($_SESSION['carrito'][$id_libro]) ? $_SESSION['carrito'][$id_libro]['cantidad'] : 0;
            $cant_total_deseada = $cant_actual_carrito + $cantidad;

            if ($cant_total_deseada > (int)$libro['stock']) {
                $_SESSION['mensaje'] = "No hay suficiente stock para agregar " . $cant_total_deseada . " unidades de '" . $libro['titulo'] . "'. Stock máximo: " . $libro['stock'];
                $_SESSION['tipo_mensaje'] = "warning";
            } else {
                $_SESSION['carrito'][$id_libro] = [
                    'id_libro' => $libro['id_libro'],
                    'titulo' => $libro['titulo'],
                    'isbn' => $libro['isbn'],
                    'precio' => (float)$libro['precio_venta'],
                    'portada' => $libro['portada'],
                    'cantidad' => $cant_total_deseada
                ];

                $_SESSION['mensaje'] = "¡'" . $libro['titulo'] . "' añadido al carrito de compras!";
                $_SESSION['tipo_mensaje'] = "success";
            }
        } else {
            $_SESSION['mensaje'] = "El libro seleccionado no está disponible.";
            $_SESSION['tipo_mensaje'] = "danger";
        }
    }
}

header("Location: " . BASE_URL . "modulos/carrito/index.php");
exit();
?>