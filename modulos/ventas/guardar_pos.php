<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';
requerir_personal();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_cliente = isset($_POST['tipo_cliente']) ? $_POST['tipo_cliente'] : 'EXISTENTE';
    $id_libro = intval($_POST['id_libro']);
    $cantidad = intval($_POST['cantidad']);
    $metodo_pago = mysqli_real_escape_string($conexion, $_POST['metodo_pago']);
    $num_operacion = isset($_POST['num_operacion']) ? mysqli_real_escape_string($conexion, $_POST['num_operacion']) : '';

    mysqli_begin_transaction($conexion);

    try {
        // A) GESTIÓN DEL CLIENTE
        if ($tipo_cliente === 'NUEVO') {
            $nuevo_dni = trim(mysqli_real_escape_string($conexion, $_POST['nuevo_dni']));
            $nuevo_nombre = trim(mysqli_real_escape_string($conexion, $_POST['nuevo_nombre']));
            $nuevo_apellido = trim(mysqli_real_escape_string($conexion, $_POST['nuevo_apellido']));
            $nuevo_telefono = isset($_POST['nuevo_telefono']) ? trim(mysqli_real_escape_string($conexion, $_POST['nuevo_telefono'])) : '';
            $email_generado = $nuevo_dni . "@cliente.com";
            $password_default = password_hash($nuevo_dni, PASSWORD_DEFAULT);

            // Verificar si el DNI ya existe
            $check_dni = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE dni = '$nuevo_dni'");
            if (mysqli_num_rows($check_dni) > 0) {
                $user_existing = mysqli_fetch_assoc($check_dni);
                $id_cliente = $user_existing['id_usuario'];
            } else {
                // Inserción asignando id_rol = 2 (Cliente)
                $sql_ins_u = "INSERT INTO usuarios (id_rol, dni, nombre, apellido, email, password, telefono, estado) 
                              VALUES (2, '$nuevo_dni', '$nuevo_nombre', '$nuevo_apellido', '$email_generado', '$password_default', '$nuevo_telefono', 'ACTIVO')";
                if (!mysqli_query($conexion, $sql_ins_u)) {
                    throw new Exception("Error al registrar cliente: " . mysqli_error($conexion));
                }
                $id_cliente = mysqli_insert_id($conexion);
            }
        } else {
            $id_cliente = intval($_POST['id_usuario_cliente']);
        }

        // B) VERIFICACIÓN DE LIBRO Y STOCK
        $res_libro = mysqli_query($conexion, "SELECT stock, precio_venta, titulo FROM libros WHERE id_libro = $id_libro");
        $libro = mysqli_fetch_assoc($res_libro);

        if (!$libro || $libro['stock'] < $cantidad) {
            throw new Exception("Stock insuficiente para realizar la venta.");
        }

        // C) CÁLCULOS DE VENTA (SUBTOTAL, IGV Y TOTAL)
        $precio_u = (float)$libro['precio_venta'];
        $total = $precio_u * $cantidad;
        $subtotal = round($total / 1.18, 2);
        $igv = round($total - $subtotal, 2);
        $num_comprobante = 'BOL-' . date('Ymd') . '-' . rand(1000, 9999);

        // Cabecera de Venta
        $sql_v = "INSERT INTO ventas (id_usuario, num_comprobante, subtotal, igv, total, metodo_pago, num_operacion, fecha_venta) 
                  VALUES ($id_cliente, '$num_comprobante', $subtotal, $igv, $total, '$metodo_pago', '$num_operacion', NOW())";
        if (!mysqli_query($conexion, $sql_v)) {
            throw new Exception("Error al registrar la venta: " . mysqli_error($conexion));
        }
        $id_venta = mysqli_insert_id($conexion);

        // D) DETALLE DE VENTA (Tabla: ventas_detalle)
        $sql_d = "INSERT INTO ventas_detalle (id_venta, id_libro, cantidad, precio_unitario, subtotal) 
                  VALUES ($id_venta, $id_libro, $cantidad, $precio_u, $total)";
        if (!mysqli_query($conexion, $sql_d)) {
            throw new Exception("Error al registrar detalle de venta: " . mysqli_error($conexion));
        }

        // E) ACTUALIZACIÓN DE STOCK Y KÁRDEX
        $stock_ant = (int)$libro['stock'];
        $stock_act = $stock_ant - $cantidad;

        $sql_upd_stk = "UPDATE libros SET stock = $stock_act, estado = " . ($stock_act > 0 ? "'DISPONIBLE'" : "'AGOTADO'") . " WHERE id_libro = $id_libro";
        mysqli_query($conexion, $sql_upd_stk);

        $sql_k = "INSERT INTO kardex (id_libro, tipo_movimiento, cantidad, stock_anterior, stock_actual, motivo) 
                  VALUES ($id_libro, 'SALIDA_VENTA', $cantidad, $stock_ant, $stock_act, 'Venta Mostrador #$num_comprobante')";
        mysqli_query($conexion, $sql_k);

        mysqli_commit($conexion);

        header("Location: " . BASE_URL . "modulos/ventas/comprobante.php?id=" . $id_venta);
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        $_SESSION['mensaje'] = $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: " . BASE_URL . "modulos/ventas/crear.php");
        exit();
    }
}