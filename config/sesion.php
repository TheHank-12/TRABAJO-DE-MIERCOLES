<?php


// CONTROL DE SESIONES Y FUNCIONES 


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function esta_autenticado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

// 1. Verificar si es Administrador
function es_admin() {
    return esta_autenticado() && isset($_SESSION['rol_nombre']) && $_SESSION['rol_nombre'] === 'Administrador';
}

// 2. Verificar si es Bibliotecario
function es_bibliotecario() {
    return esta_autenticado() && isset($_SESSION['rol_nombre']) && $_SESSION['rol_nombre'] === 'Bibliotecario';
}

// 3. Verificar si es Personal Autorizado (Admin o Bibliotecario)
function es_personal() {
    return es_admin() || es_bibliotecario();
}

function requerir_autenticacion() {
    if (!esta_autenticado()) {
        $_SESSION['mensaje'] = "Debe iniciar sesión para acceder a este recurso.";
        $_SESSION['tipo_mensaje'] = "warning";
        header("Location: " . BASE_URL . "modulos/auth/login.php");
        exit();
    }
}

// Middleware: Solo Administrador (Control total)
function requerir_admin() {
    requerir_autenticacion();
    if (!es_admin()) {
        $_SESSION['mensaje'] = "Acceso denegado: Se requieren privilegios de Administrador.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
}

// Middleware: Administrador o Bibliotecario (Operaciones de la librería)
function requerir_personal() {
    requerir_autenticacion();
    if (!es_personal()) {
        $_SESSION['mensaje'] = "Acceso denegado: Se requieren permisos de personal autorizado.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
}

function obtener_usuario_actual() {
    if (esta_autenticado()) {
        return [
            'id' => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'],
            'apellido' => $_SESSION['usuario_apellido'],
            'email' => $_SESSION['usuario_email'],
            'dni' => $_SESSION['usuario_dni'],
            'rol' => $_SESSION['rol_nombre']
        ];
    }
    return null;
}

function inicializar_carrito() {
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }
}

function obtener_conteo_carrito() {
    inicializar_carrito();
    $total_items = 0;
    foreach ($_SESSION['carrito'] as $id_libro => $cantidad) {
        $total_items += (int)$cantidad;
    }
    return $total_items;
}

// Definición de URL Base dinámicamente
if (!defined('BASE_URL')) {
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_path = preg_replace('#/(modulos|includes|config)(/.*)?$#', '', $script_dir);
    $base_path = rtrim($base_path, '/') . '/';
    define('BASE_URL', $base_path);
}
?>