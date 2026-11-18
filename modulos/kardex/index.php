<?php
$titulo_pagina = "Kárdex de Inventario";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$filtro_libro = isset($_GET['id_libro']) ? intval($_GET['id_libro']) : 0;
$filtro_tipo = isset($_GET['tipo']) ? trim(mysqli_real_escape_string($conexion, $_GET['tipo'])) : '';

// Consulta de libros para el selector de filtro
$libros_res = mysqli_query($conexion, "SELECT id_libro, titulo, isbn FROM libros ORDER BY titulo ASC");

// Consulta del historial de Kárdex
$sql_kardex = "SELECT k.*, l.titulo AS libro_titulo, l.isbn 
               FROM kardex k 
               INNER JOIN libros l ON k.id_libro = l.id_libro 
               WHERE 1=1";

if ($filtro_libro > 0) {
    $sql_kardex .= " AND k.id_libro = $filtro_libro";
}

if (!empty($filtro_tipo)) {
    $sql_kardex .= " AND k.tipo_movimiento = '$filtro_tipo'";
}

$sql_kardex .= " ORDER BY k.fecha_movimiento DESC, k.id_kardex DESC";
$res_kardex = mysqli_query($conexion, $sql_kardex);

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>📦 Kárdex - Historial de Entradas y Salidas</h2>
    <a href="<?php echo BASE_URL; ?>modulos/kardex/registrar.php" class="btn btn-success">➕ Registrar Movimiento Manual</a>
</div>

<!-- Filtros de Kárdex -->
<div class="form-card" style="max-width: 100%; padding: 1.2rem; margin-bottom: 1.5rem;">
    <form action="index.php" method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem; align-items: end;">
        <div class="form-group" style="margin-bottom: 0;">
            <label for="id_libro">Filtrar por Libro:</label>
            <select name="id_libro" id="id_libro" class="form-control">
                <option value="0">-- Todos los Libros --</option>
                <?php while ($l = mysqli_fetch_assoc($libros_res)): ?>
                    <option value="<?php echo $l['id_libro']; ?>" <?php echo $filtro_libro === (int)$l['id_libro'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($l['titulo']); ?> (ISBN: <?php echo htmlspecialchars($l['isbn']); ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="tipo">Tipo de Movimiento:</label>
            <select name="tipo" id="tipo" class="form-control">
                <option value="">-- Todos los Tipos --</option>
                <option value="ENTRADA" <?php echo $filtro_tipo === 'ENTRADA' ? 'selected' : ''; ?>>ENTRADA</option>
                <option value="SALIDA_VENTA" <?php echo $filtro_tipo === 'SALIDA_VENTA' ? 'selected' : ''; ?>>SALIDA VENTA</option>
                <option value="SALIDA_ALQUILER" <?php echo $filtro_tipo === 'SALIDA_ALQUILER' ? 'selected' : ''; ?>>SALIDA ALQUILER</option>
                <option value="DEVOLUCION" <?php echo $filtro_tipo === 'DEVOLUCION' ? 'selected' : ''; ?>>DEVOLUCION</option>
                <option value="AJUSTE" <?php echo $filtro_tipo === 'AJUSTE' ? 'selected' : ''; ?>>AJUSTE</option>
            </select>
        </div>

        <div style="display: flex; gap: 5px;">
            <button type="submit" class="btn btn-primary btn-block">🔍 Filtrar</button>
            <?php if ($filtro_libro > 0 || !empty($filtro_tipo)): ?>
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
                <th>Fecha y Hora</th>
                <th>Libro / ISBN</th>
                <th>Tipo Movimiento</th>
                <th>Cantidad</th>
                <th>Stock Previo</th>
                <th>Stock Nuevo</th>
                <th>Motivo / Observación</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($res_kardex) === 0): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No se encontraron registros en el Kárdex con los filtros aplicados.</td>
                </tr>
            <?php else: ?>
                <?php while ($k = mysqli_fetch_assoc($res_kardex)): ?>
                    <tr>
                        <td><?php echo $k['id_kardex']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($k['fecha_movimiento'])); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($k['libro_titulo']); ?></strong><br>
                            <small style="color: var(--text-muted);">ISBN: <?php echo htmlspecialchars($k['isbn']); ?></small>
                        </td>
                        <td>
                            <?php 
                            $badge_class = 'badge-client';
                            if (strpos($k['tipo_movimiento'], 'SALIDA') !== false) {
                                $badge_class = 'badge-admin';
                            } else if ($k['tipo_movimiento'] === 'ENTRADA' || $k['tipo_movimiento'] === 'DEVOLUCION') {
                                $badge_class = 'badge-client';
                            }
                            ?>
                            <span class="user-role-badge <?php echo $badge_class; ?>">
                                <?php echo htmlspecialchars($k['tipo_movimiento']); ?>
                            </span>
                        </td>
                        <td><strong><?php echo $k['cantidad']; ?> un.</strong></td>
                        <td><?php echo $k['stock_anterior']; ?></td>
                        <td><strong style="color: var(--primary);"><?php echo $k['stock_actual']; ?></strong></td>
                        <td><?php echo htmlspecialchars($k['motivo'] ? $k['motivo'] : 'Sin observación'); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>