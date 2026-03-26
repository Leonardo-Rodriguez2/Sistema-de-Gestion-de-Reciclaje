-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 26-03-2026 a las 01:46:00
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `reciclaje_platform`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `barrios`
--

CREATE TABLE `barrios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ciudad` varchar(100) NOT NULL DEFAULT 'Cusco',
  `codigo_postal` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `barrios`
--

INSERT INTO `barrios` (`id`, `nombre`, `ciudad`, `codigo_postal`) VALUES
(1, 'Barrio San Blas', 'Cusco', NULL),
(2, 'Santa Ana', 'Cusco', NULL),
(3, 'Wanchaq', 'Cusco', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cobros`
--

CREATE TABLE `cobros` (
  `id` int(11) NOT NULL,
  `vivienda_id` int(11) NOT NULL,
  `mes` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('Pendiente','Pagado','Vencido') DEFAULT 'Pendiente',
  `recaudacion_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cobros`
--

INSERT INTO `cobros` (`id`, `vivienda_id`, `mes`, `anio`, `monto`, `fecha_emision`, `fecha_vencimiento`, `estado`, `recaudacion_id`) VALUES
(1, 1, 2, 2026, 25.00, '2026-02-01', '2026-02-28', 'Vencido', NULL),
(2, 1, 3, 2026, 25.00, '2026-03-01', '2026-03-31', 'Pendiente', NULL),
(3, 2, 3, 2026, 40.00, '2026-03-01', '2026-03-31', 'Pendiente', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_gestor`
--

CREATE TABLE `detalles_gestor` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `especialidad` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalles_gestor`
--

INSERT INTO `detalles_gestor` (`id`, `usuario_id`, `dni`, `telefono`, `area`, `especialidad`) VALUES
(2, 2, '12345678', '912345678', NULL, NULL),
(3, 10, '31107189', '041612121', 'encargado de los cobros', 'contabilidad');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_jefe_cuadra`
--

CREATE TABLE `detalles_jefe_cuadra` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `estado_civil` varchar(50) DEFAULT NULL,
  `ocupacion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalles_jefe_cuadra`
--

INSERT INTO `detalles_jefe_cuadra` (`id`, `usuario_id`, `barrio_id`, `dni`, `telefono`, `direccion`, `estado_civil`, `ocupacion`) VALUES
(3, 5, 1, '45678901', '987654321', NULL, NULL, NULL),
(7, 9, 2, '31107192', '0416123243', NULL, NULL, 'programador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_recolector`
--

CREATE TABLE `detalles_recolector` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `licencia` varchar(50) DEFAULT NULL,
  `turno` enum('Mañana','Tarde','Noche') DEFAULT 'Mañana',
  `grupo_sanguineo` varchar(10) DEFAULT NULL,
  `contacto_emergencia` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalles_recolector`
--

INSERT INTO `detalles_recolector` (`id`, `usuario_id`, `dni`, `telefono`, `licencia`, `turno`, `grupo_sanguineo`, `contacto_emergencia`) VALUES
(3, 3, '87654321', '933445566', 'Q1234567', 'Mañana', 'O+', 'Maria Recolector (999888777)');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `contacto` varchar(255) NOT NULL,
  `materiales` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `cobro_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `gestor_id` int(11) DEFAULT NULL,
  `monto_pagado` decimal(10,2) NOT NULL,
  `fecha_pago` timestamp NULL DEFAULT current_timestamp(),
  `metodo_pago` enum('Efectivo','Tarjeta','Transferencia') NOT NULL,
  `comprobante` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recaudaciones`
--

CREATE TABLE `recaudaciones` (
  `id` int(11) NOT NULL,
  `jefe_id` int(11) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','Verificado') DEFAULT 'Pendiente',
  `fecha_recaudacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `recaudaciones`
--

INSERT INTO `recaudaciones` (`id`, `jefe_id`, `barrio_id`, `monto_total`, `estado`, `fecha_recaudacion`) VALUES
(1, 5, 1, 150.00, 'Pendiente', '2026-03-23 16:22:06'),
(2, 5, 2, 85.50, 'Verificado', '2026-03-22 16:22:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `ubicacion_nombre` varchar(100) NOT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `tipo_residuo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','En proceso','Completado') DEFAULT 'Pendiente',
  `fotos` varchar(255) DEFAULT NULL,
  `fecha_reporte` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reportes`
--

INSERT INTO `reportes` (`id`, `usuario_id`, `ubicacion_nombre`, `latitud`, `longitud`, `tipo_residuo`, `descripcion`, `cantidad`, `estado`, `fotos`, `fecha_reporte`) VALUES
(1, 1, 'Av. El Sol 123', NULL, NULL, 'Plásticos', 'Gran acumulación de botellas PET.', 15.50, 'Pendiente', NULL, '2026-03-23 16:22:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Control total del sistema, viviendas, barrios y usuarios.'),
(2, 'Gestor de Pagos', 'Encargado de revisar cobros, validar pagos y generar reportes financieros.'),
(3, 'Recolector', 'Encargado de ver las rutas, los reportes en proceso e ir a recoger los residuos.'),
(5, 'Jefe de Cuadra', 'Encargado de recibir el dinero, registrar viviendas y marcar sus pagos.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `genero` enum('M','F','Otro') DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL DEFAULT 4,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `genero`, `fecha_nacimiento`, `password_hash`, `rol_id`, `creado_en`) VALUES
(1, 'Admin', 'Sistema', 'admin@gmail.com', NULL, NULL, '$2y$10$cGTJCOs8F3urGLa.nsuCweiQ3EomwGo3nc0ZmlKfWcUaUUFcUeCom', 1, '2026-03-20 13:23:47'),
(2, 'Julio', 'Pagos', 'gestor@ecocusco.com', NULL, NULL, '$2y$10$/gkxq6jLVIP53mheew6cWOiyZCBWQw18ueq0eK798CbicmlLJxbX.', 2, '2026-03-20 13:23:47'),
(3, 'Carlos', 'Recolector', 'recolector@gmail.com', NULL, NULL, '$2y$10$cGTJCOs8F3urGLa.nsuCweiQ3EomwGo3nc0ZmlKfWcUaUUFcUeCom', 3, '2026-03-20 13:23:47'),
(5, 'Roberto', 'Jefe', 'jefe@gmail.com', NULL, NULL, '$2y$10$cGTJCOs8F3urGLa.nsuCweiQ3EomwGo3nc0ZmlKfWcUaUUFcUeCom', 5, '2026-03-20 13:23:47'),
(9, 'Leonardo', 'Rodriguez', 'leonardo@gmail.com', 'M', '2009-03-25', '$2y$10$vDpqgNJATHekYQjQxFI7cuyDkDhnDJ/mepgTbwRlkEdBWF0rKHAze', 5, '2026-03-25 17:17:13'),
(10, 'Leonardo', 'Rodriguez', 'leonardo@gmail.com1', 'M', '2026-02-25', '$2y$10$E/xgLwoV6OFftC/skZXB3uJUKg.PJBcidRk5ZEu7sbWk.Hc4LJCIm', 2, '2026-03-25 17:19:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `viviendas`
--

CREATE TABLE `viviendas` (
  `id` int(11) NOT NULL,
  `jefe_cuadra_id` int(11) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  `propietario` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `numero_casa` varchar(20) DEFAULT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `viviendas`
--

INSERT INTO `viviendas` (`id`, `jefe_cuadra_id`, `barrio_id`, `propietario`, `telefono`, `direccion`, `numero_casa`, `referencia`, `fecha_registro`) VALUES
(1, 5, 1, 'Familia Quispe', NULL, 'Calle Tandapata', '120', NULL, '2026-03-20 13:23:47'),
(2, 5, 3, 'Familia Mamani', NULL, 'Av. Garcilaso', '505', NULL, '2026-03-20 13:23:47');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `barrios`
--
ALTER TABLE `barrios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cobros`
--
ALTER TABLE `cobros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vivienda_id` (`vivienda_id`),
  ADD KEY `fk_recaudacion` (`recaudacion_id`);

--
-- Indices de la tabla `detalles_gestor`
--
ALTER TABLE `detalles_gestor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `detalles_jefe_cuadra`
--
ALTER TABLE `detalles_jefe_cuadra`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD KEY `barrio_id` (`barrio_id`);

--
-- Indices de la tabla `detalles_recolector`
--
ALTER TABLE `detalles_recolector`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cobro_id` (`cobro_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `gestor_id` (`gestor_id`);

--
-- Indices de la tabla `recaudaciones`
--
ALTER TABLE `recaudaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jefe_id` (`jefe_id`),
  ADD KEY `barrio_id` (`barrio_id`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `rol_id` (`rol_id`);

--
-- Indices de la tabla `viviendas`
--
ALTER TABLE `viviendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jefe_cuadra_id` (`jefe_cuadra_id`),
  ADD KEY `barrio_id` (`barrio_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `barrios`
--
ALTER TABLE `barrios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `cobros`
--
ALTER TABLE `cobros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `detalles_gestor`
--
ALTER TABLE `detalles_gestor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `detalles_jefe_cuadra`
--
ALTER TABLE `detalles_jefe_cuadra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `detalles_recolector`
--
ALTER TABLE `detalles_recolector`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recaudaciones`
--
ALTER TABLE `recaudaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `viviendas`
--
ALTER TABLE `viviendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cobros`
--
ALTER TABLE `cobros`
  ADD CONSTRAINT `cobros_ibfk_1` FOREIGN KEY (`vivienda_id`) REFERENCES `viviendas` (`id`),
  ADD CONSTRAINT `fk_recaudacion` FOREIGN KEY (`recaudacion_id`) REFERENCES `recaudaciones` (`id`);

--
-- Filtros para la tabla `detalles_gestor`
--
ALTER TABLE `detalles_gestor`
  ADD CONSTRAINT `detalles_gestor_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalles_jefe_cuadra`
--
ALTER TABLE `detalles_jefe_cuadra`
  ADD CONSTRAINT `detalles_jefe_cuadra_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalles_jefe_cuadra_ibfk_2` FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`);

--
-- Filtros para la tabla `detalles_recolector`
--
ALTER TABLE `detalles_recolector`
  ADD CONSTRAINT `detalles_recolector_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`cobro_id`) REFERENCES `cobros` (`id`),
  ADD CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `pagos_ibfk_3` FOREIGN KEY (`gestor_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `recaudaciones`
--
ALTER TABLE `recaudaciones`
  ADD CONSTRAINT `recaudaciones_ibfk_1` FOREIGN KEY (`jefe_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `recaudaciones_ibfk_2` FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`);

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);

--
-- Filtros para la tabla `viviendas`
--
ALTER TABLE `viviendas`
  ADD CONSTRAINT `viviendas_ibfk_1` FOREIGN KEY (`jefe_cuadra_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `viviendas_ibfk_2` FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
