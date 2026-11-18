<?php
$titulo_pagina = "Detalle del Libro";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

$id_libro = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql_libro = "SELECT l.*, c.nombre AS categoria, a.nombre AS autor, a.nacionalidad AS autor_nacionalidad, 
                     e.nombre AS editorial, p.razon_social AS proveedor,
                     COALESCE(AVG(r.puntuacion), 0) AS promedio_estrellas,
                     COUNT(r.id_resena) AS total_reseñas
              FROM libros l
              INNER JOIN categorias c ON l.id_categoria = c.id_categoria
              INNER JOIN autores a ON l.id_autor = a.id_autor
              INNER JOIN editoriales e ON l.id_editorial = e.id_editorial
              INNER JOIN proveedores p ON l.id_proveedor = p.id_proveedor
              LEFT JOIN resenas r ON l.id_libro = r.id_libro
              WHERE l.id_libro = $id_libro
              GROUP BY l.id_libro LIMIT 1";

$res = mysqli_query($conexion, $sql_libro);

if (!$res || mysqli_num_rows($res) === 0) {
    $_SESSION['mensaje'] = "El libro solicitado no se encuentra registrado.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$libro = mysqli_fetch_assoc($res);

// Cargar Reseñas asociadas
$sql_resenas = "SELECT r.*, u.nombre, u.apellido 
                FROM resenas r 
                INNER JOIN usuarios u ON r.id_usuario = u.id_usuario 
                WHERE r.id_libro = $id_libro 
                ORDER BY r.fecha DESC";
$res_resenas = mysqli_query($conexion, $sql_resenas);

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-secondary">⬅️ Volver al Catálogo</a>
</div>

<div class="form-card" style="max-width: 900px; padding: 2.5rem;">
    <div style="display: grid; grid-template-columns: 280px 1fr; gap: 2rem;">
        <div>
            <img src="<?php echo BASE_URL . htmlspecialchars($libro['portada']); ?>" alt="<?php echo htmlspecialchars($libro['titulo']); ?>" style="width: 100%; border-radius: 8px; box-shadow: var(--shadow-md);" onerror="this.src='<?php echo BASE_URL; ?>img/no-cover.png'">
            
            <div style="margin-top: 1.5rem; background: var(--bg-light); padding: 1rem; border-radius: var(--radius); text-align: center;">
                <span style="font-size: 0.85rem; color: var(--text-muted); display: block;">Precio de Venta</span>
                <h2 style="color: var(--primary); margin-bottom: 0.5rem;">S/ <?php echo number_format($libro['precio_venta'], 2); ?></h2>
                
                <span style="font-size: 0.85rem; color: var(--text-muted); display: block;">Precio de Alquiler por día</span>
                <h4 style="color: var(--secondary); margin-bottom: 1rem;">S/ <?php echo number_format($libro['precio_alquiler'], 2); ?></h4>

                <?php if ($libro['stock'] > 0): ?>
                    <form action="<?php echo BASE_URL; ?>modulos/carrito/agregar.php" method="POST">
                        <input type="hidden" name="id_libro" value="<?php echo $libro['id_libro']; ?>">
                        <div style="display: flex; gap: 8px; margin-bottom: 0.8rem;">
                            <button type="submit" class="btn btn-primary btn-block">🛒 Añadir al Carrito</button>
                        </div>
                    </form>
                    <a href="<?php echo BASE_URL; ?>modulos/alquileres/crear.php?id_libro=<?php echo $libro['id_libro']; ?>" class="btn btn-success btn-block btn-sm">📖 Solicitar Alquiler</a>
                <?php else: ?>
                    <button class="btn btn-block" style="background-color: #ccc; cursor: not-allowed;" disabled>Agotado temporalmente</button>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <span class="user-role-badge badge-client" style="font-size: 0.85rem; margin-bottom: 0.5rem; display: inline-block;">
                <?php echo htmlspecialchars($libro['categoria']); ?>
            </span>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem; color: var(--secondary);"><?php echo htmlspecialchars($libro['titulo']); ?></h1>
            <p style="font-size: 1.1rem; color: var(--text-muted); margin-bottom: 1rem;">
                Por <strong><?php echo htmlspecialchars($libro['autor']); ?></strong> (<?php echo htmlspecialchars($libro['autor_nacionalidad']); ?>)
            </p>

            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 1.5rem; background: #fff8e7; padding: 0.6rem 1rem; border-radius: var(--radius); border: 1px solid #ffe0b2;">
                <div style="color: var(--accent); font-size: 1.3rem;">
                    <?php 
                    $prom = round($libro['promedio_estrellas']);
                    for ($i = 1; $i <= 5; $i++) {
                        echo ($i <= $prom) ? '★' : '☆';
                    }
                    ?>
                </div>
                <span style="font-weight: 700; font-size: 1.1rem;"><?php echo number_format($libro['promedio_estrellas'], 1); ?> / 5.0</span>
                <span style="color: var(--text-muted); font-size: 0.9rem;">(Basado en <?php echo $libro['total_reseñas']; ?> evaluaciones)</span>
            </div>

            <div style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td style="padding: 8px 0; color: var(--text-muted); width: 40%;"><strong>Código ISBN:</strong></td>
                        <td style="padding: 8px 0; font-family: monospace; font-weight: bold;"><?php echo htmlspecialchars($libro['isbn']); ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td style="padding: 8px 0; color: var(--text-muted);"><strong>Editorial:</strong></td>
                        <td style="padding: 8px 0;"><?php echo htmlspecialchars($libro['editorial']); ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td style="padding: 8px 0; color: var(--text-muted);"><strong>Proveedor Autorizado:</strong></td>
                        <td style="padding: 8px 0;"><?php echo htmlspecialchars($libro['proveedor']); ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td style="padding: 8px 0; color: var(--text-muted);"><strong>Fecha de Edición:</strong></td>
                        <td style="padding: 8px 0;"><?php echo date('d/m/Y', strtotime($libro['fecha_publicacion'])); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: var(--text-muted);"><strong>Stock Físico Disponible:</strong></td>
                        <td style="padding: 8px 0;"><strong><?php echo $libro['stock']; ?> unidades</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Sección de Reseñas y Valoraciones -->
    <div style="margin-top: 3rem; border-top: 2px solid var(--border-color); padding-top: 2rem;">
        <h3 style="margin-bottom: 1.5rem;">💬 Reseñas y Puntuaciones de Lectores</h3>

        <?php if (esta_autenticado()): ?>
            <div style="background: var(--bg-light); padding: 1.5rem; border-radius: var(--radius); margin-bottom: 2rem;">
                <h4>Dejar una Valoración para este Libro</h4>
                <form action="<?php echo BASE_URL; ?>modulos/resenas/guardar.php" method="POST" style="margin-top: 1rem;">
                    <input type="hidden" name="id_libro" value="<?php echo $id_libro; ?>">
                    <input type="hidden" name="puntuacion" id="puntuacion_val" value="5">

                    <div class="form-group">
                        <label>Seleccione Puntuación:</label><br>
                        <div class="star-rating">
                            <input type="radio" id="star5" name="rating" value="5" checked><label for="star5" data-value="5">★</label>
                            <input type="radio" id="star4" name="rating" value="4"><label for="star4" data-value="4">★</label>
                            <input type="radio" id="star3" name="rating" value="3"><label for="star3" data-value="3">★</label>
                            <input type="radio" id="star2" name="rating" value="2"><label for="star2" data-value="2">★</label>
                            <input type="radio" id="star1" name="rating" value="1"><label for="star1" data-value="1">★</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="comentario">Escriba su opinión o reseña:</label>
                        <textarea name="comentario" id="comentario" class="form-control" rows="3" placeholder="¿Qué le pareció la obra?" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">⭐ Publicar Reseña</button>
                </form>
            </div>
        <?php else: ?>
            <div class="alerta-box alerta-info" style="margin-bottom: 2rem;">
                <div class="alerta-icono">ℹ️</div>
                <div class="alerta-contenido">
                    Debe <a href="<?php echo BASE_URL; ?>modulos/auth/login.php" style="color: var(--primary); font-weight: 700;">Iniciar Sesión</a> para escribir una reseña y calificar este libro.
                </div>
            </div>
        <?php endif; ?>

        <!-- Listado de comentarios existentes -->
        <?php if (mysqli_num_rows($res_resenas) === 0): ?>
            <p style="color: var(--text-muted); font-style: italic;">Aún no existen reseñas para este libro. ¡Sé el primero en calificarlo!</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php while ($r = mysqli_fetch_assoc($res_resenas)): ?>
                    <div style="background: #fff; border: 1px solid var(--border-color); padding: 1rem; border-radius: var(--radius);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                            <strong><?php echo htmlspecialchars($r['nombre'] . ' ' . $r['apellido']); ?></strong>
                            <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo date('d/m/Y H:i', strtotime($r['fecha'])); ?></span>
                        </div>
                        <div style="color: var(--accent); margin-bottom: 0.5rem;">
                            <?php 
                            for ($i = 1; $i <= 5; $i++) {
                                echo ($i <= (int)$r['puntuacion']) ? '★' : '☆';
                            }
                            ?>
                        </div>
                        <p style="font-size: 0.95rem; color: var(--text-dark);"><?php echo nl2br(htmlspecialchars($r['comentario'])); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>