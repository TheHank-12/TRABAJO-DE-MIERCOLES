<?php
$titulo_pagina = "Gestión de Alquileres";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

// Captura de filtros por fecha y búsqueda
$fecha_desde = isset($_GET['fecha_desde']) ? trim(mysqli_real_escape_string($conexion, $_GET['fecha_desde'])) : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? trim(mysqli_real_escape_string($conexion, $_GET['fecha_hasta'])) : '';
$busqueda = isset($_GET['q']) ? trim(mysqli_real_escape_string($conexion, $_GET['q'])) : '';

$sql_alq = "SELECT a.*, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.dni AS usuario_dni,
                   l.titulo AS libro_titulo, l.isbn AS libro_isbn,
                   m.id_multa, m.monto_total AS monto_multa, m.estado AS estado_multa
            FROM alquileres a
            INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
            INNER JOIN libros l ON a.id_libro = l.id_libro
            LEFT JOIN multas m ON a.id_alquiler = m.id_alquiler
            WHERE 1=1";

if (!empty($fecha_desde)) {
    $sql_alq .= " AND a.fecha_inicio >= '$fecha_desde'";
}

if (!empty($fecha_hasta)) {
    $sql_alq .= " AND a.fecha_inicio <= '$fecha_hasta'";
}

if (!empty($busqueda)) {
    $sql_alq .= " AND (u.dni LIKE '%$busqueda%' OR u.nombre LIKE '%$busqueda%' OR u.apellido LIKE '%$busqueda%' OR l.titulo LIKE '%$busqueda%')";
}

$sql_alq .= " ORDER BY a.id_alquiler DESC";
$res_alq = mysqli_query($conexion, $sql_alq);

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>📖 Control de Alquileres de Libros</h2>
    <div style="display: flex; gap: 10px;">
        <a href="<?php echo BASE_URL; ?>modulos/alquileres/multas.php" class="btn btn-warning">⚠️ Gestión de Multas</a>
        <a href="<?php echo BASE_URL; ?>modulos/alquileres/crear.php" class="btn btn-success">➕ Registrar Nuevo Alquiler</a>
    </div>
</div>

<!-- Filtro por Rango de Fechas y Búsqueda -->
<div class="form-card" style="max-width: 100%; padding: 1.2rem; margin-bottom: 1.5rem;">
    <form action="index.php" method="GET" style="display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr; gap: 1rem; align-items: end;">
        <div class="form-group" style="margin-bottom: 0;">
            <label for="q">Buscar Cliente / Libro:</label>
            <input type="text" name="q" id="q" class="form-control" placeholder="DNI, cliente o libro..." value="<?php echo htmlspecialchars($busqueda); ?>">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="fecha_desde">Desde:</label>
            <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" value="<?php echo htmlspecialchars($fecha_desde); ?>">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="fecha_hasta">Hasta:</label>
            <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
        </div>

        <div style="display: flex; gap: 5px;">
            <button type="submit" class="btn btn-primary btn-block">🔍 Filtrar</button>
            <?php if (!empty($fecha_desde) || !empty($fecha_hasta) || !empty($busqueda)): ?>
                <a href="index.php" class="btn btn-danger" title="Limpiar Filtros">✖</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente / DNI</th>
                <th>Libro Prestado</th>
                <th>F. Inicio</th>
                <th>F. Dev. Esperada</th>
                <th>F. Dev. Real</th>
                <th>Monto Alquiler</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($res_alq) === 0): ?>
                <tr>
                    <td colspan="9" style="text-align: center;">No se registraron alquileres con los criterios seleccionados.</td>
                </tr>
            <?php else: ?>
                <?php while ($a = mysqli_fetch_assoc($res_alq)): ?>
                    <tr>
                        <td><?php echo $a['id_alquiler']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($a['usuario_nombre'] . ' ' . $a['usuario_apellido']); ?></strong><br>
                            <small style="color: var(--text-muted);">DNI: <?php echo htmlspecialchars($a['usuario_dni']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($a['libro_titulo']); ?></strong><br>
                            <small style="color: var(--text-muted);">ISBN: <?php echo htmlspecialchars($a['libro_isbn']); ?></small>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($a['fecha_inicio'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($a['fecha_dev_esperada'])); ?></td>
                        <td><?php echo !empty($a['fecha_dev_real']) ? date('d/m/Y', strtotime($a['fecha_dev_real'])) : '-'; ?></td>
                        <td>S/ <?php echo number_format($a['monto_alquiler'], 2); ?></td>
                        <td>
                            <?php 
                            if ($a['estado'] === 'En Alquiler') {
                                // Verificar si está vencido pero aún no devuelto
                                if (date('Y-m-d') > $a['fecha_dev_esperada']) {
                                    echo '<span style="background-color: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">Con Demora</span>';
                                } else {
                                    echo '<span style="background-color: #cce5ff; color: #004085; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">En Alquiler</span>';
                                }
                            } else {
                                echo '<span style="background-color: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">Devuelto</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 4px; align-items: center;">
                                <?php if ($a['estado'] === 'En Alquiler'): ?>
                                    <a href="<?php echo BASE_URL; ?>modulos/alquileres/devolver.php?id=<?php echo $a['id_alquiler']; ?>" class="btn btn-success btn-sm" title="Procesar Devolución">📥 Devolver</a>
                                <?php else: ?>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">Finalizado</span>
                                <?php endif; ?>

                                <?php if (!empty($a['id_multa'])): ?>
                                    <a href="<?php echo BASE_URL; ?>modulos/alquileres/multas.php" class="btn btn-danger btn-sm" title="Ver Multa (S/ <?php echo number_format($a['monto_multa'], 2); ?>)">⚠️ Multa</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>