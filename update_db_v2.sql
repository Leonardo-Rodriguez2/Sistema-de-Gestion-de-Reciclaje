-- Migration script v2: Multi-level Management

-- 1. Update Roles
UPDATE `roles` SET `nombre` = 'Encargado de Barrio', `descripcion` = 'Administra el barrio completo y supervisa a los encargados de calle.' WHERE `id` = 5;
INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES (6, 'Encargado de Calle', 'Gestiona las viviendas de una calle específica y reporta pagos al encargado de barrio.');

-- 2. Create Calles Table
CREATE TABLE IF NOT EXISTS `calles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `barrio_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`barrio_id`) REFERENCES `barrios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Modify Viviendas Table
-- Add calle_id and rename encargado_calle_id to encargado_calle_id
ALTER TABLE `viviendas` ADD COLUMN `calle_id` int(11) DEFAULT NULL;
ALTER TABLE `viviendas` ADD CONSTRAINT `fk_vivienda_calle` FOREIGN KEY (`calle_id`) REFERENCES `calles` (`id`) ON DELETE SET NULL;

-- Rename encargado_calle_id (Street Manager)
-- Note: We'll keep the column name for now but update our logic to treat it as Street Manager (Encargado de Calle).
-- Or rename it if we are sure we can update all code.
-- Let's rename it to be consistent: encargado_calle_id
ALTER TABLE `viviendas` CHANGE `jefe_cuadra_id` `encargado_calle_id` int(11) NOT NULL;

-- 4. Create Detalles Encargado Calle Table
CREATE TABLE IF NOT EXISTS `detalles_encargado_calle` (
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

-- 5. Rename Detalles Jefe Cuadra to Detalles Encargado Barrio
RENAME TABLE `detalles_jefe_cuadra` TO `detalles_encargado_barrio`;

-- 6. Create Solicitudes Vivienda Table
CREATE TABLE IF NOT EXISTS `solicitudes_vivienda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('Alta','Baja') NOT NULL,
  `calle_id` int(11) NOT NULL,
  `vivienda_id` int(11) DEFAULT NULL,
  `propietario` varchar(100) DEFAULT NULL,
  `numero_casa` varchar(20) DEFAULT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `estado` enum('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
  `creado_por` int(11) NOT NULL, -- Encargado de Calle
  `revisado_por` int(11) DEFAULT NULL, -- Encargado de Barrio or Admin
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_revision` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`calle_id`) REFERENCES `calles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`vivienda_id`) REFERENCES `viviendas` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`revisado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Update Recaudaciones Table to support Calle -> Barrio flow
-- Currently: jefe_id (Barrio Manager), barrio_id, monto_total, estado
-- We need to know which Street Manager sent it.
ALTER TABLE `recaudaciones` ADD COLUMN `tipo` enum('Calle','Barrio') NOT NULL DEFAULT 'Calle' AFTER `id`;
ALTER TABLE `recaudaciones` ADD COLUMN `calle_id` int(11) DEFAULT NULL AFTER `barrio_id`;
ALTER TABLE `recaudaciones` ADD CONSTRAINT `fk_recaudacion_calle` FOREIGN KEY (`calle_id`) REFERENCES `calles` (`id`);
-- Rename jefe_id to emisor_id to be generic
ALTER TABLE `recaudaciones` CHANGE `jefe_id` `emisor_id` int(11) NOT NULL;
ALTER TABLE `recaudaciones` ADD COLUMN `receptor_id` int(11) DEFAULT NULL AFTER `emisor_id`;
ALTER TABLE `recaudaciones` ADD CONSTRAINT `fk_recaudacion_receptor` FOREIGN KEY (`receptor_id`) REFERENCES `usuarios` (`id`);
