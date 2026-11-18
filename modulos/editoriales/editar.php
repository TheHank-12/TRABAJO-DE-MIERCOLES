<?php
$titulo_pagina = "Editar Editorial";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$id_editorial = isset($_GET['id']) ? intval($_GET['id']) : 0;
$res = mysqli_query($conexion, "SELECT * FROM editoriales WHERE id_editorial = $id_editorial LIMIT 1");

if (!$res || mysqli_num_rows($res) === 0) {
    $_SESSION['mensaje'] = "Editorial no encontrada.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: " . BASE_URL . "modulos/editoriales/index.php");
    exit();
}

$ed = mysqli_fetch_assoc($res);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim(mysqli_real_escape_string($conexion, $_POST['nombre']));
    $contacto = trim(mysqli_real_escape_string($conexion, $_POST['contacto']));

    if (empty($nombre)) {
        $error = "El nombre de la editorial es obligatorio.";
    } else {
        $update = "UPDATE editoriales SET nombre = '$nombre', contacto = '$contacto' WHERE id_editorial = $id_editorial";
        if (mysqli_query($conexion, $update)) {
            $_SESSION['mensaje'] = "Editorial actualizada correctamente.";
            $_SESSION['tipo_mensaje'] = "success";
            header("Location: " . BASE_URL . "modulos/editoriales/index.php");
            exit();
        } else {
            $error = "Error al actualizar la editorial: " . mysqli_error($conexion);
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>modulos/editoriales/index.php" class="btn btn-secondary">⬅️ Volver a Editoriales</a>
</div>

<div class="form-card">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Editar Editorial</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $id_editorial; ?>" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre de la Editorial (*):</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($ed['nombre']); ?>" required>
        </div>

        <div class="form-group">
            <label for="contacto">Contacto / Email:</label>
            <input type="text" name="contacto" id="contacto" class="form-control" value="<?php echo htmlspecialchars($ed['contacto']); ?>">
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">💾 Actualizar Editorial</button>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>