<?php
$titulo_pagina = "Registrar Alquiler";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_personal();

$error = '';
$id_libro_pre = isset($_GET['id_libro']) ? intval($_GET['id_libro']) : 0;

// Obtener clientes activos existentes
$clientes = mysqli_query($conexion, "SELECT id_usuario, dni, nombre, apellido FROM usuarios WHERE estado = 'ACTIVO' AND id_rol = 2 ORDER BY nombre ASC");
// Obtener libros con stock disponible
$libros = mysqli_query($conexion, "SELECT id_libro, titulo, precio_alquiler, stock, isbn FROM libros WHERE estado = 'DISPONIBLE' AND stock > 0 ORDER BY titulo ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_cliente = isset($_POST['tipo_cliente']) ? $_POST['tipo_cliente'] : 'EXISTENTE';
    $id_libro = intval($_POST['id_libro']);
    $fecha_inicio = mysqli_real_escape_string($conexion, $_POST['fecha_inicio']);
    $dias_alquiler = intval($_POST['dias_alquiler']);
    $metodo_pago = trim(mysqli_real_escape_string($conexion, $_POST['metodo_pago']));
    $num_operacion = trim(mysqli_real_escape_string($conexion, $_POST['num_operacion']));

    $id_usuario = 0;

    // 1. GESTIÓN Y OBTENCIÓN DEL CLIENTE
    if ($tipo_cliente === 'NUEVO') {
        $nuevo_dni = trim(mysqli_real_escape_string($conexion, $_POST['nuevo_dni']));
        $nuevo_nombre = trim(mysqli_real_escape_string($conexion, $_POST['nuevo_nombre']));
        $nuevo_apellido = trim(mysqli_real_escape_string($conexion, $_POST['nuevo_apellido']));
        $nuevo_telefono = isset($_POST['nuevo_telefono']) ? trim(mysqli_real_escape_string($conexion, $_POST['nuevo_telefono'])) : '';

        if (empty($nuevo_dni) || empty($nuevo_nombre) || empty($nuevo_apellido)) {
            $error = "Por favor, complete los datos obligatorios del nuevo cliente (DNI, Nombre y Apellido).";
        } else {
            // Verificar si el DNI ya existe en la base de datos
            $check_dni = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE dni = '$nuevo_dni' LIMIT 1");
            if ($check_dni && mysqli_num_rows($check_dni) > 0) {
                $u_existente = mysqli_fetch_assoc($check_dni);
                $id_usuario = (int)$u_existente['id_usuario'];
            } else {
                // Registrar nuevo usuario cliente (id_rol = 2)
                $email_generado = $nuevo_dni . "@cliente.com";
                $pass_hash = password_hash($nuevo_dni, PASSWORD_DEFAULT);

                $sql_ins_cli = "INSERT INTO usuarios (id_rol, dni, nombre, apellido, email, password, telefono, estado) 
                                VALUES (2, '$nuevo_dni', '$nuevo_nombre', '$nuevo_apellido', '$email_generado', '$pass_hash', '$nuevo_telefono', 'ACTIVO')";
                
                if (mysqli_query($conexion, $sql_ins_cli)) {
                    $id_usuario = mysqli_insert_id($conexion);
                } else {
                    $error = "Error al registrar al nuevo cliente: " . mysqli_error($conexion);
                }
            }
        }
    } else {
        $id_usuario = intval($_POST['id_usuario']);
    }

    // 2. VALIDACIONES DE CAMPOS Y STOCK
    if (empty($error)) {
        if ($id_usuario === 0 || $id_libro === 0 || $dias_alquiler <= 0 || empty($fecha_inicio)) {
            $error = "Por favor, complete todos los campos obligatorios para el alquiler.";
        } else {

            // 🛑 A) VERIFICACIÓN DE MULTAS PENDIENTES DEL CLIENTE
            $sql_check_multas = "SELECT COUNT(*) AS total_pendientes 
                                 FROM multas m 
                                 INNER JOIN alquileres a ON m.id_alquiler = a.id_alquiler 
                                 WHERE a.id_usuario = $id_usuario AND m.estado = 'Pendiente'";
            $res_multas = mysqli_query($conexion, $sql_check_multas);
            $data_multa = mysqli_fetch_assoc($res_multas);

            if ($data_multa['total_pendientes'] > 0) {
                $error = "⚠️ No se puede registrar el alquiler: El cliente seleccionado registra multas pendientes de pago.";
            } else {

                // 📦 B) CONSULTAR STOCK Y PRECIO DEL LIBRO
                $res_lib = mysqli_query($conexion, "SELECT stock, precio_alquiler, titulo FROM libros WHERE id_libro = $id_libro LIMIT 1");
                if ($res_lib && mysqli_num_rows($res_lib) === 1) {
                    $libro_data = mysqli_fetch_assoc($res_lib);
                    $stock_actual = (int)$libro_data['stock'];

                    if ($stock_actual <= 0) {
                        $error = "El libro '" . $libro_data['titulo'] . "' no cuenta con stock disponible en este momento.";
                    } else {

                        // 🔄 C) INICIAR TRANSACCIÓN Y REGISTRAR PRÉSTAMO
                        $fecha_dev_esperada = date('Y-m-d', strtotime($fecha_inicio . " + $dias_alquiler days"));
                        $monto_alquiler = (float)$libro_data['precio_alquiler'] * $dias_alquiler;

                        mysqli_autocommit($conexion, false);

                        $sql_insert = "INSERT INTO alquileres (id_usuario, id_libro, fecha_inicio, fecha_dev_esperada, monto_alquiler, metodo_pago, num_operacion, estado) 
                                       VALUES ($id_usuario, $id_libro, '$fecha_inicio', '$fecha_dev_esperada', $monto_alquiler, '$metodo_pago', '$num_operacion', 'En Alquiler')";

                        $nuevo_stock = $stock_actual - 1;
                        $sql_update_stock = "UPDATE libros SET stock = $nuevo_stock, estado = " . ($nuevo_stock > 0 ? "'DISPONIBLE'" : "'AGOTADO'") . " WHERE id_libro = $id_libro";

                        $sql_kardex = "INSERT INTO kardex (id_libro, tipo_movimiento, cantidad, stock_anterior, stock_actual, motivo) 
                                       VALUES ($id_libro, 'SALIDA_ALQUILER', 1, $stock_actual, $nuevo_stock, 'Alquiler registrado por $dias_alquiler días')";

                        if (mysqli_query($conexion, $sql_insert) && mysqli_query($conexion, $sql_update_stock) && mysqli_query($conexion, $sql_kardex)) {
                            mysqli_commit($conexion);
                            mysqli_autocommit($conexion, true);

                            $_SESSION['mensaje'] = "Alquiler registrado exitosamente. Devolución programada para el: " . date('d/m/Y', strtotime($fecha_dev_esperada));
                            $_SESSION['tipo_mensaje'] = "success";
                            header("Location: " . BASE_URL . "modulos/alquileres/index.php");
                            exit();
                        } else {
                            mysqli_rollback($conexion);
                            mysqli_autocommit($conexion, true);
                            $error = "Error al procesar el registro de alquiler: " . mysqli_error($conexion);
                        }
                    }
                } else {
                    $error = "El libro seleccionado no es válido.";
                }
            }
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>modulos/alquileres/index.php" class="btn btn-secondary">⬅️ Volver a Alquileres</a>
</div>

<div class="form-card" style="max-width: 750px;">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Registrar Nuevo Alquiler de Libro</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger" style="margin-bottom: 1.2rem;">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        
        <!-- SELECTOR TIPO DE CLIENTE -->
        <div class="form-group" style="background: var(--bg-light); padding: 0.8rem 1rem; border-radius: var(--radius); border: 1px solid var(--border-color); margin-bottom: 1.2rem;">
            <label style="font-weight: bold; margin-bottom: 0.5rem; display: block;">Modalidad de Selección de Cliente:</label>
            <div style="display: flex; gap: 20px;">
                <label style="cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <input type="radio" name="tipo_cliente" value="EXISTENTE" checked onclick="toggleTipoCliente('EXISTENTE')"> 👤 Cliente Registrado
                </label>
                <label style="cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <input type="radio" name="tipo_cliente" value="NUEVO" onclick="toggleTipoCliente('NUEVO')"> ➕ Registrar Nuevo Cliente al Instante
                </label>
            </div>
        </div>

        <!-- CONTENEDOR: CLIENTE EXISTENTE -->
        <div id="sec_cliente_existente" class="form-group">
            <label for="id_usuario">Seleccionar Cliente (*):</label>
            <select name="id_usuario" id="id_usuario" class="form-control">
                <option value="">-- Seleccionar Cliente --</option>
                <?php while ($c = mysqli_fetch_assoc($clientes)): ?>
                    <option value="<?php echo $c['id_usuario']; ?>">
                        DNI: <?php echo htmlspecialchars($c['dni']); ?> | <?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- CONTENEDOR: CLIENTE NUEVO -->
        <div id="sec_cliente_nuevo" style="display: none; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.2rem;">
            <h4 style="margin-top: 0; margin-bottom: 0.8rem; color: #166534;">Datos del Nuevo Cliente</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="nuevo_dni">DNI (*):</label>
                    <input type="text" name="nuevo_dni" id="nuevo_dni" class="form-control" maxlength="15" placeholder="Ingrese DNI">
                </div>
                <div class="form-group">
                    <label for="nuevo_telefono">Teléfono:</label>
                    <input type="text" name="nuevo_telefono" id="nuevo_telefono" class="form-control" placeholder="Ej. 987654321">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="nuevo_nombre">Nombres (*):</label>
                    <input type="text" name="nuevo_nombre" id="nuevo_nombre" class="form-control" placeholder="Nombres del cliente">
                </div>
                <div class="form-group">
                    <label for="nuevo_apellido">Apellidos (*):</label>
                    <input type="text" name="nuevo_apellido" id="nuevo_apellido" class="form-control" placeholder="Apellidos del cliente">
                </div>
            </div>
        </div>

        <!-- SELECCIÓN DE LIBRO -->
        <div class="form-group">
            <label for="id_libro">Seleccionar Libro disponible (*):</label>
            <select name="id_libro" id="id_libro" class="form-control" required>
                <option value="">-- Seleccionar Libro --</option>
                <?php while ($l = mysqli_fetch_assoc($libros)): ?>
                    <option value="<?php echo $l['id_libro']; ?>" data-precio="<?php echo $l['precio_alquiler']; ?>" <?php echo $id_libro_pre === (int)$l['id_libro'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($l['titulo']); ?> (S/ <?php echo number_format($l['precio_alquiler'], 2); ?>/día) - Stock: <?php echo $l['stock']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- FECHA, DÍAS Y TARIFAS -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="fecha_inicio">Fecha Inicio (*):</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="dias_alquiler">Días Alquiler (*):</label>
                <input type="number" name="dias_alquiler" id="dias_alquiler" class="form-control" min="1" value="5" required>
            </div>
            <div class="form-group">
                <label for="precio_alquiler_diario">Tarifa Diaria (S/):</label>
                <input type="text" id="precio_alquiler_diario" class="form-control" value="0.00" readonly style="background-color: #eee;">
            </div>
        </div>

        <!-- PREVISUALIZACIÓN DE TOTALES Y FECHA ESTIMADA -->
        <div style="background-color: #fff8e7; border: 1px solid #ffe0b2; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.2rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.3rem;">
                <span>Fecha Devolución Estimada:</span>
                <strong id="fecha_dev_esperada_preview" style="color: var(--primary);">-</strong>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 1.1rem;">
                <span>Total a Cobrar por Alquiler:</span>
                <strong id="total_alquiler_preview" style="color: var(--success);">S/ 0.00</strong>
            </div>
        </div>

        <!-- MÉTODOS DE PAGO -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="metodo_pago">Método de Pago (*):</label>
                <select name="metodo_pago" id="metodo_pago" class="form-control" required>
                    <option value="EFECTIVO" selected>💵 Efectivo</option>
                    <option value="YAPE">📱 Yape</option>
                    <option value="PLIN">📲 Plin</option>
                    <option value="TARJETA">💳 Tarjeta</option>
                    <option value="TRANSFERENCIA">🏦 Transferencia</option>
                </select>
            </div>
            <div class="form-group">
                <label for="num_operacion">Nº Operación / Referencia:</label>
                <input type="text" name="num_operacion" id="num_operacion" class="form-control" placeholder="Ej. YP-99238">
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-block" style="margin-top: 1rem;">📖 Confirmar Registro de Alquiler</button>
    </form>
</div>

<script>
function toggleTipoCliente(tipo) {
    const secExistente = document.getElementById("sec_cliente_existente");
    const secNuevo = document.getElementById("sec_cliente_nuevo");
    const inputDni = document.getElementById("nuevo_dni");
    const inputNombre = document.getElementById("nuevo_nombre");
    const inputApellido = document.getElementById("nuevo_apellido");
    const selectUsuario = document.getElementById("id_usuario");

    if (tipo === "NUEVO") {
        secExistente.style.display = "none";
        secNuevo.style.display = "block";
        inputDni.required = true;
        inputNombre.required = true;
        inputApellido.required = true;
        selectUsuario.required = false;
        selectUsuario.value = "";
    } else {
        secExistente.style.display = "block";
        secNuevo.style.display = "none";
        inputDni.required = false;
        inputNombre.required = false;
        inputApellido.required = false;
        selectUsuario.required = true;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const selectLibro = document.getElementById("id_libro");
    const inputPrecioDiario = document.getElementById("precio_alquiler_diario");
    const inputDias = document.getElementById("dias_alquiler");
    const inputFechaInicio = document.getElementById("fecha_inicio");
    const previewFecha = document.getElementById("fecha_dev_esperada_preview");
    const previewTotal = document.getElementById("total_alquiler_preview");

    function calcularAlquiler() {
        let precio = 0;
        if (selectLibro.selectedIndex > 0) {
            const option = selectLibro.options[selectLibro.selectedIndex];
            precio = parseFloat(option.getAttribute("data-precio")) || 0;
            inputPrecioDiario.value = precio.toFixed(2);
        } else {
            inputPrecioDiario.value = "0.00";
        }

        const dias = parseInt(inputDias.value) || 0;
        const total = precio * dias;
        previewTotal.textContent = "S/ " + total.toFixed(2);

        if (inputFechaInicio.value && dias > 0) {
            const fecha = new Date(inputFechaInicio.value + "T00:00:00");
            fecha.setDate(fecha.getDate() + dias);
            const dd = String(fecha.getDate()).padStart(2, '0');
            const mm = String(fecha.getMonth() + 1).padStart(2, '0');
            const yyyy = fecha.getFullYear();
            previewFecha.textContent = `${dd}/${mm}/${yyyy}`;
        } else {
            previewFecha.textContent = "-";
        }
    }

    if (selectLibro) selectLibro.addEventListener("change", calcularAlquiler);
    if (inputDias) inputDias.addEventListener("input", calcularAlquiler);
    if (inputFechaInicio) inputFechaInicio.addEventListener("change", calcularAlquiler);

    // Inicializar estado por defecto (Cliente Existente)
    toggleTipoCliente('EXISTENTE');
    calcularAlquiler();
});
</script>

<?php include_once __DIR__ . '/../../includes/header.php'; ?>