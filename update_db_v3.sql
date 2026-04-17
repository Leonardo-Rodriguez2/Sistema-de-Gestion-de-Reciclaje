-- Migration script v3: Payment & Renewal Management

-- 1. Ensure 'estado_servicio' exists in 'viviendas'
ALTER TABLE `viviendas` ADD COLUMN IF NOT EXISTS `estado_servicio` ENUM('Activo', 'Suspendido', 'Anulado') DEFAULT 'Activo' AFTER `referencia`;

-- 2. Update 'solicitudes_vivienda' type ENUM to include 'Renovacion'
ALTER TABLE `solicitudes_vivienda` MODIFY COLUMN `tipo` ENUM('Alta', 'Baja', 'Renovacion') NOT NULL;
