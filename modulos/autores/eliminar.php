<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_admin();

$id_autor = isset($_GET['id']) ? intval($_GET['id']) : 0;

$chk = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM libros WHERE id_autor = $id_autor");
$row = mysqli_fetch_assoc($chk);

if ($row['total'] > 0) {
    $_SESSION['mensaje'] = "No se puede eliminar el autor porque tiene " . $row['total'] . " libro(s) asignado(s) en catálogo.";
    $_SESSION['tipo_mensaje'] = "warning";
} else {
    if (mysqli_query($conexion, "DELETE FROM autores WHERE id_autor = $id_autor")) {
        $_SESSION['mensaje'] = "Autor eliminado correctamente.";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar autor: " . mysqli_error($conexion);
        $_SESSION['tipo_mensaje'] = "danger";
    }
}

header("Location: " . BASE_URL . "modulos/autores/index.php");
exit();
?>