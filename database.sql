-- Script de Base de Datos para Sistema de Reciclaje y Cobros
-- Fecha de creación: 2026-03-15

-- 1. Crear Base de Datos (si no existe)
CREATE DATABASE IF NOT EXISTS `reciclaje_platform` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `reciclaje_platform`;

SET FOREIGN_KEY_CHECKS=0;

-- 2. Estructura de Tablas Actualizadas

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `rol_id` int NOT NULL DEFAULT '4', -- Por defecto es 4 (usuario_vecino)
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`rol_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `empresas`;
CREATE TABLE `empresas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `contacto` varchar(255) NOT NULL,
  `materiales` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `reportes`;
CREATE TABLE `reportes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `ubicacion_nombre` varchar(100) NOT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `tipo_residuo` varchar(50) NOT NULL,
  `descripcion` text,
  `cantidad` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','En proceso','Completado') DEFAULT 'Pendiente',
  `fotos` varchar(255) DEFAULT NULL,
  `fecha_reporte` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Nuevas Tablas: Cobros y Viviendas

DROP TABLE IF EXISTS `barrios`;
CREATE TABLE `barrios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `ciudad` varchar(100) NOT NULL DEFAULT 'Cusco',
  `codigo_postal` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `viviendas`;
CREATE TABLE `viviendas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL, -- Propietario o responsable del pago
  `barrio_id` int NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `numero_casa` varchar(20) DEFAULT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cobros`;
CREATE TABLE `cobros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vivienda_id` int NOT NULL,
  `mes` int NOT NULL,
  `anio` int NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('Pendiente', 'Pagado', 'Vencido') DEFAULT 'Pendiente',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`vivienda_id`) REFERENCES `viviendas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `pagos`;
CREATE TABLE `pagos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cobro_id` int NOT NULL,
  `usuario_id` int NOT NULL, -- Usuario que paga (vecino)
  `gestor_id` int DEFAULT NULL, -- Gestor que validó o registró físicamente el pago (si aplica)
  `monto_pagado` decimal(10,2) NOT NULL,
  `fecha_pago` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `metodo_pago` enum('Efectivo', 'Tarjeta', 'Transferencia') NOT NULL,
  `comprobante` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`cobro_id`) REFERENCES `cobros` (`id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  FOREIGN KEY (`gestor_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 4. Inserción de Datos Mínimos y de Ejemplo

-- Insertar roles
INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Control total del sistema, viviendas, barrios y usuarios.'),
(2, 'Gestor de Pagos', 'Encargado de revisar cobros, validar pagos y generar reportes financieros.'),
(3, 'Recolector', 'Encargado de ver las rutas, los reportes en proceso e ir a recoger los residuos.'),
(4, 'Usuario', 'Vecino que reporta residuos y debe pagar por su recolección de casa.');

-- Insertar Usuarios de Ejemplo (La contraseña encriptada es '123456')
-- password_hash de '123456' = $2y$10$pLw2p4G/.8O9/R.M0yQeK.bU1.W5g/6X9mY23dDMBR.H0n45r8.Wq
INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `password_hash`, `rol_id`) VALUES
(1, 'Admin', 'Sistema', 'admin@ecocusco.com', '$2y$10$pLw2p4G/.8O9/R.M0yQeK.bU1.W5g/6X9mY23dDMBR.H0n45r8.Wq', 1),
(2, 'Julio', 'Pagos', 'gestor@ecocusco.com', '$2y$10$pLw2p4G/.8O9/R.M0yQeK.bU1.W5g/6X9mY23dDMBR.H0n45r8.Wq', 2),
(3, 'Carlos', 'Recolector', 'recolector@ecocusco.com', '$2y$10$pLw2p4G/.8O9/R.M0yQeK.bU1.W5g/6X9mY23dDMBR.H0n45r8.Wq', 3),
(4, 'Vecino', 'Ejemplo', 'vecino@gmail.com', '$2y$10$pLw2p4G/.8O9/R.M0yQeK.bU1.W5g/6X9mY23dDMBR.H0n45r8.Wq', 4);

-- Insertar Barrios
INSERT INTO `barrios` (`id`, `nombre`, `ciudad`) VALUES
(1, 'Barrio San Blas', 'Cusco'),
(2, 'Santa Ana', 'Cusco'),
(3, 'Wanchaq', 'Cusco');

-- Insertar Viviendas de ejemplo (asociadas al usuario 4 - Vecino)
INSERT INTO `viviendas` (`id`, `usuario_id`, `barrio_id`, `direccion`, `numero_casa`) VALUES
(1, 4, 1, 'Calle Tandapata', '120'),
(2, 4, 3, 'Av. Garcilaso', '505');

-- Insertar Cobros de ejemplo para el Vecino
INSERT INTO `cobros` (`id`, `vivienda_id`, `mes`, `anio`, `monto`, `fecha_emision`, `fecha_vencimiento`, `estado`) VALUES
(1, 1, 2, 2026, 25.00, '2026-02-01', '2026-02-28', 'Vencido'),
(2, 1, 3, 2026, 25.00, '2026-03-01', '2026-03-31', 'Pendiente'),
(3, 2, 3, 2026, 40.00, '2026-03-01', '2026-03-31', 'Pendiente');

SET FOREIGN_KEY_CHECKS=1;
