<?php
$titulo_pagina = "Registrar Usuario";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_admin();

$error = '';
$roles_res = mysqli_query($conexion, "SELECT * FROM roles ORDER BY id_rol ASC");

$datos = [
    'dni' => '',
    'nombre' => '',
    'apellido' => '',
    'email' => '',
    'telefono' => '',
    'direccion' => '',
    'id_rol' => 2,
    'estado' => 'ACTIVO'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['dni'] = trim(mysqli_real_escape_string($conexion, $_POST['dni']));
    $datos['nombre'] = trim(mysqli_real_escape_string($conexion, $_POST['nombre']));
    $datos['apellido'] = trim(mysqli_real_escape_string($conexion, $_POST['apellido']));
    $datos['email'] = trim(mysqli_real_escape_string($conexion, $_POST['email']));
    $datos['telefono'] = trim(mysqli_real_escape_string($conexion, $_POST['telefono']));
    $datos['direccion'] = trim(mysqli_real_escape_string($conexion, $_POST['direccion']));
    $datos['id_rol'] = intval($_POST['id_rol']);
    $datos['estado'] = trim(mysqli_real_escape_string($conexion, $_POST['estado']));
    $password = trim($_POST['password']);

    if (empty($datos['dni']) || empty($datos['nombre']) || empty($datos['apellido']) || empty($datos['email']) || empty($password)) {
        $error = "Por favor, complete todos los campos obligatorios (*).";
    } else {
        // Validar DNI único
        $check_dni = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE dni = '{$datos['dni']}' LIMIT 1");
        if (mysqli_num_rows($check_dni) > 0) {
            $error = "El número de DNI especificado ya pertenece a otro usuario registrado.";
        } else {
            // Validar Email único
            $check_email = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE email = '{$datos['email']}' LIMIT 1");
            if (mysqli_num_rows($check_email) > 0) {
                $error = "El correo electrónico ingresado ya se encuentra registrado.";
            } else {
                $hash_pass = password_hash($password, PASSWORD_BCRYPT);
                $query_insert = "INSERT INTO usuarios (id_rol, dni, nombre, apellido, email, password, telefono, direccion, estado) 
                                VALUES ({$datos['id_rol']}, '{$datos['dni']}', '{$datos['nombre']}', '{$datos['apellido']}', '{$datos['email']}', '$hash_pass', '{$datos['telefono']}', '{$datos['direccion']}', '{$datos['estado']}')";

                if (mysqli_query($conexion, $query_insert)) {
                    $_SESSION['mensaje'] = "Usuario registrado correctamente en el sistema.";
                    $_SESSION['tipo_mensaje'] = "success";
                    header("Location: " . BASE_URL . "modulos/usuarios/index.php");
                    exit();
                } else {
                    $error = "Error al crear el usuario: " . mysqli_error($conexion);
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
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Registrar Nuevo Usuario</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="dni">DNI / Documento (*):</label>
                <input type="text" name="dni" id="dni" class="form-control" maxlength="15" value="<?php echo htmlspecialchars($datos['dni']); ?>" required>
            </div>
            <div class="form-group">
                <label for="id_rol">Rol asignado (*):</label>
                <select name="id_rol" id="id_rol" class="form-control" required>
                    <?php while ($rol = mysqli_fetch_assoc($roles_res)): ?>
                        <option value="<?php echo $rol['id_rol']; ?>" <?php echo $datos['id_rol'] === (int)$rol['id_rol'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($rol['nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="nombre">Nombres (*):</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($datos['nombre']); ?>" required>
            </div>
            <div class="form-group">
                <label for="apellido">Apellidos (*):</label>
                <input type="text" name="apellido" id="apellido" class="form-control" value="<?php echo htmlspecialchars($datos['apellido']); ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="email">Correo Electrónico (*):</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($datos['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña inicial (*):</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="telefono">Teléfono / Celular:</label>
                <input type="text" name="telefono" id="telefono" class="form-control" value="<?php echo htmlspecialchars($datos['telefono']); ?>">
            </div>
            <div class="form-group">
                <label for="estado">Estado Inicial (*):</label>
                <select name="estado" id="estado" class="form-control" required>
                    <option value="ACTIVO" <?php echo $datos['estado'] === 'ACTIVO' ? 'selected' : ''; ?>>ACTIVO</option>
                    <option value="INACTIVO" <?php echo $datos['estado'] === 'INACTIVO' ? 'selected' : ''; ?>>INACTIVO</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="direccion">Dirección de Domicilio:</label>
            <input type="text" name="direccion" id="direccion" class="form-control" value="<?php echo htmlspecialchars($datos['direccion']); ?>">
        </div>

        <button type="submit" class="btn btn-success btn-block" style="margin-top: 1rem;">💾 Guardar Usuario</button>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>