<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_autenticacion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

    if (empty($carrito)) {
        $_SESSION['mensaje'] = "El carrito de compras está vacío.";
        $_SESSION['tipo_mensaje'] = "warning";
        header("Location: " . BASE_URL . "index.php");
        exit();
    }

    // Capturar datos del formulario de checkout
    $id_usuario_cliente = isset($_POST['id_usuario_cliente']) && !empty($_POST['id_usuario_cliente']) 
        ? intval($_POST['id_usuario_cliente']) 
        : (isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : 0);
        
    $metodo_pago = isset($_POST['metodo_pago']) ? mysqli_real_escape_string($conexion, $_POST['metodo_pago']) : 'EFECTIVO';
    $num_operacion = isset($_POST['num_operacion']) ? mysqli_real_escape_string($conexion, $_POST['num_operacion']) : '';

    if ($id_usuario_cliente <= 0) {
        $_SESSION['mensaje'] = "Debe seleccionar un cliente válido para procesar la compra.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: " . BASE_URL . "modulos/carrito/index.php");
        exit();
    }

    mysqli_begin_transaction($conexion);

    try {
        // 1. Validar Productos, Precios y Stock desde la Base de Datos
        $total_general = 0.00;
        $items_procesar = [];

        foreach ($carrito as $id_libro => $cantidad) {
            $id_libro = (int)$id_libro;
            $cantidad = (int)$cantidad;

            if ($id_libro <= 0 || $cantidad <= 0) continue;

            $res_lib = mysqli_query($conexion, "SELECT id_libro, titulo, precio_venta, stock FROM libros WHERE id_libro = $id_libro LIMIT 1");
            if (!$res_lib || mysqli_num_rows($res_lib) === 0) {
                throw new Exception("Uno de los libros seleccionados ya no existe en el catálogo.");
            }

            $libro = mysqli_fetch_assoc($res_lib);

            if ($libro['stock'] < $cantidad) {
                throw new Exception("Stock insuficiente para '" . $libro['titulo'] . "'. Stock disponible: " . $libro['stock'] . " unidades.");
            }

            $precio_u = (float)$libro['precio_venta'];
            $subtotal_item = $precio_u * $cantidad;
            $total_general += $subtotal_item;

            $items_procesar[] = [
                'id_libro' => $id_libro,
                'titulo' => $libro['titulo'],
                'precio_unitario' => $precio_u,
                'cantidad' => $cantidad,
                'subtotal' => $subtotal_item,
                'stock_actual' => (int)$libro['stock']
            ];
        }

        if (empty($items_procesar)) {
            throw new Exception("No hay productos válidos en el carrito.");
        }

        // Cálculos de Subtotal e IGV (18%)
        $subtotal_general = round($total_general / 1.18, 2);
        $igv_general = round($total_general - $subtotal_general, 2);
        $num_comprobante = 'BOL-' . date('Ymd') . '-' . rand(10000, 99999);

        // 2. Registrar Cabecera de la Venta (Tabla `ventas`)
        $sql_venta = "INSERT INTO ventas (id_usuario, num_comprobante, subtotal, igv, total, metodo_pago, num_operacion, fecha_venta) 
                      VALUES ($id_usuario_cliente, '$num_comprobante', $subtotal_general, $igv_general, $total_general, '$metodo_pago', '$num_operacion', NOW())";
        
        if (!mysqli_query($conexion, $sql_venta)) {
            throw new Exception("Error al registrar la venta: " . mysqli_error($conexion));
        }

        $id_venta = mysqli_insert_id($conexion);

        // 3. Registrar Detalle, Actualizar Inventarios y Registrar Kárdex
        foreach ($items_procesar as $item) {
            $id_libro = $item['id_libro'];
            $cant = $item['cantidad'];
            $pu = $item['precio_unitario'];
            $sub = $item['subtotal'];
            $stk_ant = $item['stock_actual'];
            $stk_act = $stk_ant - $cant;

            // Insertar en `ventas_detalle`
            $sql_det = "INSERT INTO ventas_detalle (id_venta, id_libro, cantidad, precio_unitario, subtotal) 
                        VALUES ($id_venta, $id_libro, $cant, $pu, $sub)";
            if (!mysqli_query($conexion, $sql_det)) {
                throw new Exception("Error al guardar detalle de la venta: " . mysqli_error($conexion));
            }

            // Descontar Stock en `libros`
            $estado_nuevo = $stk_act > 0 ? 'DISPONIBLE' : 'AGOTADO';
            $sql_upd_stk = "UPDATE libros SET stock = $stk_act, estado = '$estado_nuevo' WHERE id_libro = $id_libro";
            if (!mysqli_query($conexion, $sql_upd_stk)) {
                throw new Exception("Error al actualizar el inventario: " . mysqli_error($conexion));
            }

            // Registrar movimiento en `kardex`
            $sql_kardex = "INSERT INTO kardex (id_libro, tipo_movimiento, cantidad, stock_anterior, stock_actual, motivo) 
                           VALUES ($id_libro, 'SALIDA_VENTA', $cant, $stk_ant, $stk_act, 'Venta Carrito Web #$num_comprobante')";
            if (!mysqli_query($conexion, $sql_kardex)) {
                throw new Exception("Error al registrar movimiento en Kárdex: " . mysqli_error($conexion));
            }
        }

        // Confirmar la transacción
        mysqli_commit($conexion);

        // Vaciar la sesión del carrito
        unset($_SESSION['carrito']);

        $_SESSION['mensaje'] = "¡Compra realizada exitosamente! Comprobante registrado N° $num_comprobante";
        $_SESSION['tipo_mensaje'] = "success";

        header("Location: " . BASE_URL . "modulos/ventas/comprobante.php?id=" . $id_venta);
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        $_SESSION['mensaje'] = $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: " . BASE_URL . "modulos/carrito/index.php");
        exit();
    }
} else {
    header("Location: " . BASE_URL . "modulos/carrito/index.php");
    exit();
}