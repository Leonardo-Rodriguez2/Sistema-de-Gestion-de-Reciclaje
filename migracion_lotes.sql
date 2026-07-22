-- ============================================================
-- MIGRACIÓN: Módulo de Verificación de Pagos en Cascada
-- Ejecutar en phpMyAdmin o consola MySQL
-- ============================================================

-- 1. LOTES DE CALLE
-- Agrupa los pagos recolectados por el Encargado de Calle
-- y los envía al Encargado de Barrio para su aprobación.
CREATE TABLE IF NOT EXISTS `lotes_calle` (
  `id`                  INT(11) NOT NULL AUTO_INCREMENT,
  `periodo_mes`         INT(2) NOT NULL COMMENT 'Mes de facturación (1-12)',
  `periodo_anio`        INT(4) NOT NULL COMMENT 'Año de facturación',
  `calle_id`            INT(11) NOT NULL,
  `barrio_id`           INT(11) NOT NULL,
  `encargado_calle_id`  INT(11) NOT NULL COMMENT 'Usuario Rol 6 que emite el lote',
  `encargado_barrio_id` INT(11) DEFAULT NULL COMMENT 'Usuario Rol 5 receptor',
  `monto_esperado`      DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Suma total de todas las casas activas',
  `monto_recolectado`   DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Suma de pagos confirmados con comprobante',
  `total_casas`         INT(11) NOT NULL DEFAULT 0,
  `casas_pagadas`       INT(11) NOT NULL DEFAULT 0,
  `casas_morosas`       INT(11) NOT NULL DEFAULT 0,
  `estado`              ENUM('Borrador','Enviado','Aprobado','Rechazado') NOT NULL DEFAULT 'Borrador'
                        COMMENT 'Borrador=editable, Enviado=bloqueado, Aprobado/Rechazado=procesado',
  `alerta_deuda`        TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 si monto_recolectado < monto_esperado',
  `observaciones_calle` TEXT DEFAULT NULL COMMENT 'Nota del Encargado de Calle al enviar',
  `observaciones_barrio` TEXT DEFAULT NULL COMMENT 'Motivo de rechazo del Barrio',
  `fecha_creacion`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_envio`         TIMESTAMP NULL DEFAULT NULL,
  `fecha_revision`      TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_lote_calle_periodo` (`calle_id`, `periodo_mes`, `periodo_anio`),
  FOREIGN KEY (`calle_id`)            REFERENCES `calles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`barrio_id`)           REFERENCES `barrios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`encargado_calle_id`)  REFERENCES `usuarios` (`id`),
  FOREIGN KEY (`encargado_barrio_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. LOTES DE BARRIO
-- Agrupa los lotes aprobados de todas las calles del barrio
-- y los envía al Gestor/Administrador para aprobación final.
CREATE TABLE IF NOT EXISTS `lotes_barrio` (
  `id`                  INT(11) NOT NULL AUTO_INCREMENT,
  `periodo_mes`         INT(2) NOT NULL,
  `periodo_anio`        INT(4) NOT NULL,
  `barrio_id`           INT(11) NOT NULL,
  `encargado_barrio_id` INT(11) NOT NULL COMMENT 'Usuario Rol 5 que emite el lote',
  `gestor_id`           INT(11) DEFAULT NULL COMMENT 'Usuario Rol 1 o 2 que aprueba',
  `monto_total_esperado`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `monto_total_recolectado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_calles`        INT(11) NOT NULL DEFAULT 0,
  `calles_completas`    INT(11) NOT NULL DEFAULT 0,
  `estado`              ENUM('Borrador','Enviado','Aprobado','Rechazado') NOT NULL DEFAULT 'Borrador',
  `alerta_deuda`        TINYINT(1) NOT NULL DEFAULT 0,
  `comprobante_barrio`  VARCHAR(255) DEFAULT NULL COMMENT 'Foto/PDF del pago global al gestor',
  `observaciones_barrio` TEXT DEFAULT NULL,
  `observaciones_gestor` TEXT DEFAULT NULL COMMENT 'Motivo de rechazo del Gestor',
  `recibo_generado`     TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 si se emitió el recibo de finiquito',
  `recibo_path`         VARCHAR(255) DEFAULT NULL,
  `fecha_creacion`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_envio`         TIMESTAMP NULL DEFAULT NULL,
  `fecha_aprobacion`    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_lote_barrio_periodo` (`barrio_id`, `periodo_mes`, `periodo_anio`),
  FOREIGN KEY (`barrio_id`)            REFERENCES `barrios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`encargado_barrio_id`)  REFERENCES `usuarios` (`id`),
  FOREIGN KEY (`gestor_id`)            REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. RELACIÓN lotes_calle → lotes_barrio
ALTER TABLE `lotes_calle`
  ADD COLUMN IF NOT EXISTS `lote_barrio_id` INT(11) DEFAULT NULL
    COMMENT 'FK al lote de barrio al que pertenece',
  ADD FOREIGN KEY IF NOT EXISTS `fk_lc_lote_barrio` (`lote_barrio_id`)
    REFERENCES `lotes_barrio` (`id`) ON DELETE SET NULL;

-- 4. COLUMNAS ADICIONALES EN cobros (si no existen)
ALTER TABLE `cobros`
  ADD COLUMN IF NOT EXISTS `tipo_cobro`              VARCHAR(30) NOT NULL DEFAULT 'Servicio',
  ADD COLUMN IF NOT EXISTS `estado_verificacion`     VARCHAR(20) NOT NULL DEFAULT 'Pendiente',
  ADD COLUMN IF NOT EXISTS `verificado_por`          INT NULL,
  ADD COLUMN IF NOT EXISTS `verificado_en`           TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS `motivo_rechazo`          TEXT NULL,
  ADD COLUMN IF NOT EXISTS `comprobante_calle`       VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS `observaciones`           TEXT NULL,
  ADD COLUMN IF NOT EXISTS `fecha_confirmacion_calle` DATE NULL,
  ADD COLUMN IF NOT EXISTS `lote_calle_id`           INT(11) NULL COMMENT 'FK al lote de calle',
  ADD FOREIGN KEY IF NOT EXISTS `fk_cobro_lote_calle` (`lote_calle_id`)
    REFERENCES `lotes_calle` (`id`) ON DELETE SET NULL;

-- 5. TABLA DE RECIBOS DE FINIQUITO
CREATE TABLE IF NOT EXISTS `recibos_finiquito` (
  `id`              INT(11) NOT NULL AUTO_INCREMENT,
  `lote_barrio_id`  INT(11) NOT NULL,
  `barrio_id`       INT(11) NOT NULL,
  `periodo_mes`     INT(2) NOT NULL,
  `periodo_anio`    INT(4) NOT NULL,
  `monto_aprobado`  DECIMAL(10,2) NOT NULL,
  `generado_por`    INT(11) NOT NULL COMMENT 'Gestor o Admin que aprobó',
  `fecha_emision`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `numero_recibo`   VARCHAR(30) NOT NULL UNIQUE,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`lote_barrio_id`) REFERENCES `lotes_barrio` (`id`),
  FOREIGN KEY (`barrio_id`)      REFERENCES `barrios` (`id`),
  FOREIGN KEY (`generado_por`)   REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
