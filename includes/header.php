<?php
require_once __DIR__ . '/../config/sesion.php';
$usuario_actual = obtener_usuario_actual();
$cant_carrito = obtener_conteo_carrito();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo_pagina) ? htmlspecialchars($titulo_pagina) . ' - Librería Perú' : 'Librería Perú - Ventas y Alquileres'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="navbar-header">
        <div class="contenedor navbar-container">
            <a href="<?php echo BASE_URL; ?>index.php" class="brand-logo">
                <img src="<?php echo BASE_URL; ?>img/logo.png" alt="Logo Librería" onerror="this.src='<?php echo BASE_URL; ?>img/no-cover.png'">
                <span class="brand-name">Librería<span class="brand-accent">Vallejo</span></span>
            </a>

            <button class="menu-toggle" id="btn-menu-toggle" aria-label="Abrir Menú">
                ☰
            </button>

            <nav class="nav-menu" id="nav-menu-main">
                <ul class="nav-list">
                    <li><a href="<?php echo BASE_URL; ?>index.php" class="nav-link">Catálogo</a></li>
                    
                    <!-- Menú Operativo (Administrador y Bibliotecario) -->
                    <?php if (es_personal()): ?>
                        <li><a href="<?php echo BASE_URL; ?>modulos/libros/index.php" class="nav-link">Libros</a></li>
                        
                        <!-- Módulo exclusivo de Administrador -->
                        <?php if (es_admin()): ?>
                            <li><a href="<?php echo BASE_URL; ?>modulos/usuarios/index.php" class="nav-link">Usuarios</a></li>
                        <?php endif; ?>

                        <li><a href="<?php echo BASE_URL; ?>modulos/alquileres/index.php" class="nav-link">Alquileres</a></li>
                        <li><a href="<?php echo BASE_URL; ?>modulos/ventas/index.php" class="nav-link">Ventas</a></li>
                        <li><a href="<?php echo BASE_URL; ?>modulos/kardex/index.php" class="nav-link">Kardex</a></li>
                        <li><a href="<?php echo BASE_URL; ?>modulos/reportes/index.php" class="nav-link">Reportes</a></li>
                    <?php endif; ?>

                    <!-- Menú exclusivo para Clientes finales -->
                    <?php if (esta_autenticado() && !es_personal()): ?>
                        <li><a href="<?php echo BASE_URL; ?>modulos/cliente/mis_alquileres.php" class="nav-link">Mis Alquileres</a></li>
                        <li><a href="<?php echo BASE_URL; ?>modulos/cliente/mis_compras.php" class="nav-link">Mis Compras</a></li>
                        <li><a href="<?php echo BASE_URL; ?>modulos/cliente/mis_multas.php" class="nav-link">Mis Multas</a></li>
                    <?php endif; ?>
                </ul>

                <div class="nav-user-actions">
                    <a href="<?php echo BASE_URL; ?>modulos/carrito/index.php" class="btn-cart-badge">
                        🛒 Carrito <span class="badge-count" id="cart-counter-badge"><?php echo $cant_carrito; ?></span>
                    </a>

                    <?php if (esta_autenticado()): ?>
                        <div class="user-pill">
                            <span class="user-role-badge <?php echo es_personal() ? 'badge-admin' : 'badge-client'; ?>">
                                <?php echo htmlspecialchars($usuario_actual['rol']); ?>
                            </span>
                            <span class="user-name"><?php echo htmlspecialchars($usuario_actual['nombre']); ?></span>
                            <a href="<?php echo BASE_URL; ?>modulos/auth/logout.php" class="btn-logout" title="Cerrar Sesión">🚪 Salir</a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>modulos/auth/login.php" class="btn-login">Ingresar</a>
                        <a href="<?php echo BASE_URL; ?>modulos/auth/registro.php" class="btn-register">Registrarse</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main class="contenedor principal-content">
        <?php include_once __DIR__ . '/alerta.php'; ?>