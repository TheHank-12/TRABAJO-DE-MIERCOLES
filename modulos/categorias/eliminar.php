<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_admin();

$id_categoria = isset($_GET['id']) ? intval($_GET['id']) : 0;

// REGLA DE INTEGRIDAD REFERENCIAL: Verificar si hay libros asociados
$chk_libros = mysqli_query($conexion, "SELECT COUNT(*) AS total FROM libros WHERE id_categoria = $id_categoria");
$row = mysqli_fetch_assoc($chk_libros);

if ($row['total'] > 0) {
    $_SESSION['mensaje'] = "No se puede eliminar la categoría porque tiene " . $row['total'] . " libro(s) asociado(s). Reasigne o elimine los libros primero.";
    $_SESSION['tipo_mensaje'] = "warning";
} else {
    $delete = "DELETE FROM categorias WHERE id_categoria = $id_categoria";
    if (mysqli_query($conexion, $delete)) {
        $_SESSION['mensaje'] = "Categoría eliminada exitosamente.";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar la categoría: " . mysqli_error($conexion);
        $_SESSION['tipo_mensaje'] = "danger";
    }
}

header("Location: " . BASE_URL . "modulos/categorias/index.php");
exit();
?>