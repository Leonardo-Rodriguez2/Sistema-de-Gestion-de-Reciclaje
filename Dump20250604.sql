-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: reciclaje_platform
-- ------------------------------------------------------
-- Server version	8.0.41

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `empresas`
--

DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `contacto` varchar(255) NOT NULL,
  `materiales` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresas`
--

LOCK TABLES `empresas` WRITE;
/*!40000 ALTER TABLE `empresas` DISABLE KEYS */;
INSERT INTO `empresas` VALUES (1,'EcoPlast S.A.','Reciclaje de plásticos PET y HDPE','contacto@ecoplast.com','Plástico/PET, Plástico/HDPE'),(2,'PaperCycle','Procesamiento de papel y cartón','info@papercycle.mx','Papel/Cartón'),(3,'GlassWorks','Reciclaje especializado en vidrio','ventas@glassworks.com','Vidrio/Envases'),(4,'BioOrgánicos','Manejo de residuos orgánicos','servicios@biorganicos.com','Orgánico/Vegetal'),(5,'ReciclaTotal','Solución integral de reciclaje','atencion@reciclatotal.com','Plástico, Papel, Vidrio'),(6,'GreenVidrio','Fabricación con vidrio reciclado','contacto@greenvidrio.mx','Vidrio'),(7,'EcoCajas','Producción de cajas recicladas','ventas@ecocajas.com','Papel/Cartón'),(8,'PlastiRec','Transformación de plásticos','info@plastirec.com','Plástico'),(9,'VerdEco','Gestión sostenible de residuos','soporte@verdeco.org','Orgánico, Papel'),(10,'CleanTech','Tecnología para reciclaje','servicio@cleantech.com','Plástico, Vidrio');
/*!40000 ALTER TABLE `empresas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (15,'María','Torres','maria@example.com','123456','2025-05-04 00:58:12'),(16,'José','Ramírez','jose@example.com','123456','2025-05-04 00:58:12'),(17,'Lucía','Fernández','lucia@example.com','123456','2025-05-04 00:58:12'),(18,'Diego','Castro','diego@example.com','123456','2025-05-04 00:58:12'),(19,'Carmen','Ruiz','carmen@example.com','123456','2025-05-04 00:58:12'),(20,'Raúl','Soto','raul@example.com','123456','2025-05-04 00:58:12'),(21,'Verónica','Mendoza','veronica@example.com','123456','2025-05-04 00:58:12'),(22,'Fernando','Vargas','fernando@example.com','123456','2025-05-04 00:58:12'),(23,'Andrea','Morales','andrea@example.com','123456','2025-05-04 00:58:12'),(24,'Marco','Herrera','marco@example.com','123456','2025-05-04 00:58:12'),(25,'Sofía','Silva','sofia@example.com','123456','2025-05-04 00:58:12'),(26,'Héctor','López','hector@example.com','123456','2025-05-04 00:58:12'),(27,'Patricia','Ortega','patricia@example.com','123456','2025-05-04 00:58:12'),(28,'Jorge','Reyes','jorge@example.com','123456','2025-05-04 00:58:12'),(29,'Claudia','Rojas','claudia@example.com','123456','2025-05-04 00:58:12'),(30,'Luis','Gutiérrez','luisg@example.com','123456','2025-05-04 00:58:12'),(31,'Natalia','Campos','natalia@example.com','123456','2025-05-04 00:58:12'),(32,'Gabriel','Paredes','gabriel@example.com','123456','2025-05-04 00:58:12'),(33,'Diana','Delgado','diana@example.com','123456','2025-05-04 00:58:12'),(34,'Bruno','Salazar','bruno@example.com','123456','2025-05-04 00:58:12'),(35,'justo','domingo','justo@gmail.com','$2y$12$gKV96pac0ZqpEjSEJcyf8u6ddPD7fwqUOcyYDougaNiacDt/xPhKK','2025-05-04 01:10:48');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reportes`
--

DROP TABLE IF EXISTS `reportes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reportes`
--

LOCK TABLES `reportes` WRITE;
/*!40000 ALTER TABLE `reportes` DISABLE KEYS */;
INSERT INTO `reportes` VALUES (46,15,'Parque Kennedy',-12.12111000,-77.03021000,'Vidrio','Botellas transparentes',10.00,'Pendiente',NULL,'2024-01-10 15:30:00'),(47,15,'Av. Angamos',-12.11321000,-77.02800000,'Plástico','Tapas y botellas',13.20,'Completado',NULL,'2024-02-11 14:15:00'),(48,16,'Jr. Amazonas',-12.10345000,-77.02100000,'Orgánico','Restos de comida',18.70,'En proceso',NULL,'2024-03-12 17:00:00'),(49,27,'Plaza San Martín',-12.04500000,-77.03000000,'Metal','Latas de conserva',5.60,'Pendiente',NULL,'2024-04-13 19:20:00'),(50,18,'Jr. Junín',-12.04588000,-77.03500000,'Papel','Revistas viejas',7.80,'Completado',NULL,'2024-05-14 21:00:00'),(51,29,'Av. Arequipa',-12.10400000,-77.04500000,'Vidrio','Vidrios de ventanas',11.30,'En proceso',NULL,'2024-06-15 16:30:00'),(52,30,'Mercado Central',-12.04912000,-77.04000000,'Orgánico','Frutas podridas',30.00,'Pendiente',NULL,'2024-07-16 15:00:00'),(53,31,'Calle Cuzco',-12.04800000,-77.03800000,'Metal','Chatarra',14.00,'Completado',NULL,'2024-08-17 18:15:00'),(54,32,'Av. Pardo',-12.12700000,-77.03500000,'Plástico','Botellas y envolturas',19.00,'Pendiente',NULL,'2024-09-18 14:45:00'),(55,23,'Parque Universitario',-12.04650000,-77.03000000,'Papel','Cuadernos usados',22.10,'En proceso',NULL,'2024-10-19 20:00:00'),(56,34,'Jr. Moquegua',-12.04500000,-77.03700000,'Orgánico','Restos vegetales',12.40,'Pendiente',NULL,'2024-11-20 13:10:00'),(57,25,'Av. La Marina',-12.09100000,-77.07300000,'Vidrio','Copas rotas',9.50,'Completado',NULL,'2024-12-21 23:00:00'),(58,16,'Calle Tarapacá',-12.04750000,-77.03800000,'Plástico','Bolsas',8.90,'Pendiente',NULL,'2025-01-22 12:45:00'),(59,27,'Calle Belén',-12.04600000,-77.03600000,'Metal','Piezas mecánicas',16.30,'En proceso',NULL,'2025-02-23 17:30:00'),(60,28,'Av. Abancay',-12.04533000,-77.03322000,'Papel','Papeles de oficina',10.20,'Pendiente',NULL,'2025-03-24 15:10:00'),(61,19,'Plaza Manco Cápac',-12.05888000,-77.03500000,'Orgánico','Basura vegetal',26.00,'Pendiente',NULL,'2025-04-25 19:40:00'),(62,20,'Av. Túpac Amaru',-12.10101000,-77.06500000,'Plástico','Envases y botellas',17.70,'Completado',NULL,'2025-05-26 21:20:00'),(63,21,'Parque Zonal',-12.09000000,-77.07000000,'Vidrio','Botellas verdes',9.90,'En proceso',NULL,'2025-06-27 14:00:00'),(64,22,'Mercado Magdalena',-12.09456000,-77.07200000,'Metal','Trozos de fierro',20.00,'Pendiente',NULL,'2025-07-28 15:50:00'),(65,23,'Av. Faucett',-12.04688000,-77.11345000,'Orgánico','Residuos de comida',35.50,'Pendiente',NULL,'2025-08-29 13:30:00');
/*!40000 ALTER TABLE `reportes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-04 15:00:25
