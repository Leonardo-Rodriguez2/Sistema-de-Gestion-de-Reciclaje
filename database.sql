-- Final Database Schema for Reciclaje Platform
-- Consolidated from reciclaje_platform.sql and migrations v2-v5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table Structure
-- --------------------------------------------------------

CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Control total del sistema, viviendas, barrios y usuarios.'),
(2, 'Gestor de Pagos', 'Encargado de revisar cobros, validar pagos y generar reportes financieros.'),
(3, 'Recolector', 'Encargado de ver las rutas, los reportes en proceso e ir a recoger los residuos.'),
(5, 'Encargado de Barrio', 'Administra el barrio completo y supervisa a los encargados de calle.'),
(6, 'Encargado de Calle', 'Gestiona las viviendas de una calle específica y reporta pagos al encargado de barrio.');

-- --------------------------------------------------------

CREATE TABLE `barrios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `ciudad` varchar(100) NOT NULL DEFAULT 'Cusco',
  `codigo_postal` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `barrios` (`id`, `nombre`, `ciudad`, `codigo_postal`) VALUES
(1, 'Barrio San Blas', 'Cusco', NULL),
(2, 'Santa Ana', 'Cusco', NULL),
(3, 'Wanchaq', 'Cusco', NULL);

-- --------------------------------------------------------

CREATE TABLE `calles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `genero` enum('M','F','Otro') DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL DEFAULT 4,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `genero`, `fecha_nacimiento`, `password_hash`, `rol_id`) VALUES
(1, 'Admin', 'Sistema', 'admin@gmail.com', NULL, NULL, '$2y$10$cGTJCOs8F3urGLa.nsuCweiQ3EomwGo3nc0ZmlKfWcUaUUFcUeCom', 1),
(2, 'Julio', 'Pagos', 'gestor@ecocusco.com', NULL, NULL, '$2y$10$/gkxq6jLVIP53mheew6cWOiyZCBWQw18ueq0eK798CbicmlLJxbX.', 2);

-- --------------------------------------------------------

CREATE TABLE `detalles_gestor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `detalles_recolector` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `turno` enum('Mañana','Tarde','Noche') DEFAULT 'Mañana',
  `contacto_emergencia` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `detalles_encargado_barrio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `detalles_encargado_calle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `calle_id` int(11) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`calle_id`) REFERENCES `calles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `viviendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `encargado_calle_id` int(11) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  `calle_id` int(11) DEFAULT NULL,
  `propietario` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `numero_casa` varchar(20) DEFAULT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `estado_servicio` ENUM('Activo', 'Suspendido', 'Anulado') DEFAULT 'Activo',
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`encargado_calle_id`) REFERENCES `usuarios` (`id`),
  FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`),
  FOREIGN KEY (`calle_id`) REFERENCES `calles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `recaudaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('Calle','Barrio') NOT NULL DEFAULT 'Calle',
  `emisor_id` int(11) NOT NULL,
  `receptor_id` int(11) DEFAULT NULL,
  `barrio_id` int(11) NOT NULL,
  `calle_id` int(11) DEFAULT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','Verificado') DEFAULT 'Pendiente',
  `fecha_recaudacion` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`emisor_id`) REFERENCES `usuarios` (`id`),
  FOREIGN KEY (`receptor_id`) REFERENCES `usuarios` (`id`),
  FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`),
  FOREIGN KEY (`calle_id`) REFERENCES `calles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `cobros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vivienda_id` int(11) NOT NULL,
  `mes` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('Pendiente','Pagado','Vencido') DEFAULT 'Pendiente',
  `recaudacion_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`vivienda_id`) REFERENCES `viviendas` (`id`),
  FOREIGN KEY (`recaudacion_id`) REFERENCES `recaudaciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cobro_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `gestor_id` int(11) DEFAULT NULL,
  `monto_pagado` decimal(10,2) NOT NULL,
  `fecha_pago` timestamp NULL DEFAULT current_timestamp(),
  `metodo_pago` enum('Efectivo','Tarjeta','Transferencia') NOT NULL,
  `comprobante` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`cobro_id`) REFERENCES `cobros` (`id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  FOREIGN KEY (`gestor_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `solicitudes_vivienda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` ENUM('Alta', 'Baja', 'Renovacion') NOT NULL,
  `calle_id` int(11) NOT NULL,
  `vivienda_id` int(11) DEFAULT NULL,
  `monto_deuda` DECIMAL(10,2) DEFAULT 0.00,
  `detalles_deuda` TEXT DEFAULT NULL,
  `propietario` varchar(100) DEFAULT NULL,
  `numero_casa` varchar(20) DEFAULT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `estado` enum('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
  `creado_por` int(11) NOT NULL,
  `revisado_por` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_revision` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`calle_id`) REFERENCES `calles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`vivienda_id`) REFERENCES `viviendas` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`revisado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `configuraciones_barrio` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `barrio_id` INT NOT NULL,
  `cuota_mensual` DECIMAL(10,2) NOT NULL DEFAULT 10.00,
  `multa_renovacion` DECIMAL(10,2) NOT NULL DEFAULT 5.00,
  `actualizado_por` INT,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (`barrio_id`),
  FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`actualizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `reportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `ubicacion_nombre` varchar(100) NOT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `tipo_residuo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','En proceso','Completado') DEFAULT 'Pendiente',
  `fotos` varchar(255) DEFAULT NULL,
  `fecha_reporte` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `contacto` varchar(255) NOT NULL,
  `materiales` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
