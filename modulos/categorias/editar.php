<?php
$titulo_pagina = "Editar Categoría";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$id_categoria = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = "SELECT * FROM categorias WHERE id_categoria = $id_categoria LIMIT 1";
$res = mysqli_query($conexion, $query);

if (!$res || mysqli_num_rows($res) === 0) {
    $_SESSION['mensaje'] = "Categoría no encontrada.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: " . BASE_URL . "modulos/categorias/index.php");
    exit();
}

$cat = mysqli_fetch_assoc($res);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim(mysqli_real_escape_string($conexion, $_POST['nombre']));
    $descripcion = trim(mysqli_real_escape_string($conexion, $_POST['descripcion']));

    if (empty($nombre)) {
        $error = "El nombre de la categoría es obligatorio.";
    } else {
        $chk = mysqli_query($conexion, "SELECT id_categoria FROM categorias WHERE nombre = '$nombre' AND id_categoria != $id_categoria LIMIT 1");
        if (mysqli_num_rows($chk) > 0) {
            $error = "El nombre de categoría especificado ya está en uso.";
        } else {
            $update = "UPDATE categorias SET nombre = '$nombre', descripcion = '$descripcion' WHERE id_categoria = $id_categoria";
            if (mysqli_query($conexion, $update)) {
                $_SESSION['mensaje'] = "Categoría actualizada correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
                header("Location: " . BASE_URL . "modulos/categorias/index.php");
                exit();
            } else {
                $error = "Error al actualizar categoría: " . mysqli_error($conexion);
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
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Editar Categoría</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $id_categoria; ?>" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre de la Categoría (*):</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($cat['nombre']); ?>" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion" id="descripcion" class="form-control" rows="4"><?php echo htmlspecialchars($cat['descripcion']); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">💾 Actualizar Categoría</button>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>