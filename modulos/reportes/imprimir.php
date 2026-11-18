<?php
$titulo_pagina = "Reporte Ejecutivo Consolidado";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$fecha_generacion = date('d/m/Y H:i:s');

// Consultar Ventas Totales
$res_v = mysqli_query($conexion, "SELECT v.*, u.nombre, u.apellido, u.dni FROM ventas v INNER JOIN usuarios u ON v.id_usuario = u.id_usuario ORDER BY v.id_venta DESC");

// Consultar Alquileres Totales
$res_a = mysqli_query($conexion, "SELECT a.*, u.nombre, u.apellido, l.titulo FROM alquileres a INNER JOIN usuarios u ON a.id_usuario = u.id_usuario INNER JOIN libros l ON a.id_libro = l.id_libro ORDER BY a.id_alquiler DESC");

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="no-print" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="<?php echo BASE_URL; ?>modulos/reportes/index.php" class="btn btn-secondary">⬅️ Volver a Reportes</a>
    <button onclick="window.print();" class="btn btn-success">🖨️ Imprimir / Guardar como PDF</button>
</div>

<div class="form-card" style="max-width: 100%; padding: 2rem; background: #fff;">
    <div style="text-align: center; border-bottom: 2px solid var(--secondary); padding-bottom: 1rem; margin-bottom: 1.5rem;">
        <h1 style="color: var(--primary);">LIBRERÍA PERÚ — REPORTE CONSOLIDADO DE OPERACIONES</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Fecha de emisión: <?php echo $fecha_generacion; ?> | Sistema de Gestión TP2 Procedimental</p>
    </div>

    <h3 style="margin-bottom: 0.8rem; color: var(--secondary);">1. Registro Histórico de Ventas</h3>
    <div class="table-responsive" style="box-shadow: none; border: 1px solid #ccc; margin-bottom: 2rem;">
        <table class="tabla-custom">
            <thead>
                <tr>
                    <th>Nº Comprobante</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Método Pago</th>
                    <th>Total S/</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($v = mysqli_fetch_assoc($res_v)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($v['num_comprobante']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($v['fecha_venta'])); ?></td>
                        <td><?php echo htmlspecialchars($v['nombre'] . ' ' . $v['apellido']); ?> (DNI: <?php echo htmlspecialchars($v['dni']); ?>)</td>
                        <td><?php echo htmlspecialchars($v['metodo_pago']); ?></td>
                        <td><strong>S/ <?php echo number_format($v['total'], 2); ?></strong></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <h3 style="margin-bottom: 0.8rem; color: var(--secondary);">2. Registro Histórico de Alquileres y Préstamos</h3>
    <div class="table-responsive" style="box-shadow: none; border: 1px solid #ccc;">
        <table class="tabla-custom">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Libro</th>
                    <th>F. Inicio</th>
                    <th>F. Dev. Esperada</th>
                    <th>Monto S/</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($a = mysqli_fetch_assoc($res_a)): ?>
                    <tr>
                        <td>#<?php echo $a['id_alquiler']; ?></td>
                        <td><?php echo htmlspecialchars($a['nombre'] . ' ' . $a['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($a['titulo']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($a['fecha_inicio'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($a['fecha_dev_esperada'])); ?></td>
                        <td>S/ <?php echo number_format($a['monto_alquiler'], 2); ?></td>
                        <td><?php echo htmlspecialchars($a['estado']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 3rem; text-align: center; border-top: 1px solid #eee; padding-top: 1rem; font-size: 0.85rem; color: var(--text-muted);">
        <p>Documento oficial autogenerado por el Sistema de Gestión de Librería. Exclusivo para auditoría interna.</p>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>