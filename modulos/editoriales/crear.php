<?php
$titulo_pagina = "Registrar Editorial";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$error = '';
$nombre = '';
$contacto = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim(mysqli_real_escape_string($conexion, $_POST['nombre']));
    $contacto = trim(mysqli_real_escape_string($conexion, $_POST['contacto']));

    if (empty($nombre)) {
        $error = "El nombre de la editorial es obligatorio.";
    } else {
        $query = "INSERT INTO editoriales (nombre, contacto) VALUES ('$nombre', '$contacto')";
        if (mysqli_query($conexion, $query)) {
            $_SESSION['mensaje'] = "Editorial registrada exitosamente.";
            $_SESSION['tipo_mensaje'] = "success";
            header("Location: " . BASE_URL . "modulos/editoriales/index.php");
            exit();
        } else {
            $error = "Error al crear la editorial: " . mysqli_error($conexion);
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>modulos/editoriales/index.php" class="btn btn-secondary">⬅️ Volver a Editoriales</a>
</div>

<div class="form-card">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Registrar Nueva Editorial</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre de la Editorial (*):</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($nombre); ?>" placeholder="Ej. Editorial Planeta Perú" required>
        </div>

        <div class="form-group">
            <label for="contacto">Persona de Contacto o Correo:</label>
            <input type="text" name="contacto" id="contacto" class="form-control" value="<?php echo htmlspecialchars($contacto); ?>" placeholder="Ej. contacto@editorial.pe">
        </div>

        <button type="submit" class="btn btn-success btn-block" style="margin-top: 1rem;">💾 Guardar Editorial</button>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>