<?php
$titulo_pagina = "Mis Compras";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_autenticacion();

$id_usuario_logueado = (int)$_SESSION['usuario_id'];

$sql_mis_ventas = "SELECT * FROM ventas WHERE id_usuario = $id_usuario_logueado ORDER BY id_venta DESC";
$res_mis_ventas = mysqli_query($conexion, $sql_mis_ventas);

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <h2>🛍️ Mis Compras Realizadas</h2>
    <p style="color: var(--text-muted);">Historial de boletas y comprobantes generados en sus adquisiciones.</p>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>Nº Comprobante</th>
                <th>Fecha de Compra</th>
                <th>Método de Pago</th>
                <th>Subtotal</th>
                <th>IGV (18%)</th>
                <th>Total S/</th>
                <th>Comprobante</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($res_mis_ventas) === 0): ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Usted aún no registra compras de libros en la tienda.</td>
                </tr>
            <?php else: ?>
                <?php while ($v = mysqli_fetch_assoc($res_mis_ventas)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($v['num_comprobante']); ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($v['fecha_venta'])); ?></td>
                        <td>
                            <span class="badge-pago <?php echo strtolower($v['metodo_pago']); ?>">
                                <?php echo htmlspecialchars($v['metodo_pago']); ?>
                            </span>
                        </td>
                        <td>S/ <?php echo number_format($v['subtotal'], 2); ?></td>
                        <td>S/ <?php echo number_format($v['igv'], 2); ?></td>
                        <td><strong style="color: var(--primary); font-size: 1.05rem;">S/ <?php echo number_format($v['total'], 2); ?></strong></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>modulos/ventas/comprobante.php?id=<?php echo $v['id_venta']; ?>" class="btn btn-secondary btn-sm" title="Ver Boleta">📄 Ver Boleta</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>