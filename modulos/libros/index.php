<?php
$titulo_pagina = "Gestión de Libros";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$sql_libros = "SELECT l.*, c.nombre AS categoria, a.nombre AS autor, e.nombre AS editorial, p.razon_social AS proveedor
               FROM libros l
               INNER JOIN categorias c ON l.id_categoria = c.id_categoria
               INNER JOIN autores a ON l.id_autor = a.id_autor
               INNER JOIN editoriales e ON l.id_editorial = e.id_editorial
               INNER JOIN proveedores p ON l.id_proveedor = p.id_proveedor
               ORDER BY l.id_libro DESC";
$res_libros = mysqli_query($conexion, $sql_libros);

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>📚 Gestión del Catálogo de Libros</h2>
    <div style="display: flex; gap: 10px;">
        <a href="<?php echo BASE_URL; ?>modulos/categorias/index.php" class="btn btn-secondary btn-sm">🏷️ Categorías</a>
        <a href="<?php echo BASE_URL; ?>modulos/autores/index.php" class="btn btn-secondary btn-sm">✍️ Autores</a>
        <a href="<?php echo BASE_URL; ?>modulos/editoriales/index.php" class="btn btn-secondary btn-sm">🏢 Editoriales</a>
        <a href="<?php echo BASE_URL; ?>modulos/libros/crear.php" class="btn btn-success">➕ Añadir Libro</a>
    </div>
</div>

<div class="catalogo-header" style="margin-bottom: 1rem;">
    <div class="buscador-box">
        <input type="text" id="input-search-live" class="input-search" placeholder="Filtrar por título, autor, ISBN o categoría...">
    </div>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>Portada</th>
                <th>Título / ISBN</th>
                <th>Autor</th>
                <th>Categoría</th>
                <th>P. Venta</th>
                <th>P. Alquiler</th>
                <th>Stock</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($res_libros) === 0): ?>
                <tr>
                    <td colspan="9" style="text-align: center;">No hay libros registrados en el inventario.</td>
                </tr>
            <?php else: ?>
                <?php while ($lib = mysqli_fetch_assoc($res_libros)): ?>
                    <tr>
                        <td style="width: 60px;">
                            <img src="<?php echo BASE_URL . htmlspecialchars($lib['portada']); ?>" alt="Portada" style="width: 45px; height: 60px; object-fit: cover; border-radius: 4px;" onerror="this.src='<?php echo BASE_URL; ?>img/no-cover.png'">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($lib['titulo']); ?></strong><br>
                            <small style="color: var(--text-muted);">ISBN: <?php echo htmlspecialchars($lib['isbn']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($lib['autor']); ?></td>
                        <td><?php echo htmlspecialchars($lib['categoria']); ?></td>
                        <td><strong>S/ <?php echo number_format($lib['precio_venta'], 2); ?></strong></td>
                        <td>S/ <?php echo number_format($lib['precio_alquiler'], 2); ?></td>
                        <td>
                            <span style="font-weight: 700; color: <?php echo $lib['stock'] > 5 ? 'var(--success)' : 'var(--danger)'; ?>;">
                                <?php echo $lib['stock']; ?> un.
                            </span>
                        </td>
                        <td>
                            <span class="user-role-badge <?php echo $lib['estado'] === 'DISPONIBLE' ? 'badge-client' : 'badge-admin'; ?>">
                                <?php echo $lib['estado']; ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 4px;">
                                <a href="<?php echo BASE_URL; ?>modulos/libros/detalle.php?id=<?php echo $lib['id_libro']; ?>" class="btn btn-secondary btn-sm" title="Ver Detalle">👁️</a>
                                <a href="<?php echo BASE_URL; ?>modulos/libros/editar.php?id=<?php echo $lib['id_libro']; ?>" class="btn btn-primary btn-sm" title="Editar">✏️</a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmarAccion('<?php echo BASE_URL; ?>modulos/libros/eliminar.php?id=<?php echo $lib['id_libro']; ?>', '¿Está seguro de eliminar el libro <?php echo htmlspecialchars($lib['titulo']); ?>?');">🗑️</button>
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