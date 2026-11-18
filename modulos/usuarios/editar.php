<?php
$titulo_pagina = "Editar Usuario";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_admin();

$error = '';
$id_usuario = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener datos del usuario
$query_usr = "SELECT * FROM usuarios WHERE id_usuario = $id_usuario LIMIT 1";
$res_usr = mysqli_query($conexion, $query_usr);

if (!$res_usr || mysqli_num_rows($res_usr) === 0) {
    $_SESSION['mensaje'] = "Usuario no encontrado.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: " . BASE_URL . "modulos/usuarios/index.php");
    exit();
}

$usuario = mysqli_fetch_assoc($res_usr);
$roles_res = mysqli_query($conexion, "SELECT * FROM roles ORDER BY id_rol ASC");

// Verificar si el usuario a editar es el mismo administrador logueado
$es_mismo_admin = ((int)$_SESSION['usuario_id'] === $id_usuario);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = trim(mysqli_real_escape_string($conexion, $_POST['dni']));
    $nombre = trim(mysqli_real_escape_string($conexion, $_POST['nombre']));
    $apellido = trim(mysqli_real_escape_string($conexion, $_POST['apellido']));
    $email = trim(mysqli_real_escape_string($conexion, $_POST['email']));
    $telefono = trim(mysqli_real_escape_string($conexion, $_POST['telefono']));
    $direccion = trim(mysqli_real_escape_string($conexion, $_POST['direccion']));
    $id_rol = intval($_POST['id_rol']);
    $estado = trim(mysqli_real_escape_string($conexion, $_POST['estado']));
    $password_nueva = trim($_POST['password']);

    // REGLA DE SEGURIDAD: El Admin no puede quitarse su propio rol de Admin ni desactivarse
    if ($es_mismo_admin) {
        $id_rol = $usuario['id_rol']; // Mantiene su rol original
        $estado = 'ACTIVO';           // Mantiene su estado activo
    }

    if (empty($dni) || empty($nombre) || empty($apellido) || empty($email)) {
        $error = "Por favor, llene todos los campos obligatorios.";
    } else {
        // Validar DNI único exceptuando a este usuario
        $check_dni = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE dni = '$dni' AND id_usuario != $id_usuario LIMIT 1");
        if (mysqli_num_rows($check_dni) > 0) {
            $error = "El DNI ingresado ya está asignado a otro usuario.";
        } else {
            // Validar Email único exceptuando a este usuario
            $check_email = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE email = '$email' AND id_usuario != $id_usuario LIMIT 1");
            if (mysqli_num_rows($check_email) > 0) {
                $error = "El correo electrónico ya está en uso por otro usuario.";
            } else {
                if (!empty($password_nueva)) {
                    $hash = password_hash($password_nueva, PASSWORD_BCRYPT);
                    $sql_update = "UPDATE usuarios SET id_rol = $id_rol, dni = '$dni', nombre = '$nombre', apellido = '$apellido', email = '$email', password = '$hash', telefono = '$telefono', direccion = '$direccion', estado = '$estado' WHERE id_usuario = $id_usuario";
                } else {
                    $sql_update = "UPDATE usuarios SET id_rol = $id_rol, dni = '$dni', nombre = '$nombre', apellido = '$apellido', email = '$email', telefono = '$telefono', direccion = '$direccion', estado = '$estado' WHERE id_usuario = $id_usuario";
                }

                if (mysqli_query($conexion, $sql_update)) {
                    // Si el admin editó sus propios datos, actualizar la sesión
                    if ($es_mismo_admin) {
                        $_SESSION['usuario_nombre'] = $nombre;
                        $_SESSION['usuario_apellido'] = $apellido;
                        $_SESSION['usuario_email'] = $email;
                        $_SESSION['usuario_dni'] = $dni;
                    }

                    $_SESSION['mensaje'] = "Datos de usuario actualizados correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                    header("Location: " . BASE_URL . "modulos/usuarios/index.php");
                    exit();
                } else {
                    $error = "Error al actualizar usuario: " . mysqli_error($conexion);
                }
            }
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>modulos/usuarios/index.php" class="btn btn-secondary">⬅️ Volver a Usuarios</a>
</div>

<div class="form-card">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">
        Editar Usuario: <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>
    </h2>

    <?php if ($es_mismo_admin): ?>
        <div class="alerta-box alerta-warning" style="margin-bottom: 1rem;">
            <div class="alerta-icono">⚠️</div>
            <div class="alerta-contenido">Está editando su propia cuenta activa de Administrador. Por motivos de seguridad no se permite auto-desactivación ni cambio de rol.</div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $id_usuario; ?>" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="dni">DNI / Documento (*):</label>
                <input type="text" name="dni" id="dni" class="form-control" value="<?php echo htmlspecialchars($usuario['dni']); ?>" required>
            </div>
            <div class="form-group">
                <label for="id_rol">Rol asignado (*):</label>
                <select name="id_rol" id="id_rol" class="form-control" <?php echo $es_mismo_admin ? 'disabled' : ''; ?> required>
                    <?php while ($rol = mysqli_fetch_assoc($roles_res)): ?>
                        <option value="<?php echo $rol['id_rol']; ?>" <?php echo (int)$usuario['id_rol'] === (int)$rol['id_rol'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($rol['nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <?php if ($es_mismo_admin): ?>
                    <input type="hidden" name="id_rol" value="<?php echo $usuario['id_rol']; ?>">
                <?php endif; ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="nombre">Nombres (*):</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
            </div>
            <div class="form-group">
                <label for="apellido">Apellidos (*):</label>
                <input type="text" name="apellido" id="apellido" class="form-control" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="email">Correo Electrónico (*):</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Nueva Contraseña (Dejar en blanco si no cambia):</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="telefono">Teléfono / Celular:</label>
                <input type="text" name="telefono" id="telefono" class="form-control" value="<?php echo htmlspecialchars($usuario['telefono']); ?>">
            </div>
            <div class="form-group">
                <label for="estado">Estado (*):</label>
                <select name="estado" id="estado" class="form-control" <?php echo $es_mismo_admin ? 'disabled' : ''; ?> required>
                    <option value="ACTIVO" <?php echo $usuario['estado'] === 'ACTIVO' ? 'selected' : ''; ?>>ACTIVO</option>
                    <option value="INACTIVO" <?php echo $usuario['estado'] === 'INACTIVO' ? 'selected' : ''; ?>>INACTIVO</option>
                </select>
                <?php if ($es_mismo_admin): ?>
                    <input type="hidden" name="estado" value="ACTIVO">
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="direccion">Dirección de Domicilio:</label>
            <input type="text" name="direccion" id="direccion" class="form-control" value="<?php echo htmlspecialchars($usuario['direccion']); ?>">
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">💾 Actualizar Cambios</button>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>