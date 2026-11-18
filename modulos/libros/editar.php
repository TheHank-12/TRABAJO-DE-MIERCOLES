<?php
$titulo_pagina = "Editar Libro";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$id_libro = isset($_GET['id']) ? intval($_GET['id']) : 0;
$res_lib = mysqli_query($conexion, "SELECT * FROM libros WHERE id_libro = $id_libro LIMIT 1");

if (!$res_lib || mysqli_num_rows($res_lib) === 0) {
    $_SESSION['mensaje'] = "Libro no encontrado.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: " . BASE_URL . "modulos/libros/index.php");
    exit();
}

$libro = mysqli_fetch_assoc($res_lib);
$error = '';

$categorias = mysqli_query($conexion, "SELECT * FROM categorias ORDER BY nombre ASC");
$autores = mysqli_query($conexion, "SELECT * FROM autores ORDER BY nombre ASC");
$editoriales = mysqli_query($conexion, "SELECT * FROM editoriales ORDER BY nombre ASC");
$proveedores = mysqli_query($conexion, "SELECT * FROM proveedores ORDER BY razon_social ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim(mysqli_real_escape_string($conexion, $_POST['titulo']));
    $isbn = trim(mysqli_real_escape_string($conexion, $_POST['isbn']));
    $id_categoria = intval($_POST['id_categoria']);
    $id_autor = intval($_POST['id_autor']);
    $id_editorial = intval($_POST['id_editorial']);
    $id_proveedor = intval($_POST['id_proveedor']);
    $precio_venta = floatval($_POST['precio_venta']);
    $precio_alquiler = floatval($_POST['precio_alquiler']);
    $stock = intval($_POST['stock']);
    $fecha_publicacion = mysqli_real_escape_string($conexion, $_POST['fecha_publicacion']);
    $estado = mysqli_real_escape_string($conexion, $_POST['estado']);

    if (empty($titulo) || empty($isbn)) {
        $error = "Complete los campos obligatorios (*).";
    } else {
        $chk_isbn = mysqli_query($conexion, "SELECT id_libro FROM libros WHERE isbn = '$isbn' AND id_libro != $id_libro LIMIT 1");
        if (mysqli_num_rows($chk_isbn) > 0) {
            $error = "El código ISBN ya está asignado a otro libro.";
        } else {
            $ruta_portada = $libro['portada']; // Mantener la portada existente si no sube otra

            if (isset($_FILES['portada']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['portada']['name'], PATHINFO_EXTENSION));
                $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($ext, $ext_permitidas)) {
                    $nombre_foto = 'libro_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $directorio_destino = __DIR__ . '/../../uploads/portadas/';

                    if (!is_dir($directorio_destino)) {
                        mkdir($directorio_destino, 0777, true);
                    }

                    if (move_uploaded_file($_FILES['portada']['tmp_name'], $directorio_destino . $nombre_foto)) {
                        $ruta_portada = 'uploads/portadas/' . $nombre_foto;
                    }
                } else {
                    $error = "Formato de imagen no válido. Solo JPG, PNG, WEBP.";
                }
            }

            if (empty($error)) {
                // Registrar ajuste en KÁRDEX si varió el stock
                if ($stock !== (int)$libro['stock']) {
                    $diferencia = $stock - (int)$libro['stock'];
                    $tipo_mov = $diferencia > 0 ? 'ENTRADA' : 'AJUSTE';
                    $cant_abs = abs($diferencia);
                    $kardex_sql = "INSERT INTO kardex (id_libro, tipo_movimiento, cantidad, stock_anterior, stock_actual, motivo) 
                                   VALUES ($id_libro, '$tipo_mov', $cant_abs, {$libro['stock']}, $stock, 'Ajuste manual de stock en edición')";
                    mysqli_query($conexion, $kardex_sql);
                }

                $sql_update = "UPDATE libros SET 
                                id_categoria = $id_categoria, 
                                id_autor = $id_autor, 
                                id_editorial = $id_editorial, 
                                id_proveedor = $id_proveedor, 
                                titulo = '$titulo', 
                                isbn = '$isbn', 
                                precio_venta = $precio_venta, 
                                precio_alquiler = $precio_alquiler, 
                                stock = $stock, 
                                portada = '$ruta_portada', 
                                fecha_publicacion = '$fecha_publicacion', 
                                estado = '$estado' 
                               WHERE id_libro = $id_libro";

                if (mysqli_query($conexion, $sql_update)) {
                    $_SESSION['mensaje'] = "Libro actualizado correctamente.";
                    $_SESSION['tipo_mensaje'] = "success";
                    header("Location: " . BASE_URL . "modulos/libros/index.php");
                    exit();
                } else {
                    $error = "Error al actualizar libro: " . mysqli_error($conexion);
                }
            }
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>modulos/libros/index.php" class="btn btn-secondary">⬅️ Volver a Libros</a>
</div>

<div class="form-card" style="max-width: 850px;">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Editar Libro: <?php echo htmlspecialchars($libro['titulo']); ?></h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $id_libro; ?>" method="POST" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
            <div>
                <div class="form-group">
                    <label for="titulo">Título del Libro (*):</label>
                    <input type="text" name="titulo" id="titulo" class="form-control" value="<?php echo htmlspecialchars($libro['titulo']); ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="isbn">Código ISBN (*):</label>
                        <input type="text" name="isbn" id="isbn" class="form-control" value="<?php echo htmlspecialchars($libro['isbn']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="id_categoria">Categoría (*):</label>
                        <select name="id_categoria" id="id_categoria" class="form-control" required>
                            <?php while ($c = mysqli_fetch_assoc($categorias)): ?>
                                <option value="<?php echo $c['id_categoria']; ?>" <?php echo (int)$libro['id_categoria'] === (int)$c['id_categoria'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="id_autor">Autor (*):</label>
                        <select name="id_autor" id="id_autor" class="form-control" required>
                            <?php while ($a = mysqli_fetch_assoc($autores)): ?>
                                <option value="<?php echo $a['id_autor']; ?>" <?php echo (int)$libro['id_autor'] === (int)$a['id_autor'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($a['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_editorial">Editorial (*):</label>
                        <select name="id_editorial" id="id_editorial" class="form-control" required>
                            <?php while ($e = mysqli_fetch_assoc($editoriales)): ?>
                                <option value="<?php echo $e['id_editorial']; ?>" <?php echo (int)$libro['id_editorial'] === (int)$e['id_editorial'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($e['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="id_proveedor">Proveedor / Suministrador (*):</label>
                    <select name="id_proveedor" id="id_proveedor" class="form-control" required>
                        <?php while ($p = mysqli_fetch_assoc($proveedores)): ?>
                            <option value="<?php echo $p['id_proveedor']; ?>" <?php echo (int)$libro['id_proveedor'] === (int)$p['id_proveedor'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['razon_social']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div>
                <div class="form-group">
                    <label>Reemplazar Portada:</label>
                    <input type="file" name="portada" id="input-portada" class="form-control" accept="image/*">
                    <div class="preview-container" style="margin-top: 10px; width: 100%; height: 180px;">
                        <img src="<?php echo BASE_URL . htmlspecialchars($libro['portada']); ?>" id="img-preview" class="img-preview" alt="Portada actual" onerror="this.src='<?php echo BASE_URL; ?>img/no-cover.png'">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <div class="form-group">
                        <label for="precio_venta">P. Venta (S/):</label>
                        <input type="number" step="0.10" name="precio_venta" id="precio_venta" class="form-control" value="<?php echo $libro['precio_venta']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="precio_alquiler">Alquiler (S/):</label>
                        <input type="number" step="0.10" name="precio_alquiler" id="precio_alquiler" class="form-control" value="<?php echo $libro['precio_alquiler']; ?>" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <div class="form-group">
                        <label for="stock">Stock Actual:</label>
                        <input type="number" name="stock" id="stock" class="form-control" value="<?php echo $libro['stock']; ?>" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <select name="estado" id="estado" class="form-control">
                            <option value="DISPONIBLE" <?php echo $libro['estado'] === 'DISPONIBLE' ? 'selected' : ''; ?>>DISPONIBLE</option>
                            <option value="AGOTADO" <?php echo $libro['estado'] === 'AGOTADO' ? 'selected' : ''; ?>>AGOTADO</option>
                            <option value="DESCONTINUADO" <?php echo $libro['estado'] === 'DESCONTINUADO' ? 'selected' : ''; ?>>DESCONTINUADO</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="fecha_publicacion">Fecha Edición:</label>
                    <input type="date" name="fecha_publicacion" id="fecha_publicacion" class="form-control" value="<?php echo $libro['fecha_publicacion']; ?>">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">💾 Actualizar Datos del Libro</button>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>