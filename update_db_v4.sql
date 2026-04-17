-- Migration v4: Debt tracking for housing requests
ALTER TABLE `solicitudes_vivienda` ADD COLUMN IF NOT EXISTS `monto_deuda` DECIMAL(10,2) DEFAULT 0.00 AFTER `vivienda_id`;
ALTER TABLE `solicitudes_vivienda` ADD COLUMN IF NOT EXISTS `detalles_deuda` TEXT DEFAULT NULL AFTER `monto_deuda`;
