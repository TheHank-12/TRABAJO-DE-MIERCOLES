<?php
$titulo_pagina = "Registrar Movimiento de Kárdex";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$error = '';
$libros = mysqli_query($conexion, "SELECT id_libro, titulo, stock, isbn FROM libros ORDER BY titulo ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_libro = intval($_POST['id_libro']);
    $tipo_movimiento = trim(mysqli_real_escape_string($conexion, $_POST['tipo_movimiento']));
    $cantidad = intval($_POST['cantidad']);
    $motivo = trim(mysqli_real_escape_string($conexion, $_POST['motivo']));

    if ($id_libro === 0 || empty($tipo_movimiento) || $cantidad <= 0) {
        $error = "Seleccione un libro, tipo de movimiento y una cantidad válida mayor a cero.";
    } else {
        // Consultar stock actual del libro
        $res_lib = mysqli_query($conexion, "SELECT stock, titulo FROM libros WHERE id_libro = $id_libro LIMIT 1");
        if ($res_lib && mysqli_num_rows($res_lib) === 1) {
            $libro_data = mysqli_fetch_assoc($res_lib);
            $stock_previo = (int)$libro_data['stock'];

            if (strpos($tipo_movimiento, 'SALIDA') !== false && $cantidad > $stock_previo) {
                $error = "No se puede realizar una salida de $cantidad unidades. El stock actual es de sólo $stock_previo unidades.";
            } else {
                $stock_nuevo = $stock_previo;
                if ($tipo_movimiento === 'ENTRADA' || $tipo_movimiento === 'DEVOLUCION') {
                    $stock_nuevo = $stock_previo + $cantidad;
                } else if (strpos($tipo_movimiento, 'SALIDA') !== false) {
                    $stock_nuevo = $stock_previo - $cantidad;
                } else if ($tipo_movimiento === 'AJUSTE') {
                    $stock_nuevo = $cantidad; // Ajuste directo al valor ingresado
                }

                // Iniciar transacción procedimental
                mysqli_autocommit($conexion, false);

                $sql_kardex = "INSERT INTO kardex (id_libro, tipo_movimiento, cantidad, stock_anterior, stock_actual, motivo) 
                               VALUES ($id_libro, '$tipo_movimiento', $cantidad, $stock_previo, $stock_nuevo, '$motivo')";
                
                $sql_stock = "UPDATE libros SET stock = $stock_nuevo, estado = " . ($stock_nuevo > 0 ? "'DISPONIBLE'" : "'AGOTADO'") . " WHERE id_libro = $id_libro";

                if (mysqli_query($conexion, $sql_kardex) && mysqli_query($conexion, $sql_stock)) {
                    mysqli_commit($conexion);
                    mysqli_autocommit($conexion, true);

                    $_SESSION['mensaje'] = "Movimiento de Kárdex registrado y stock actualizado a $stock_nuevo unidades.";
                    $_SESSION['tipo_mensaje'] = "success";
                    header("Location: " . BASE_URL . "modulos/kardex/index.php");
                    exit();
                } else {
                    mysqli_rollback($conexion);
                    mysqli_autocommit($conexion, true);
                    $error = "Error al procesar el movimiento: " . mysqli_error($conexion);
                }
            }
        } else {
            $error = "El libro seleccionado no existe.";
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>modulos/kardex/index.php" class="btn btn-secondary">⬅️ Volver al Kárdex</a>
</div>

<div class="form-card">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Registrar Movimiento de Inventario</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <div class="form-group">
            <label for="id_libro">Seleccionar Libro (*):</label>
            <select name="id_libro" id="id_libro" class="form-control" required>
                <option value="">-- Seleccionar --</option>
                <?php while ($l = mysqli_fetch_assoc($libros)): ?>
                    <option value="<?php echo $l['id_libro']; ?>">
                        <?php echo htmlspecialchars($l['titulo']); ?> (Stock Actual: <?php echo $l['stock']; ?> un.)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="tipo_movimiento">Tipo de Movimiento (*):</label>
                <select name="tipo_movimiento" id="tipo_movimiento" class="form-control" required>
                    <option value="ENTRADA">ENTRADA (Ingreso de lote / Compra)</option>
                    <option value="SALIDA_VENTA">SALIDA VENTA (Despacho)</option>
                    <option value="SALIDA_ALQUILER">SALIDA ALQUILER (Préstamo)</option>
                    <option value="DEVOLUCION">DEVOLUCION (Retorno de Alquiler)</option>
                    <option value="AJUSTE">AJUSTE (Corrección de inventario)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="cantidad">Cantidad de Unidades (*):</label>
                <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" value="1" required>
            </div>
        </div>

        <div class="form-group">
            <label for="motivo">Motivo / Justificación:</label>
            <textarea name="motivo" id="motivo" class="form-control" rows="3" placeholder="Ej. Recepción de factura F001-445 / Ajuste por deterioro de ejemplar..." required></textarea>
        </div>

        <button type="submit" class="btn btn-success btn-block" style="margin-top: 1rem;">💾 Confirmar y Registrar Movimiento</button>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>