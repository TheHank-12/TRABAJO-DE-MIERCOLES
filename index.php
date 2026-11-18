<?php
$titulo_pagina = "Catálogo de Libros";
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/config/sesion.php';

// Captura de filtros
$categoria_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$editorial_id = isset($_GET['editorial']) ? intval($_GET['editorial']) : 0;
$orden        = isset($_GET['orden']) ? $_GET['orden'] : 'recientes';
$busqueda     = isset($_GET['q']) ? trim(mysqli_real_escape_string($conexion, $_GET['q'])) : '';

// Consulta de Categorías
$sql_cat = "SELECT * FROM categorias ORDER BY nombre ASC";
$res_cat = mysqli_query($conexion, $sql_cat);

// Consulta de Editoriales
$sql_edit = "SELECT * FROM editoriales ORDER BY nombre ASC";
$res_edit = mysqli_query($conexion, $sql_edit);

// Definición de Criterio de Ordenamiento
switch ($orden) {
    case 'precio_asc':  $order_by = "l.precio_venta ASC"; break;
    case 'precio_desc': $order_by = "l.precio_venta DESC"; break;
    case 'rating':      $order_by = "promedio_estrellas DESC"; break;
    case 'titulo_asc':  $order_by = "l.titulo ASC"; break;
    default:            $order_by = "l.id_libro DESC"; break;
}

// 1. CONSULTA: Libros mejor valorados (Top Rated)
$sql_top = "SELECT l.*, c.nombre AS categoria, a.nombre AS autor,
                   COALESCE(AVG(r.puntuacion), 0) AS promedio_estrellas,
                   COUNT(r.id_resena) AS total_reseñas
            FROM libros l
            INNER JOIN categorias c ON l.id_categoria = c.id_categoria
            INNER JOIN autores a ON l.id_autor = a.id_autor
            LEFT JOIN resenas r ON l.id_libro = r.id_libro
            WHERE l.estado = 'DISPONIBLE'
            GROUP BY l.id_libro
            HAVING promedio_estrellas > 0
            ORDER BY promedio_estrellas DESC, total_reseñas DESC
            LIMIT 4";
$res_top = mysqli_query($conexion, $sql_top);

// 2. CONSULTA: Catálogo General con Filtros por Categoría, Editorial y Orden
$sql_libros = "SELECT l.*, c.nombre AS categoria, a.nombre AS autor, e.nombre AS editorial,
               COALESCE(AVG(r.puntuacion), 0) AS promedio_estrellas,
               COUNT(r.id_resena) AS total_reseñas
               FROM libros l
               INNER JOIN categorias c ON l.id_categoria = c.id_categoria
               INNER JOIN autores a ON l.id_autor = a.id_autor
               INNER JOIN editoriales e ON l.id_editorial = e.id_editorial
               LEFT JOIN resenas r ON l.id_libro = r.id_libro
               WHERE l.estado = 'DISPONIBLE'";

if ($categoria_id > 0) {
    $sql_libros .= " AND l.id_categoria = $categoria_id";
}

if ($editorial_id > 0) {
    $sql_libros .= " AND l.id_editorial = $editorial_id";
}

if (!empty($busqueda)) {
    $sql_libros .= " AND (l.titulo LIKE '%$busqueda%' OR a.nombre LIKE '%$busqueda%' OR l.isbn LIKE '%$busqueda%')";
}

$sql_libros .= " GROUP BY l.id_libro ORDER BY $order_by";
$res_libros = mysqli_query($conexion, $sql_libros);

include_once __DIR__ . '/includes/header.php';
?>

<!-- 1. BANNER DE BIENVENIDA -->
<div style="background-color: var(--secondary); color: #fff; padding: 2rem; border-radius: var(--radius); margin-bottom: 2rem; text-align: center;">
    <h1 style="color: var(--accent); margin-bottom: 0.5rem;">Bienvenido a la Librería Vallejo</h1>
    <p style="font-size: 1.1rem; max-width: 800px; margin: 0 auto;">
        Explore nuestro amplio catálogo de obras literarias, novedades académicas y clásicos. Disponibles para venta y alquiler a nivel nacional.
    </p>
</div>

