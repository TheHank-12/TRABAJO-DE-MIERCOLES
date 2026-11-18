<?php
$titulo_pagina = "Iniciar Sesión";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

if (esta_autenticado()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(mysqli_real_escape_string($conexion, $_POST['email']));
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Por favor, complete todos los campos.";
    } else {
        $query = "SELECT u.*, r.nombre AS rol_nombre 
                  FROM usuarios u 
                  INNER JOIN roles r ON u.id_rol = r.id_rol 
                  WHERE u.email = '$email' LIMIT 1";
        $resultado = mysqli_query($conexion, $query);

        if ($resultado && mysqli_num_rows($resultado) === 1) {
            $usuario = mysqli_fetch_assoc($resultado);

            if ($usuario['estado'] !== 'ACTIVO') {
                $error = "Su cuenta se encuentra INACTIVA. Póngase en contacto con el administrador.";
            } else if (password_verify($password, $usuario['password'])) {
                // Iniciar sesión y guardar variables en $_SESSION
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_apellido'] = $usuario['apellido'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_dni'] = $usuario['dni'];
                $_SESSION['rol_nombre'] = $usuario['rol_nombre'];

                $_SESSION['mensaje'] = "Bienvenido(a) " . $usuario['nombre'] . " al sistema.";
                $_SESSION['tipo_mensaje'] = "success";

                header("Location: " . BASE_URL . "index.php");
                exit();
            } else {
                $error = "Contraseña incorrecta. Verifique sus credenciales.";
            }
        } else {
            $error = "El correo electrónico no se encuentra registrado.";
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div class="form-card" style="margin-top: 2rem; margin-bottom: 2rem;">
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <img src="<?php echo BASE_URL; ?>img/logo.png" alt="Logo Librería" style="height: 65px; margin-bottom: 0.5rem;" onerror="this.src='<?php echo BASE_URL; ?>img/no-cover.png'">
        <h2>Acceso al Sistema</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Ingrese sus credenciales para acceder a la librería</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="ejemplo@libreria.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required autocomplete="email">
        </div>

        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">🔑 Iniciar Sesión</button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color); font-size: 0.9rem;">
        <p>¿No tiene una cuenta aún? <a href="<?php echo BASE_URL; ?>modulos/auth/registro.php" style="color: var(--primary); font-weight: 600;">Regístrese aquí</a></p>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>