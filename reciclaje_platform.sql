-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 05-07-2026 a las 16:36:14
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
-- Estructura de tabla para la tabla `calles`
--

CREATE TABLE `calles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `barrio_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `calles`
--

INSERT INTO `calles` (`id`, `nombre`, `barrio_id`) VALUES
(1, 'calle principal', 2);

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
  `recaudacion_id` int(11) DEFAULT NULL,
  `tipo_cobro` varchar(30) NOT NULL DEFAULT 'Servicio',
  `estado_verificacion` varchar(20) NOT NULL DEFAULT 'Pendiente',
  `verificado_por` int(11) DEFAULT NULL,
  `verificado_en` timestamp NULL DEFAULT NULL,
  `motivo_rechazo` text DEFAULT NULL,
  `comprobante_calle` varchar(255) DEFAULT NULL,
  `comprobante_barrio` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_confirmacion_calle` date DEFAULT NULL,
  `fecha_confirmacion_barrio` date DEFAULT NULL,
  `lote_calle_id` int(11) DEFAULT NULL,
  `referencia_pago` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cobros`
--

INSERT INTO `cobros` (`id`, `vivienda_id`, `mes`, `anio`, `monto`, `fecha_emision`, `fecha_vencimiento`, `estado`, `recaudacion_id`, `tipo_cobro`, `estado_verificacion`, `verificado_por`, `verificado_en`, `motivo_rechazo`, `comprobante_calle`, `comprobante_barrio`, `observaciones`, `fecha_confirmacion_calle`, `fecha_confirmacion_barrio`, `lote_calle_id`, `referencia_pago`) VALUES
(1, 1, 7, 2026, 5.00, '2026-07-04', '2026-07-04', 'Pagado', NULL, 'Servicio', 'Pendiente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 2, 7, 2026, 5.00, '2026-07-04', '2026-07-04', 'Pagado', NULL, 'Servicio', 'Pendiente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2445'),
(3, 3, 7, 2026, 10.00, '2026-07-05', '2026-07-05', 'Pagado', NULL, 'Servicio', 'Pendiente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0412'),
(4, 4, 7, 2026, 10.00, '2026-07-05', '2026-07-05', 'Pagado', NULL, 'Servicio', 'Pendiente', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pago directo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones_barrio`
--

CREATE TABLE `configuraciones_barrio` (
  `id` int(11) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  `cuota_mensual` decimal(10,2) NOT NULL DEFAULT 10.00,
  `multa_renovacion` decimal(10,2) NOT NULL DEFAULT 5.00,
  `actualizado_por` int(11) DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuraciones_barrio`
--

INSERT INTO `configuraciones_barrio` (`id`, `barrio_id`, `cuota_mensual`, `multa_renovacion`, `actualizado_por`, `fecha_actualizacion`) VALUES
(1, 2, 10.00, 5.00, NULL, '2026-07-05 12:43:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_encargado_barrio`
--

CREATE TABLE `detalles_encargado_barrio` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalles_encargado_barrio`
--

INSERT INTO `detalles_encargado_barrio` (`id`, `usuario_id`, `barrio_id`, `dni`, `telefono`, `direccion`) VALUES
(1, 5, 2, '', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_encargado_calle`
--

CREATE TABLE `detalles_encargado_calle` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `calle_id` int(11) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalles_encargado_calle`
--

INSERT INTO `detalles_encargado_calle` (`id`, `usuario_id`, `calle_id`, `dni`, `telefono`) VALUES
(2, 4, 1, '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_gestor`
--

CREATE TABLE `detalles_gestor` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_recolector`
--

CREATE TABLE `detalles_recolector` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `turno` enum('Mañana','Tarde','Noche') DEFAULT 'Mañana',
  `contacto_emergencia` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Estructura de tabla para la tabla `lotes_barrio`
--

CREATE TABLE `lotes_barrio` (
  `id` int(11) NOT NULL,
  `periodo_mes` int(2) NOT NULL,
  `periodo_anio` int(4) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  `encargado_barrio_id` int(11) NOT NULL,
  `gestor_id` int(11) DEFAULT NULL,
  `monto_total_esperado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_total_recolectado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_calles` int(11) NOT NULL DEFAULT 0,
  `calles_completas` int(11) NOT NULL DEFAULT 0,
  `estado` enum('Borrador','Enviado','Aprobado','Rechazado') NOT NULL DEFAULT 'Borrador',
  `alerta_deuda` tinyint(1) NOT NULL DEFAULT 0,
  `comprobante_barrio` varchar(255) DEFAULT NULL,
  `observaciones_barrio` text DEFAULT NULL,
  `observaciones_gestor` text DEFAULT NULL,
  `recibo_generado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_envio` timestamp NULL DEFAULT NULL,
  `fecha_aprobacion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lotes_calle`
--

CREATE TABLE `lotes_calle` (
  `id` int(11) NOT NULL,
  `calle_id` int(11) NOT NULL,
  `mes` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `monto_recolectado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('Abierto','Enviado','Aprobado','Rechazado') NOT NULL DEFAULT 'Abierto',
  `observaciones` text DEFAULT NULL,
  `fecha_envio` datetime DEFAULT NULL,
  `fecha_evaluacion` datetime DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `cobro_id` int(11) NOT NULL,
  `recolectado_por_id` int(11) NOT NULL,
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
  `tipo` enum('Calle','Barrio') NOT NULL DEFAULT 'Calle',
  `emisor_id` int(11) NOT NULL,
  `receptor_id` int(11) DEFAULT NULL,
  `barrio_id` int(11) NOT NULL,
  `calle_id` int(11) DEFAULT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','Verificado') DEFAULT 'Pendiente',
  `fecha_recaudacion` timestamp NULL DEFAULT current_timestamp(),
  `comprobante` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibos_finiquito`
--

CREATE TABLE `recibos_finiquito` (
  `id` int(11) NOT NULL,
  `lote_barrio_id` int(11) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  `periodo_mes` int(2) NOT NULL,
  `periodo_anio` int(4) NOT NULL,
  `monto_aprobado` decimal(10,2) NOT NULL,
  `generado_por` int(11) NOT NULL,
  `numero_recibo` varchar(30) NOT NULL,
  `fecha_emision` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(5, 'Encargado de Barrio', 'Administra el barrio completo y supervisa a los encargados de calle.'),
(6, 'Encargado de Calle', 'Gestiona las viviendas de una calle específica y reporta pagos al encargado de barrio.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_vivienda`
--

CREATE TABLE `solicitudes_vivienda` (
  `id` int(11) NOT NULL,
  `tipo` enum('Alta','Baja','Renovacion') NOT NULL,
  `calle_id` int(11) NOT NULL,
  `vivienda_id` int(11) DEFAULT NULL,
  `monto_deuda` decimal(10,2) DEFAULT 0.00,
  `detalles_deuda` text DEFAULT NULL,
  `propietario` varchar(100) DEFAULT NULL,
  `numero_casa` varchar(20) DEFAULT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `estado` enum('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
  `creado_por` int(11) NOT NULL,
  `revisado_por` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_revision` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `solicitudes_vivienda`
--

INSERT INTO `solicitudes_vivienda` (`id`, `tipo`, `calle_id`, `vivienda_id`, `monto_deuda`, `detalles_deuda`, `propietario`, `numero_casa`, `referencia`, `estado`, `creado_por`, `revisado_por`, `fecha_creacion`, `fecha_revision`) VALUES
(1, 'Alta', 1, NULL, 0.00, NULL, 'Familia Silba', '312', 'Casa color amarillo', 'Aprobado', 4, 5, '2026-07-04 13:55:56', '2026-07-04 21:07:19'),
(2, 'Alta', 1, NULL, 0.00, NULL, 'Familia Valero', '321', '', 'Aprobado', 4, 5, '2026-07-04 21:42:58', '2026-07-04 21:43:06'),
(3, 'Alta', 1, NULL, 0.00, NULL, 'Familia Moreno', '343', '', 'Aprobado', 4, 5, '2026-07-04 21:56:16', '2026-07-05 12:32:47'),
(4, 'Alta', 1, NULL, 0.00, NULL, 'Family uzcategui', '432', '', 'Aprobado', 4, 5, '2026-07-05 13:11:58', '2026-07-05 13:12:04');

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
(1, 'Admin', 'Sistema', 'admin@gmail.com', NULL, NULL, '$2y$10$cGTJCOs8F3urGLa.nsuCweiQ3EomwGo3nc0ZmlKfWcUaUUFcUeCom', 1, '2026-07-04 12:53:02'),
(2, 'Julio', 'Pagos', 'gestor@ecocusco.com', NULL, NULL, '$2y$10$/gkxq6jLVIP53mheew6cWOiyZCBWQw18ueq0eK798CbicmlLJxbX.', 2, '2026-07-04 12:53:02'),
(4, 'calle', 'calle', 'calle@gmail.com', 'M', '1979-07-12', '$2y$10$Ax.onSIzKtRgZVQmf//hFOW4GJzbNmWIu2zerGQSTmsEEzZVV.k3y', 6, '2026-07-04 12:58:03'),
(5, 'barrio', 'barrio', 'barrio@gmail.com', 'M', '1979-07-18', '$2y$10$BXUU0.9/6d8Illqs9TjJFebAxW4CIAapKK3sa/jS2YrSGmVPcYSZK', 5, '2026-07-04 13:50:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `viviendas`
--

CREATE TABLE `viviendas` (
  `id` int(11) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  `calle_id` int(11) DEFAULT NULL,
  `propietario` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `numero_casa` varchar(20) DEFAULT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `estado_servicio` enum('Activo','Suspendido','Anulado') DEFAULT 'Activo',
  `fecha_registro` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `viviendas`
--

INSERT INTO `viviendas` (`id`, `barrio_id`, `calle_id`, `propietario`, `telefono`, `direccion`, `numero_casa`, `referencia`, `estado_servicio`, `fecha_registro`) VALUES
(1, 2, 1, 'Familia Silba', NULL, 'calle principal', '312', 'Casa color amarillo', 'Activo', '2026-07-04 21:07:19'),
(2, 2, 1, 'Familia Valero', NULL, 'calle principal', '321', '', 'Activo', '2026-07-04 21:43:06'),
(3, 2, 1, 'Familia Moreno', NULL, 'calle principal', '343', '', 'Activo', '2026-07-05 12:32:47'),
(4, 2, 1, 'Family uzcategui', NULL, 'calle principal', '432', '', 'Activo', '2026-07-05 13:12:04');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `barrios`
--
ALTER TABLE `barrios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `calles`
--
ALTER TABLE `calles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `barrio_id` (`barrio_id`);

--
-- Indices de la tabla `cobros`
--
ALTER TABLE `cobros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vivienda_id` (`vivienda_id`),
  ADD KEY `recaudacion_id` (`recaudacion_id`);

--
-- Indices de la tabla `configuraciones_barrio`
--
ALTER TABLE `configuraciones_barrio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barrio_id` (`barrio_id`),
  ADD KEY `actualizado_por` (`actualizado_por`);

--
-- Indices de la tabla `detalles_encargado_barrio`
--
ALTER TABLE `detalles_encargado_barrio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD KEY `barrio_id` (`barrio_id`);

--
-- Indices de la tabla `detalles_encargado_calle`
--
ALTER TABLE `detalles_encargado_calle`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD KEY `calle_id` (`calle_id`);

--
-- Indices de la tabla `detalles_gestor`
--
ALTER TABLE `detalles_gestor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

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
-- Indices de la tabla `lotes_barrio`
--
ALTER TABLE `lotes_barrio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lote_barrio` (`barrio_id`,`periodo_mes`,`periodo_anio`);

--
-- Indices de la tabla `lotes_calle`
--
ALTER TABLE `lotes_calle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `calle_id` (`calle_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cobro_id` (`cobro_id`),
  ADD KEY `recolectado_por_id` (`recolectado_por_id`),
  ADD KEY `gestor_id` (`gestor_id`);

--
-- Indices de la tabla `recaudaciones`
--
ALTER TABLE `recaudaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emisor_id` (`emisor_id`),
  ADD KEY `receptor_id` (`receptor_id`),
  ADD KEY `barrio_id` (`barrio_id`),
  ADD KEY `calle_id` (`calle_id`);

--
-- Indices de la tabla `recibos_finiquito`
--
ALTER TABLE `recibos_finiquito`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_numero_recibo` (`numero_recibo`);

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
-- Indices de la tabla `solicitudes_vivienda`
--
ALTER TABLE `solicitudes_vivienda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `calle_id` (`calle_id`),
  ADD KEY `vivienda_id` (`vivienda_id`),
  ADD KEY `creado_por` (`creado_por`),
  ADD KEY `revisado_por` (`revisado_por`);

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
  ADD KEY `barrio_id` (`barrio_id`),
  ADD KEY `calle_id` (`calle_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `barrios`
--
ALTER TABLE `barrios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `calles`
--
ALTER TABLE `calles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cobros`
--
ALTER TABLE `cobros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `configuraciones_barrio`
--
ALTER TABLE `configuraciones_barrio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `detalles_encargado_barrio`
--
ALTER TABLE `detalles_encargado_barrio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalles_encargado_calle`
--
ALTER TABLE `detalles_encargado_calle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `detalles_gestor`
--
ALTER TABLE `detalles_gestor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_recolector`
--
ALTER TABLE `detalles_recolector`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lotes_barrio`
--
ALTER TABLE `lotes_barrio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lotes_calle`
--
ALTER TABLE `lotes_calle`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recibos_finiquito`
--
ALTER TABLE `recibos_finiquito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `solicitudes_vivienda`
--
ALTER TABLE `solicitudes_vivienda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `viviendas`
--
ALTER TABLE `viviendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `calles`
--
ALTER TABLE `calles`
  ADD CONSTRAINT `calles_ibfk_1` FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cobros`
--
ALTER TABLE `cobros`
  ADD CONSTRAINT `cobros_ibfk_1` FOREIGN KEY (`vivienda_id`) REFERENCES `viviendas` (`id`),
  ADD CONSTRAINT `cobros_ibfk_2` FOREIGN KEY (`recaudacion_id`) REFERENCES `recaudaciones` (`id`);

--
-- Filtros para la tabla `configuraciones_barrio`
--
ALTER TABLE `configuraciones_barrio`
  ADD CONSTRAINT `configuraciones_barrio_ibfk_1` FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `configuraciones_barrio_ibfk_2` FOREIGN KEY (`actualizado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `detalles_encargado_barrio`
--
ALTER TABLE `detalles_encargado_barrio`
  ADD CONSTRAINT `detalles_encargado_barrio_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalles_encargado_barrio_ibfk_2` FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`);

--
-- Filtros para la tabla `detalles_encargado_calle`
--
ALTER TABLE `detalles_encargado_calle`
  ADD CONSTRAINT `detalles_encargado_calle_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalles_encargado_calle_ibfk_2` FOREIGN KEY (`calle_id`) REFERENCES `calles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalles_gestor`
--
ALTER TABLE `detalles_gestor`
  ADD CONSTRAINT `detalles_gestor_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalles_recolector`
--
ALTER TABLE `detalles_recolector`
  ADD CONSTRAINT `detalles_recolector_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `lotes_calle`
--
ALTER TABLE `lotes_calle`
  ADD CONSTRAINT `lotes_calle_ibfk_1` FOREIGN KEY (`calle_id`) REFERENCES `calles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`cobro_id`) REFERENCES `cobros` (`id`),
  ADD CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`recolectado_por_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `pagos_ibfk_3` FOREIGN KEY (`gestor_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `recaudaciones`
--
ALTER TABLE `recaudaciones`
  ADD CONSTRAINT `recaudaciones_ibfk_1` FOREIGN KEY (`emisor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `recaudaciones_ibfk_2` FOREIGN KEY (`receptor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `recaudaciones_ibfk_3` FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`),
  ADD CONSTRAINT `recaudaciones_ibfk_4` FOREIGN KEY (`calle_id`) REFERENCES `calles` (`id`);

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `solicitudes_vivienda`
--
ALTER TABLE `solicitudes_vivienda`
  ADD CONSTRAINT `solicitudes_vivienda_ibfk_1` FOREIGN KEY (`calle_id`) REFERENCES `calles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitudes_vivienda_ibfk_2` FOREIGN KEY (`vivienda_id`) REFERENCES `viviendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitudes_vivienda_ibfk_3` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitudes_vivienda_ibfk_4` FOREIGN KEY (`revisado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);

--
-- Filtros para la tabla `viviendas`
--
ALTER TABLE `viviendas`
  ADD CONSTRAINT `viviendas_ibfk_1` FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`),
  ADD CONSTRAINT `viviendas_ibfk_2` FOREIGN KEY (`calle_id`) REFERENCES `calles` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
