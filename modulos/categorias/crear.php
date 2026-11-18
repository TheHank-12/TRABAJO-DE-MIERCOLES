<?php
$titulo_pagina = "Registrar Categoría";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$error = '';
$nombre = '';
$descripcion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim(mysqli_real_escape_string($conexion, $_POST['nombre']));
    $descripcion = trim(mysqli_real_escape_string($conexion, $_POST['descripcion']));

    if (empty($nombre)) {
        $error = "El nombre de la categoría es obligatorio.";
    } else {
        $chk = mysqli_query($conexion, "SELECT id_categoria FROM categorias WHERE nombre = '$nombre' LIMIT 1");
        if (mysqli_num_rows($chk) > 0) {
            $error = "Ya existe una categoría con ese mismo nombre.";
        } else {
            $query = "INSERT INTO categorias (nombre, descripcion) VALUES ('$nombre', '$descripcion')";
            if (mysqli_query($conexion, $query)) {
                $_SESSION['mensaje'] = "Categoría creada exitosamente.";
                $_SESSION['tipo_mensaje'] = "success";
                header("Location: " . BASE_URL . "modulos/categorias/index.php");
                exit();
            } else {
                $error = "Error al guardar la categoría: " . mysqli_error($conexion);
            }
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>modulos/categorias/index.php" class="btn btn-secondary">⬅️ Volver a Categorías</a>
</div>

<div class="form-card">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Crear Nueva Categoría</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre de la Categoría (*):</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($nombre); ?>" placeholder="Ej. Novela Histórica" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion" id="descripcion" class="form-control" rows="4" placeholder="Breve detalle sobre esta categoría..."><?php echo htmlspecialchars($descripcion); ?></textarea>
        </div>

        <button type="submit" class="btn btn-success btn-block" style="margin-top: 1rem;">💾 Guardar Categoría</button>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>