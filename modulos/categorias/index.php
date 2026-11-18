<?php
$titulo_pagina = "Gestión de Categorías";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$sql_cat = "SELECT c.*, COUNT(l.id_libro) AS total_libros 
            FROM categorias c 
            LEFT JOIN libros l ON c.id_categoria = l.id_categoria 
            GROUP BY c.id_categoria 
            ORDER BY c.nombre ASC";
$res_cat = mysqli_query($conexion, $sql_cat);

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>🏷️ Administración de Categorías</h2>
    <a href="<?php echo BASE_URL; ?>modulos/categorias/crear.php" class="btn btn-success">➕ Nueva Categoría</a>
</div>

<div class="catalogo-header" style="margin-bottom: 1rem;">
    <div class="buscador-box">
        <input type="text" id="input-search-live" class="input-search" placeholder="Filtrar por nombre o descripción...">
    </div>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Total Libros</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($res_cat) === 0): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No se encontraron categorías registradas.</td>
                </tr>
            <?php else: ?>
                <?php while ($cat = mysqli_fetch_assoc($res_cat)): ?>
                    <tr>
                        <td><?php echo $cat['id_categoria']; ?></td>
                        <td><strong><?php echo htmlspecialchars($cat['nombre']); ?></strong></td>
                        <td><?php echo htmlspecialchars($cat['descripcion'] ? $cat['descripcion'] : 'Sin descripción'); ?></td>
                        <td><span class="user-role-badge badge-client"><?php echo $cat['total_libros']; ?> libros</span></td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <a href="<?php echo BASE_URL; ?>modulos/categorias/editar.php?id=<?php echo $cat['id_categoria']; ?>" class="btn btn-secondary btn-sm" title="Editar">✏️</a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmarAccion('<?php echo BASE_URL; ?>modulos/categorias/eliminar.php?id=<?php echo $cat['id_categoria']; ?>', '¿Está seguro de eliminar la categoría <?php echo htmlspecialchars($cat['nombre']); ?>?');">🗑️</button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="modal-confirmacion">
    <div class="modal-content">
        <h3 style="margin-bottom: 1rem; color: var(--primary);">Confirmación de Eliminación</h3>
        <p id="modal-texto-confirmacion">¿Está seguro de realizar esta acción?</p>
        <div class="modal-acciones">
            <button type="button" class="btn btn-secondary" onclick="cerrarModal();">Cancelar</button>
            <a id="modal-btn-aceptar" href="#" class="btn btn-danger">Eliminar</a>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>