<!-- 2. SECCIÓN PRINCIPAL: LIBROS MEJOR VALORADOS -->
<?php if ($res_top && mysqli_num_rows($res_top) > 0): ?>
    <div style="margin-bottom: 2.5rem; background: #fff8f0; padding: 1.5rem; border-radius: var(--radius); border: 1px solid #ffe8d6;">
        <h2 style="color: #d97706; margin-top: 0; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
            ⭐ Libros Mejor Valorados por la Comunidad
        </h2>
        <div class="grid-libros">
            <?php while ($top = mysqli_fetch_assoc($res_top)): ?>
                <div class="card-libro" style="border: 2px solid #fef3c7;">
                    <div class="card-portada-wrapper">
                        <img src="<?php echo BASE_URL . htmlspecialchars($top['portada']); ?>" alt="Portada" class="card-portada" onerror="this.src='<?php echo BASE_URL; ?>img/no-cover.png.jpg'">
                        <span class="card-badge-stock" style="background: #d97706;">TOP ★</span>
                    </div>
                    <div class="card-body">
                        <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #d97706; margin-bottom: 4px;">
                            <?php echo htmlspecialchars($top['categoria']); ?>
                        </span>
                        <h3 class="card-titulo"><?php echo htmlspecialchars($top['titulo']); ?></h3>
                        <p class="card-autor">Por: <?php echo htmlspecialchars($top['autor']); ?></p>

                        <div class="card-estrellas" style="color: #f59e0b;">
                            <?php 
                            $prom_top = round($top['promedio_estrellas']);
                            for ($i = 1; $i <= 5; $i++) {
                                echo ($i <= $prom_top) ? '★' : '☆';
                            }
                            ?>
                            <span style="font-size: 0.8rem; color: var(--text-muted);"> (<?php echo $top['total_reseñas']; ?>)</span>
                        </div>

                        <div class="card-precios">
                            <div>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display:block;">Venta</span>
                                <span class="precio-venta">S/ <?php echo number_format($top['precio_venta'], 2); ?></span>
                            </div>
                        </div>

                        <div class="card-acciones" style="margin-top: 0.8rem;">
                            <a href="<?php echo BASE_URL; ?>modulos/libros/detalle.php?id=<?php echo $top['id_libro']; ?>" class="btn btn-primary btn-sm btn-block">👁️ Ver Detalle</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
<?php endif; ?>

<!-- ANCLA DE NAVEGACIÓN (Evita que el scroll regrese al inicio al filtrar) -->
<div id="catalogo"></div>

