<?php
$titulo_pagina = "Carrito de Compras";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

inicializar_carrito();

$carrito = $_SESSION['carrito'];
$subtotal_general = 0.00;
$igv_general = 0.00;
$total_general = 0.00;

// Consulta de usuarios clientes para checkout si el operador es Admin (Rol 2 = Cliente)
$clientes_res = null;
if (es_admin()) {
    $clientes_res = mysqli_query($conexion, "SELECT id_usuario, dni, nombre, apellido FROM usuarios WHERE estado = 'ACTIVO' AND id_rol = 2 ORDER BY nombre ASC");
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <h2>🛒 Carrito de Compras Multi-Producto</h2>
    <p style="color: var(--text-muted);">Revise los libros seleccionados antes de proceder al pago en Soles (S/)</p>
</div>

<?php if (empty($carrito)): ?>
    <div class="form-card" style="text-align: center; padding: 3rem;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">🛒</div>
        <h3>Su carrito de compras está vacío</h3>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Explore nuestro catálogo para agregar las mejores obras literarias.</p>
        <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-primary">📚 Explorar Catálogo</a>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: 2.5fr 1.2fr; gap: 1.5rem; align-items: start;">
        
        <!-- Lista de Productos en Carrito -->
        <div>
            <div class="table-responsive" style="background: #fff; border-radius: var(--radius); border: 1px solid var(--border-color);">
                <table class="tabla-custom">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Portada</th>
                            <th>Libro</th>
                            <th>Precio Unit.</th>
                            <th style="text-align: center;">Cantidad</th>
                            <th>Subtotal</th>
                            <th style="text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($carrito as $id_libro => $cantidad): 
                            $id_libro = (int)$id_libro;
                            $cantidad = (int)$cantidad;

                            // Consulta de los datos del libro directamente desde la BD
                            $res_l = mysqli_query($conexion, "SELECT id_libro, titulo, isbn, precio_venta, stock, portada FROM libros WHERE id_libro = $id_libro LIMIT 1");
                            if (!$res_l || mysqli_num_rows($res_l) === 0) continue;

                            $libro = mysqli_fetch_assoc($res_l);
                            $precio_unitario = (float)$libro['precio_venta'];
                            $sub = $precio_unitario * $cantidad;
                            $subtotal_general += $sub;

                            $portada_url = !empty($libro['portada']) ? BASE_URL . htmlspecialchars($libro['portada']) : BASE_URL . 'img/no-cover.png';
                        ?>
                            <tr>
                                <td style="text-align: center;">
                                    <img src="<?php echo $portada_url; ?>" alt="Portada" style="width: 40px; height: 55px; object-fit: cover; border-radius: 4px;" onerror="this.onerror=null; this.src='<?php echo BASE_URL; ?>img/no-cover.png';">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($libro['titulo']); ?></strong><br>
                                    <small style="color: var(--text-muted);">ISBN: <?php echo htmlspecialchars($libro['isbn']); ?></small>
                                </td>
                                <td>S/ <?php echo number_format($precio_unitario, 2); ?></td>
                                <td>
                                    <form action="<?php echo BASE_URL; ?>modulos/carrito/actualizar.php" method="POST" style="display: flex; gap: 5px; align-items: center; justify-content: center;">
                                        <input type="hidden" name="id_libro" value="<?php echo $id_libro; ?>">
                                        <input type="number" 
                                               name="cantidad" 
                                               value="<?php echo $cantidad; ?>" 
                                               min="1" 
                                               max="<?php echo (int)$libro['stock']; ?>" 
                                               class="form-control" 
                                               style="width: 65px; text-align: center; padding: 4px;" 
                                               required>
                                        <button type="submit" class="btn btn-secondary btn-sm" title="Actualizar cantidad">🔄</button>
                                    </form>
                                </td>      
                                <td><strong>S/ <?php echo number_format($sub, 2); ?></strong></td>
                                <td style="text-align: center;">
                                    <a href="<?php echo BASE_URL; ?>modulos/carrito/eliminar.php?id=<?php echo $id_libro; ?>" class="btn btn-danger btn-sm" title="Quitar del carrito">🗑️</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php
                        // Cálculos de Totales (IGV 18%)
                        $igv_general = $subtotal_general * 0.18;
                        $total_general = $subtotal_general + $igv_general;
                        ?>
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-secondary">⬅️ Seguir Comprando</a>
                <a href="<?php echo BASE_URL; ?>modulos/carrito/eliminar.php?vaciar=1" class="btn btn-danger btn-sm" onclick="return confirm('¿Desea vaciar todo el carrito?');">🗑️ Vaciar Carrito</a>
            </div>
        </div>

        <!-- Resumen de Pago y Checkout -->
        <div>
            <div class="form-card" style="max-width: 100%; padding: 1.5rem;">
                <h3 style="margin-bottom: 1rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Resumen de Compra</h3>

                <div style="font-size: 0.95rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Subtotal (Base Imponible):</span>
                        <strong>S/ <?php echo number_format($subtotal_general, 2); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>IGV (18%):</span>
                        <strong>S/ <?php echo number_format($igv_general, 2); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 1.25rem; color: var(--primary); font-weight: 700; border-top: 1px solid var(--border-color); padding-top: 0.5rem;">
                        <span>TOTAL A PAGAR:</span>
                        <span>S/ <?php echo number_format($total_general, 2); ?></span>
                    </div>
                </div>

                <?php if (esta_autenticado()): ?>
                    <!-- Formulario de Checkout -->
                    <form action="<?php echo BASE_URL; ?>modulos/carrito/procesar.php" method="POST" id="form-checkout">
                        <?php if (es_admin()): ?>
                            <div class="form-group">
                                <label for="id_usuario_cliente">Cliente de la Venta (*):</label>
                                <select name="id_usuario_cliente" id="id_usuario_cliente" class="form-control" required>
                                    <option value="">-- Seleccionar Cliente --</option>
                                    <?php if ($clientes_res): ?>
                                        <?php while ($c = mysqli_fetch_assoc($clientes_res)): ?>
                                            <option value="<?php echo $c['id_usuario']; ?>">
                                                <?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido']); ?> (DNI: <?php echo htmlspecialchars($c['dni']); ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="id_usuario_cliente" value="<?php echo $_SESSION['usuario_id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="metodo_pago">Método de Pago (*):</label>
                            <select name="metodo_pago" id="metodo_pago" class="form-control" required>
                                <option value="EFECTIVO">💵 Efectivo en Mostrador</option>
                                <option value="YAPE">📱 Yape (QR / Teléfono)</option>
                                <option value="PLIN">📲 Plin (QR / Teléfono)</option>
                                <option value="TARJETA">💳 Tarjeta de Crédito / Débito</option>
                            </select>
                        </div>

                        <!-- Campo Oculto para capturar el Nº de Operación de los Modales -->
                        <input type="hidden" name="num_operacion" id="num_operacion_hidden" value="">

                        <!-- Resumen visual del pago seleccionado -->
                        <div id="resumen-pago-seleccionado" style="margin-bottom: 1rem; font-size: 0.9rem; font-weight: 600; color: var(--primary);"></div>

                        <button type="submit" class="btn btn-success btn-block btn-lg" id="btn-submit-checkout">
                            💳 Finalizar Compra y Generar Boleta
                        </button>
                    </form>

                    <!-- MODAL 1: PAGO DIGITAL (YAPE / PLIN) -->
                    <div class="modal-overlay" id="modal-pago-digital">
                        <div class="modal-content" style="max-width: 400px; text-align: center;">
                            <h3 id="modal-digital-titulo" style="color: var(--secondary); margin-bottom: 0.5rem;">Pago con Billetera Digital</h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">Escanee el código QR desde la app de su celular</p>
                            
                            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; border: 1px dashed #ccc; margin-bottom: 1rem;">
                                <img src="<?php echo BASE_URL; ?>img/qr_yape.png" id="modal-qr-img" alt="Código QR" style="width: 180px; height: 180px; object-fit: contain; margin: 0 auto 0.5rem auto; display: block;" onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'180\' height=\'180\' viewBox=\'0 0 180 180\'><rect width=\'180\' height=\'180\' fill=\'%23f1f5f9\'/><text x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%2364748b\' font-size=\'14\'>QR PAGO DIGITAL</text></svg>';">
                                <span id="modal-digital-numero" style="font-size: 1.1rem; font-weight: 700; color: var(--primary);">Celular: 987 654 321</span>
                            </div>

                            <div class="form-group" style="text-align: left;">
                                <label for="input-num-op-digital">Nº de Operación (*):</label>
                                <input type="text" id="input-num-op-digital" class="form-control" maxlength="15" placeholder="Ej. YP-984321 / 443210">
                            </div>

                            <div class="modal-acciones">
                                <button type="button" class="btn btn-secondary" onclick="cerrarModalPago('modal-pago-digital');">Cancelar</button>
                                <button type="button" class="btn btn-success" onclick="confirmarPagoDigital();">Confirmar Pago</button>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL 2: PASARELA DE TARJETA DE CRÉDITO/DÉBITO -->
                    <div class="modal-overlay" id="modal-pago-tarjeta">
                        <div class="modal-content" style="max-width: 450px; text-align: left;">
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 0.8rem; margin-bottom: 1rem;">
                                <h3 style="margin: 0; color: var(--secondary);">💳 Pasarela de Pago Segura</h3>
                                <span style="font-size: 0.8rem; background: #e2e8f0; padding: 2px 6px; border-radius: 4px;">SSL 256-bit</span>
                            </div>

                            <div class="form-group">
                                <label for="card-number">Número de Tarjeta (*):</label>
                                <input type="text" id="card-number" class="form-control" maxlength="19" placeholder="4557 0000 0000 0000" autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label for="card-holder">Nombre del Titular (*):</label>
                                <input type="text" id="card-holder" class="form-control" placeholder="JUAN PEREZ ALVAREZ" style="text-transform: uppercase;">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label for="card-exp">Vencimiento (*):</label>
                                    <input type="text" id="card-exp" class="form-control" maxlength="5" placeholder="MM/AA">
                                </div>
                                <div class="form-group">
                                    <label for="card-cvv">CVV (*):</label>
                                    <input type="password" id="card-cvv" class="form-control" maxlength="4" placeholder="123">
                                </div>
                            </div>

                            <div class="modal-acciones" style="margin-top: 1rem;">
                                <button type="button" class="btn btn-secondary" onclick="cerrarModalPago('modal-pago-tarjeta');">Cancelar</button>
                                <button type="button" class="btn btn-primary" onclick="confirmarPagoTarjeta();">Validar y Pagar</button>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="alerta-box alerta-warning" style="margin-top: 1.5rem;">
                        <div class="alerta-icono">⚠️</div>
                        <div class="alerta-contenido">
                            Debe <a href="<?php echo BASE_URL; ?>modulos/auth/login.php" style="color: var(--primary); font-weight: 700;">Iniciar Sesión</a> o registrarse para finalizar su pedido.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>