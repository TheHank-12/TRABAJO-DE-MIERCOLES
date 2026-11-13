-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 13-11-2026 a las 04:04:50
-- Versión del servidor: 5.7.37
-- Versión de PHP: 8.1.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bd_libreria`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alquileres`
--

CREATE TABLE `alquileres` (
  `id_alquiler` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_libro` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_dev_esperada` date NOT NULL,
  `fecha_dev_real` date DEFAULT NULL,
  `monto_alquiler` decimal(10,2) NOT NULL DEFAULT '0.00',
  `metodo_pago` enum('EFECTIVO','YAPE','PLIN','TARJETA','TRANSFERENCIA') COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'EFECTIVO',
  `num_operacion` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `estado` enum('En Alquiler','Devuelto','Con Demora') COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'En Alquiler'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `alquileres`
--

INSERT INTO `alquileres` (`id_alquiler`, `id_usuario`, `id_libro`, `fecha_inicio`, `fecha_dev_esperada`, `fecha_dev_real`, `monto_alquiler`, `metodo_pago`, `num_operacion`, `estado`) VALUES
(1, 2, 1, '2026-11-01', '2026-11-05', NULL, '8.00', 'YAPE', 'YP-988341', 'Con Demora'),
(2, 4, 3, '2026-11-02', '2026-11-07', '2026-11-06', '10.00', 'PLIN', 'PL-112233', 'Devuelto'),
(3, 5, 4, '2026-11-03', '2026-11-08', NULL, '9.00', 'TARJETA', 'TX-887766', 'En Alquiler'),
(4, 6, 7, '2026-10-25', '2026-10-29', '2026-11-02', '7.00', 'EFECTIVO', NULL, 'Devuelto'),
(5, 7, 2, '2026-11-04', '2026-11-09', NULL, '5.00', 'YAPE', 'YP-334455', 'En Alquiler');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autores`
--

CREATE TABLE `autores` (
  `id_autor` int(11) NOT NULL,
  `nombre` varchar(120) COLLATE utf8mb4_spanish_ci NOT NULL,
  `nacionalidad` varchar(80) COLLATE utf8mb4_spanish_ci DEFAULT 'Peruana'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `autores`
--

INSERT INTO `autores` (`id_autor`, `nombre`, `nacionalidad`) VALUES
(1, 'Mario Vargas Llosa', 'Peruana'),
(2, 'César Vallejo', 'Peruana'),
(3, 'Gabriel García Márquez', 'Colombiana'),
(4, 'Julio Ramón Ribeyro', 'Peruana'),
(5, 'José María Arguedas', 'Peruana'),
(6, 'Isabel Allende', 'Chilena');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_spanish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`) VALUES
(1, 'Literatura Peruana', 'Obras clásicas y contemporáneas de autores peruanos.'),
(2, 'Ficción y Novela', 'Novelas de autores internacionales y narrativa hispanoamericana.'),
(3, 'Ciencia y Tecnología', 'Libros de ingeniería, informática y ciencias exactas.'),
(4, 'Poesía', 'Antologías y obras poéticas universales.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `editoriales`
--

CREATE TABLE `editoriales` (
  `id_editorial` int(11) NOT NULL,
  `nombre` varchar(120) COLLATE utf8mb4_spanish_ci NOT NULL,
  `contacto` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `editoriales`
--

INSERT INTO `editoriales` (`id_editorial`, `nombre`, `contacto`) VALUES
(1, 'Editorial Peisa', 'info@peisa.com.pe'),
(2, 'Alfaguara', 'atencion@alfaguara.pe'),
(3, 'Seix Barral', 'contacto@seixbarral.com'),
(4, 'Debolsillo', 'contacto@debolsillo.pe');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `kardex`
--

CREATE TABLE `kardex` (
  `id_kardex` int(11) NOT NULL,
  `id_libro` int(11) NOT NULL,
  `tipo_movimiento` enum('ENTRADA','SALIDA_VENTA','SALIDA_ALQUILER','DEVOLUCION','AJUSTE') COLLATE utf8mb4_spanish_ci NOT NULL,
  `cantidad` int(11) NOT NULL,
  `stock_anterior` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `fecha_movimiento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `kardex`
--

INSERT INTO `kardex` (`id_kardex`, `id_libro`, `tipo_movimiento`, `cantidad`, `stock_anterior`, `stock_actual`, `motivo`, `fecha_movimiento`) VALUES
(1, 1, 'ENTRADA', 25, 0, 25, 'Carga inicial de inventario', '2026-11-12 18:34:15'),
(2, 2, 'ENTRADA', 15, 0, 15, 'Carga inicial de inventario', '2026-11-12 18:34:15'),
(3, 3, 'ENTRADA', 20, 0, 20, 'Carga inicial de inventario', '2026-11-12 18:34:15'),
(4, 4, 'ENTRADA', 18, 0, 18, 'Carga inicial de inventario', '2026-11-12 18:34:15'),
(5, 5, 'ENTRADA', 12, 0, 12, 'Carga inicial de inventario', '2026-11-12 18:34:15'),
(6, 6, 'ENTRADA', 10, 0, 10, 'Carga inicial de inventario', '2026-11-12 18:34:15'),
(7, 7, 'ENTRADA', 14, 0, 14, 'Carga inicial de inventario', '2026-11-12 18:34:15'),
(8, 4, 'SALIDA_VENTA', 1, 18, 17, 'Venta Carrito Web #BOL-20261112-55448', '2026-11-12 18:38:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

CREATE TABLE `libros` (
  `id_libro` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_autor` int(11) NOT NULL,
  `id_editorial` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_spanish_ci NOT NULL,
  `isbn` varchar(30) COLLATE utf8mb4_spanish_ci NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `precio_alquiler` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock` int(11) NOT NULL DEFAULT '0',
  `portada` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT 'img/no-cover.png.jpg',
  `fecha_publicacion` date DEFAULT NULL,
  `estado` enum('DISPONIBLE','AGOTADO','DESCONTINUADO') COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'DISPONIBLE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`id_libro`, `id_categoria`, `id_autor`, `id_editorial`, `id_proveedor`, `titulo`, `isbn`, `precio_venta`, `precio_alquiler`, `stock`, `portada`, `fecha_publicacion`, `estado`) VALUES
(1, 1, 1, 2, 2, 'La Ciudad y los Perros', '978-612-4264-01-1', '49.90', '8.00', 25, 'uploads/portadas/ciudad_perros.jpg', '1963-10-15', 'DISPONIBLE'),
(2, 4, 2, 1, 1, 'Los Heraldos Negros / Trilce', '978-612-4264-02-8', '29.90', '5.00', 15, 'uploads/portadas/heraldos_negros.jpg', '1919-01-01', 'DISPONIBLE'),
(3, 2, 3, 3, 2, 'Cien Años de Soledad', '978-030-7474-72-8', '59.90', '10.00', 20, 'uploads/portadas/cien_anos.jpg', '1967-05-30', 'DISPONIBLE'),
(4, 2, 6, 4, 2, 'La Casa de los Espíritus', '978-849-7592-20-8', '54.90', '9.00', 17, 'uploads/portadas/casa_espiritus.jpg', '1982-10-01', 'DISPONIBLE'),
(5, 1, 4, 1, 1, 'La Palabra del Mudo', '978-612-4264-05-9', '45.00', '7.50', 12, 'uploads/portadas/palabra_mudo.jpg', '1973-04-12', 'DISPONIBLE'),
(6, 4, 2, 1, 1, 'Poemas Humanos', '978-612-4264-06-6', '32.00', '6.00', 10, 'uploads/portadas/poemas_humanos.jpg', '1939-07-15', 'DISPONIBLE'),
(7, 1, 5, 2, 1, 'Los Ríos Profundos', '978-612-4264-07-3', '38.00', '7.00', 14, 'uploads/portadas/rios_profundos.jpg', '1958-08-20', 'DISPONIBLE');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `multas`
--

CREATE TABLE `multas` (
  `id_multa` int(11) NOT NULL,
  `id_alquiler` int(11) NOT NULL,
  `dias_atraso` int(11) NOT NULL DEFAULT '0',
  `monto_diario` decimal(10,2) NOT NULL DEFAULT '5.00',
  `monto_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `estado` enum('Pendiente','Pagada') COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'Pendiente',
  `fecha_pago` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `multas`
--

INSERT INTO `multas` (`id_multa`, `id_alquiler`, `dias_atraso`, `monto_diario`, `monto_total`, `estado`, `fecha_pago`) VALUES
(1, 1, 2, '5.00', '10.00', 'Pendiente', NULL),
(2, 4, 4, '5.00', '20.00', 'Pagada', '2026-11-02 18:30:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `ruc_dni` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL,
  `razon_social` varchar(150) COLLATE utf8mb4_spanish_ci NOT NULL,
  `contacto` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `ruc_dni`, `razon_social`, `contacto`, `telefono`, `email`, `direccion`) VALUES
(1, '20100123456', 'Distribuidora Cultural Peruana S.A.C.', 'Juan Pérez', '014238900', 'ventas@culturalperu.pe', 'Av. Arequipa 2500, Lima'),
(2, '20554433221', 'Editorial Planeta Perú S.A.', 'María Gómez', '016143500', 'contacto@planeta.pe', 'Av. Santa Cruz 830, Miraflores'),
(3, '20456789012', 'SBS Librerías S.A.C.', 'Roberto Díaz', '012087000', 'contacto@sbs.com.pe', 'Av. Angamos Oeste 301, Miraflores');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resenas`
--

CREATE TABLE `resenas` (
  `id_resena` int(11) NOT NULL,
  `id_libro` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `puntuacion` int(11) NOT NULL,
  `comentario` text COLLATE utf8mb4_spanish_ci,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `resenas`
--

INSERT INTO `resenas` (`id_resena`, `id_libro`, `id_usuario`, `puntuacion`, `comentario`, `fecha`) VALUES
(1, 1, 2, 5, 'Excelente obra maestra de la literatura peruana. Entregado a tiempo.', '2026-11-03 14:20:00'),
(2, 1, 4, 5, 'Insuperable Vargas Llosa. Te atrapa de principio a fin.', '2026-11-04 09:15:00'),
(3, 1, 6, 4, 'Muy buena narrativa, súper recomendada para estudiantes.', '2026-11-05 18:00:00'),
(4, 3, 2, 5, 'Un clásico imperdible, edición impecable de Seix Barral.', '2026-11-04 11:00:00'),
(5, 3, 4, 5, 'Macondo en su máxima expresión, fantástico de inicio a fin.', '2026-11-05 10:30:00'),
(6, 3, 5, 5, 'Sin palabras, el mejor libro del realismo mágico.', '2026-11-06 12:45:00'),
(7, 4, 6, 5, 'Hermosa novela de Isabel Allende, muy conmovedora.', '2026-11-04 16:20:00'),
(8, 4, 7, 5, 'Personajes memorables y una trama fascinante.', '2026-11-06 14:10:00'),
(9, 5, 2, 4, 'Los mejores cuentos de Ribeyro.', '2026-11-05 16:45:00'),
(10, 5, 4, 5, 'Ribeyro retrata Lima como nadie más puede hacerlo.', '2026-11-06 08:30:00'),
(11, 7, 6, 5, 'Magistral Arguedas, transmite toda la magia e identidad andina.', '2026-11-05 19:10:00'),
(12, 7, 7, 4, 'Gran obra de la literatura indigenista.', '2026-11-06 15:50:00'),
(13, 2, 5, 5, 'Poesía sublime de César Vallejo, imprescindible en el hogar.', '2026-11-06 11:00:00'),
(14, 4, 1, 5, 'dsadasd', '2026-11-12 18:36:11'),
(15, 3, 8, 4, 'pepito tiene una manzana', '2026-11-12 18:40:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Acceso total al sistema, reportes, usuarios y gestión de catálogo.'),
(2, 'Cliente', 'Acceso a compras, alquileres, reseñas y carrito de compras.'),
(3, 'Bibliotecario', 'Atención en mostrador, ventas POS, alquileres, devoluciones e inventario.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `dni` varchar(15) COLLATE utf8mb4_spanish_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_spanish_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'ACTIVO',
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `dni`, `nombre`, `apellido`, `email`, `password`, `telefono`, `direccion`, `estado`, `fecha_registro`) VALUES
(1, 1, '77777777', 'Administrador', 'General', 'admin@libreria.com', '$2y$10$123456789012345678901u./zCLeARnBuyfsw17qkjYMfd7L4o2om', '987654321', 'Av. Javier Prado 1234, Lima', 'ACTIVO', '2026-11-12 18:34:15'),
(2, 2, '12345678', 'Carlos', 'Mendoza', 'cliente@libreria.com', '$2y$10$123456789012345678901u./zCLeARnBuyfsw17qkjYMfd7L4o2om', '912345678', 'Jr. Las Flores 432, Lima', 'ACTIVO', '2026-11-12 18:34:15'),
(3, 3, '88888888', 'Ana', 'Torres', 'bibliotecario@libreria.com', '$2y$10$123456789012345678901u./zCLeARnBuyfsw17qkjYMfd7L4o2om', '955443322', 'Av. Brasil 890, Lima', 'ACTIVO', '2026-11-12 18:34:15'),
(4, 2, '45678912', 'Lucía', 'Ramírez', 'lucia.ramirez@gmail.com', '$2y$10$123456789012345678901u./zCLeARnBuyfsw17qkjYMfd7L4o2om', '944556677', 'Calle Los Olivos 234, Surco', 'ACTIVO', '2026-11-12 18:34:15'),
(5, 2, '34567891', 'Fernando', 'Castillo', 'fcastillo@hotmail.com', '$2y$10$123456789012345678901u./zCLeARnBuyfsw17qkjYMfd7L4o2om', '933445566', 'Av. Larco 567, Miraflores', 'ACTIVO', '2026-11-12 18:34:15'),
(6, 2, '23456789', 'Sofía', 'Benítez', 'sbenitez@outlook.com', '$2y$10$123456789012345678901u./zCLeARnBuyfsw17qkjYMfd7L4o2om', '922334455', 'Jr. Arequipa 123, San Miguel', 'ACTIVO', '2026-11-12 18:34:15'),
(7, 2, '56789123', 'Miguel', 'Ángel', 'mangel@yahoo.com', '$2y$10$123456789012345678901u./zCLeARnBuyfsw17qkjYMfd7L4o2om', '911223344', 'Av. Petit Thouars 1420, Lince', 'ACTIVO', '2026-11-12 18:34:15'),
(8, 2, '25123131', 'dsad', '2423fasd', 'dsadjdad@gamial.com', '$2y$10$pTVUpMVsgcsqujNfgR.xUuqBRUkm7lgFBYKkdAu2u0PeG29EJIsLu', '512313212', 'sesdt3sdt2dst2', 'ACTIVO', '2026-11-12 18:37:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id_venta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `num_comprobante` varchar(30) COLLATE utf8mb4_spanish_ci NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `igv` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `metodo_pago` enum('EFECTIVO','YAPE','PLIN','TARJETA','TRANSFERENCIA') COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'EFECTIVO',
  `num_operacion` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `fecha_venta` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `id_usuario`, `num_comprobante`, `subtotal`, `igv`, `total`, `metodo_pago`, `num_operacion`, `fecha_venta`) VALUES
(1, 2, 'B001-00000001', '42.29', '7.61', '49.90', 'PLIN', 'PL-443210', '2026-11-02 10:30:00'),
(2, 4, 'B001-00000002', '88.81', '15.99', '104.80', 'TARJETA', 'TX-998811', '2026-11-03 15:20:00'),
(3, 5, 'B001-00000003', '70.34', '12.66', '83.00', 'YAPE', 'YP-776655', '2026-11-04 11:15:00'),
(4, 6, 'B001-00000004', '38.14', '6.86', '45.00', 'EFECTIVO', NULL, '2026-11-05 17:40:00'),
(5, 8, 'BOL-20261112-55448', '46.53', '8.37', '54.90', 'EFECTIVO', '', '2026-11-12 18:38:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas_detalle`
--

CREATE TABLE `ventas_detalle` (
  `id_detalle` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_libro` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `ventas_detalle`
--

INSERT INTO `ventas_detalle` (`id_detalle`, `id_venta`, `id_libro`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 1, 1, '49.90', '49.90'),
(2, 2, 4, 1, '54.90', '54.90'),
(3, 2, 1, 1, '49.90', '49.90'),
(4, 3, 5, 1, '45.00', '45.00'),
(5, 3, 7, 1, '38.00', '38.00'),
(6, 4, 5, 1, '45.00', '45.00'),
(7, 5, 4, 1, '54.90', '54.90');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alquileres`
--
ALTER TABLE `alquileres`
  ADD PRIMARY KEY (`id_alquiler`),
  ADD KEY `fk_alquileres_usuarios` (`id_usuario`),
  ADD KEY `fk_alquileres_libros` (`id_libro`);

--
-- Indices de la tabla `autores`
--
ALTER TABLE `autores`
  ADD PRIMARY KEY (`id_autor`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `editoriales`
--
ALTER TABLE `editoriales`
  ADD PRIMARY KEY (`id_editorial`);

--
-- Indices de la tabla `kardex`
--
ALTER TABLE `kardex`
  ADD PRIMARY KEY (`id_kardex`),
  ADD KEY `fk_kardex_libros` (`id_libro`);

--
-- Indices de la tabla `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id_libro`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `fk_libros_categorias` (`id_categoria`),
  ADD KEY `fk_libros_autores` (`id_autor`),
  ADD KEY `fk_libros_editoriales` (`id_editorial`),
  ADD KEY `fk_libros_proveedores` (`id_proveedor`);

--
-- Indices de la tabla `multas`
--
ALTER TABLE `multas`
  ADD PRIMARY KEY (`id_multa`),
  ADD KEY `fk_multas_alquileres` (`id_alquiler`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD UNIQUE KEY `ruc_dni` (`ruc_dni`);

--
-- Indices de la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD PRIMARY KEY (`id_resena`),
  ADD KEY `fk_resenas_libros` (`id_libro`),
  ADD KEY `fk_resenas_usuarios` (`id_usuario`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_usuarios_roles` (`id_rol`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id_venta`),
  ADD UNIQUE KEY `num_comprobante` (`num_comprobante`),
  ADD KEY `fk_ventas_usuarios` (`id_usuario`);

--
-- Indices de la tabla `ventas_detalle`
--
ALTER TABLE `ventas_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_detalle_ventas` (`id_venta`),
  ADD KEY `fk_detalle_libros` (`id_libro`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alquileres`
--
ALTER TABLE `alquileres`
  MODIFY `id_alquiler` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `autores`
--
ALTER TABLE `autores`
  MODIFY `id_autor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `editoriales`
--
ALTER TABLE `editoriales`
  MODIFY `id_editorial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `kardex`
--
ALTER TABLE `kardex`
  MODIFY `id_kardex` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `libros`
--
ALTER TABLE `libros`
  MODIFY `id_libro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `multas`
--
ALTER TABLE `multas`
  MODIFY `id_multa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `resenas`
--
ALTER TABLE `resenas`
  MODIFY `id_resena` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ventas_detalle`
--
ALTER TABLE `ventas_detalle`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alquileres`
--
ALTER TABLE `alquileres`
  ADD CONSTRAINT `fk_alquileres_libros` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_alquileres_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `kardex`
--
ALTER TABLE `kardex`
  ADD CONSTRAINT `fk_kardex_libros` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `libros`
--
ALTER TABLE `libros`
  ADD CONSTRAINT `fk_libros_autores` FOREIGN KEY (`id_autor`) REFERENCES `autores` (`id_autor`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_libros_categorias` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_libros_editoriales` FOREIGN KEY (`id_editorial`) REFERENCES `editoriales` (`id_editorial`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_libros_proveedores` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `multas`
--
ALTER TABLE `multas`
  ADD CONSTRAINT `fk_multas_alquileres` FOREIGN KEY (`id_alquiler`) REFERENCES `alquileres` (`id_alquiler`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD CONSTRAINT `fk_resenas_libros` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_resenas_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_ventas_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `ventas_detalle`
--
ALTER TABLE `ventas_detalle`
  ADD CONSTRAINT `fk_detalle_libros` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_ventas` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
