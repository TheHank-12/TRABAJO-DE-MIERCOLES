<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_admin();

$id_proveedor = isset($_GET['id']) ? intval($_GET['id']) : 0;

// REGLA DE INTEGRIDAD REFERENCIAL
$chk = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM libros WHERE id_proveedor = $id_proveedor");
$row = mysqli_fetch_assoc($chk);

if ($row['total'] > 0) {
    $_SESSION['mensaje'] = "No se puede eliminar el proveedor porque suministra " . $row['total'] . " libro(s) registrado(s) en el sistema.";
    $_SESSION['tipo_mensaje'] = "warning";
} else {
    if (mysqli_query($conexion, "DELETE FROM proveedores WHERE id_proveedor = $id_proveedor")) {
        $_SESSION['mensaje'] = "Proveedor eliminado correctamente.";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar el proveedor: " . mysqli_error($conexion);
        $_SESSION['tipo_mensaje'] = "danger";
    }
}

header("Location: " . BASE_URL . "modulos/proveedores/index.php");
exit();
?>