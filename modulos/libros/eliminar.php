<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_admin();

$id_libro = isset($_GET['id']) ? intval($_GET['id']) : 0;

// REGLA DE INTEGRIDAD REFERENCIAL: Verificar transacciones activas de alquiler o ventas
$chk_alq = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM alquileres WHERE id_libro = $id_libro AND estado IN ('En Alquiler', 'Con Demora')");
$row_alq = mysqli_fetch_assoc($chk_alq);

if ($row_alq['total'] > 0) {
    $_SESSION['mensaje'] = "No se puede eliminar el libro porque está prestado actualmente en " . $row_alq['total'] . " alquiler(es) activo(s).";
    $_SESSION['tipo_mensaje'] = "warning";
    header("Location: " . BASE_URL . "modulos/libros/index.php");
    exit();
}

// Intentar borrado físico
if (mysqli_query($conexion, "DELETE FROM libros WHERE id_libro = $id_libro")) {
    $_SESSION['mensaje'] = "El libro ha sido eliminado del catálogo.";
    $_SESSION['tipo_mensaje'] = "success";
} else {
    $_SESSION['mensaje'] = "No se pudo eliminar el libro debido a que posee historial en ventas o transacciones pasadas. Se recomienda cambiar su estado a AGOTADO o DESCONTINUADO.";
    $_SESSION['tipo_mensaje'] = "danger";
}

header("Location: " . BASE_URL . "modulos/libros/index.php");
exit();
?>