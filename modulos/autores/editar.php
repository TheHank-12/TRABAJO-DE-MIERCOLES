<?php
$titulo_pagina = "Editar Autor";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$id_autor = isset($_GET['id']) ? intval($_GET['id']) : 0;
$res = mysqli_query($conexion, "SELECT * FROM autores WHERE id_autor = $id_autor LIMIT 1");

if (!$res || mysqli_num_rows($res) === 0) {
    $_SESSION['mensaje'] = "Autor no encontrado.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: " . BASE_URL . "modulos/autores/index.php");
    exit();
}

$autor = mysqli_fetch_assoc($res);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim(mysqli_real_escape_string($conexion, $_POST['nombre']));
    $nacionalidad = trim(mysqli_real_escape_string($conexion, $_POST['nacionalidad']));

    if (empty($nombre)) {
        $error = "El nombre del autor es obligatorio.";
    } else {
        $update = "UPDATE autores SET nombre = '$nombre', nacionalidad = '$nacionalidad' WHERE id_autor = $id_autor";
        if (mysqli_query($conexion, $update)) {
            $_SESSION['mensaje'] = "Autor actualizado exitosamente.";
            $_SESSION['tipo_mensaje'] = "success";
            header("Location: " . BASE_URL . "modulos/autores/index.php");
            exit();
        } else {
            $error = "Error al actualizar autor: " . mysqli_error($conexion);
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>modulos/autores/index.php" class="btn btn-secondary">⬅️ Volver a Autores</a>
</div>

<div class="form-card">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Editar Autor</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $id_autor; ?>" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre Completo (*):</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($autor['nombre']); ?>" required>
        </div>

        <div class="form-group">
            <label for="nacionalidad">Nacionalidad:</label>
            <input type="text" name="nacionalidad" id="nacionalidad" class="form-control" value="<?php echo htmlspecialchars($autor['nacionalidad']); ?>">
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">💾 Actualizar Autor</button>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>