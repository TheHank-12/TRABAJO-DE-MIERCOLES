<?php
$titulo_pagina = "Historial de Ventas";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$busqueda = isset($_GET['q']) ? trim(mysqli_real_escape_string($conexion, $_GET['q'])) : '';

$sql_ventas = "SELECT v.*, u.nombre, u.apellido, u.dni, u.email 
               FROM ventas v 
               INNER JOIN usuarios u ON v.id_usuario = u.id_usuario 
               WHERE 1=1";

if (!empty($busqueda)) {
    $sql_ventas .= " AND (v.num_comprobante LIKE '%$busqueda%' OR u.dni LIKE '%$busqueda%' OR u.nombre LIKE '%$busqueda%' OR u.apellido LIKE '%$busqueda%')";
}

$sql_ventas .= " ORDER BY v.fecha_venta DESC, v.id_venta DESC";
$res_ventas = mysqli_query($conexion, $sql_ventas);

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>💵 Regístro e Histórico de Ventas</h2>
    <a href="<?php echo BASE_URL; ?>modulos/ventas/crear.php" class="btn btn-success">➕ Nueva Venta Directa en Mostrador</a>
</div>

<div class="catalogo-header" style="margin-bottom: 1rem;">
    <div class="buscador-box">
        <input type="text" id="input-search-live" class="input-search" placeholder="Filtrar por comprobante, cliente o DNI...">
    </div>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>Nº Comprobante</th>
                <th>Fecha y Hora</th>
                <th>Cliente / DNI</th>
                <th>Método Pago</th>
                <th>Subtotal</th>
                <th>IGV (18%)</th>
                <th>Total S/</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($res_ventas) === 0): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No se registraron ventas en el sistema.</td>
                </tr>
            <?php else: ?>
                <?php while ($v = mysqli_fetch_assoc($res_ventas)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($v['num_comprobante']); ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($v['fecha_venta'])); ?></td>
                        <td>
                            <?php echo htmlspecialchars($v['nombre'] . ' ' . $v['apellido']); ?><br>
                            <small style="color: var(--text-muted);">DNI: <?php echo htmlspecialchars($v['dni']); ?></small>
                        </td>
                        <td>
                            <span class="badge-pago <?php echo strtolower($v['metodo_pago']); ?>">
                                <?php echo htmlspecialchars($v['metodo_pago']); ?>
                            </span>
                            <?php if (!empty($v['num_operacion'])): ?>
                                <br><small style="font-size: 0.75rem; color: var(--text-muted);">Ref: <?php echo htmlspecialchars($v['num_operacion']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>S/ <?php echo number_format($v['subtotal'], 2); ?></td>
                        <td>S/ <?php echo number_format($v['igv'], 2); ?></td>
                        <td><strong style="color: var(--primary); font-size: 1.05rem;">S/ <?php echo number_format($v['total'], 2); ?></strong></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>modulos/ventas/comprobante.php?id=<?php echo $v['id_venta']; ?>" class="btn btn-secondary btn-sm" title="Ver / Imprimir Boleta">📄 Boleta</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>