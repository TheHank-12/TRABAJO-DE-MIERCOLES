<?php
$titulo_pagina = "Gestión de Editoriales";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$sql = "SELECT e.*, COUNT(l.id_libro) AS total_libros 
        FROM editoriales e 
        LEFT JOIN libros l ON e.id_editorial = l.id_editorial 
        GROUP BY e.id_editorial 
        ORDER BY e.nombre ASC";
$res = mysqli_query($conexion, $sql);

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>🏢 Administración de Editoriales</h2>
    <a href="<?php echo BASE_URL; ?>modulos/editoriales/crear.php" class="btn btn-success">➕ Nueva Editorial</a>
</div>

<div class="catalogo-header" style="margin-bottom: 1rem;">
    <div class="buscador-box">
        <input type="text" id="input-search-live" class="input-search" placeholder="Filtrar por nombre o contacto...">
    </div>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Editorial</th>
                <th>Contacto / Email</th>
                <th>Libros Publicados</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($res) === 0): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No hay editoriales registradas.</td>
                </tr>
            <?php else: ?>
                <?php while ($ed = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td><?php echo $ed['id_editorial']; ?></td>
                        <td><strong><?php echo htmlspecialchars($ed['nombre']); ?></strong></td>
                        <td><?php echo htmlspecialchars($ed['contacto'] ? $ed['contacto'] : '-'); ?></td>
                        <td><span class="user-role-badge badge-client"><?php echo $ed['total_libros']; ?> libros</span></td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <a href="<?php echo BASE_URL; ?>modulos/editoriales/editar.php?id=<?php echo $ed['id_editorial']; ?>" class="btn btn-secondary btn-sm" title="Editar">✏️</a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmarAccion('<?php echo BASE_URL; ?>modulos/editoriales/eliminar.php?id=<?php echo $ed['id_editorial']; ?>', '¿Está seguro de eliminar la editorial <?php echo htmlspecialchars($ed['nombre']); ?>?');">🗑️</button>
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