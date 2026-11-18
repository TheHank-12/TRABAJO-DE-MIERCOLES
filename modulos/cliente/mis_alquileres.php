<?php
$titulo_pagina = "Mis Alquileres";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_autenticacion();

$id_usuario_logueado = (int)$_SESSION['usuario_id'];

$sql_mis_alq = "SELECT a.*, l.titulo AS libro_titulo, l.isbn AS libro_isbn, l.portada,
                       m.monto_total AS monto_multa, m.estado AS estado_multa
                FROM alquileres a
                INNER JOIN libros l ON a.id_libro = l.id_libro
                LEFT JOIN multas m ON a.id_alquiler = m.id_alquiler
                WHERE a.id_usuario = $id_usuario_logueado
                ORDER BY a.id_alquiler DESC";

$res_mis_alq = mysqli_query($conexion, $sql_mis_alq);

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <h2>📚 Mis Alquileres de Libros</h2>
    <p style="color: var(--text-muted);">Consulte el estado de sus préstamos activos y devoluciones realizadas.</p>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>Portada</th>
                <th>Libro Alquilado</th>
                <th>Fecha Inicio</th>
                <th>Fecha Límite Devolución</th>
                <th>Monto Alquiler</th>
                <th>Estado</th>
                <th>Multa por Mora</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($res_mis_alq) === 0): ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Usted no posee alquileres registrados actualmente.</td>
                </tr>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($res_mis_alq)): ?>
                    <tr>
                        <td style="width: 50px;">
                            <img src="<?php echo BASE_URL . htmlspecialchars($row['portada']); ?>" alt="Portada" style="width: 40px; height: 55px; object-fit: cover; border-radius: 4px;" onerror="this.src='<?php echo BASE_URL; ?>img/no-cover.png'">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['libro_titulo']); ?></strong><br>
                            <small style="color: var(--text-muted);">ISBN: <?php echo htmlspecialchars($row['libro_isbn']); ?></small>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($row['fecha_inicio'])); ?></td>
                        <td><strong style="color: var(--primary);"><?php echo date('d/m/Y', strtotime($row['fecha_dev_esperada'])); ?></strong></td>
                        <td>S/ <?php echo number_format($row['monto_alquiler'], 2); ?></td>
                        <td>
                            <?php 
                            if ($row['estado'] === 'En Alquiler') {
                                echo '<span style="background-color: #cce5ff; color: #004085; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">En Alquiler</span>';
                            } else if ($row['estado'] === 'Devuelto') {
                                echo '<span style="background-color: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">Devuelto</span>';
                            } else {
                                echo '<span style="background-color: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">Con Demora</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if (!empty($row['monto_multa'])): ?>
                                <span style="color: var(--danger); font-weight: 700;">S/ <?php echo number_format($row['monto_multa'], 2); ?></span>
                                <small style="display: block; font-size: 0.75rem; color: var(--text-muted);">(<?php echo $row['estado_multa']; ?>)</small>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>