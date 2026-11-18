<?php
$titulo_pagina = "Registrar Autor";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$error = '';
$nombre = '';
$nacionalidad = 'Peruana';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim(mysqli_real_escape_string($conexion, $_POST['nombre']));
    $nacionalidad = trim(mysqli_real_escape_string($conexion, $_POST['nacionalidad']));

    if (empty($nombre)) {
        $error = "El nombre del autor es obligatorio.";
    } else {
        $query = "INSERT INTO autores (nombre, nacionalidad) VALUES ('$nombre', '$nacionalidad')";
        if (mysqli_query($conexion, $query)) {
            $_SESSION['mensaje'] = "Autor guardado correctamente.";
            $_SESSION['tipo_mensaje'] = "success";
            header("Location: " . BASE_URL . "modulos/autores/index.php");
            exit();
        } else {
            $error = "Error al registrar el autor: " . mysqli_error($conexion);
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>modulos/autores/index.php" class="btn btn-secondary">⬅️ Volver a Autores</a>
</div>

<div class="form-card">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Registrar Nuevo Autor</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre Completo del Autor (*):</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($nombre); ?>" placeholder="Ej. Julio Ramón Ribeyro" required>
        </div>

        <div class="form-group">
            <label for="nacionalidad">Nacionalidad:</label>
            <input type="text" name="nacionalidad" id="nacionalidad" class="form-control" value="<?php echo htmlspecialchars($nacionalidad); ?>" placeholder="Ej. Peruana">
        </div>

        <button type="submit" class="btn btn-success btn-block" style="margin-top: 1rem;">💾 Guardar Autor</button>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>