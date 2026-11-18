<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['mensaje']) && !empty($_SESSION['mensaje'])):
    $tipo = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : 'info';
    $mensaje = $_SESSION['mensaje'];
    
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
?>
    <div class="alerta-box alerta-<?php echo htmlspecialchars($tipo); ?>" id="alerta-sistema">
        <div class="alerta-icono">
            <?php 
                switch($tipo) {
                    case 'success': echo '✅'; break;
                    case 'danger': echo '❌'; break;
                    case 'warning': echo '⚠️'; break;
                    default: echo 'ℹ️'; break;
                }
            ?>
        </div>
        <div class="alerta-contenido">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
        <button type="button" class="alerta-cerrar" onclick="document.getElementById('alerta-sistema').remove();">&times;</button>
    </div>
<?php endif; ?>