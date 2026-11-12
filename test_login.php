<?php
$pdo = new PDO('mysql:host=localhost;dbname=libreria;charset=utf8mb4', 'root', 'usbw');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare(
  'SELECT u.id_usuario, u.password, r.nombre AS rol
     FROM usuarios u JOIN roles r ON r.id_rol = u.id_rol
    WHERE u.email = ? AND u.estado = "ACTIVO"');
$stmt->execute(['admin@libreria.com']);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

echo ($u && password_verify('Admin123', $u['password']))
  ? "OK: login correcto, rol = {$u['rol']}"
  : "Falló el login";