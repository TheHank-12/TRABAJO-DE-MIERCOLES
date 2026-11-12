========================================================
-- DATOS DE PRUEBA - Librería Online
-- Ejecutar DESPUÉS de libreria.sql (ahí se crean las tablas y los roles)
-- ========================================================
USE `libreria`;
 
-- --------------------------------------------------------
-- ROLES (INSERT IGNORE: no falla si ya existen)
-- --------------------------------------------------------
INSERT IGNORE INTO `roles` (`nombre`, `descripcion`) VALUES
  ('Administrador', 'Acceso total: libros, usuarios, ventas, alquileres y reportes'),
  ('Empleado',      'Gestiona ventas, alquileres y devoluciones'),
  ('Cliente',       'Compra, alquila y reseña libros');
 
-- --------------------------------------------------------
-- USUARIOS (contraseñas guardadas con hash bcrypt, para password_verify() de PHP)
--   admin@libreria.com     / Admin123     (Administrador)
--   empleado@libreria.com  / Empleado123  (Empleado)
--   cliente@libreria.com   / Cliente123   (Cliente)
-- --------------------------------------------------------
INSERT INTO `usuarios` (`id_rol`, `dni`, `nombre`, `apellido`, `email`, `password`, `telefono`, `direccion`, `estado`) VALUES
((SELECT id_rol FROM roles WHERE nombre = 'Administrador'), '10000001', 'Admin',    'Prueba', 'admin@libreria.com',
  '$2y$10$rN5Bxt3ioNyMA2D.ECb.rO9kLOlOUVrr7vxii5YMCqQMeybmf3nnO', '999000001', 'Lima', 'ACTIVO'),
((SELECT id_rol FROM roles WHERE nombre = 'Empleado'),      '10000002', 'Empleado', 'Prueba', 'empleado@libreria.com',
  '$2y$10$cXNt1AX/kuvnrZl/V9wwvetoXP092GhssAooOEYLEzW0Aj.LeoSau', '999000002', 'Lima', 'ACTIVO'),
((SELECT id_rol FROM roles WHERE nombre = 'Cliente'),       '10000003', 'Cliente',  'Prueba', 'cliente@libreria.com',
  '$2y$10$MbcLoiNBK.YbYySCi9U2qOAJNCa7JWPUdayl4YEYAJ0sMoXD0iJcu', '999000003', 'Lima', 'ACTIVO');
 
-- --------------------------------------------------------
-- CATÁLOGO DE PRUEBA
-- --------------------------------------------------------
INSERT INTO `categorias` (`nombre`, `descripcion`) VALUES
('Novela',   'Narrativa de ficción'),
('Cuento',   'Colecciones de cuentos'),
('Ciencia',  'Divulgación científica');
 
INSERT INTO `autores` (`nombre`, `nacionalidad`) VALUES
('Mario Vargas Llosa',  'Peruana'),
('Ciro Alegría',        'Peruana'),
('Gabriel García Márquez', 'Colombiana');
 
INSERT INTO `editoriales` (`nombre`, `contacto`) VALUES
('Alfaguara', 'contacto@alfaguara.test'),
('Planeta',   'contacto@planeta.test');
 
INSERT INTO `libros`
(`id_categoria`, `id_autor`, `id_editorial`, `titulo`, `isbn`, `descripcion`, `precio_venta`, `precio_alquiler`, `stock`, `fecha_publicacion`) VALUES
((SELECT id_categoria FROM categorias WHERE nombre='Novela'),
 (SELECT id_autor FROM autores WHERE nombre='Mario Vargas Llosa'),
 (SELECT id_editorial FROM editoriales WHERE nombre='Alfaguara'),
 'La ciudad y los perros', 'ISBN-TEST-0001', 'Novela de prueba para el catálogo.', 55.00, 8.00, 10, '1963-10-01'),
((SELECT id_categoria FROM categorias WHERE nombre='Novela'),
 (SELECT id_autor FROM autores WHERE nombre='Ciro Alegría'),
 (SELECT id_editorial FROM editoriales WHERE nombre='Planeta'),
 'El mundo es ancho y ajeno', 'ISBN-TEST-0002', 'Novela de prueba para el catálogo.', 48.50, 7.00, 5, '1941-01-01'),
((SELECT id_categoria FROM categorias WHERE nombre='Novela'),
 (SELECT id_autor FROM autores WHERE nombre='Gabriel García Márquez'),
 (SELECT id_editorial FROM editoriales WHERE nombre='Alfaguara'),
 'Cien años de soledad', 'ISBN-TEST-0003', 'Novela de prueba para el catálogo.', 60.00, 9.00, 8, '1967-05-30');