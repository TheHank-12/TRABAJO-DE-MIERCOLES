<?php
$titulo_pagina = "Registro de Cliente";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

if (esta_autenticado()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$error = '';
$datos = [
    'dni' => '',
    'nombre' => '',
    'apellido' => '',
    'email' => '',
    'telefono' => '',
    'direccion' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['dni'] = trim(mysqli_real_escape_string($conexion, $_POST['dni']));
    $datos['nombre'] = trim(mysqli_real_escape_string($conexion, $_POST['nombre']));
    $datos['apellido'] = trim(mysqli_real_escape_string($conexion, $_POST['apellido']));
    $datos['email'] = trim(mysqli_real_escape_string($conexion, $_POST['email']));
    $datos['telefono'] = trim(mysqli_real_escape_string($conexion, $_POST['telefono']));
    $datos['direccion'] = trim(mysqli_real_escape_string($conexion, $_POST['direccion']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($datos['dni']) || empty($datos['nombre']) || empty($datos['apellido']) || empty($datos['email']) || empty($password)) {
        $error = "Por favor, complete todos los campos obligatorios.";
    } else if ($password !== $confirm_password) {
        $error = "Las contraseñas ingresadas no coinciden.";
    } else if (strlen($password) < 6) {
        $error = "La contraseña debe contener al menos 6 caracteres.";
    } else {
        // Validar DNI duplicado
        $check_dni = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE dni = '{$datos['dni']}' LIMIT 1");
        if (mysqli_num_rows($check_dni) > 0) {
            $error = "El número de DNI ingresado ya se encuentra registrado.";
        } else {
            // Validar Email duplicado
            $check_email = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE email = '{$datos['email']}' LIMIT 1");
            if (mysqli_num_rows($check_email) > 0) {
                $error = "El correo electrónico ingresado ya está registrado.";
            } else {
                // Hashear contraseña con Bcrypt
                $hash_password = password_hash($password, PASSWORD_BCRYPT);
                $id_rol_cliente = 2; // Rol Cliente por defecto

                $query_insert = "INSERT INTO usuarios (id_rol, dni, nombre, apellido, email, password, telefono, direccion, estado) 
                                VALUES ($id_rol_cliente, '{$datos['dni']}', '{$datos['nombre']}', '{$datos['apellido']}', '{$datos['email']}', '$hash_password', '{$datos['telefono']}', '{$datos['direccion']}', 'ACTIVO')";

                if (mysqli_query($conexion, $query_insert)) {
                    $_SESSION['mensaje'] = "Cuenta creada con éxito. Ahora puede iniciar sesión.";
                    $_SESSION['tipo_mensaje'] = "success";
                    header("Location: " . BASE_URL . "modulos/auth/login.php");
                    exit();
                } else {
                    $error = "Error al crear la cuenta: " . mysqli_error($conexion);
                }
            }
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="form-card" style="margin-top: 2rem; margin-bottom: 2rem;">
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <h2>Crear Cuenta de Cliente</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Complete el formulario para comprar y alquilar libros en Soles (S/)</p>
    </div>

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
                <label for="telefono">Teléfono / Celular:</label>
                <input type="text" name="telefono" id="telefono" class="form-control" value="<?php echo htmlspecialchars($datos['telefono']); ?>">
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

        <div class="form-group">
            <label for="email">Correo Electrónico (*):</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($datos['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="direccion">Dirección de Domicilio / Residencia:</label>
            <input type="text" name="direccion" id="direccion" class="form-control" value="<?php echo htmlspecialchars($datos['direccion']); ?>">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="password">Contraseña (*):</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña (*):</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repita la contraseña" required>
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-block" style="margin-top: 1.5rem;">📝 Registrarme Ahora</button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color); font-size: 0.9rem;">
        <p>¿Ya posee una cuenta? <a href="<?php echo BASE_URL; ?>modulos/auth/login.php" style="color: var(--primary); font-weight: 600;">Iniciar Sesión</a></p>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>