<!-- 3. BARRA DE FILTROS Y BÚSQUEDA DEL CATÁLOGO -->
<form action="index.php#catalogo" method="GET" style="background: #ffffff; padding: 1.2rem; border-radius: var(--radius); border: 1px solid var(--border-color); margin-bottom: 1.5rem;">
    <?php if ($categoria_id > 0): ?>
        <input type="hidden" name="categoria" value="<?php echo $categoria_id; ?>">
    <?php endif; ?>

    <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <!-- Búsqueda por Texto -->
        <div style="flex: 2; min-width: 220px;">
            <input type="text" name="q" class="input-search" placeholder="Buscar por título, autor o ISBN..." value="<?php echo htmlspecialchars($busqueda); ?>" style="width: 100%;">
        </div>

        <!-- Filtro por Editorial -->
        <div style="flex: 1; min-width: 180px;">
            <select name="editorial" class="form-control" onchange="this.form.submit()">
                <option value="0">-- Todas las Editoriales --</option>
                <?php while ($ed = mysqli_fetch_assoc($res_edit)): ?>
                    <option value="<?php echo $ed['id_editorial']; ?>" <?php echo $editorial_id === (int)$ed['id_editorial'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ed['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Filtro de Ordenamiento -->
        <div style="flex: 1; min-width: 180px;">
            <select name="orden" class="form-control" onchange="this.form.submit()">
                <option value="recientes" <?php echo $orden === 'recientes' ? 'selected' : ''; ?>>🆕 Más recientes</option>
                <option value="precio_asc" <?php echo $orden === 'precio_asc' ? 'selected' : ''; ?>>💲 Precio: Menor a Mayor</option>
                <option value="precio_desc" <?php echo $orden === 'precio_desc' ? 'selected' : ''; ?>>💲 Precio: Mayor a Menor</option>
                <option value="rating" <?php echo $orden === 'rating' ? 'selected' : ''; ?>>⭐ Mejor valorados</option>
                <option value="titulo_asc" <?php echo $orden === 'titulo_asc' ? 'selected' : ''; ?>>🔤 Título (A - Z)</option>
            </select>
        </div>

        <!-- Botones -->
        <div style="display: flex; gap: 8px; align-items: center;">
            <button type="submit" class="btn btn-primary">🔍 Buscar</button>
            <?php if (!empty($busqueda) || $categoria_id > 0 || $editorial_id > 0 || $orden !== 'recientes'): ?>
                <a href="index.php#catalogo" class="btn btn-danger" title="Limpiar todos los filtros">✖ Limpiar</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- 4. CHIPS DE FILTRO POR CATEGORÍA -->
<div style="margin-bottom: 2rem; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
    <span style="font-weight: 600; font-size: 0.9rem; margin-right: 5px;">Categoría:</span>
    <a href="index.php?editorial=<?php echo $editorial_id; ?>&orden=<?php echo $orden; ?>&q=<?php echo urlencode($busqueda); ?>#catalogo" 
       class="btn btn-sm <?php echo $categoria_id === 0 ? 'btn-primary' : 'btn-secondary'; ?>">Todas</a>
    
    <?php while ($cat = mysqli_fetch_assoc($res_cat)): ?>
        <a href="index.php?categoria=<?php echo $cat['id_categoria']; ?>&editorial=<?php echo $editorial_id; ?>&orden=<?php echo $orden; ?>&q=<?php echo urlencode($busqueda); ?>#catalogo" 
           class="btn btn-sm <?php echo $categoria_id === (int)$cat['id_categoria'] ? 'btn-primary' : 'btn-secondary'; ?>">
            <?php echo htmlspecialchars($cat['nombre']); ?>
        </a>
    <?php endwhile; ?>
</div>

<!-- 5. CATÁLOGO GENERAL DE LIBROS -->
<div class="catalogo-header">
    <h2>Catálogo de Libros (<?php echo mysqli_num_rows($res_libros); ?> disponibles)</h2>
</div>

<?php if (mysqli_num_rows($res_libros) === 0): ?>
    <div class="alerta-box alerta-info">
        <div class="alerta-icono">ℹ️</div>
        <div class="alerta-contenido">No se encontraron libros que coincidan con los criterios de búsqueda o filtros especificados.</div>
    </div>
<?php else: ?>
    <div class="grid-libros">
        <?php while ($libro = mysqli_fetch_assoc($res_libros)): ?>
            <div class="card-libro">
                <div class="card-portada-wrapper">
                    <img src="<?php echo BASE_URL . htmlspecialchars($libro['portada']); ?>" alt="Portada de <?php echo htmlspecialchars($libro['titulo']); ?>" class="card-portada" onerror="this.src='<?php echo BASE_URL; ?>img/no-cover.png.jpg'">
                    <span class="card-badge-stock">Stock: <?php echo $libro['stock']; ?></span>
                </div>
                <div class="card-body">
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--primary); margin-bottom: 4px;">
                        <?php echo htmlspecialchars($libro['categoria']); ?>
                    </span>
                    <h3 class="card-titulo"><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                    <p class="card-autor">Por: <?php echo htmlspecialchars($libro['autor']); ?></p>

                    <div class="card-estrellas">
                        <?php 
                        $prom = round($libro['promedio_estrellas']);
                        for ($i = 1; $i <= 5; $i++) {
                            echo ($i <= $prom) ? '★' : '☆';
                        }
                        ?>
                        <span style="font-size: 0.8rem; color: var(--text-muted);"> (<?php echo $libro['total_reseñas']; ?>)</span>
                    </div>

                    <div class="card-precios">
                        <div>
                            <span style="font-size: 0.75rem; color: var(--text-muted); display:block;">Venta</span>
                            <span class="precio-venta">S/ <?php echo number_format($libro['precio_venta'], 2); ?></span>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.75rem; color: var(--text-muted); display:block;">Alquiler/Día</span>
                            <span class="precio-alquiler">S/ <?php echo number_format($libro['precio_alquiler'], 2); ?></span>
                        </div>
                    </div>

                    <div class="card-acciones">
                        <a href="<?php echo BASE_URL; ?>modulos/libros/detalle.php?id=<?php echo $libro['id_libro']; ?>" class="btn btn-secondary btn-sm" style="flex: 1;">👁️ Detalle</a>
                        
                        <?php if ($libro['stock'] > 0): ?>
                            <form action="<?php echo BASE_URL; ?>modulos/carrito/agregar.php" method="POST" style="flex: 1;">
                                <input type="hidden" name="id_libro" value="<?php echo $libro['id_libro']; ?>">
                                <input type="hidden" name="cantidad" value="1">
                                <button type="submit" class="btn btn-primary btn-sm btn-block">🛒 Añadir</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-sm" style="flex: 1; background-color: #ccc; cursor: not-allowed;" disabled>Agotado</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php include_once __DIR__ . '/includes/footer.php'; ?>