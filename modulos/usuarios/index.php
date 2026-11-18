<?php
$titulo_pagina = "Gestión de Usuarios";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_admin();

$sql_usuarios = "SELECT u.*, r.nombre AS rol_nombre 
                 FROM usuarios u 
                 INNER JOIN roles r ON u.id_rol = r.id_rol 
                 ORDER BY u.id_usuario DESC";
$res_usuarios = mysqli_query($conexion, $sql_usuarios);

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>👥 Administración de Usuarios y Clientes</h2>
    <a href="<?php echo BASE_URL; ?>modulos/usuarios/crear.php" class="btn btn-success">➕ Registrar Nuevo Usuario</a>
</div>

<div class="catalogo-header" style="margin-bottom: 1rem;">
    <div class="buscador-box">
        <input type="text" id="input-search-live" class="input-search" placeholder="Filtrar por nombre, DNI, correo o rol...">
    </div>
</div>

<div class="table-responsive">
    <table class="tabla-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>DNI</th>
                <th>Usuario</th>
                <th>Correo Electrónico</th>
                <th>Teléfono</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Fecha Reg.</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($res_usuarios) === 0): ?>
                <tr>
                    <td colspan="9" style="text-align: center;">No hay usuarios registrados en el sistema.</td>
                </tr>
            <?php else: ?>
                <?php while ($usr = mysqli_fetch_assoc($res_usuarios)): ?>
                    <tr>
                        <td><?php echo $usr['id_usuario']; ?></td>
                        <td><strong><?php echo htmlspecialchars($usr['dni']); ?></strong></td>
                        <td><?php echo htmlspecialchars($usr['nombre'] . ' ' . $usr['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($usr['email']); ?></td>
                        <td><?php echo htmlspecialchars($usr['telefono'] ? $usr['telefono'] : '-'); ?></td>
                        <td>
                            <span class="user-role-badge <?php echo $usr['rol_nombre'] === 'Administrador' ? 'badge-admin' : 'badge-client'; ?>">
                                <?php echo htmlspecialchars($usr['rol_nombre']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($usr['estado'] === 'ACTIVO'): ?>
                                <span style="background-color: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">ACTIVO</span>
                            <?php else: ?>
                                <span style="background-color: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 0.8rem;">INACTIVO</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($usr['fecha_registro'])); ?></td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <a href="<?php echo BASE_URL; ?>modulos/usuarios/editar.php?id=<?php echo $usr['id_usuario']; ?>" class="btn btn-secondary btn-sm" title="Editar">✏️</a>
                                
                                <?php if ((int)$usr['id_usuario'] === (int)$_SESSION['usuario_id']): ?>
                                    <button class="btn btn-sm" style="background-color: #ccc; cursor: not-allowed;" title="Protección de cuenta propia activa" disabled>🛑</button>
                                <?php else: ?>
                                    <!-- Botón cambiar estado -->
                                    <a href="<?php echo BASE_URL; ?>modulos/usuarios/estado.php?id=<?php echo $usr['id_usuario']; ?>" class="btn <?php echo $usr['estado'] === 'ACTIVO' ? 'btn-danger' : 'btn-success'; ?> btn-sm" title="<?php echo $usr['estado'] === 'ACTIVO' ? 'Desactivar' : 'Activar'; ?>">
                                        <?php echo $usr['estado'] === 'ACTIVO' ? '🔒' : '🔓'; ?>
                                    </a>
                                    <!-- Botón eliminar -->
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmarAccion('<?php echo BASE_URL; ?>modulos/usuarios/eliminar.php?id=<?php echo $usr['id_usuario']; ?>', '¿Está seguro de eliminar permanentemente al usuario <?php echo htmlspecialchars($usr['nombre']); ?>?');">🗑️</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Componente Modal para confirmaciones de borrado -->
<div class="modal-overlay" id="modal-confirmacion">
    <div class="modal-content">
        <h3 style="margin-bottom: 1rem; color: var(--primary);">Confirmación de Acción</h3>
        <p id="modal-texto-confirmacion">¿Está seguro de realizar esta acción?</p>
        <div class="modal-acciones">
            <button type="button" class="btn btn-secondary" onclick="cerrarModal();">Cancelar</button>
            <a id="modal-btn-aceptar" href="#" class="btn btn-danger">Aceptar y Continuar</a>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>