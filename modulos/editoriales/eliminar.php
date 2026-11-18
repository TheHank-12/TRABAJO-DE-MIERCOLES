<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_admin();

$id_editorial = isset($_GET['id']) ? intval($_GET['id']) : 0;

$chk = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM libros WHERE id_editorial = $id_editorial");
$row = mysqli_fetch_assoc($chk);

if ($row['total'] > 0) {
    $_SESSION['mensaje'] = "No se puede eliminar la editorial porque existen " . $row['total'] . " libro(s) asociados a ella.";
    $_SESSION['tipo_mensaje'] = "warning";
} else {
    if (mysqli_query($conexion, "DELETE FROM editoriales WHERE id_editorial = $id_editorial")) {
        $_SESSION['mensaje'] = "Editorial eliminada exitosamente.";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar la editorial: " . mysqli_error($conexion);
        $_SESSION['tipo_mensaje'] = "danger";
    }
}

header("Location: " . BASE_URL . "modulos/editoriales/index.php");
exit();
?>