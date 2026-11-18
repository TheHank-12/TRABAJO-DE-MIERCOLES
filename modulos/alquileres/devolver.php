<?php
$titulo_pagina = "Devolución de Libro";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$id_alquiler = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql_alq = "SELECT a.*, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.dni AS usuario_dni,
                   l.titulo AS libro_titulo, l.isbn AS libro_isbn, l.id_libro
            FROM alquileres a
            INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
            INNER JOIN libros l ON a.id_libro = l.id_libro
            WHERE a.id_alquiler = $id_alquiler LIMIT 1";

$res = mysqli_query($conexion, $sql_alq);

if (!$res || mysqli_num_rows($res) === 0) {
    $_SESSION['mensaje'] = "Alquiler no encontrado.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: " . BASE_URL . "modulos/alquileres/index.php");
    exit();
}

$alquiler = mysqli_fetch_assoc($res);
$error = '';

$fecha_hoy = date('Y-m-d');
$fecha_esperada = $alquiler['fecha_dev_esperada'];

$dias_demora = 0;
$monto_diario_mora = 5.00;
$total_multa = 0.00;

if ($fecha_hoy > $fecha_esperada) {
    $diff = strtotime($fecha_hoy) - strtotime($fecha_esperada);
    $dias_demora = floor($diff / 86400);
    if ($dias_demora > 0) {
        $total_multa = $dias_demora * $monto_diario_mora;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_dev_real = mysqli_real_escape_string($conexion, $_POST['fecha_dev_real']);
    $cobrar_multa_ahora = isset($_POST['cobrar_multa_ahora']) ? true : false;

    $dias_demora_calc = 0;
    if ($fecha_dev_real > $fecha_esperada) {
        $diff = strtotime($fecha_dev_real) - strtotime($fecha_esperada);
        $dias_demora_calc = floor($diff / 86400);
    }

    $monto_multa_final = $dias_demora_calc * $monto_diario_mora;

    mysqli_autocommit($conexion, false);

    // 1. Actualizar Alquiler
    $sql_upd_alq = "UPDATE alquileres SET fecha_dev_real = '$fecha_dev_real', estado = 'Devuelto' WHERE id_alquiler = $id_alquiler";

    // 2. Incrementar Stock
    $id_libro = (int)$alquiler['id_libro'];
    $res_stk = mysqli_query($conexion, "SELECT stock FROM libros WHERE id_libro = $id_libro");
    $stk_val = mysqli_fetch_assoc($res_stk);
    $stock_ant = (int)$stk_val['stock'];
    $stock_act = $stock_ant + 1;

    $sql_upd_stock = "UPDATE libros SET stock = $stock_act, estado = 'DISPONIBLE' WHERE id_libro = $id_libro";

    // 3. Registrar Kárdex (Columnas: stock_anterior, stock_actual)
    $sql_kardex = "INSERT INTO kardex (id_libro, tipo_movimiento, cantidad, stock_anterior, stock_actual, motivo) 
                   VALUES ($id_libro, 'DEVOLUCION', 1, $stock_ant, $stock_act, 'Devolución de alquiler N° $id_alquiler')";

    $ok_multa = true;

    // 4. Registrar Multa (En la tabla multas segun tu DDL exacto)
    if ($dias_demora_calc > 0) {
        $estado_multa = $cobrar_multa_ahora ? 'Pagada' : 'Pendiente';
        $fecha_pago_val = $cobrar_multa_ahora ? "'" . date('Y-m-d H:i:s') . "'" : "NULL";

        $sql_multa = "INSERT INTO multas (id_alquiler, dias_atraso, monto_diario, monto_total, estado, fecha_pago) 
                      VALUES ($id_alquiler, $dias_demora_calc, $monto_diario_mora, $monto_multa_final, '$estado_multa', $fecha_pago_val)";
        
        if (!mysqli_query($conexion, $sql_multa)) {
            $ok_multa = false;
        }
    }

    if (mysqli_query($conexion, $sql_upd_alq) && mysqli_query($conexion, $sql_upd_stock) && mysqli_query($conexion, $sql_kardex) && $ok_multa) {
        mysqli_commit($conexion);
        mysqli_autocommit($conexion, true);

        $_SESSION['mensaje'] = "Devolución procesada correctamente. Ejemplar devuelto al inventario.";
        $_SESSION['tipo_mensaje'] = "success";
        header("Location: " . BASE_URL . "modulos/alquileres/index.php");
        exit();
    } else {
        mysqli_rollback($conexion);
        mysqli_autocommit($conexion, true);
        $error = "Error al procesar devolución: " . mysqli_error($conexion);
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>modulos/alquileres/index.php" class="btn btn-secondary">⬅️ Volver a Alquileres</a>
</div>

<div class="form-card" style="max-width: 650px;">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Procesar Devolución de Libro</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <div style="background: var(--bg-light); padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($alquiler['usuario_nombre'] . ' ' . $alquiler['usuario_apellido']); ?> (DNI: <?php echo htmlspecialchars($alquiler['usuario_dni']); ?>)</p>
        <p><strong>Libro:</strong> <?php echo htmlspecialchars($alquiler['libro_titulo']); ?></p>
        <p><strong>Fecha Inicio:</strong> <?php echo date('d/m/Y', strtotime($alquiler['fecha_inicio'])); ?></p>
        <p><strong>Fecha Programada Devolución:</strong> <strong style="color: var(--primary);"><?php echo date('d/m/Y', strtotime($alquiler['fecha_dev_esperada'])); ?></strong></p>
    </div>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $id_alquiler; ?>" method="POST">
        <div class="form-group">
            <label for="fecha_dev_real">Fecha Real de Devolución (*):</label>
            <input type="date" name="fecha_dev_real" id="fecha_dev_real" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <?php if ($dias_demora > 0): ?>
            <div class="alerta-box alerta-danger" style="margin-bottom: 1.2rem;">
                <div class="alerta-icono">⚠️</div>
                <div class="alerta-contenido">
                    <strong>¡ALERTA DE MOROSIDAD!</strong><br>
                    Este préstamo cuenta con <strong><?php echo $dias_demora; ?> días de atraso</strong>.<br>
                    Multa generada (S/ 5.00/día): <strong style="font-size: 1.2rem;">S/ <?php echo number_format($total_multa, 2); ?></strong>
                </div>
            </div>

            <div class="form-group" style="background: #fff; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: var(--radius);">
                <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="cobrar_multa_ahora" value="1" checked style="width: 18px; height: 18px;">
                    <span>Cobrar y registrar multa de <strong>S/ <?php echo number_format($total_multa, 2); ?></strong> de inmediato</span>
                </label>
            </div>
        <?php else: ?>
            <div class="alerta-box alerta-success" style="margin-bottom: 1.2rem;">
                <div class="alerta-icono">✅</div>
                <div class="alerta-contenido">Devolución dentro del plazo contratado. Sin multas por mora.</div>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-success btn-block" style="margin-top: 1rem;">📥 Recepcionar Libro y Finalizar</button>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>