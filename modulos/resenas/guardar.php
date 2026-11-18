<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_autenticacion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_libro = isset($_POST['id_libro']) ? intval($_POST['id_libro']) : 0;
    $puntuacion = isset($_POST['puntuacion']) ? intval($_POST['puntuacion']) : 5;
    $comentario = trim(mysqli_real_escape_string($conexion, $_POST['comentario']));
    $id_usuario = (int)$_SESSION['usuario_id'];

    if ($puntuacion < 1) $puntuacion = 1;
    if ($puntuacion > 5) $puntuacion = 5;

    if ($id_libro > 0 && !empty($comentario)) {
        $query = "INSERT INTO resenas (id_libro, id_usuario, puntuacion, comentario) 
                  VALUES ($id_libro, $id_usuario, $puntuacion, '$comentario')";
        
        if (mysqli_query($conexion, $query)) {
            $_SESSION['mensaje'] = "¡Gracias! Su reseña ha sido registrada correctamente.";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al publicar la reseña: " . mysqli_error($conexion);
            $_SESSION['tipo_mensaje'] = "danger";
        }
    } else {
        $_SESSION['mensaje'] = "Por favor ingrese un comentario válido para enviar su calificación.";
        $_SESSION['tipo_mensaje'] = "warning";
    }

    header("Location: " . BASE_URL . "modulos/libros/detalle.php?id=" . $id_libro);
    exit();
} else {
    header("Location: " . BASE_URL . "index.php");
    exit();
}
?>