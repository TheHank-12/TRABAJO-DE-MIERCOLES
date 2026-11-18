<?php
$titulo_pagina = "Mis Multas";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_autenticacion();

$id_usuario_logueado = (int)$_SESSION['usuario_id'];

$sql_mis_multas = "SELECT m.*, l.titulo AS libro_titulo, a.fecha_dev_esperada, a.fecha_dev_real
                   FROM multas m
                   INNER JOIN alquileres a ON m.id_alquiler = a.id_alquiler
                   INNER JOIN libros l ON a.id_libro = l.id_libro
                   WHERE a.id_usuario = $id_usuario_logueado
                   ORDER BY m.id_multa DESC";

$res_mis_multas = mysqli_query($conexion, $sql_mis_multas);

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <h2>⚠️ Estado de Cuenta y Multas por Morosidad</h2>
    <p style="color: var(--text-muted);">Consulte el estado de sus recargos acumulados por devoluciones fuera de fecha.</p>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>ID Multa</th>
                <th>Libro</th>
                <th>Fecha Límite</th>
                <th>Días de Atraso</th>
                <th>Tarifa Diaria</th>
                <th>Monto Total Multa</th>
                <th>Estado</th>
                <th>Fecha Pago</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($res_mis_multas) === 0): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">¡Felicitaciones! No registra multas ni morosidades pendientes en su cuenta.</td>
                </tr>
            <?php else: ?>
                <?php while ($m = mysqli_fetch_assoc($res_mis_multas)): ?>
                    <tr>
                        <td>#<?php echo $m['id_multa']; ?></td>
                        <td><strong><?php echo htmlspecialchars($m['libro_titulo']); ?></strong></td>
                        <td><?php echo date('d/m/Y', strtotime($m['fecha_dev_esperada'])); ?></td>
                        <td><strong style="color: var(--danger);"><?php echo $m['dias_atraso']; ?> días</strong></td>
                        <td>S/ <?php echo number_format($m['monto_diario'], 2); ?></td>
                        <td><strong style="font-size: 1.05rem; color: var(--danger);">S/ <?php echo number_format($m['monto_total'], 2); ?></strong></td>
                        <td>
                            <?php if ($m['estado'] === 'Pendiente'): ?>
                                <span style="background-color: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">PENDIENTE DE PAGO</span>
                            <?php else: ?>
                                <span style="background-color: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">PAGADA / SALDADA</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $m['fecha_pago'] ? date('d/m/Y H:i', strtotime($m['fecha_pago'])) : '-'; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>