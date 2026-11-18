<?php
$titulo_pagina = "Editar Proveedor";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_admin();

$id_proveedor = isset($_GET['id']) ? intval($_GET['id']) : 0;
$res = mysqli_query($conexion, "SELECT * FROM proveedores WHERE id_proveedor = $id_proveedor LIMIT 1");

if (!$res || mysqli_num_rows($res) === 0) {
    $_SESSION['mensaje'] = "Proveedor no encontrado.";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: " . BASE_URL . "modulos/proveedores/index.php");
    exit();
}

$prov = mysqli_fetch_assoc($res);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ruc_dni = trim(mysqli_real_escape_string($conexion, $_POST['ruc_dni']));
    $razon_social = trim(mysqli_real_escape_string($conexion, $_POST['razon_social']));
    $contacto = trim(mysqli_real_escape_string($conexion, $_POST['contacto']));
    $telefono = trim(mysqli_real_escape_string($conexion, $_POST['telefono']));
    $email = trim(mysqli_real_escape_string($conexion, $_POST['email']));
    $direccion = trim(mysqli_real_escape_string($conexion, $_POST['direccion']));

    if (empty($ruc_dni) || empty($razon_social)) {
        $error = "El RUC/DNI y la Razón Social son campos obligatorios.";
    } else {
        $chk = mysqli_query($conexion, "SELECT id_proveedor FROM proveedores WHERE ruc_dni = '$ruc_dni' AND id_proveedor != $id_proveedor LIMIT 1");
        if (mysqli_num_rows($chk) > 0) {
            $error = "El RUC/DNI ingresado ya está en uso por otro proveedor.";
        } else {
            $update = "UPDATE proveedores SET ruc_dni = '$ruc_dni', razon_social = '$razon_social', contacto = '$contacto', telefono = '$telefono', email = '$email', direccion = '$direccion' WHERE id_proveedor = $id_proveedor";
            if (mysqli_query($conexion, $update)) {
                $_SESSION['mensaje'] = "Proveedor actualizado correctamente.";
                $_SESSION['tipo_mensaje'] = "success";
                header("Location: " . BASE_URL . "modulos/proveedores/index.php");
                exit();
            } else {
                $error = "Error al actualizar proveedor: " . mysqli_error($conexion);
            }
        }
    }
}

include_once __DIR__ . '/../../includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>modulos/proveedores/index.php" class="btn btn-secondary">⬅️ Volver a Proveedores</a>
</div>

<div class="form-card">
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Editar Proveedor</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $id_proveedor; ?>" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="ruc_dni">RUC / DNI (*):</label>
                <input type="text" name="ruc_dni" id="ruc_dni" class="form-control" value="<?php echo htmlspecialchars($prov['ruc_dni']); ?>" required>
            </div>
            <div class="form-group">
                <label for="razon_social">Razón Social (*):</label>
                <input type="text" name="razon_social" id="razon_social" class="form-control" value="<?php echo htmlspecialchars($prov['razon_social']); ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="contacto">Contacto Responsable:</label>
                <input type="text" name="contacto" id="contacto" class="form-control" value="<?php echo htmlspecialchars($prov['contacto']); ?>">
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="text" name="telefono" id="telefono" class="form-control" value="<?php echo htmlspecialchars($prov['telefono']); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($prov['email']); ?>">
        </div>

        <div class="form-group">
            <label for="direccion">Dirección Comercial:</label>
            <input type="text" name="direccion" id="direccion" class="form-control" value="<?php echo htmlspecialchars($prov['direccion']); ?>">
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem;">💾 Actualizar Proveedor</button>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>