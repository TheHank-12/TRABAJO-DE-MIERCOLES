-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 13-11-2026 a las 04:04:50
-- Versión del servidor: 5.7.37
-- Versión de PHP: 8.1.7

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
-- --------------------------------------------------------
-- LIMPIEZA DE TABLAS PREVIAS
-- --------------------------------------------------------
DROP TABLE IF EXISTS `multas`;
DROP TABLE IF EXISTS `alquileres`;
DROP TABLE IF EXISTS `ventas_detalle`;
DROP TABLE IF EXISTS `ventas`;
DROP TABLE IF EXISTS `kardex`;
DROP TABLE IF EXISTS `resenas`;
DROP TABLE IF EXISTS `libros`;
DROP TABLE IF EXISTS `editoriales`;
DROP TABLE IF EXISTS `autores`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `proveedores`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `roles`;

-- --------------------------------------------------------
-- 1. TABLA: roles
-- --------------------------------------------------------
CREATE TABLE `roles` (
  `id_rol` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------
-- 2. TABLA: usuarios
-- --------------------------------------------------------
CREATE TABLE `usuarios` (
  `id_usuario` INT(11) NOT NULL AUTO_INCREMENT,
  `id_rol` INT(11) NOT NULL,
  `dni` VARCHAR(15) NOT NULL UNIQUE,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `direccion` VARCHAR(255) DEFAULT NULL,
  `estado` ENUM('ACTIVO', 'INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------
-- 3. TABLA: proveedores
-- --------------------------------------------------------
CREATE TABLE `proveedores` (
  `id_proveedor` INT(11) NOT NULL AUTO_INCREMENT,
  `ruc_dni` VARCHAR(20) NOT NULL UNIQUE,
  `razon_social` VARCHAR(150) NOT NULL,
  `contacto` VARCHAR(100) DEFAULT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(120) DEFAULT NULL,
  `direccion` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------
-- 4. TABLA: categorias
-- --------------------------------------------------------
CREATE TABLE `categorias` (
  `id_categoria` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL UNIQUE,
  `descripcion` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------
-- 5. TABLA: autores
-- --------------------------------------------------------
CREATE TABLE `autores` (
  `id_autor` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(120) NOT NULL,
  `nacionalidad` VARCHAR(80) DEFAULT 'Peruana',
  PRIMARY KEY (`id_autor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------
-- 6. TABLA: editoriales
-- --------------------------------------------------------
CREATE TABLE `editoriales` (
  `id_editorial` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(120) NOT NULL,
  `contacto` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id_editorial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------
-- 7. TABLA: libros
-- --------------------------------------------------------
CREATE TABLE `libros` (
  `id_libro` INT(11) NOT NULL AUTO_INCREMENT,
  `id_categoria` INT(11) NOT NULL,
  `id_autor` INT(11) NOT NULL,
  `id_editorial` INT(11) NOT NULL,
  `id_proveedor` INT(11) NOT NULL,
  `titulo` VARCHAR(200) NOT NULL,
  `isbn` VARCHAR(30) NOT NULL UNIQUE,
  `precio_venta` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `precio_alquiler` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock` INT(11) NOT NULL DEFAULT 0,
  `portada` VARCHAR(255) DEFAULT 'img/no-cover.png.jpg',
  `fecha_publicacion` DATE DEFAULT NULL,
  `estado` ENUM('DISPONIBLE', 'AGOTADO', 'DESCONTINUADO') NOT NULL DEFAULT 'DISPONIBLE',
  PRIMARY KEY (`id_libro`),
  CONSTRAINT `fk_libros_categorias` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_libros_autores` FOREIGN KEY (`id_autor`) REFERENCES `autores` (`id_autor`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_libros_editoriales` FOREIGN KEY (`id_editorial`) REFERENCES `editoriales` (`id_editorial`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_libros_proveedores` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------
-- 8. TABLA: kardex (Movimientos de Inventario)
-- --------------------------------------------------------
CREATE TABLE `kardex` (
  `id_kardex` INT(11) NOT NULL AUTO_INCREMENT,
  `id_libro` INT(11) NOT NULL,
  `tipo_movimiento` ENUM('ENTRADA', 'SALIDA_VENTA', 'SALIDA_ALQUILER', 'DEVOLUCION', 'AJUSTE') NOT NULL,
  `cantidad` INT(11) NOT NULL,
  `stock_anterior` INT(11) NOT NULL,
  `stock_actual` INT(11) NOT NULL,
  `motivo` VARCHAR(255) DEFAULT NULL,
  `fecha_movimiento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kardex`),
  CONSTRAINT `fk_kardex_libros` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------
-- 9. TABLA: alquileres
-- --------------------------------------------------------
CREATE TABLE `alquileres` (
  `id_alquiler` INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` INT(11) NOT NULL,
  `id_libro` INT(11) NOT NULL,
  `fecha_inicio` DATE NOT NULL,
  `fecha_dev_esperada` DATE NOT NULL,
  `fecha_dev_real` DATE DEFAULT NULL,
  `monto_alquiler` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` ENUM('EFECTIVO', 'YAPE', 'PLIN', 'TARJETA', 'TRANSFERENCIA') NOT NULL DEFAULT 'EFECTIVO',
  `num_operacion` VARCHAR(50) DEFAULT NULL,
  `estado` ENUM('En Alquiler', 'Devuelto', 'Con Demora') NOT NULL DEFAULT 'En Alquiler',
  PRIMARY KEY (`id_alquiler`),
  CONSTRAINT `fk_alquileres_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_alquileres_libros` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------
-- 10. TABLA: multas
-- --------------------------------------------------------
CREATE TABLE `multas` (
  `id_multa` INT(11) NOT NULL AUTO_INCREMENT,
  `id_alquiler` INT(11) NOT NULL,
  `dias_atraso` INT(11) NOT NULL DEFAULT 0,
  `monto_diario` DECIMAL(10,2) NOT NULL DEFAULT 5.00,
  `monto_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `estado` ENUM('Pendiente', 'Pagada') NOT NULL DEFAULT 'Pendiente',
  `fecha_pago` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_multa`),
  CONSTRAINT `fk_multas_alquileres` FOREIGN KEY (`id_alquiler`) REFERENCES `alquileres` (`id_alquiler`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------
-- 11. TABLA: ventas
-- --------------------------------------------------------
CREATE TABLE `ventas` (
  `id_venta` INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` INT(11) NOT NULL,
  `num_comprobante` VARCHAR(30) NOT NULL UNIQUE,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `igv` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` ENUM('EFECTIVO', 'YAPE', 'PLIN', 'TARJETA', 'TRANSFERENCIA') NOT NULL DEFAULT 'EFECTIVO',
  `num_operacion` VARCHAR(50) DEFAULT NULL,
  `fecha_venta` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_venta`),
  CONSTRAINT `fk_ventas_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------
-- 12. TABLA: ventas_detalle
-- --------------------------------------------------------
CREATE TABLE `ventas_detalle` (
  `id_detalle` INT(11) NOT NULL AUTO_INCREMENT,
  `id_venta` INT(11) NOT NULL,
  `id_libro` INT(11) NOT NULL,
  `cantidad` INT(11) NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id_detalle`),
  CONSTRAINT `fk_detalle_ventas` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_libros` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------
-- 13. TABLA: resenas
-- --------------------------------------------------------
CREATE TABLE `resenas` (
  `id_resena` INT(11) NOT NULL AUTO_INCREMENT,
  `id_libro` INT(11) NOT NULL,
  `id_usuario` INT(11) NOT NULL,
  `puntuacion` INT(11) NOT NULL CHECK (`puntuacion` BETWEEN 1 AND 5),
  `comentario` TEXT DEFAULT NULL,
  `fecha` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_resena`),
  CONSTRAINT `fk_resenas_libros` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_resenas_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ============================================================================
-- INSERT DE DATOS DE PRUEBA (DML)
-- Contraseñas para todos los usuarios: admin123
-- ============================================================================

-- 1. Roles
INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Acceso total al sistema, reportes, usuarios y gestión de catálogo.'),
(2, 'Cliente', 'Acceso a compras, alquileres, reseñas y carrito de compras.'),
(3, 'Bibliotecario', 'Atención en mostrador, ventas POS, alquileres, devoluciones e inventario.');

-- 2. Usuarios (1 Admin, 1 Bibliotecario y 5 Clientes)
INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `dni`, `nombre`, `apellido`, `email`, `password`, `telefono`, `direccion`, `estado`) VALUES
(1, 1, '77777777', 'Administrador', 'General', 'admin@libreria.com', '$2y$10$123456789012345678901u./zCLeARnBuyfsw17qkjYMfd7L4o2om', '987654321', 'Av. Javier Prado 1234, Lima', 'ACTIVO'),
(2, 2, '12345678', 'Carlos', 'Mendoza', 'cliente@libreria.com', '$2y$10$123456789012345678901u./zCLeARnBuyfsw17qkjYMfd7L4o2om', '912345678', 'Jr. Las Flores 432, Lima', 'ACTIVO'),
(3, 3, '88888888', 'Ana', 'Torres', 'bibliotecario@libreria.com', '$2y$10$123456789012345678901u./zCLeARnBuyfsw17qkjYMfd7L4o2om', '955443322', 'Av. Brasil 890, Lima', 'ACTIVO'),
(4, 2, '45678912', 'Lucía', 'Ramírez', 'lucia.ramirez@gmail.com', '$2y$10$123456789012345678901u./zCLeARnBuyfsw17qkjYMfd7L4o2om', '944556677', 'Calle Los Olivos 234, Surco', 'ACTIVO'),
(5, 2, '34567891', 'Fernando', 'Castillo', 'fcastillo@hotmail.com', '$2y$10$123456789012345678901u./zCLeARnBuyfsw17qkjYMfd7L4o2om', '933445566', 'Av. Larco 567, Miraflores', 'ACTIVO'),
(6, 2, '23456789', 'Sofía', 'Benítez', 'sbenitez@outlook.com', '$2y$10$123456789012345678901u./zCLeARnBuyfsw17qkjYMfd7L4o2om', '922334455', 'Jr. Arequipa 123, San Miguel', 'ACTIVO'),
(7, 2, '56789123', 'Miguel', 'Ángel', 'mangel@yahoo.com', '$2y$10$123456789012345678901u./zCLeARnBuyfsw17qkjYMfd7L4o2om', '911223344', 'Av. Petit Thouars 1420, Lince', 'ACTIVO');

-- 3. Proveedores
INSERT INTO `proveedores` (`id_proveedor`, `ruc_dni`, `razon_social`, `contacto`, `telefono`, `email`, `direccion`) VALUES
(1, '20100123456', 'Distribuidora Cultural Peruana S.A.C.', 'Juan Pérez', '014238900', 'ventas@culturalperu.pe', 'Av. Arequipa 2500, Lima'),
(2, '20554433221', 'Editorial Planeta Perú S.A.', 'María Gómez', '016143500', 'contacto@planeta.pe', 'Av. Santa Cruz 830, Miraflores'),
(3, '20456789012', 'SBS Librerías S.A.C.', 'Roberto Díaz', '012087000', 'contacto@sbs.com.pe', 'Av. Angamos Oeste 301, Miraflores');

-- 4. Categorías
INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`) VALUES
(1, 'Literatura Peruana', 'Obras clásicas y contemporáneas de autores peruanos.'),
(2, 'Ficción y Novela', 'Novelas de autores internacionales y narrativa hispanoamericana.'),
(3, 'Ciencia y Tecnología', 'Libros de ingeniería, informática y ciencias exactas.'),
(4, 'Poesía', 'Antologías y obras poéticas universales.');

-- 5. Autores
INSERT INTO `autores` (`id_autor`, `nombre`, `nacionalidad`) VALUES
(1, 'Mario Vargas Llosa', 'Peruana'),
(2, 'César Vallejo', 'Peruana'),
(3, 'Gabriel García Márquez', 'Colombiana'),
(4, 'Julio Ramón Ribeyro', 'Peruana'),
(5, 'José María Arguedas', 'Peruana'),
(6, 'Isabel Allende', 'Chilena');

-- 6. Editoriales
INSERT INTO `editoriales` (`id_editorial`, `nombre`, `contacto`) VALUES
(1, 'Editorial Peisa', 'info@peisa.com.pe'),
(2, 'Alfaguara', 'atencion@alfaguara.pe'),
(3, 'Seix Barral', 'contacto@seixbarral.com'),
(4, 'Debolsillo', 'contacto@debolsillo.pe');

-- 7. Libros (Mantiene exactamente tus 7 títulos con sus imágenes en uploads/portadas/)
INSERT INTO `libros` (`id_libro`, `id_categoria`, `id_autor`, `id_editorial`, `id_proveedor`, `titulo`, `isbn`, `precio_venta`, `precio_alquiler`, `stock`, `portada`, `fecha_publicacion`, `estado`) VALUES
(1, 1, 1, 2, 2, 'La Ciudad y los Perros', '978-612-4264-01-1', 49.90, 8.00, 25, 'uploads/portadas/ciudad_perros.jpg', '1963-10-15', 'DISPONIBLE'),
(2, 4, 2, 1, 1, 'Los Heraldos Negros / Trilce', '978-612-4264-02-8', 29.90, 5.00, 15, 'uploads/portadas/heraldos_negros.jpg', '1919-01-01', 'DISPONIBLE'),
(3, 2, 3, 3, 2, 'Cien Años de Soledad', '978-030-7474-72-8', 59.90, 10.00, 20, 'uploads/portadas/cien_anos.jpg', '1967-05-30', 'DISPONIBLE'),
(4, 2, 6, 4, 2, 'La Casa de los Espíritus', '978-849-7592-20-8', 54.90, 9.00, 18, 'uploads/portadas/casa_espiritus.jpg', '1982-10-01', 'DISPONIBLE'),
(5, 1, 4, 1, 1, 'La Palabra del Mudo', '978-612-4264-05-9', 45.00, 7.50, 12, 'uploads/portadas/palabra_mudo.jpg', '1973-04-12', 'DISPONIBLE'),
(6, 4, 2, 1, 1, 'Poemas Humanos', '978-612-4264-06-6', 32.00, 6.00, 10, 'uploads/portadas/poemas_humanos.jpg', '1939-07-15', 'DISPONIBLE'),
(7, 1, 5, 2, 1, 'Los Ríos Profundos', '978-612-4264-07-3', 38.00, 7.00, 14, 'uploads/portadas/rios_profundos.jpg', '1958-08-20', 'DISPONIBLE');

-- 8. Kárdex de Inventario Inicial
INSERT INTO `kardex` (`id_kardex`, `id_libro`, `tipo_movimiento`, `cantidad`, `stock_anterior`, `stock_actual`, `motivo`) VALUES
(1, 1, 'ENTRADA', 25, 0, 25, 'Carga inicial de inventario'),
(2, 2, 'ENTRADA', 15, 0, 15, 'Carga inicial de inventario'),
(3, 3, 'ENTRADA', 20, 0, 20, 'Carga inicial de inventario'),
(4, 4, 'ENTRADA', 18, 0, 18, 'Carga inicial de inventario'),
(5, 5, 'ENTRADA', 12, 0, 12, 'Carga inicial de inventario'),
(6, 6, 'ENTRADA', 10, 0, 10, 'Carga inicial de inventario'),
(7, 7, 'ENTRADA', 14, 0, 14, 'Carga inicial de inventario');

-- 9. Alquileres de Prueba
INSERT INTO `alquileres` (`id_alquiler`, `id_usuario`, `id_libro`, `fecha_inicio`, `fecha_dev_esperada`, `fecha_dev_real`, `monto_alquiler`, `metodo_pago`, `num_operacion`, `estado`) VALUES
(1, 2, 1, '2026-11-01', '2026-11-05', NULL, 8.00, 'YAPE', 'YP-988341', 'Con Demora'),
(2, 4, 3, '2026-11-02', '2026-11-07', '2026-11-06', 10.00, 'PLIN', 'PL-112233', 'Devuelto'),
(3, 5, 4, '2026-11-03', '2026-11-08', NULL, 9.00, 'TARJETA', 'TX-887766', 'En Alquiler'),
(4, 6, 7, '2026-10-25', '2026-10-29', '2026-11-02', 7.00, 'EFECTIVO', NULL, 'Devuelto'),
(5, 7, 2, '2026-11-04', '2026-11-09', NULL, 5.00, 'YAPE', 'YP-334455', 'En Alquiler');

-- 10. Multas de Prueba
INSERT INTO `multas` (`id_multa`, `id_alquiler`, `dias_atraso`, `monto_diario`, `monto_total`, `estado`, `fecha_pago`) VALUES
(1, 1, 2, 5.00, 10.00, 'Pendiente', NULL),
(2, 4, 4, 5.00, 20.00, 'Pagada', '2026-11-02 18:30:00');

-- 11. Ventas Registradas
INSERT INTO `ventas` (`id_venta`, `id_usuario`, `num_comprobante`, `subtotal`, `igv`, `total`, `metodo_pago`, `num_operacion`, `fecha_venta`) VALUES
(1, 2, 'B001-00000001', 42.29, 7.61, 49.90, 'PLIN', 'PL-443210', '2026-11-02 10:30:00'),
(2, 4, 'B001-00000002', 88.81, 15.99, 104.80, 'TARJETA', 'TX-998811', '2026-11-03 15:20:00'),
(3, 5, 'B001-00000003', 70.34, 12.66, 83.00, 'YAPE', 'YP-776655', '2026-11-04 11:15:00'),
(4, 6, 'B001-00000004', 38.14, 6.86, 45.00, 'EFECTIVO', NULL, '2026-11-05 17:40:00');

-- 12. Detalle de Ventas
INSERT INTO `ventas_detalle` (`id_detalle`, `id_venta`, `id_libro`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 1, 1, 49.90, 49.90),
(2, 2, 4, 1, 54.90, 54.90),
(3, 2, 1, 1, 49.90, 49.90),
(4, 3, 5, 1, 45.00, 45.00),
(5, 3, 7, 1, 38.00, 38.00),
(6, 4, 5, 1, 45.00, 45.00);

-- 13. Reseñas Enriquecidas (Genera excelentes promedios para "Libros Mejor Valorados")
INSERT INTO `resenas` (`id_resena`, `id_libro`, `id_usuario`, `puntuacion`, `comentario`, `fecha`) VALUES
-- Libro 1: La Ciudad y los Perros
(1, 1, 2, 5, 'Excelente obra maestra de la literatura peruana. Entregado a tiempo.', '2026-11-03 14:20:00'),
(2, 1, 4, 5, 'Insuperable Vargas Llosa. Te atrapa de principio a fin.', '2026-11-04 09:15:00'),
(3, 1, 6, 4, 'Muy buena narrativa, súper recomendada para estudiantes.', '2026-11-05 18:00:00'),

-- Libro 3: Cien Años de Soledad
(4, 3, 2, 5, 'Un clásico imperdible, edición impecable de Seix Barral.', '2026-11-04 11:00:00'),
(5, 3, 4, 5, 'Macondo en su máxima expresión, fantástico de inicio a fin.', '2026-11-05 10:30:00'),
(6, 3, 5, 5, 'Sin palabras, el mejor libro del realismo mágico.', '2026-11-06 12:45:00'),

-- Libro 4: La Casa de los Espíritus
(7, 4, 6, 5, 'Hermosa novela de Isabel Allende, muy conmovedora.', '2026-11-04 16:20:00'),
(8, 4, 7, 5, 'Personajes memorables y una trama fascinante.', '2026-11-06 14:10:00'),

-- Libro 5: La Palabra del Mudo
(9, 5, 2, 4, 'Los mejores cuentos de Ribeyro.', '2026-11-05 16:45:00'),
(10, 5, 4, 5, 'Ribeyro retrata Lima como nadie más puede hacerlo.', '2026-11-06 08:30:00'),

-- Libro 7: Los Ríos Profundos
(11, 7, 6, 5, 'Magistral Arguedas, transmite toda la magia e identidad andina.', '2026-11-05 19:10:00'),
(12, 7, 7, 4, 'Gran obra de la literatura indigenista.', '2026-11-06 15:50:00'),

-- Libro 2: Los Heraldos Negros / Trilce
(13, 2, 5, 5, 'Poesía sublime de César Vallejo, imprescindible en el hogar.', '2026-11-06 11:00:00');
