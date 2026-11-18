<?php
$titulo_pagina = "Añadir Nuevo Libro";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$error = '';

$categorias = mysqli_query($conexion, "SELECT * FROM categorias ORDER BY nombre ASC");
$autores = mysqli_query($conexion, "SELECT * FROM autores ORDER BY nombre ASC");
$editoriales = mysqli_query($conexion, "SELECT * FROM editoriales ORDER BY nombre ASC");
$proveedores = mysqli_query($conexion, "SELECT * FROM proveedores ORDER BY razon_social ASC");

$datos = [
    'titulo' => '',
    'isbn' => '',
    'id_categoria' => '',
    'id_autor' => '',
    'id_editorial' => '',
    'id_proveedor' => '',
    'precio_venta' => '0.00',
    'precio_alquiler' => '0.00',
    'stock' => '10',
    'fecha_publicacion' => date('Y-m-d'),
    'estado' => 'DISPONIBLE'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['titulo'] = trim(mysqli_real_escape_string($conexion, $_POST['titulo']));
    $datos['isbn'] = trim(mysqli_real_escape_string($conexion, $_POST['isbn']));
    $datos['id_categoria'] = intval($_POST['id_categoria']);
    $datos['id_autor'] = intval($_POST['id_autor']);
    $datos['id_editorial'] = intval($_POST['id_editorial']);
    $datos['id_proveedor'] = intval($_POST['id_proveedor']);
    $datos['precio_venta'] = floatval($_POST['precio_venta']);
    $datos['precio_alquiler'] = floatval($_POST['precio_alquiler']);
    $datos['stock'] = intval($_POST['stock']);
    $datos['fecha_publicacion'] = mysqli_real_escape_string($conexion, $_POST['fecha_publicacion']);
    $datos['estado'] = mysqli_real_escape_string($conexion, $_POST['estado']);

    if (empty($datos['titulo']) || empty($datos['isbn']) || $datos['id_categoria'] === 0 || $datos['id_autor'] === 0) {
        $error = "Complete los campos obligatorios (*).";
    } else {
        $chk_isbn = mysqli_query($conexion, "SELECT id_libro FROM libros WHERE isbn = '{$datos['isbn']}' LIMIT 1");
        if (mysqli_num_rows($chk_isbn) > 0) {
            $error = "El código ISBN ingresado ya está registrado para otro libro.";
        } else {
            // Manejo de Carga de Imagen de Portada
            $ruta_portada = 'img/no-cover.png'; // Comodín por defecto

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
                    $error = "Formato de imagen no válido. Formatos permitidos: JPG, PNG, WEBP.";
                }
            }

            if (empty($error)) {
                $query = "INSERT INTO libros (id_categoria, id_autor, id_editorial, id_proveedor, titulo, isbn, precio_venta, precio_alquiler, stock, portada, fecha_publicacion, estado) 
                          VALUES ({$datos['id_categoria']}, {$datos['id_autor']}, {$datos['id_editorial']}, {$datos['id_proveedor']}, '{$datos['titulo']}', '{$datos['isbn']}', {$datos['precio_venta']}, {$datos['precio_alquiler']}, {$datos['stock']}, '$ruta_portada', '{$datos['fecha_publicacion']}', '{$datos['estado']}')";

                if (mysqli_query($conexion, $query)) {
                    $id_libro_nuevo = mysqli_insert_id($conexion);

                    // Registrar Carga Inicial en KÁRDEX
                    if ($datos['stock'] > 0) {
                        $kardex_sql = "INSERT INTO kardex (id_libro, tipo_movimiento, cantidad, stock_anterior, stock_actual, motivo) 
                                       VALUES ($id_libro_nuevo, 'ENTRADA', {$datos['stock']}, 0, {$datos['stock']}, 'Carga inicial al registrar libro')";
                        mysqli_query($conexion, $kardex_sql);
                    }

                    $_SESSION['mensaje'] = "Libro registrado exitosamente en el inventario.";
                    $_SESSION['tipo_mensaje'] = "success";
                    header("Location: " . BASE_URL . "modulos/libros/index.php");
                    exit();
                } else {
                    $error = "Error al guardar el libro: " . mysqli_error($conexion);
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
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Registrar Nuevo Libro</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
            <div>
                <div class="form-group">
                    <label for="titulo">Título del Libro (*):</label>
                    <input type="text" name="titulo" id="titulo" class="form-control" value="<?php echo htmlspecialchars($datos['titulo']); ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="isbn">Código ISBN (*):</label>
                        <input type="text" name="isbn" id="isbn" class="form-control" value="<?php echo htmlspecialchars($datos['isbn']); ?>" placeholder="978-..." required>
                    </div>
                    <div class="form-group">
                        <label for="id_categoria">Categoría (*):</label>
                        <select name="id_categoria" id="id_categoria" class="form-control" required>
                            <option value="">-- Seleccionar --</option>
                            <?php while ($c = mysqli_fetch_assoc($categorias)): ?>
                                <option value="<?php echo $c['id_categoria']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="id_autor">Autor (*):</label>
                        <select name="id_autor" id="id_autor" class="form-control" required>
                            <option value="">-- Seleccionar --</option>
                            <?php while ($a = mysqli_fetch_assoc($autores)): ?>
                                <option value="<?php echo $a['id_autor']; ?>"><?php echo htmlspecialchars($a['nombre']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_editorial">Editorial (*):</label>
                        <select name="id_editorial" id="id_editorial" class="form-control" required>
                            <option value="">-- Seleccionar --</option>
                            <?php while ($e = mysqli_fetch_assoc($editoriales)): ?>
                                <option value="<?php echo $e['id_editorial']; ?>"><?php echo htmlspecialchars($e['nombre']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="id_proveedor">Proveedor / Suministrador (*):</label>
                    <select name="id_proveedor" id="id_proveedor" class="form-control" required>
                        <option value="">-- Seleccionar --</option>
                        <?php while ($p = mysqli_fetch_assoc($proveedores)): ?>
                            <option value="<?php echo $p['id_proveedor']; ?>"><?php echo htmlspecialchars($p['razon_social']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <!-- Columna lateral para Portada y Precios -->
            <div>
                <div class="form-group">
                    <label>Portada del Libro:</label>
                    <input type="file" name="portada" id="input-portada" class="form-control" accept="image/*">
                    <div class="preview-container" style="margin-top: 10px; width: 100%; height: 180px;">
                        <img src="<?php echo BASE_URL; ?>img/no-cover.png" id="img-preview" class="img-preview" alt="Previsualización">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <div class="form-group">
                        <label for="precio_venta">P. Venta (S/):</label>
                        <input type="number" step="0.10" name="precio_venta" id="precio_venta" class="form-control" value="<?php echo $datos['precio_venta']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="precio_alquiler">Alquiler (S/):</label>
                        <input type="number" step="0.10" name="precio_alquiler" id="precio_alquiler" class="form-control" value="<?php echo $datos['precio_alquiler']; ?>" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <div class="form-group">
                        <label for="stock">Stock Inicial:</label>
                        <input type="number" name="stock" id="stock" class="form-control" value="<?php echo $datos['stock']; ?>" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <select name="estado" id="estado" class="form-control">
                            <option value="DISPONIBLE">DISPONIBLE</option>
                            <option value="AGOTADO">AGOTADO</option>
                            <option value="DESCONTINUADO">DESCONTINUADO</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="fecha_publicacion">Fecha Edición:</label>
                    <input type="date" name="fecha_publicacion" id="fecha_publicacion" class="form-control" value="<?php echo $datos['fecha_publicacion']; ?>">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-block" style="margin-top: 1.5rem;">💾 Guardar Libro en Catálogo</button>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>