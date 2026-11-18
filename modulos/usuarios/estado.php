<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_admin();

$id_usuario = isset($_GET['id']) ? intval($_GET['id']) : 0;

// REGLA DE SEGURIDAD: El Admin no puede desactivar su propia cuenta activa
if ($id_usuario === (int)$_SESSION['usuario_id']) {
    $_SESSION['mensaje'] = "Acción denegada: No puede desactivar su propia cuenta de administrador activa.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: " . BASE_URL . "modulos/usuarios/index.php");
    exit();
}

$query = "SELECT estado FROM usuarios WHERE id_usuario = $id_usuario LIMIT 1";
$res = mysqli_query($conexion, $query);

if ($res && mysqli_num_rows($res) === 1) {
    $row = mysqli_fetch_assoc($res);
    $nuevo_estado = ($row['estado'] === 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';

    $update = "UPDATE usuarios SET estado = '$nuevo_estado' WHERE id_usuario = $id_usuario";
    if (mysqli_query($conexion, $update)) {
        $_SESSION['mensaje'] = "El estado del usuario ha sido cambiado a: " . $nuevo_estado;
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al actualizar estado: " . mysqli_error($conexion);
        $_SESSION['tipo_mensaje'] = "danger";
    }
} else {
    $_SESSION['mensaje'] = "Usuario no encontrado.";
    $_SESSION['tipo_mensaje'] = "danger";
}

header("Location: " . BASE_URL . "modulos/usuarios/index.php");
exit();
?>