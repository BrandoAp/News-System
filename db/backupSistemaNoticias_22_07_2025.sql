-- MySQL dump 10.13  Distrib 9.1.0, for Win64 (x86_64)
--
-- Host: localhost    Database: bd_sistema_noticias
-- ------------------------------------------------------
-- Server version	9.1.0

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
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `icono` varchar(50) DEFAULT NULL,
  `descripcion` text,
  `id_estado` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `id_estado` (`id_estado`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'Deportes','⚽','Futbol, Basquetbol, Beisbol',1),(2,'Farandula','🤩','Wao farandula',1),(3,'Anime','👻','Noticia de los Anime de los buenos',1),(4,'Tecnologia','📲','Todo sobre tecnologia',1),(5,'Crimenes','🍽️','Crimen de estado mano',1),(6,'Internacionales','🌐','MR WORLDWIDE TU SAE',2),(7,'Politica','🧻','Politicas',2),(8,'Administracion','💼','Administadores',1);
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comentarios`
--

DROP TABLE IF EXISTS `comentarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comentarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_noticia` int NOT NULL,
  `id_usuario` int NOT NULL,
  `contenido` text NOT NULL,
  `id_respuesta` int DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_estado` int NOT NULL DEFAULT '1',
  `id_comentario_padre` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_noticia` (`id_noticia`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_respuesta` (`id_respuesta`),
  KEY `id_estado` (`id_estado`),
  KEY `fk_comentario_padre` (`id_comentario_padre`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comentarios`
--

LOCK TABLES `comentarios` WRITE;
/*!40000 ALTER TABLE `comentarios` DISABLE KEYS */;
INSERT INTO `comentarios` VALUES (1,13,8,'Uuffff manito ta god',NULL,'2025-07-21 03:15:18',1,NULL),(6,13,11,'a saco pago mano',NULL,'2025-07-21 08:27:32',1,NULL),(4,12,8,'god',NULL,'2025-07-21 03:16:00',1,NULL),(7,13,11,'uuuffff',NULL,'2025-07-21 08:27:37',1,NULL),(12,13,10,'god ah',7,'2025-07-21 03:39:27',1,NULL),(9,6,11,'que god eres',NULL,'2025-07-21 08:27:53',1,NULL),(10,6,11,'asi si',NULL,'2025-07-21 08:27:57',1,NULL),(13,13,10,'god ah',7,'2025-07-21 03:43:59',1,NULL),(14,13,10,'god ah',7,'2025-07-21 03:44:06',1,NULL),(23,17,11,'GOD MANO',NULL,'2025-07-21 19:04:52',1,NULL),(21,15,11,'papu god',NULL,'2025-07-21 13:29:31',1,NULL),(26,20,11,'waos',NULL,'2025-07-21 21:33:34',1,NULL),(22,15,4,'papu gracias',21,'2025-07-21 08:30:03',1,NULL),(24,17,4,'gracia gracia',23,'2025-07-21 14:05:12',1,NULL),(27,20,4,'muchas gracias',26,'2025-07-21 16:34:13',1,NULL),(29,28,17,'me gusto mucho',NULL,'2025-07-22 18:16:39',1,NULL),(30,28,4,'muchas gracias',29,'2025-07-22 13:20:28',1,NULL);
/*!40000 ALTER TABLE `comentarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estados`
--

DROP TABLE IF EXISTS `estados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estados`
--

LOCK TABLES `estados` WRITE;
/*!40000 ALTER TABLE `estados` DISABLE KEYS */;
INSERT INTO `estados` VALUES (1,'activo','Registro activo y habilitado'),(2,'inactivo','Registro inactivo o deshabilitado'),(3,'publicado','Noticia publicada y visible al público'),(4,'archivado','Noticia archivada, no visible');
/*!40000 ALTER TABLE `estados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imagenes`
--

DROP TABLE IF EXISTS `imagenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `imagenes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_noticia` int NOT NULL,
  `url_thumbnail` varchar(255) NOT NULL,
  `url_thumbnail_1` varchar(255) NOT NULL,
  `url_thumbnail_2` varchar(255) NOT NULL,
  `url_grande` varchar(255) NOT NULL,
  `descripcion` text,
  `es_principal` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_noticia` (`id_noticia`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imagenes`
--

LOCK TABLES `imagenes` WRITE;
/*!40000 ALTER TABLE `imagenes` DISABLE KEYS */;
INSERT INTO `imagenes` VALUES (21,6,'/News-System/public/uploads/noticias/687d8371ba673_nojuimo.png','','','/News-System/public/uploads/noticias/687d8371ba673_nojuimo.png',NULL,1),(22,6,'/News-System/public/uploads/noticias/687d8371babb1_waza.png','','','/News-System/public/uploads/noticias/687d8371babb1_waza.png',NULL,0),(23,6,'/News-System/public/uploads/noticias/687d8371bafee_squi.png','','','/News-System/public/uploads/noticias/687d8371bafee_squi.png',NULL,0),(18,12,'/News-System/public/uploads/noticias/687d7ed109f17_waza.png','','','/News-System/public/uploads/noticias/687d7ed109f17_waza.png',NULL,0),(19,12,'/News-System/public/uploads/noticias/687d7efa1fb46_heladodecanela.jpg','','','/News-System/public/uploads/noticias/687d7efa1fb46_heladodecanela.jpg',NULL,1),(8,13,'/News-System/public/uploads/noticias/687d3669dd338_FONDOESCRITORIO.png','','','/News-System/public/uploads/noticias/687d3669dd338_FONDOESCRITORIO.png',NULL,1),(9,13,'/News-System/public/uploads/noticias/687d369a31d8e_nojuimo.png','','','/News-System/public/uploads/noticias/687d369a31d8e_nojuimo.png',NULL,1),(10,13,'/News-System/public/uploads/noticias/687d369a321a8_squi.png','','','/News-System/public/uploads/noticias/687d369a321a8_squi.png',NULL,0),(17,12,'/News-System/public/uploads/noticias/687d7ed10940c_nojuimo.png','','','/News-System/public/uploads/noticias/687d7ed10940c_nojuimo.png',NULL,1),(24,15,'687df72c12240_1753085740.png','687df72c1381e_1753085740.png','687df72c146a2_1753085740.png','687df72c1119c_1753085740.png','Imágenes de la noticia',1),(25,16,'687e3f655e3d7_1753104229.png','687e3f655f776_1753104229.png','687e3f65603aa_1753104229.png','687e3f655c205_1753104229.png','Imágenes de la noticia',1),(30,17,'687e4352875ff_1753105234.jpg','687e4352879e2_1753105234.png','687e435287f69_1753105234.png','687e4352872e7_1753105234.jpg','Imágenes de la noticia actualizadas',1),(31,18,'687e51c6c032b_1753108934.png','687e51c6c06ef_1753108934.jpg','687e51c6c09c1_1753108934.png','687e51c6bfdf5_1753108934.png','Imágenes de la noticia',1),(32,19,'687e525b7712c_1753109083.png','687e525b77b12_1753109083.png','687e525b78872_1753109083.png','687e525b7669e_1753109083.png','Imágenes de la noticia',1),(33,20,'687e57f886b09_1753110520.png','687e57f8879e0_1753110520.png','687e57f888f79_1753110520.png','687e57f885403_1753110520.png','Imágenes de la noticia',1),(35,21,'','687e58be5e0aa_1753110718.jpg','687e58be5ef70_1753110718.png','','Imágenes de la noticia actualizadas',0),(36,22,'687e5972cb4f3_1753110898.png','687e5972cc785_1753110898.jpg','687e5972cd99d_1753110898.png','687e5972cad7e_1753110898.png','Imágenes de la noticia',1),(38,24,'687e5af349dd7_1753111283.jpg','687e5af34b42c_1753111283.jpg','687e5af34c7ba_1753111283.jpg','687e5af34874b_1753111283.jpg','Imágenes de la noticia',1),(42,23,'687e5c0668d47_1753111558.png','687e5c066a0f3_1753111558.jpg','','687e5c0668a87_1753111558.png','Imágenes de la noticia actualizadas',1),(41,25,'687e5bc78564c_1753111495.jpeg','687e5bc785b21_1753111495.jpeg','687e5bc78602f_1753111495.jpg','687e5bc78506a_1753111495.jpeg','Imágenes de la noticia',1),(43,26,'687e5c5e4f52f_1753111646.jpg','687e5c5e50b9a_1753111646.png','687e5c5e51db5_1753111646.png','687e5c5e4e560_1753111646.jpg','Imágenes de la noticia',1),(44,27,'687e5e0c9039a_1753112076.jpg','687e5e0c91b2d_1753112076.jpeg','687e5e0c93348_1753112076.jpg','687e5e0c8f71b_1753112076.jpg','Imágenes de la noticia',1),(45,28,'687e6ae9388a2_1753115369.webp','687e6ae939046_1753115369.jpg','687e6ae939ae8_1753115369.png','687e6ae935907_1753115369.webp','Imágenes de la noticia',1),(46,29,'687f9069ca331_1753190505.webp','687f9069cc71d_1753190505.jpeg','687f9069ce903_1753190505.jpg','687f9069c6a49_1753190505.webp','Imágenes de la noticia',1),(47,30,'687f90d6bc908_1753190614.jpeg','687f90d6be023_1753190614.jpg','687f90d6bf0d1_1753190614.png','687f90d6bc014_1753190614.jpeg','Imágenes de la noticia',1);
/*!40000 ALTER TABLE `imagenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `likes`
--

DROP TABLE IF EXISTS `likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `likes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_noticia` int NOT NULL,
  `id_usuario` int NOT NULL,
  `id_tipo_reaccion` int NOT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`id_noticia`,`id_usuario`,`id_tipo_reaccion`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_tipo_reaccion` (`id_tipo_reaccion`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `likes`
--

LOCK TABLES `likes` WRITE;
/*!40000 ALTER TABLE `likes` DISABLE KEYS */;
INSERT INTO `likes` VALUES (1,12,8,1,'2025-07-21 03:16:02'),(2,13,8,1,'2025-07-21 03:16:11'),(3,15,11,1,'2025-07-21 13:16:41'),(4,15,11,2,'2025-07-21 13:16:44'),(5,20,15,1,'2025-07-21 21:19:18'),(6,28,17,1,'2025-07-22 18:16:34');
/*!40000 ALTER TABLE `likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `noticias`
--

DROP TABLE IF EXISTS `noticias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `noticias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `autor` varchar(150) NOT NULL,
  `id_usuario_creador` int NOT NULL,
  `resumen` text,
  `contenido` longtext NOT NULL,
  `id_categoria` int NOT NULL,
  `id_estado` int NOT NULL DEFAULT '3',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `publicado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_categoria` (`id_categoria`),
  KEY `id_usuario_creador` (`id_usuario_creador`),
  KEY `id_estado` (`id_estado`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `noticias`
--

LOCK TABLES `noticias` WRITE;
/*!40000 ALTER TABLE `noticias` DISABLE KEYS */;
INSERT INTO `noticias` VALUES (24,'LA NOTICIA MANO','Papu',4,'GANO EL MADRID 3-0','GANO EL MADRID! Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#39;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',1,3,'2025-07-21 20:21:23','2025-07-21 20:22:29','2025-07-21 20:22:29'),(23,'Lo que ha pasao','Lolo',12,'Pero que ha pasao','Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#38;#38;#38;#38;#39;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',2,3,'2025-07-21 20:17:20','2025-07-21 20:28:09','2025-07-21 15:28:09'),(22,'La IA','Ana',10,'Asie como la tecnologia eta avanzando','Obselva ete nuevo robos&#13;&#10;Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#39;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',4,3,'2025-07-21 20:14:58','2025-07-21 20:28:03','2025-07-21 15:28:03'),(20,'Pokemon','Shiro',4,'Es pokemon el mejor anime?','Pokemon es el mejor anime claro que si.&#13;&#10;Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#39;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',3,3,'2025-07-21 20:08:40','2025-07-21 21:32:18','2025-07-21 16:32:18'),(25,'Lo mas epiko','Yorsh',4,'WAOS COMO PASO ','Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#39;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',2,3,'2025-07-21 20:24:55','2025-07-21 20:28:14','2025-07-21 15:28:14'),(26,'Este grupo es el god','Shiro',4,'A ve como nos fue','Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#39;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',2,3,'2025-07-21 20:27:26','2025-07-21 20:28:06','2025-07-21 15:28:06'),(27,'INTERNATIONAL LOVE','Pitbul',10,'WAAAAAAAOOOSS','Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&#38;#39;s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',2,3,'2025-07-21 20:34:36','2025-07-21 21:27:25','2025-07-21 21:27:25'),(28,'JavaScript','Ana',4,'Javascript es bueno','si claro que si',4,3,'2025-07-21 21:29:29','2025-07-21 21:29:57','2025-07-21 16:29:57'),(29,'Ultimo momento','Luis',4,'Han asesinado a alguien','Aasesinaron a un inocente',5,3,'2025-07-22 18:21:45','2025-07-22 18:21:53','2025-07-22 13:21:53'),(30,'WAZA','Anah',10,'Ultima informacion del mundo del anime','ha pasado esto esto y aquello',3,3,'2025-07-22 18:23:34','2025-07-22 18:23:47','2025-07-22 13:23:47');
/*!40000 ALTER TABLE `noticias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permisos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (1,'gestionar_usuarios','Puede agregar, editar y ver usuarios'),(2,'crear_noticias','Puede crear noticias'),(3,'modificar_noticias_propias','Puede modificar solo sus noticias'),(4,'modificar_noticias_todas','Puede modificar cualquier noticia'),(5,'publicar_noticias','Puede publicar noticias'),(6,'ver_noticias','Puede ver noticias públicas'),(7,'comentar_noticias','Puede comentar en noticias'),(8,'gestionar_categorias','Puede crear, editar, eliminar y ver categorías'),(9,'reaccionar_noticias','Puede dar reacciones a noticias'),(10,'responder_comentarios','Puede responder comentarios de lectores'),(11,'moderar_comentarios','Puede desactivar comentarios inapropiados');
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','Administrador completo del sistema'),(2,'supervisor','Puede gestionar usuarios y noticias'),(3,'editor','Puede agregar y modificar solo sus propias noticias'),(4,'lector','Puede leer noticias, comentar y reaccionar en el sitio público');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_permisos`
--

DROP TABLE IF EXISTS `roles_permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles_permisos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_rol` int NOT NULL,
  `id_permiso` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_rol` (`id_rol`,`id_permiso`),
  KEY `id_permiso` (`id_permiso`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_permisos`
--

LOCK TABLES `roles_permisos` WRITE;
/*!40000 ALTER TABLE `roles_permisos` DISABLE KEYS */;
INSERT INTO `roles_permisos` VALUES (1,1,1),(2,1,2),(3,1,3),(4,1,4),(5,1,5),(6,1,6),(7,1,8),(8,1,10),(9,2,2),(10,2,4),(11,2,5),(12,2,6),(13,2,11),(14,3,2),(15,3,3),(16,3,6),(17,4,6),(18,4,7),(19,4,9);
/*!40000 ALTER TABLE `roles_permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipos_reaccion`
--

DROP TABLE IF EXISTS `tipos_reaccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_reaccion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `icono` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_reaccion`
--

LOCK TABLES `tipos_reaccion` WRITE;
/*!40000 ALTER TABLE `tipos_reaccion` DISABLE KEYS */;
INSERT INTO `tipos_reaccion` VALUES (1,'me_gusta','👍'),(2,'interesante','⭐'),(3,'verde','🌿');
/*!40000 ALTER TABLE `tipos_reaccion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `id_estado` int NOT NULL DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_rol` int DEFAULT NULL,
  `creado_por` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`),
  KEY `id_estado` (`id_estado`),
  KEY `fk_usuarios_rol` (`id_rol`),
  KEY `creado_por` (`creado_por`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (10,'Patricia','patri@gmail.com','$2y$10$oHFmos5Tac9p25/0dZ08hO2O4xd1at.hfNqX/ryDTXiDDBHU38aZq',1,'2025-07-20 22:38:30',2,4),(4,'Administrador','admin@example.com','$2y$10$Pklf6X1QGPWy35apen5WjOlXbc/Gswjf7PFGiXzGmAy4/hTdZnGGu',1,'2025-07-20 05:33:33',1,NULL),(13,'Patrick','patrik@gmail.com','$2y$10$A7BXit48IX2E/Ln1F2Tty.QJaFj8.C6fnknPCfkqnDdHl4sSuCODy',1,'2025-07-21 14:52:19',4,NULL),(14,'Mau','mau@gmail.com','$2y$10$PgDsERN01VdbcB4kxqqVouRlkvGov8fDB0VznSsbfQN9rfTNDbpjC',1,'2025-07-21 14:52:34',4,NULL),(11,'mario','mario@gmail.com','$2y$10$6.g.Ujit8l6nx0UB6ka4A.yUcFR6s61.gXP.QsOIaXcM5cvDA4NbG',1,'2025-07-20 22:40:00',4,NULL),(12,'Bruno','bruno@gmail.com','$2y$10$RXtDPWEdUCMnDncMb7WskusdFfuBSpvdg2S/gI1LXigyFjvLhzVqi',1,'2025-07-21 08:39:13',3,1),(15,'Irina','irina@gmail.com','$2y$10$/0iga2ixy1hMe7M/m9LJVeUNSGTXVcRUPP8jWvmaPK/PCNQNdde9y',1,'2025-07-21 16:18:19',4,NULL),(16,'Fabio','fabio@gmail.com','$2y$10$Mainui1yS8A7547a5UjR7uRmZfsG59HogHMHsl.zw5dUQmGH1Vsie',1,'2025-07-21 16:23:18',2,4),(17,'Luis','luis@gmail.com','$2y$10$MXlQmJIpLSRfWIbxDtk7m.sFIrAyxMgNY6k..wFL/cxtoP0DjtjcW',1,'2025-07-22 13:15:53',4,NULL),(18,'Luis','luis1@gmail.com','$2y$10$jrHwj5WgbcYj/DXYN8AsqOa6eknDMILRL5GxY2IkDMd6iu8umQyya',1,'2025-07-22 13:19:31',1,4);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `videos`
--

DROP TABLE IF EXISTS `videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `videos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_noticia` int NOT NULL,
  `url_video` varchar(255) NOT NULL,
  `descripcion` text,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_noticia` (`id_noticia`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `videos`
--

LOCK TABLES `videos` WRITE;
/*!40000 ALTER TABLE `videos` DISABLE KEYS */;
/*!40000 ALTER TABLE `videos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitantes`
--

DROP TABLE IF EXISTS `visitantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `visitantes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `fecha` date NOT NULL,
  `visitas` int DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_fecha` (`ip`,`fecha`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitantes`
--

LOCK TABLES `visitantes` WRITE;
/*!40000 ALTER TABLE `visitantes` DISABLE KEYS */;
INSERT INTO `visitantes` VALUES (1,'::1','2025-07-20',105),(2,'::1','2025-07-21',146),(3,'::1','2025-07-22',16);
/*!40000 ALTER TABLE `visitantes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-22  9:41:34
