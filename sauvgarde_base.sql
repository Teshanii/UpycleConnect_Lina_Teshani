mysqldump: [Warning] Using a password on the command line interface can be insecure.
-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: upcycle_connect
-- ------------------------------------------------------
-- Server version	8.0.46

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
-- Table structure for table `annonces`
--

DROP TABLE IF EXISTS `annonces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `annonces` (
  `id_annonce` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(150) NOT NULL,
  `statut_validation` tinyint DEFAULT '0',
  `id_user_auteur` int NOT NULL,
  `description` text,
  `categorie` varchar(50) DEFAULT NULL,
  `type_annonce` varchar(10) DEFAULT 'don',
  `prix` decimal(10,2) DEFAULT '0.00',
  `statut_annonce` varchar(20) DEFAULT 'disponible',
  `motif_refus` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_annonce`),
  KEY `id_user_auteur` (`id_user_auteur`),
  CONSTRAINT `annonces_ibfk_1` FOREIGN KEY (`id_user_auteur`) REFERENCES `utilisateurs` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `annonces`
--

LOCK TABLES `annonces` WRITE;
/*!40000 ALTER TABLE `annonces` DISABLE KEYS */;
INSERT INTO `annonces` VALUES (3,'test2',1,3,'test2','BOIS','don',0.00,'disponible',NULL,'uploads/1783533742_6a4e90ae26a5f.jpg'),(4,'LINA',1,8,'linaaaa ','BOIS','vente',20.00,'recupere',NULL,'uploads/1783537837_6a4ea0ada3b64.jpg');
/*!40000 ALTER TABLE `annonces` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `article_conseil`
--

DROP TABLE IF EXISTS `article_conseil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `article_conseil` (
  `id_article` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `id_auteur` int NOT NULL,
  `statut_validation` tinyint DEFAULT '1',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_article`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article_conseil`
--

LOCK TABLES `article_conseil` WRITE;
/*!40000 ALTER TABLE `article_conseil` DISABLE KEYS */;
/*!40000 ALTER TABLE `article_conseil` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `box`
--

DROP TABLE IF EXISTS `box`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `box` (
  `id_box` int NOT NULL AUTO_INCREMENT,
  `adresse` varchar(255) NOT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `capacite_max` int NOT NULL,
  PRIMARY KEY (`id_box`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `box`
--

LOCK TABLES `box` WRITE;
/*!40000 ALTER TABLE `box` DISABLE KEYS */;
INSERT INTO `box` VALUES (1,'12 rue X','Paris 11',20),(2,'52 avenue laplace','Arcueil',3);
/*!40000 ALTER TABLE `box` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `casiers`
--

DROP TABLE IF EXISTS `casiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `casiers` (
  `id_casier` int NOT NULL AUTO_INCREMENT,
  `numero` varchar(10) NOT NULL,
  `statut` varchar(20) DEFAULT 'libre',
  `id_box` int NOT NULL,
  PRIMARY KEY (`id_casier`),
  KEY `id_box` (`id_box`),
  CONSTRAINT `casiers_ibfk_1` FOREIGN KEY (`id_box`) REFERENCES `box` (`id_box`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `casiers`
--

LOCK TABLES `casiers` WRITE;
/*!40000 ALTER TABLE `casiers` DISABLE KEYS */;
INSERT INTO `casiers` VALUES (1,'A1','libre',2),(2,'A2','libre',2),(3,'A3','libre',2),(4,'A1','libre',1);
/*!40000 ALTER TABLE `casiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id_cat` int NOT NULL AUTO_INCREMENT,
  `code_ref_cat` varchar(50) NOT NULL,
  PRIMARY KEY (`id_cat`),
  UNIQUE KEY `code_ref_cat` (`code_ref_cat`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'BOIS'),(3,'PLASTIQUE'),(2,'TEXTILE');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `demandes_depot`
--

DROP TABLE IF EXISTS `demandes_depot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `demandes_depot` (
  `id_demande` int NOT NULL AUTO_INCREMENT,
  `statut_check` varchar(50) DEFAULT 'en_attente',
  `code_ouverture` varchar(10) DEFAULT NULL,
  `code_barre_scan` varchar(100) DEFAULT NULL,
  `code_artisan` varchar(10) DEFAULT NULL,
  `motif_refus` varchar(255) DEFAULT NULL,
  `id_casier` int DEFAULT NULL,
  `date_demande` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_user` int NOT NULL,
  `id_objet` int NOT NULL,
  `id_box` int NOT NULL,
  `id_artisan` int DEFAULT NULL,
  `id_annonce` int DEFAULT NULL,
  PRIMARY KEY (`id_demande`),
  KEY `id_user` (`id_user`),
  KEY `id_objet` (`id_objet`),
  KEY `id_box` (`id_box`),
  KEY `id_casier` (`id_casier`),
  KEY `id_annonce` (`id_annonce`),
  CONSTRAINT `demandes_depot_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateurs` (`id_user`),
  CONSTRAINT `demandes_depot_ibfk_2` FOREIGN KEY (`id_objet`) REFERENCES `objets` (`id_objet`),
  CONSTRAINT `demandes_depot_ibfk_3` FOREIGN KEY (`id_box`) REFERENCES `box` (`id_box`),
  CONSTRAINT `demandes_depot_ibfk_4` FOREIGN KEY (`id_casier`) REFERENCES `casiers` (`id_casier`),
  CONSTRAINT `demandes_depot_ibfk_5` FOREIGN KEY (`id_annonce`) REFERENCES `annonces` (`id_annonce`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `demandes_depot`
--

LOCK TABLES `demandes_depot` WRITE;
/*!40000 ALTER TABLE `demandes_depot` DISABLE KEYS */;
INSERT INTO `demandes_depot` VALUES (1,'recupere','SQUNS4','1KXCU1YRM6BE','PRU9ID',NULL,1,'2026-07-08 19:12:30',8,1,2,4,4);
/*!40000 ALTER TABLE `demandes_depot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id_document` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_document`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateurs` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `etapes_projet`
--

DROP TABLE IF EXISTS `etapes_projet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etapes_projet` (
  `id_etape` int NOT NULL AUTO_INCREMENT,
  `titre_etape` varchar(150) DEFAULT NULL,
  `description_etape` text,
  `image_etape` varchar(255) DEFAULT NULL,
  `ordre` int DEFAULT NULL,
  `id_projet` int NOT NULL,
  PRIMARY KEY (`id_etape`),
  KEY `id_projet` (`id_projet`),
  CONSTRAINT `etapes_projet_ibfk_1` FOREIGN KEY (`id_projet`) REFERENCES `projets` (`id_projet`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `etapes_projet`
--

LOCK TABLES `etapes_projet` WRITE;
/*!40000 ALTER TABLE `etapes_projet` DISABLE KEYS */;
INSERT INTO `etapes_projet` VALUES (1,'step 1','on va colorier','uploads/1783538890_6a4ea4ca1d6e9.png',1,1);
/*!40000 ALTER TABLE `etapes_projet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evenements`
--

DROP TABLE IF EXISTS `evenements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evenements` (
  `id_event` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(150) DEFAULT NULL,
  `type_event` varchar(50) DEFAULT NULL,
  `lieu` varchar(150) DEFAULT NULL,
  `description` text,
  `date_debut` datetime DEFAULT NULL,
  `date_fin` datetime DEFAULT NULL,
  `prix_actuel` decimal(10,2) NOT NULL,
  `places_max` int NOT NULL,
  `statut_validation` tinyint DEFAULT '0',
  `motif_refus` varchar(255) DEFAULT NULL,
  `id_animateur` int NOT NULL,
  PRIMARY KEY (`id_event`),
  KEY `id_animateur` (`id_animateur`),
  CONSTRAINT `evenements_ibfk_1` FOREIGN KEY (`id_animateur`) REFERENCES `utilisateurs` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evenements`
--

LOCK TABLES `evenements` WRITE;
/*!40000 ALTER TABLE `evenements` DISABLE KEYS */;
INSERT INTO `evenements` VALUES (1,'renovation','Formation','salle b 11 arpifez','ramenez des trucs','2026-07-17 19:43:00','2026-07-18 19:43:00',12.00,10,1,NULL,1);
/*!40000 ALTER TABLE `evenements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inscriptions`
--

DROP TABLE IF EXISTS `inscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inscriptions` (
  `id_inscription` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_event` int NOT NULL,
  `date_inscription` datetime DEFAULT CURRENT_TIMESTAMP,
  `present` tinyint DEFAULT '0',
  PRIMARY KEY (`id_inscription`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inscriptions`
--

LOCK TABLES `inscriptions` WRITE;
/*!40000 ALTER TABLE `inscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `inscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `langues`
--

DROP TABLE IF EXISTS `langues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `langues` (
  `id_langue` int NOT NULL AUTO_INCREMENT,
  `code_iso` varchar(5) NOT NULL,
  `nom_langue` varchar(50) NOT NULL,
  PRIMARY KEY (`id_langue`),
  UNIQUE KEY `code_iso` (`code_iso`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `langues`
--

LOCK TABLES `langues` WRITE;
/*!40000 ALTER TABLE `langues` DISABLE KEYS */;
INSERT INTO `langues` VALUES (1,'fr','Français'),(2,'en','English');
/*!40000 ALTER TABLE `langues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_forums`
--

DROP TABLE IF EXISTS `message_forums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `message_forums` (
  `id_message` int NOT NULL AUTO_INCREMENT,
  `contenu` text NOT NULL,
  `id_user_auteur` int NOT NULL,
  `date_message` datetime DEFAULT CURRENT_TIMESTAMP,
  `est_modere` tinyint DEFAULT '0',
  `categorie` varchar(50) DEFAULT 'Général',
  `epingle` tinyint DEFAULT '0',
  `titre` varchar(255) DEFAULT NULL,
  `id_message_parent` int DEFAULT NULL,
  PRIMARY KEY (`id_message`),
  KEY `id_user_auteur` (`id_user_auteur`),
  CONSTRAINT `message_forums_ibfk_1` FOREIGN KEY (`id_user_auteur`) REFERENCES `utilisateurs` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_forums`
--

LOCK TABLES `message_forums` WRITE;
/*!40000 ALTER TABLE `message_forums` DISABLE KEYS */;
INSERT INTO `message_forums` VALUES (1,'bonjour tout le monde',4,'2026-07-08 17:42:09',0,'',0,'',NULL),(2,'comment all\"ez boud',1,'2026-07-08 17:45:14',0,'Annonces',0,'LINA',NULL),(3,'test',3,'2026-07-08 18:07:16',0,'Annonces',0,'test',NULL);
/*!40000 ALTER TABLE `message_forums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mouvements_portefeuille`
--

DROP TABLE IF EXISTS `mouvements_portefeuille`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mouvements_portefeuille` (
  `id_mouvement` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `type` varchar(30) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date_mouvement` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mouvement`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mouvements_portefeuille`
--

LOCK TABLES `mouvements_portefeuille` WRITE;
/*!40000 ALTER TABLE `mouvements_portefeuille` DISABLE KEYS */;
INSERT INTO `mouvements_portefeuille` VALUES (1,8,18.60,'vente_objet','Vente de « LINA »','2026-07-08 19:15:35'),(2,4,48.50,'vente_prestation','Vente de « linalina »','2026-07-08 19:18:44'),(3,4,0.00,'vente_prestation','Vente de « linalina »','2026-07-08 19:19:16'),(4,4,-48.00,'retrait','Retrait vers votre compte bancaire','2026-07-08 19:21:30');
/*!40000 ALTER TABLE `mouvements_portefeuille` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id_notif` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(150) DEFAULT NULL,
  `message` text,
  `date_envoi` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_notif`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,'Nouvel atelier disponible','L\'atelier \"renovation\" est maintenant ouvert aux inscriptions.','2026-07-08 17:45:53');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objets`
--

DROP TABLE IF EXISTS `objets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `objets` (
  `id_objet` int NOT NULL AUTO_INCREMENT,
  `description` text,
  `etat` varchar(50) DEFAULT NULL,
  `poids_estime` decimal(10,2) DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_cat` int DEFAULT NULL,
  PRIMARY KEY (`id_objet`),
  KEY `id_cat` (`id_cat`),
  CONSTRAINT `objets_ibfk_1` FOREIGN KEY (`id_cat`) REFERENCES `categories` (`id_cat`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objets`
--

LOCK TABLES `objets` WRITE;
/*!40000 ALTER TABLE `objets` DISABLE KEYS */;
INSERT INTO `objets` VALUES (1,'linaaaa ',NULL,NULL,'2026-07-08 19:12:30',NULL);
/*!40000 ALTER TABLE `objets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participants_projet`
--

DROP TABLE IF EXISTS `participants_projet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `participants_projet` (
  `id_participation` int NOT NULL AUTO_INCREMENT,
  `id_projet` int NOT NULL,
  `id_user` int NOT NULL,
  `tache` varchar(255) DEFAULT NULL,
  `statut` varchar(20) DEFAULT 'en_attente',
  `date_demande` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_participation`),
  KEY `id_projet` (`id_projet`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `participants_projet_ibfk_1` FOREIGN KEY (`id_projet`) REFERENCES `projets` (`id_projet`) ON DELETE CASCADE,
  CONSTRAINT `participants_projet_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `utilisateurs` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participants_projet`
--

LOCK TABLES `participants_projet` WRITE;
/*!40000 ALTER TABLE `participants_projet` DISABLE KEYS */;
/*!40000 ALTER TABLE `participants_projet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prestations`
--

DROP TABLE IF EXISTS `prestations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prestations` (
  `id_prestation` int NOT NULL AUTO_INCREMENT,
  `nom_prestation` varchar(150) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `description` text,
  `photo` varchar(255) DEFAULT NULL,
  `id_createur` int DEFAULT NULL,
  `statut_validation` tinyint DEFAULT '0',
  `motif_refus` varchar(255) DEFAULT NULL,
  `vendu` tinyint DEFAULT '0',
  PRIMARY KEY (`id_prestation`),
  KEY `id_createur` (`id_createur`),
  CONSTRAINT `prestations_ibfk_1` FOREIGN KEY (`id_createur`) REFERENCES `utilisateurs` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prestations`
--

LOCK TABLES `prestations` WRITE;
/*!40000 ALTER TABLE `prestations` DISABLE KEYS */;
INSERT INTO `prestations` VALUES (1,'linalina',0.00,'qscdfbgnh','uploads/1783538180_6a4ea20456f4f.png',4,1,NULL,1),(2,'linalina',50.00,'<sdfgh','uploads/1783538245_6a4ea24582574.png',4,1,NULL,1);
/*!40000 ALTER TABLE `prestations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projets`
--

DROP TABLE IF EXISTS `projets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projets` (
  `id_projet` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(150) NOT NULL,
  `description_generale` text,
  `adresse` varchar(255) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `statut` varchar(20) DEFAULT 'en_cours',
  `photo_couverture` varchar(255) DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_debut` datetime DEFAULT NULL,
  `date_fin` datetime DEFAULT NULL,
  `ouvert_participation` tinyint DEFAULT '1',
  `est_sponsorise` tinyint DEFAULT '0',
  `date_fin_sponsoring` datetime DEFAULT NULL,
  `id_createur` int NOT NULL,
  PRIMARY KEY (`id_projet`),
  KEY `id_createur` (`id_createur`),
  CONSTRAINT `projets_ibfk_1` FOREIGN KEY (`id_createur`) REFERENCES `utilisateurs` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projets`
--

LOCK TABLES `projets` WRITE;
/*!40000 ALTER TABLE `projets` DISABLE KEYS */;
INSERT INTO `projets` VALUES (1,'palette','je suis palzette','adresse','paris','en_cours','uploads/1783538860_6a4ea4ace8f33.png','2026-07-08 19:27:41','2026-07-09 21:27:00','2026-07-10 21:27:00',1,0,NULL,4);
/*!40000 ALTER TABLE `projets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recoit_notif`
--

DROP TABLE IF EXISTS `recoit_notif`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recoit_notif` (
  `id_user` int NOT NULL,
  `id_notif` int NOT NULL,
  `est_lue` tinyint DEFAULT '0',
  PRIMARY KEY (`id_user`,`id_notif`),
  KEY `id_notif` (`id_notif`),
  CONSTRAINT `recoit_notif_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateurs` (`id_user`),
  CONSTRAINT `recoit_notif_ibfk_2` FOREIGN KEY (`id_notif`) REFERENCES `notifications` (`id_notif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recoit_notif`
--

LOCK TABLES `recoit_notif` WRITE;
/*!40000 ALTER TABLE `recoit_notif` DISABLE KEYS */;
INSERT INTO `recoit_notif` VALUES (3,1,0),(5,1,0),(7,1,0),(8,1,0);
/*!40000 ALTER TABLE `recoit_notif` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_role` int NOT NULL AUTO_INCREMENT,
  `libelle_role` varchar(50) NOT NULL,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `libelle_role` (`libelle_role`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin'),(4,'Particulier'),(3,'Professionnel et Artisan'),(2,'Salarie');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `traductions`
--

DROP TABLE IF EXISTS `traductions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `traductions` (
  `id_traduction` int NOT NULL AUTO_INCREMENT,
  `cle` varchar(100) NOT NULL,
  `id_langue` int NOT NULL,
  `texte` text NOT NULL,
  PRIMARY KEY (`id_traduction`),
  UNIQUE KEY `cle` (`cle`,`id_langue`),
  KEY `id_langue` (`id_langue`),
  CONSTRAINT `traductions_ibfk_1` FOREIGN KEY (`id_langue`) REFERENCES `langues` (`id_langue`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `traductions`
--

LOCK TABLES `traductions` WRITE;
/*!40000 ALTER TABLE `traductions` DISABLE KEYS */;
/*!40000 ALTER TABLE `traductions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id_transac` int NOT NULL AUTO_INCREMENT,
  `montant` decimal(10,2) NOT NULL,
  `reference_stripe` varchar(255) DEFAULT NULL,
  `statut_paiement` varchar(50) DEFAULT NULL,
  `date_transac` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_user` int DEFAULT NULL,
  `type` varchar(50) DEFAULT 'atelier',
  `commission` decimal(10,2) DEFAULT '0.00',
  `id_event` int DEFAULT NULL,
  PRIMARY KEY (`id_transac`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateurs` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,12.00,'pi_3TqzZxFykYBbOVBh1m9vRpYR','refunded','2026-07-08 17:48:15',8,'atelier',0.00,1),(2,12.00,'pi_3Tqzb1FykYBbOVBh1cBbmbSS','refunded','2026-07-08 17:49:22',8,'atelier',0.00,1),(3,12.00,'pi_3TqzbhFykYBbOVBh0DCIcuPU','refunded','2026-07-08 17:50:02',8,'atelier',0.00,1),(4,12.00,'pi_3Tqze8FykYBbOVBh0Dvh4je5','refunded','2026-07-08 17:52:35',8,'atelier',0.00,1),(5,20.00,'pi_3Tr0wUFykYBbOVBh1KNUDlpO','succeeded','2026-07-08 19:15:35',4,'objet',1.40,NULL),(6,15.00,'pi_3Tr0xaFykYBbOVBh0mTbosjs','refunded','2026-07-08 19:16:51',4,'abonnement',0.00,NULL),(7,50.00,'pi_3Tr0zXFykYBbOVBh08gQQ59i','succeeded','2026-07-08 19:18:44',8,'prestation',1.50,NULL),(8,0.00,NULL,'succeeded','2026-07-08 19:19:16',8,'prestation',0.00,NULL);
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `types_abonnements`
--

DROP TABLE IF EXISTS `types_abonnements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `types_abonnements` (
  `id_type_abo` int NOT NULL AUTO_INCREMENT,
  `nom_offre` varchar(100) NOT NULL,
  `prix_mensuel_actuel` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_type_abo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `types_abonnements`
--

LOCK TABLES `types_abonnements` WRITE;
/*!40000 ALTER TABLE `types_abonnements` DISABLE KEYS */;
INSERT INTO `types_abonnements` VALUES (1,'Gratuit',0.00),(2,'Premium Artisan',29.99);
/*!40000 ALTER TABLE `types_abonnements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilisateurs` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `date_inscription` datetime DEFAULT CURRENT_TIMESTAMP,
  `score_upcycling` int DEFAULT '0',
  `abonnement` varchar(20) DEFAULT 'gratuit',
  `date_fin_abonnement` datetime DEFAULT NULL,
  `abonnement_annule` tinyint DEFAULT '0',
  `solde` decimal(10,2) DEFAULT '0.00',
  `onesignal_player_id` varchar(255) DEFAULT NULL,
  `est_actif` tinyint DEFAULT '1',
  `est_verifie` tinyint DEFAULT '1',
  `token_verification` varchar(64) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `id_role` int NOT NULL,
  `recompense_reclamee` tinyint DEFAULT '0',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`),
  KEY `id_role` (`id_role`),
  CONSTRAINT `utilisateurs_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--

LOCK TABLES `utilisateurs` WRITE;
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
INSERT INTO `utilisateurs` VALUES (1,'Fernando','Teshani','t.fernando@myskolae.fr','$2a$10$KmvKQQnt/Vcf6Sx4O8UuZeKCPKCjj1.g6sJE9qYAE6cF0xb3KU0WO','2026-07-02 12:40:51',0,'gratuit',NULL,0,0.00,'',1,1,NULL,NULL,NULL,2,0),(2,'Admin','Teshani','teshanifernandotf@gmail.com','$2a$10$PstdXoQPif2pvdxkyY2UH.C6F0J.d7533b1WC2iaM4MO108FADTry','2026-07-02 12:47:21',0,'gratuit',NULL,0,0.00,NULL,1,1,NULL,NULL,NULL,1,0),(3,'Particulier','Teshani','teshanifernando@outlook.com','$2a$10$qHw1LiherzotE1UGD/VE4elP13Sx7GQCuS.Okf0WPH5KYYaajskRi','2026-07-02 12:52:30',30,'gratuit',NULL,0,18.60,NULL,1,1,NULL,NULL,NULL,4,0),(4,'Artisan','Teshani','tfernando6@myges.fr','$2a$10$tpAEsxGDLxEMFjv1qavzB.XmeWqQm6Lss5sTnwO/THveq/Cg8OOQK','2026-07-03 05:35:47',10,'gratuit',NULL,0,0.50,'',1,1,NULL,NULL,NULL,3,0),(5,'Demo','Particulier','demo_particulier@upcycle.fr','$2b$10$KzveIpj2xoJ4m3pBGdiMt.uJ3aT1XDWBLUS76UBpbcqe/21rMBU7G','2026-07-04 06:30:09',0,'gratuit',NULL,0,0.00,'',1,1,NULL,NULL,NULL,4,0),(6,'Demo','Admin','demo_admin@upcycle.fr','$2b$10$aKyb2qcA6SzafIgvB1xbOOCz0dIVnq4e0.TPCtzFF1Jstqsl.ywdu','2026-07-04 06:30:11',0,'gratuit',NULL,0,0.00,NULL,1,1,NULL,NULL,NULL,1,0),(7,'Jean','Gabrielle','gabrielle.jean@biomedecine.fr','$2a$10$2OFEVuSEsDt0MiAOaX0GH.QBp0XlYI25s8b7qJPkI0e9S8pEiTpkq','2026-07-06 13:31:57',0,'gratuit',NULL,0,0.00,'',1,1,'67bd41d148171ba52291eee35df45b32a80b7fffd02b0249e1ffb574321007bd',NULL,NULL,4,0),(8,'Chellala','Lina','lenachellala@gmail.com','$2a$10$ePq0l23sxnv54lpfc/q29.piTCoTCNLiHv/Gtan8P5mMIYigQAy86','2026-07-06 18:05:28',10,'gratuit',NULL,0,18.60,'',1,1,'46e1005838506c6884b45634919eb83eec6d5f2fed9cbfe6755e8f17eccb3328',NULL,NULL,4,0),(9,'Demo','Artisan','demo_artisan@upcycle.fr','$2a$10$fisAhy8LdhQfC6x033vnvOUUUrWmuHWZ2ykYp1SOKADwSafic4BMi','2026-07-07 16:01:32',0,'gratuit',NULL,0,0.00,'',1,1,NULL,NULL,NULL,3,0),(10,'Demo','Salarié','demo_salarie@upcycle.fr','$2a$10$FrcpZ1SRy4tO1u3yhO5wJuH6mV3SV5rtFCizEN2PK8/mORa7Ui2Xa','2026-07-07 16:02:32',0,'gratuit',NULL,0,0.00,'',1,1,NULL,NULL,NULL,2,0),(11,'chellala','lina','djrigalealine@gmail.com','$2a$10$evS6lECFon7tdxA5QNaVye2Ay1tmYRltbIIT9uMMCEzSfEtU9apOa','2026-07-08 19:09:35',0,'gratuit',NULL,0,0.00,NULL,1,0,'16d6a8dd6d39bdbcd4dea8314e52d64494d47664e948bd72335f5f796170e6e8',NULL,NULL,3,0);
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-08 20:24:21
