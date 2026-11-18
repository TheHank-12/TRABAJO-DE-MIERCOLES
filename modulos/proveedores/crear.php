<?php
$titulo_pagina = "Registrar Proveedor";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/sesion.php';

requerir_admin();

$error = '';
$datos = [
    'ruc_dni' => '',
    'razon_social' => '',
    'contacto' => '',
    'telefono' => '',
    'email' => '',
    'direccion' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['ruc_dni'] = trim(mysqli_real_escape_string($conexion, $_POST['ruc_dni']));
    $datos['razon_social'] = trim(mysqli_real_escape_string($conexion, $_POST['razon_social']));
    $datos['contacto'] = trim(mysqli_real_escape_string($conexion, $_POST['contacto']));
    $datos['telefono'] = trim(mysqli_real_escape_string($conexion, $_POST['telefono']));
    $datos['email'] = trim(mysqli_real_escape_string($conexion, $_POST['email']));
    $datos['direccion'] = trim(mysqli_real_escape_string($conexion, $_POST['direccion']));

    if (empty($datos['ruc_dni']) || empty($datos['razon_social'])) {
        $error = "El RUC/DNI y la Razón Social son campos obligatorios.";
    } else {
        $chk = mysqli_query($conexion, "SELECT id_proveedor FROM proveedores WHERE ruc_dni = '{$datos['ruc_dni']}' LIMIT 1");
        if (mysqli_num_rows($chk) > 0) {
            $error = "El número de RUC/DNI ya se encuentra registrado.";
        } else {
            $query = "INSERT INTO proveedores (ruc_dni, razon_social, contacto, telefono, email, direccion) 
                      VALUES ('{$datos['ruc_dni']}', '{$datos['razon_social']}', '{$datos['contacto']}', '{$datos['telefono']}', '{$datos['email']}', '{$datos['direccion']}')";
            if (mysqli_query($conexion, $query)) {
                $_SESSION['mensaje'] = "Proveedor registrado con éxito.";
                $_SESSION['tipo_mensaje'] = "success";
                header("Location: " . BASE_URL . "modulos/proveedores/index.php");
                exit();
            } else {
                $error = "Error al registrar proveedor: " . mysqli_error($conexion);
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
    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">Registrar Nuevo Proveedor</h2>

    <?php if (!empty($error)): ?>
        <div class="alerta-box alerta-danger">
            <div class="alerta-icono">❌</div>
            <div class="alerta-contenido"><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="ruc_dni">RUC / DNI (*):</label>
                <input type="text" name="ruc_dni" id="ruc_dni" class="form-control" maxlength="20" value="<?php echo htmlspecialchars($datos['ruc_dni']); ?>" required>
            </div>
            <div class="form-group">
                <label for="razon_social">Razón Social / Empresa (*):</label>
                <input type="text" name="razon_social" id="razon_social" class="form-control" value="<?php echo htmlspecialchars($datos['razon_social']); ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="contacto">Contacto Responsable:</label>
                <input type="text" name="contacto" id="contacto" class="form-control" value="<?php echo htmlspecialchars($datos['contacto']); ?>">
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono / Celular:</label>
                <input type="text" name="telefono" id="telefono" class="form-control" value="<?php echo htmlspecialchars($datos['telefono']); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($datos['email']); ?>">
        </div>

        <div class="form-group">
            <label for="direccion">Dirección Comercial:</label>
            <input type="text" name="direccion" id="direccion" class="form-control" value="<?php echo htmlspecialchars($datos['direccion']); ?>">
        </div>

        <button type="submit" class="btn btn-success btn-block" style="margin-top: 1rem;">💾 Guardar Proveedor</button>
    </form>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>