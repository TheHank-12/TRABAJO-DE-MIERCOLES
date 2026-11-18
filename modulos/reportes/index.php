<?php
$titulo_pagina = "Gestión de Multas";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

// Procesar Cobro Directo de Multa
if (isset($_GET['cobrar_id'])) {
    $id_multa_cobrar = intval($_GET['cobrar_id']);

    $res_m = mysqli_query($conexion, "SELECT m.*, a.id_alquiler FROM multas m INNER JOIN alquileres a ON m.id_alquiler = a.id_alquiler WHERE m.id_multa = $id_multa_cobrar LIMIT 1");
    if ($res_m && mysqli_num_rows($res_m) === 1) {
        $m_data = mysqli_fetch_assoc($res_m);
        $id_alq = (int)$m_data['id_alquiler'];

        mysqli_autocommit($conexion, false);

        $upd_m = "UPDATE multas SET estado = 'Pagada', fecha_pago = '" . date('Y-m-d H:i:s') . "' WHERE id_multa = $id_multa_cobrar";
        $upd_a = "UPDATE alquileres SET estado = 'Devuelto' WHERE id_alquiler = $id_alq";

        if (mysqli_query($conexion, $upd_m) && mysqli_query($conexion, $upd_a)) {
            mysqli_commit($conexion);
            mysqli_autocommit($conexion, true);
            $_SESSION['mensaje'] = "Multa cobrada exitosamente. Estado de cuenta actualizado.";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            mysqli_rollback($conexion);
            mysqli_autocommit($conexion, true);
            $_SESSION['mensaje'] = "Error al cobrar multa: " . mysqli_error($conexion);
            $_SESSION['tipo_mensaje'] = "danger";
        }
    }
    header("Location: " . BASE_URL . "modulos/alquileres/multas.php");
    exit();
}

// Consultar todas las multas
$sql_multas = "SELECT m.*, a.fecha_dev_esperada, a.fecha_dev_real, 
                      u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.dni AS usuario_dni,
                      l.titulo AS libro_titulo
               FROM multas m
               INNER JOIN alquileres a ON m.id_alquiler = a.id_alquiler
               INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
               INNER JOIN libros l ON a.id_libro = l.id_libro
               ORDER BY m.id_multa DESC";

$res_multas = mysqli_query($conexion, $sql_multas);

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>⚠️ Control de Multas por Morosidad</h2>
    <a href="<?php echo BASE_URL; ?>modulos/alquileres/index.php" class="btn btn-secondary">⬅️ Volver a Alquileres</a>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>ID Multa</th>
                <th>Cliente / DNI</th>
                <th>Libro en Morosidad</th>
                <th>Días Retraso</th>
                <th>Tarifa Diaria</th>
                <th>Total Multa S/</th>
                <th>Estado</th>
                <th>Fecha Pago</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($res_multas) === 0): ?>
                <tr>
                    <td colspan="9" style="text-align: center;">No hay multas registradas en el sistema.</td>
                </tr>
            <?php else: ?>
                <?php while ($m = mysqli_fetch_assoc($res_multas)): ?>
                    <tr>
                        <td>#<?php echo $m['id_multa']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($m['usuario_nombre'] . ' ' . $m['usuario_apellido']); ?></strong><br>
                            <small style="color: var(--text-muted);">DNI: <?php echo htmlspecialchars($m['usuario_dni']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($m['libro_titulo']); ?></td>
                        <td><strong><?php echo $m['dias_atraso']; ?> días</strong></td>
                        <td>S/ <?php echo number_format($m['monto_diario'], 2); ?></td>
                        <td><strong style="color: var(--danger); font-size: 1.05rem;">S/ <?php echo number_format($m['monto_total'], 2); ?></strong></td>
                        <td>
                            <?php if ($m['estado'] === 'Pendiente'): ?>
                                <span style="background-color: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">PENDIENTE</span>
                            <?php else: ?>
                                <span style="background-color: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">PAGADA</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $m['fecha_pago'] ? date('d/m/Y H:i', strtotime($m['fecha_pago'])) : '-'; ?></td>
                        <td>
                            <?php if ($m['estado'] === 'Pendiente'): ?>
                                <button type="button" class="btn btn-success btn-sm" onclick="confirmarAccion('<?php echo BASE_URL; ?>modulos/alquileres/multas.php?cobrar_id=<?php echo $m['id_multa']; ?>', '¿Confirmar cobro de multa por S/ <?php echo number_format($m['monto_total'], 2); ?>?');">💰 Cobrar</button>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.8rem;">Saldado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="modal-confirmacion">
    <div class="modal-content">
        <h3 style="margin-bottom: 1rem; color: var(--primary);">Confirmar Cobro</h3>
        <p id="modal-texto-confirmacion">¿Desea registrar el cobro de esta multa?</p>
        <div class="modal-acciones">
            <button type="button" class="btn btn-secondary" onclick="cerrarModal();">Cancelar</button>
            <a id="modal-btn-aceptar" href="#" class="btn btn-success">Procesar Cobro</a>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>