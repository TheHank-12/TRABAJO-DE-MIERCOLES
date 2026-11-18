<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_admin();

$id_usuario = isset($_GET['id']) ? intval($_GET['id']) : 0;

// REGLA DE SEGURIDAD 1: El Administrador no puede eliminar su propia cuenta activa
if ($id_usuario === (int)$_SESSION['usuario_id']) {
    $_SESSION['mensaje'] = "Acción denegada: El Administrador activo no puede eliminar su propia cuenta.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: " . BASE_URL . "modulos/usuarios/index.php");
    exit();
}

// REGLA DE INTEGRIDAD REFERENCIAL 2: Verificar alquileres activos o pendientes
$chk_alq = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM alquileres WHERE id_usuario = $id_usuario AND estado IN ('En Alquiler', 'Con Demora')");
$row_alq = mysqli_fetch_assoc($chk_alq);

if ($row_alq['total'] > 0) {
    $_SESSION['mensaje'] = "No se puede eliminar este usuario porque posee " . $row_alq['total'] . " alquiler(es) de libros vigentes o con demora.";
    $_SESSION['tipo_mensaje'] = "warning";
    header("Location: " . BASE_URL . "modulos/usuarios/index.php");
    exit();
}

// Intento de eliminación física con captura de restricción por Clave Foránea (FK)
$delete_query = "DELETE FROM usuarios WHERE id_usuario = $id_usuario";

if (mysqli_query($conexion, $delete_query)) {
    $_SESSION['mensaje'] = "El usuario ha sido eliminado permanentemente del sistema.";
    $_SESSION['tipo_mensaje'] = "success";
} else {
    // Si falla por dependencias relacionales (ventas históricas, multas, etc.)
    $_SESSION['mensaje'] = "No se puede eliminar el usuario debido a restricciones de integridad referencial (historial de ventas o alquileres). Se recomienda cambiar su estado a INACTIVO.";
    $_SESSION['tipo_mensaje'] = "danger";
}

header("Location: " . BASE_URL . "modulos/usuarios/index.php");
exit();
?>