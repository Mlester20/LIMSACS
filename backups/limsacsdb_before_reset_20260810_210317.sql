-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: limsacsdb
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `academic_history`
--

DROP TABLE IF EXISTS `academic_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `academic_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `enrolled_by` int(11) DEFAULT NULL,
  `school_year_id` int(11) NOT NULL,
  `grade_level` varchar(50) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `enrollment_status` enum('Enrolled','Transferred','Graduated','Dropped') DEFAULT 'Enrolled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ah_school_year` (`school_year_id`),
  KEY `fk_ah_section` (`section_id`),
  KEY `fk_ah_student` (`student_id`),
  KEY `fk_academic_history_enrolled_by` (`enrolled_by`),
  CONSTRAINT `fk_academic_history_enrolled_by` FOREIGN KEY (`enrolled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ah_school_year` FOREIGN KEY (`school_year_id`) REFERENCES `school_year` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ah_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ah_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_history`
--

LOCK TABLES `academic_history` WRITE;
/*!40000 ALTER TABLE `academic_history` DISABLE KEYS */;
INSERT INTO `academic_history` VALUES (28,72,3,3,'Grade 1',6,'Enrolled','2026-08-10 12:57:17'),(29,73,3,3,'Grade 1',NULL,'Enrolled','2026-08-10 12:57:17'),(30,74,3,3,'Grade 1',6,'Enrolled','2026-08-10 12:57:17'),(31,75,3,3,'Grade 1',6,'Transferred','2026-08-10 12:57:17'),(32,76,3,3,'Grade 1',6,'Dropped','2026-08-10 12:57:17'),(33,77,3,3,'Grade 6',7,'Graduated','2026-08-10 12:57:17'),(34,78,3,3,'Grade 6',7,'Graduated','2026-08-10 12:57:17');
/*!40000 ALTER TABLE `academic_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(100) NOT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `reference_table` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('success','failed') NOT NULL DEFAULT 'success',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=316 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (4,1,'admin','DELETED USER','USER',NULL,'users','admin Deleted user record','::1','success','2026-06-07 14:08:40'),(5,1,'admin','CREATE USER','USER',NULL,'users','admin created a new user record','::1','success','2026-06-07 14:11:52'),(6,1,'admin','UPDATE USER','USER',NULL,'users','admin updated user record','::1','success','2026-06-07 14:27:55'),(7,1,'admin','DELETED USER','USER',NULL,'users','admin Deleted user record','::1','success','2026-06-07 14:28:00'),(8,3,'registrar','CREATE SECTION','SECTIONS',NULL,'sections','registrar created section: Pine','::1','success','2026-06-08 12:36:01'),(9,3,'registrar','DELETE SECTION','SECTIONS',NULL,'sections','registrar deleted section','::1','success','2026-06-08 12:39:37'),(10,3,'registrar','UPDATE SECTION','SECTIONS',NULL,'sections','registrar updated section: Mahogani','::1','success','2026-06-08 12:41:30'),(12,3,'registrar','UPDATE SECTION','SECTIONS',NULL,'sections','registrar updated section: Mahogani','::1','success','2026-06-08 12:54:07'),(13,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-08 15:31:12'),(14,3,'registrar','DELETE SECTION','SECTIONS',NULL,'sections','Registrar deleted section','::1','success','2026-06-08 15:38:29'),(15,3,'registrar','CREATE SECTION','SECTIONS',NULL,'sections','Registrar created section: Pine','::1','success','2026-06-08 15:38:40'),(16,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-08 15:45:55'),(17,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-08 15:46:16'),(18,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-08 16:02:06'),(19,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-08 16:44:36'),(20,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-09 09:03:43'),(21,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-09 09:52:16'),(22,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-09 13:21:42'),(23,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-09 13:27:43'),(24,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-09 13:28:57'),(25,3,'registrar','CREATE SECTION','SECTIONS',NULL,'sections','Registrar created section: Mahogani','::1','success','2026-06-09 13:29:58'),(26,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 2','::1','success','2026-06-09 13:30:27'),(27,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-09 13:33:28'),(28,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-09 13:34:01'),(29,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-09 13:49:11'),(30,3,'registrar','DELETE SECTION','SECTIONS',NULL,'sections','Registrar deleted section','::1','success','2026-06-09 13:50:28'),(31,3,'registrar','UPDATE SECTION','SECTIONS',NULL,'sections','Registrar updated section: Mahogani','::1','success','2026-06-09 13:51:29'),(32,3,'registrar','DELETE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar deleted a document type with ID: 9.','::1','success','2026-06-09 15:14:14'),(33,3,'registrar','DELETE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar deleted a document type with ID: 1.','::1','success','2026-06-09 15:14:19'),(34,3,'registrar','DELETE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar deleted a document type with ID: 8.','::1','success','2026-06-09 15:14:24'),(35,3,'registrar','DELETE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar deleted a document type with ID: 7.','::1','success','2026-06-09 15:14:53'),(36,3,'registrar','DELETE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar deleted a document type with ID: 6.','::1','success','2026-06-09 15:14:58'),(37,3,'registrar','DELETE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar deleted a document type with ID: 5.','::1','success','2026-06-09 15:15:01'),(38,3,'registrar','DELETE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar deleted a document type with ID: 4.','::1','success','2026-06-09 15:15:05'),(39,3,'registrar','DELETE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar deleted a document type with ID: 3.','::1','success','2026-06-09 15:15:09'),(40,3,'registrar','DELETE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar deleted a document type with ID: 2.','::1','success','2026-06-09 15:15:13'),(41,3,'registrar','CREATE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar created a new document type: Birth Certificate.','::1','success','2026-06-09 15:16:56'),(42,3,'registrar','DELETE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar deleted a document type with ID: 10.','::1','success','2026-06-09 15:17:00'),(43,3,'registrar','DELETE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar deleted a document type with ID: 16.','::1','success','2026-06-09 15:18:54'),(44,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-09 15:27:04'),(45,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-09 15:31:52'),(46,3,'registrar','UPDATE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar updated a document type with ID: 19.','::1','success','2026-06-09 15:34:06'),(47,3,'registrar','DELETE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar deleted a document type with ID: 19.','::1','success','2026-06-09 15:34:26'),(48,3,'registrar','UPDATE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar updated a document type with ID: 18.','::1','success','2026-06-09 15:35:27'),(49,3,'registrar','DELETE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar deleted a document type with ID: 18.','::1','success','2026-06-09 15:35:59'),(50,3,'registrar','UPDATE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar updated a document type with ID: 17.','::1','success','2026-06-09 15:36:59'),(51,3,'registrar','UPDATE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar updated a document type with ID: 15.','::1','success','2026-06-09 15:37:05'),(52,3,'registrar','UPDATE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar updated a document type with ID: 14.','::1','success','2026-06-09 15:37:11'),(53,3,'registrar','UPDATE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar updated a document type with ID: 13.','::1','success','2026-06-09 15:37:18'),(54,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-10 12:58:29'),(55,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-10 14:13:26'),(56,3,'registrar','DELETE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar deleted parent/guardian record with ID: 1','::1','success','2026-06-10 15:22:42'),(57,3,'registrar','DELETE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar deleted parent/guardian record with ID: 2','::1','success','2026-06-10 15:25:48'),(58,3,'registrar','DELETE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar deleted parent/guardian record with ID: 3','::1','success','2026-06-10 16:14:17'),(59,3,'registrar','CREATE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar created a new parent/guardian record for student ID: 11','::1','success','2026-06-10 16:28:52'),(60,3,'registrar','UPDATE PARENT/GUARDIAN','PARENTS_GUARDIANS',4,'parents_guardians','Registrar updated parent/guardian record with ID: 4','::1','success','2026-06-10 16:51:50'),(61,3,'registrar','DELETE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar deleted parent/guardian record with ID: 4','::1','success','2026-06-10 16:51:59'),(62,3,'registrar','CREATE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar created a new parent/guardian record for student ID: 11','::1','success','2026-06-10 16:58:46'),(63,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 2','::1','success','2026-06-10 16:59:30'),(64,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-11 07:42:23'),(65,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-12 08:54:57'),(66,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 2','::1','success','2026-06-12 08:57:02'),(67,3,'registrar','CREATE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar created a new parent/guardian record for student ID: 11','::1','success','2026-06-12 08:57:21'),(68,3,'registrar','UPDATE PARENT/GUARDIAN','PARENTS_GUARDIANS',5,'parents_guardians','Registrar updated parent/guardian record with ID: 5','::1','success','2026-06-12 08:57:30'),(69,3,'registrar','UPDATE PARENT/GUARDIAN','PARENTS_GUARDIANS',5,'parents_guardians','Registrar updated parent/guardian record with ID: 5','::1','success','2026-06-12 08:57:58'),(70,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-12 09:08:28'),(71,3,'registrar','DELETE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar deleted parent/guardian record with ID: 6','::1','success','2026-06-12 09:13:33'),(72,3,'registrar','DELETE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar deleted parent/guardian record with ID: 5','::1','success','2026-06-12 09:13:41'),(73,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-12 11:45:13'),(74,3,'registrar','UPDATE SECTION','SECTIONS',NULL,'sections','Registrar updated section: Mahogani','::1','success','2026-06-12 12:51:11'),(75,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:20:58'),(76,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:32:54'),(77,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:33:15'),(78,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:33:41'),(79,3,'registrar','CREATE SECTION','SECTIONS',NULL,'sections','Registrar created section: Pine','::1','success','2026-06-12 13:34:47'),(80,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:36:49'),(81,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:47:42'),(82,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:47:59'),(83,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:48:14'),(84,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:48:26'),(85,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:51:46'),(86,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:52:26'),(87,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:53:22'),(88,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:54:16'),(89,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:54:32'),(90,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:55:00'),(91,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:55:19'),(92,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 13:56:33'),(93,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:00:02'),(94,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:01:54'),(95,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:02:34'),(96,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:03:11'),(97,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:04:05'),(98,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:05:48'),(99,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:07:53'),(100,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:11:03'),(101,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:12:33'),(102,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:13:24'),(103,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:14:33'),(104,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:16:07'),(105,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:17:37'),(106,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:18:45'),(107,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:20:48'),(108,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-12 14:23:38'),(109,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-14 14:46:36'),(110,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-14 15:01:25'),(111,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-14 15:03:32'),(112,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-14 15:04:35'),(113,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-14 15:06:31'),(114,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-14 15:11:51'),(115,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-14 15:34:53'),(116,3,'registrar','ADD STUDENT','STUDENT',NULL,'students','Registrar added student record','::1','success','2026-06-14 15:47:51'),(117,3,'registrar','CREATE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar created a new parent/guardian record for student ID: 11','::1','success','2026-06-14 16:03:17'),(118,3,'registrar','UPDATE PARENT/GUARDIAN','PARENTS_GUARDIANS',7,'parents_guardians','Registrar updated parent/guardian record with ID: 7','::1','success','2026-06-14 16:18:47'),(119,3,'registrar','CREATE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar created a new parent/guardian record for student ID: 11','::1','success','2026-06-14 16:19:19'),(120,3,'registrar','DELETE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar deleted parent/guardian record with ID: 8','::1','success','2026-06-14 16:19:47'),(121,3,'registrar','DELETE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar deleted parent/guardian record with ID: 7','::1','success','2026-06-14 16:19:51'),(122,3,'registrar','LOGIN','AUTH',NULL,NULL,'Pogi si Lester logged in','::1','success','2026-06-14 16:30:40'),(123,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-14 16:39:59'),(124,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-15 06:30:31'),(125,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-16 05:51:21'),(126,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-16 11:53:30'),(127,3,'registrar','UPLOAD DOCUMENT','STUDENT_DOCUMENTS',NULL,'student_documents','Document uploaded for student ID 13','::1','success','2026-06-16 12:49:47'),(128,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-16 13:35:40'),(129,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 12','::1','success','2026-06-16 14:10:28'),(130,3,'registrar','DELETE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar deleted a student document with ID: 2','::1','success','2026-06-16 14:20:55'),(131,3,'registrar','DELETE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar deleted a student document with ID: 1','::1','success','2026-06-16 14:20:58'),(132,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 11','::1','success','2026-06-16 14:22:09'),(133,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 11','::1','success','2026-06-16 14:23:24'),(134,3,'registrar','DELETE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar deleted a student document with ID: 4','::1','success','2026-06-16 14:23:29'),(135,3,'registrar','UPDATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar updated student document with ID: 3','::1','success','2026-06-16 14:24:23'),(136,3,'registrar','UPDATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar updated student document with ID: 3','::1','success','2026-06-16 14:29:10'),(137,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-16 14:31:53'),(138,3,'registrar','ADD STUDENT','STUDENT',NULL,'students','Registrar added student record','::1','success','2026-06-16 14:33:24'),(139,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-16 14:33:50'),(140,3,'registrar','DELETE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar deleted a student document with ID: 3','::1','success','2026-06-16 15:04:48'),(141,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 12','::1','success','2026-06-16 15:05:25'),(142,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 44','::1','success','2026-06-16 15:10:16'),(143,3,'registrar','UPDATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar updated student document with ID: 5','::1','success','2026-06-16 15:14:42'),(144,3,'registrar','DELETE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar deleted a student document with ID: 6','::1','success','2026-06-16 15:22:05'),(145,3,'registrar','DELETE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar deleted a student document with ID: 5','::1','success','2026-06-16 15:22:08'),(146,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 43','::1','success','2026-06-16 15:23:08'),(147,3,'registrar','UPDATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar updated student document with ID: 1','::1','success','2026-06-16 15:24:19'),(148,3,'registrar','DELETE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar deleted a student document with ID: 1','::1','success','2026-06-16 15:25:32'),(149,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-16 15:26:04'),(150,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 11','::1','success','2026-06-16 15:26:31'),(151,3,'registrar','UPDATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar updated student document with ID: 2','::1','success','2026-06-16 15:31:43'),(152,3,'registrar','DELETE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar deleted a student document with ID: 2','::1','success','2026-06-16 15:35:30'),(153,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 22','::1','success','2026-06-16 15:35:51'),(154,3,'registrar','UPDATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar updated student document with ID: 3','::1','success','2026-06-16 15:36:15'),(155,3,'registrar','UPDATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar updated student document with ID: 3','::1','success','2026-06-16 15:36:42'),(156,3,'registrar','DELETE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar deleted a student document with ID: 3','::1','success','2026-06-16 15:36:50'),(157,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-17 06:37:43'),(158,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-17 06:38:04'),(159,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-17 06:42:59'),(160,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-17 06:44:10'),(161,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-17 06:49:24'),(162,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-17 06:51:37'),(163,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 17','::1','success','2026-06-17 07:01:42'),(164,3,'registrar','UPDATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar updated student document with ID: 4','::1','success','2026-06-17 07:02:26'),(165,3,'registrar','DELETE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar deleted a student document with ID: 4','::1','success','2026-06-17 07:03:01'),(166,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-17 13:32:49'),(167,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 12','::1','success','2026-06-17 13:52:30'),(168,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 11','::1','success','2026-06-17 13:53:27'),(169,3,'registrar','UPDATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar updated student document with ID: 5','::1','success','2026-06-17 13:53:44'),(170,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 11','::1','success','2026-06-17 13:56:52'),(171,3,'registrar','DELETE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar deleted a student document with ID: 5','::1','success','2026-06-17 14:04:18'),(172,3,'registrar','DELETE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar deleted a student document with ID: 6','::1','success','2026-06-17 14:04:21'),(173,3,'registrar','DELETE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar deleted a student document with ID: 7','::1','success','2026-06-17 14:04:24'),(174,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-17 14:08:22'),(175,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-18 04:21:18'),(176,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-18 05:50:32'),(177,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-18 05:51:15'),(178,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-18 06:55:27'),(179,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-18 06:55:56'),(180,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-18 06:58:11'),(181,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 11','::1','success','2026-06-18 07:00:01'),(182,3,'registrar','UPDATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar updated student document with ID: 8','::1','success','2026-06-18 07:00:47'),(183,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-18 07:23:14'),(184,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-18 07:24:39'),(185,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-18 07:25:38'),(186,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-18 13:49:25'),(187,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-18 14:12:20'),(188,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-18 14:36:12'),(189,3,'registrar','UPDATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar updated student document with ID: 8','::1','success','2026-06-18 14:38:30'),(190,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-18 14:40:55'),(191,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-18 15:12:27'),(192,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-18 15:20:43'),(193,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-19 05:14:28'),(194,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-19 05:59:25'),(195,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 33','::1','success','2026-06-19 06:00:06'),(196,3,'registrar','UPDATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar updated student document with ID: 9','::1','success','2026-06-19 06:00:47'),(197,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-19 06:10:04'),(198,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-19 06:35:44'),(199,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-19 06:46:07'),(200,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-19 06:46:55'),(201,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-19 06:55:54'),(202,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-19 07:06:05'),(203,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-19 11:57:54'),(204,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-19 12:17:03'),(205,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-19 12:56:05'),(206,3,'registrar','DROP STUDENT','ENROLLMENT',3,'academic_history','Marked Mark Lester  Raguindin as Dropped','::1','success','2026-06-19 13:03:26'),(207,3,'registrar','GRADUATE STUDENT','ENROLLMENT',2,'academic_history','Marked Armando Raguindin as Graduated','::1','success','2026-06-19 13:14:40'),(208,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-19 13:28:34'),(209,3,'registrar','UPDATE SECTION','SECTIONS',NULL,'sections','Registrar updated section: Mahogani','::1','success','2026-06-19 13:30:31'),(210,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 6','::1','success','2026-06-19 13:31:20'),(211,3,'registrar','GRADUATE STUDENT','ENROLLMENT',5,'academic_history','Marked Mark Lester  Raguindin as Graduated','::1','success','2026-06-19 13:32:39'),(212,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-19 13:53:38'),(213,3,'registrar','DELETE SECTION','SECTIONS',NULL,'sections','Registrar deleted section','::1','success','2026-06-19 14:00:33'),(214,3,'registrar','DELETE SECTION','SECTIONS',NULL,'sections','Registrar deleted section','::1','success','2026-06-19 14:00:35'),(215,3,'registrar','CREATE SECTION','SECTIONS',NULL,'sections','Registrar created section: Pine','::1','success','2026-06-19 14:23:34'),(216,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-19 14:23:54'),(217,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-21 01:52:40'),(218,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-21 02:14:53'),(219,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-21 02:16:03'),(220,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-21 12:30:25'),(221,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-21 12:30:51'),(222,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-21 12:46:41'),(223,3,'registrar','CREATE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar created a new document type: Test.','::1','success','2026-06-21 12:47:07'),(224,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-21 12:47:28'),(225,3,'registrar','DELETE DOCUMENT TYPE','DOCUMENT_TYPE',NULL,'document_types','Registrar deleted a document type with ID: 20.','::1','success','2026-06-21 12:49:22'),(226,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-21 12:59:02'),(227,3,'registrar','CREATE SECTION','SECTIONS',NULL,'sections','Registrar created section: Mahogani','::1','success','2026-06-21 12:59:42'),(228,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 6','::1','success','2026-06-21 13:00:35'),(229,3,'registrar','GRADUATE STUDENT','ENROLLMENT',9,'academic_history','Marked Maria Flores as Graduated','::1','success','2026-06-21 13:00:57'),(230,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-21 13:04:53'),(231,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-21 13:05:33'),(232,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-21 13:39:29'),(233,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-21 14:09:55'),(234,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 34','::1','success','2026-06-21 14:12:20'),(235,3,'registrar','UPDATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar updated student document with ID: 10','::1','success','2026-06-21 14:12:39'),(236,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-21 14:51:39'),(237,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-21 15:01:13'),(238,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-21 15:11:08'),(239,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-21 15:28:02'),(240,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-21 15:30:29'),(241,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-22 09:00:22'),(242,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-22 09:01:29'),(243,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-22 09:22:25'),(244,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-22 09:25:56'),(245,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-22 09:42:19'),(246,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-22 11:16:39'),(247,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-22 11:20:49'),(248,7,'teacher','LOGIN','AUTH',NULL,NULL,'John Doe logged in','::1','success','2026-06-23 09:40:50'),(249,8,'teacher','LOGIN','AUTH',NULL,NULL,'Mark Lester logged in','::1','success','2026-06-23 09:41:29'),(250,8,'teacher','LOGIN','AUTH',NULL,NULL,'Mark Lester logged in','::1','success','2026-06-23 09:42:24'),(251,8,'teacher','LOGIN','AUTH',NULL,NULL,'Mark Lester logged in','::1','success','2026-06-23 09:42:49'),(252,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-23 09:43:20'),(253,1,'admin','CREATE USER','USER',NULL,'users','admin created a new user record','::1','success','2026-06-23 09:43:45'),(254,9,'teacher','LOGIN','AUTH',NULL,NULL,'teacher 1 logged in','::1','success','2026-06-23 09:44:04'),(255,9,'teacher','LOGIN','AUTH',NULL,NULL,'teacher 1 logged in','::1','success','2026-06-23 09:44:29'),(256,8,'teacher','LOGIN','AUTH',NULL,NULL,'Mark Lester logged in','::1','success','2026-06-23 09:51:49'),(257,7,'teacher','LOGIN','AUTH',NULL,NULL,'John Doe logged in','::1','success','2026-06-23 10:17:10'),(258,8,'teacher','LOGIN','AUTH',NULL,NULL,'Mark Lester logged in','::1','success','2026-06-23 12:07:43'),(259,7,'teacher','LOGIN','AUTH',NULL,NULL,'John Doe logged in','::1','success','2026-06-23 12:08:24'),(260,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-23 12:12:55'),(261,7,'teacher','LOGIN','AUTH',NULL,NULL,'John Doe logged in','::1','success','2026-06-23 12:25:25'),(262,7,'teacher','CREATE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','John Doe created a new parent/guardian record for student ID: 19','::1','success','2026-06-23 12:44:45'),(263,7,'teacher','LOGIN','AUTH',NULL,NULL,'John Doe logged in','::1','success','2026-06-23 13:07:21'),(264,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-23 13:16:44'),(265,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-23 13:17:04'),(266,3,'registrar','CREATE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar created a new parent/guardian record for student ID: 14','::1','success','2026-06-23 13:17:36'),(267,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-25 05:22:04'),(268,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-25 05:23:37'),(269,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-25 05:27:09'),(270,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-25 06:23:38'),(271,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-25 06:32:39'),(272,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-25 06:33:22'),(273,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-25 06:38:30'),(274,3,'registrar','DROP STUDENT','ENROLLMENT',11,'academic_history','Marked Princess Villanueva as Dropped','::1','success','2026-06-25 06:39:13'),(275,3,'registrar','DROP STUDENT','ENROLLMENT',10,'academic_history','Marked Joshua Torres as Dropped','::1','success','2026-06-25 06:39:18'),(276,3,'registrar','DROP STUDENT','ENROLLMENT',12,'academic_history','Marked Michael Pascual as Dropped','::1','success','2026-06-25 06:39:21'),(277,3,'registrar','DROP STUDENT','ENROLLMENT',8,'academic_history','Marked Mark Lester  Raguindin as Dropped','::1','success','2026-06-25 06:39:25'),(278,7,'teacher','LOGIN','AUTH',NULL,NULL,'John Doe logged in','::1','success','2026-06-25 06:40:53'),(279,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-06-25 06:46:31'),(280,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-06-25 06:49:14'),(281,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-25 06:50:55'),(282,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-06-25 06:51:10'),(283,3,'registrar','DROP STUDENT','ENROLLMENT',13,'academic_history','Marked Armando Raguindin as Dropped','::1','success','2026-06-25 06:55:37'),(284,3,'registrar','DROP STUDENT','ENROLLMENT',14,'academic_history','Marked Mark Lester  Raguindin as Dropped','::1','success','2026-06-25 06:55:39'),(285,3,'registrar','ADD STUDENT','STUDENT',NULL,'students','Registrar added student record','::1','success','2026-06-25 06:58:03'),(286,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 6','::1','success','2026-06-25 06:58:42'),(287,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-07-03 02:48:54'),(288,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-07-03 02:49:05'),(289,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-07-03 02:49:35'),(290,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-07-04 06:38:26'),(291,3,'registrar','GRADUATE STUDENT','ENROLLMENT',15,'academic_history','Marked adasd fasds as Graduated','::1','success','2026-07-04 06:42:47'),(292,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-07-06 15:39:23'),(293,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-07-11 15:42:43'),(294,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 1','::1','success','2026-07-11 15:48:44'),(295,3,'registrar','CREATE PARENT/GUARDIAN','PARENTS_GUARDIANS',NULL,'parents_guardians','Registrar created a new parent/guardian record for student ID: 23','::1','success','2026-07-11 15:50:07'),(296,3,'registrar','CREATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar created a new student document for student ID: 23','::1','success','2026-07-11 15:51:56'),(297,3,'registrar','UPDATE DOCUMENT','STUDENTS_DOCUMENTS',NULL,'students_documents','Registrar updated student document with ID: 11','::1','success','2026-07-11 15:52:03'),(298,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-07-11 15:55:22'),(299,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-07-12 06:04:07'),(300,7,'teacher','LOGIN','AUTH',NULL,NULL,'John Doe logged in','::1','success','2026-07-12 06:41:52'),(301,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-07-12 06:42:00'),(302,3,'registrar','ENROLL STUDENT','ENROLLMENT',NULL,'academic_history','Student enrolled in Grade 6','::1','success','2026-07-12 06:43:37'),(303,3,'registrar','GRADUATE STUDENT','ENROLLMENT',17,'academic_history','Marked Hannah Ramos as Graduated','::1','success','2026-07-12 06:43:47'),(304,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-08-05 07:29:54'),(305,1,'admin','LOGIN','AUTH',NULL,NULL,'admin logged in','::1','success','2026-08-06 12:25:23'),(306,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-08-06 12:42:26'),(307,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-08-06 12:59:14'),(309,3,'registrar','IMPORT STUDENTS','STUDENTS',NULL,'students','1 inserted, 0 skipped, 0 failed','::1','success','2026-08-06 13:05:21'),(310,3,'registrar','DELETE STUDENT','STUDENT',52,'students','Registrar deleted student record with ID: 52','::1','success','2026-08-06 13:05:35'),(311,7,'teacher','LOGIN','AUTH',NULL,NULL,'John Doe logged in','::1','success','2026-08-06 13:30:10'),(312,3,'registrar','LOGIN','AUTH',NULL,NULL,'Registrar logged in','::1','success','2026-08-10 12:02:45'),(315,3,'registrar','IMPORT STUDENTS','STUDENTS',NULL,'students','10 students inserted, 0 skipped, 0 failed; 3 parent/guardian records added; 7 academic history records added (0 skipped); 2 graduate records added (0 skipped)','::1','success','2026-08-10 12:57:17');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_types`
--

DROP TABLE IF EXISTS `document_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_name` varchar(100) NOT NULL,
  `is_required` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_types`
--

LOCK TABLES `document_types` WRITE;
/*!40000 ALTER TABLE `document_types` DISABLE KEYS */;
INSERT INTO `document_types` VALUES (11,'Birth Certificate',1,1),(12,'Report Card',1,1),(13,'Form 137',1,1),(14,'Good Moral Certificate',1,1),(15,'Medical Certificate',1,1),(17,'Certificate of Completion',1,1);
/*!40000 ALTER TABLE `document_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `graduates`
--

DROP TABLE IF EXISTS `graduates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `graduates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `academic_history_id` int(11) NOT NULL,
  `graduation_date` date NOT NULL,
  `honors` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_academic_history_id` (`academic_history_id`),
  KEY `student_id` (`student_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `graduates_academic_history_id_fk` FOREIGN KEY (`academic_history_id`) REFERENCES `academic_history` (`id`) ON DELETE CASCADE,
  CONSTRAINT `graduates_recorded_by_fk` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `graduates_student_id_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `graduates`
--

LOCK TABLES `graduates` WRITE;
/*!40000 ALTER TABLE `graduates` DISABLE KEYS */;
INSERT INTO `graduates` VALUES (5,77,33,'2027-04-01','With Honors','Sample graduated record',3,'2026-08-10 12:57:17'),(6,78,34,'2027-04-01','With High Honors',NULL,3,'2026-08-10 12:57:17');
/*!40000 ALTER TABLE `graduates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parents_guardians`
--

DROP TABLE IF EXISTS `parents_guardians`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parents_guardians` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `father_name` varchar(150) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `father_contact` varchar(20) DEFAULT NULL,
  `mother_name` varchar(150) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL,
  `mother_contact` varchar(20) DEFAULT NULL,
  `guardian_name` varchar(150) DEFAULT NULL,
  `guardian_relationship` varchar(50) DEFAULT NULL,
  `guardian_contact` varchar(20) DEFAULT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `parents_guardians_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parents_guardians`
--

LOCK TABLES `parents_guardians` WRITE;
/*!40000 ALTER TABLE `parents_guardians` DISABLE KEYS */;
INSERT INTO `parents_guardians` VALUES (9,19,'Armando Raguindin','Tricycle Driver','09360991034','Melba Raguindin','Baby Sitter','','Melba Raguindin','Mother','','2026-06-23'),(10,14,'test','test','test','test','test','test','test','test','test','2026-06-23'),(11,23,'Juan Dela-Cruz','Farmer','','Jannah Dela Cruz','Teacher','','Jannah Dela-Cruz','Mother','','2026-07-11'),(14,71,'Pedro Garcia','Driver','09181234563','Ana Garcia','Vendor','09191234563',NULL,NULL,NULL,'2026-08-10'),(15,74,'Ramon Aquino',NULL,'09201234566','Liza Aquino',NULL,'09211234566',NULL,NULL,NULL,'2026-08-10'),(16,78,'Carlos Lopez',NULL,'09221234570','Grace Lopez',NULL,'09231234570',NULL,NULL,NULL,'2026-08-10');
/*!40000 ALTER TABLE `parents_guardians` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_year`
--

DROP TABLE IF EXISTS `school_year`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `school_year` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_year` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','inactive','archived') DEFAULT 'inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_year`
--

LOCK TABLES `school_year` WRITE;
/*!40000 ALTER TABLE `school_year` DISABLE KEYS */;
INSERT INTO `school_year` VALUES (3,'2026-2027','2026-06-08','2027-04-05','active','2026-06-06 15:01:38','2026-06-09 13:33:49'),(6,'2027-2028','2027-06-07','2028-04-03','archived','2026-06-09 13:28:47','2026-06-21 13:05:10'),(7,'2028-2029','2028-06-05','2029-04-09','archived','2026-06-21 12:33:31','2026-06-21 13:05:15');
/*!40000 ALTER TABLE `school_year` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_name` varchar(100) NOT NULL,
  `grade_level` varchar(50) DEFAULT NULL,
  `adviser_id` int(11) DEFAULT NULL,
  `school_year_id` int(11) DEFAULT NULL,
  `max_students` int(11) NOT NULL DEFAULT 35,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sections_ibfk_1` (`adviser_id`),
  KEY `sections_ibfk_2` (`school_year_id`),
  CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`adviser_id`) REFERENCES `users` (`id`),
  CONSTRAINT `sections_ibfk_2` FOREIGN KEY (`school_year_id`) REFERENCES `school_year` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sections`
--

LOCK TABLES `sections` WRITE;
/*!40000 ALTER TABLE `sections` DISABLE KEYS */;
INSERT INTO `sections` VALUES (6,'Pine','Grade 1',7,3,35,'2026-06-19 14:23:34'),(7,'Mahogani','Grade 6',8,3,35,'2026-06-21 12:59:42');
/*!40000 ALTER TABLE `sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_documents`
--

DROP TABLE IF EXISTS `student_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `status` enum('Pending','Submitted','Verified','Rejected') DEFAULT 'Submitted',
  `remarks` text DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `document_type_id` (`document_type_id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `student_documents_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `student_documents_ibfk_2` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`),
  CONSTRAINT `student_documents_ibfk_3` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_documents`
--

LOCK TABLES `student_documents` WRITE;
/*!40000 ALTER TABLE `student_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lrn` varchar(20) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `place_of_birth` varchar(150) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `lrn` (`lrn`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (11,'20242110365','Mark Lester ','Suguitan','Raguindin','','Male','2002-12-20',23,'Ilagan City, Isabela','Filipino','Roman Catholic','Rizal, Roxas, Isabela','','2026-06-09 15:49:56'),(12,'20242111365','Armando','Suguitan','Raguindin','Jr','Male','2004-10-14',22,'Ilagan City, Isabela','Filipino','','Rizal, Roxas, Isabela','','2026-06-12 08:56:37'),(13,'102345678906','Angelo','Mercado','Aquino',NULL,'Male','2008-11-04',17,'Roxas','Filipino','Roman Catholic','Zone 1, Brgy. San Jose, Roxas, Cagayan','09152223344','2026-06-12 13:05:21'),(14,'102345678907','Princess','Mae','Villanueva',NULL,'Female','2009-04-12',17,'Tuguegarao','Filipino','Roman Catholic','Blk 3 Lot 5, Stella Subdivision, Roxas, Cagayan','09278889900','2026-06-12 13:05:21'),(15,'102345678908','Christian','Bautista','Reyes',NULL,'Male','2007-09-25',18,'Manila','Filipino','Christian','14 Mabini St, Brgy. Centro, Roxas, Cagayan','09061112233','2026-06-12 13:05:21'),(16,'102345678909','Samantha','Gomez','Santos',NULL,'Female','2008-07-19',17,'Ilagan','Filipino','Roman Catholic','Zone 3, Brgy. Muñoz, Roxas, Cagayan','09174445566','2026-06-12 13:05:21'),(17,'102345678910','Alexander','Lim','Chua','III','Male','2008-02-02',18,'Quezon City','Filipino','Christian','88 Lotus St, Brgy. New Vargas, Roxas, Cagayan','09193334455','2026-06-12 13:05:21'),(18,'102345678911','Chloe','Nicole','Dizon',NULL,'Female','2009-10-14',16,'Roxas','Filipino','Iglesia ni Cristo','Zone 5, Brgy. Bantug, Roxas, Cagayan','09367778899','2026-06-12 13:05:21'),(19,'102345678912','Joshua','Gabriel','Torres',NULL,'Male','2008-06-30',17,'Santiago','Filipino','Roman Catholic','22 Luna St, Brgy. Central, Roxas, Cagayan','09985554433','2026-06-12 13:05:21'),(20,'102345678913','Beatriz','Amor','Castillo',NULL,'Female','2007-11-11',18,'Tuguegarao','Filipino','Roman Catholic','Zone 2, Brgy. San Jone, Roxas, Cagayan','09452221100','2026-06-12 13:05:21'),(21,'102345678914','Gabriel','Jose','Mendoza',NULL,'Male','2009-03-08',17,'Roxas','Filipino','Aglipayan','Zone 6, Brgy. Matusalem, Roxas, Cagayan','09263337788','2026-06-12 13:05:21'),(22,'102345678915','Hannah','Sophia','Ramos',NULL,'Female','2008-12-21',17,'Cauayan','Filipino','Born Again Christian','19 Del Pilar St, Brgy. Centro, Roxas, Cagayan','09774441122','2026-06-12 13:05:21'),(23,'102345678916','Nathaniel','David','Castro',NULL,'Male','2008-01-17',18,'Manila','Filipino','Roman Catholic','Zone 1, Brgy. Quibal, Peñablanca, Cagayan','09178883344','2026-06-12 13:05:21'),(24,'102345678917','Sofia','Isabella','Fernandez',NULL,'Female','2009-07-04',16,'Tuguegarao','Filipino','Roman Catholic','45 Caritan Norte, Tuguegarao City, Cagayan','09184445533','2026-06-12 13:05:21'),(25,'102345678918','Ethan','Jacob','Soriano',NULL,'Male','2007-10-29',18,'Aparri','Filipino','Methodist','Poblacion, Aparri, Cagayan','09226667788','2026-06-12 13:05:21'),(26,'102345678919','Althea','Rose','Guzman',NULL,'Female','2008-05-23',18,'Roxas','Filipino','Roman Catholic','Zone 4, Brgy. San Quirino, Roxas, Cagayan','09351119900','2026-06-12 13:05:21'),(27,'102345678920','Michael','Kevin','Pascual',NULL,'Male','2009-02-11',17,'Lal-lo','Filipino','Iglesia ni Cristo','Brgy. Bagumbayan, Lal-lo, Cagayan','09054443322','2026-06-12 13:05:21'),(28,'102345678921','Camila','Jane','Valdez',NULL,'Female','2008-08-08',17,'Tuguegarao','Filipino','Roman Catholic','Zone 2, Brgy. Carig Sur, Tuguegarao City, Cagayan','09167772211','2026-06-12 13:05:21'),(29,'102345678922','Daniel','Luis','Bermudez',NULL,'Male','2007-12-15',18,'Roxas','Filipino','Roman Catholic','Zone 3, Brgy. Marcos, Roxas, Cagayan','09991114455','2026-06-12 13:05:21'),(30,'102345678923','Janine','Marie','Salamat',NULL,'Female','2009-05-19',17,'Ilagan','Filipino','Christian','Zone 1, Brgy. Sotero, Roxas, Cagayan','09473332211','2026-06-12 13:05:21'),(31,'102345678924','Elijah','Paul','Corpuz',NULL,'Male','2008-04-03',18,'Tuguegarao','Filipino','Aglipayan','Zone 7, Brgy. Pengue Ruyu, Tuguegarao City, Cagayan','09362228833','2026-06-12 13:05:21'),(32,'102345678925','Alyssa','Faith','Domingo',NULL,'Female','2008-09-12',17,'Roxas','Filipino','Roman Catholic','Zone 2, Brgy. Vira, Roxas, Cagayan','09154449988','2026-06-12 13:05:21'),(33,'102345678926','Justin','Mark','Santiago',NULL,'Male','2009-11-20',16,'Manila','Filipino','Roman Catholic','12 Bonifacio St, Brgy. Centro, Roxas, Cagayan','09273334411','2026-06-12 13:05:21'),(34,'102345678927','Maria','Theresa','Flores',NULL,'Female','2008-03-14',18,'Tuguegarao','Filipino','Roman Catholic','Zone 4, Brgy. Cataggaman Pardo, Tuguegarao City, Cagayan','09192225588','2026-06-12 13:05:21'),(35,'102345678928','Matthew','James','Salvador',NULL,'Male','2007-08-27',18,'Roxas','Filipino','Jehovah\'s Witness','Zone 5, Brgy. Doña Concha, Roxas, Cagayan','09063334499','2026-06-12 13:05:21'),(36,'102345678929','Samantha','Joy','Pineda',NULL,'Female','2009-01-05',17,'Santiago','Filipino','Christian','Zone 1, Brgy. Simimba, Roxas, Cagayan','09172229900','2026-06-12 13:05:21'),(37,'102345678930','Kyle','Andrew','Gatbonton',NULL,'Male','2008-10-10',17,'Quezon City','Filipino','Roman Catholic','Zone 3, Brgy. Imelda, Roxas, Cagayan','09984441122','2026-06-12 13:05:21'),(38,'102345678931','Angelica','Mae','De Leon',NULL,'Female','2008-06-17',17,'Roxas','Filipino','Roman Catholic','Zone 2, Brgy. Lucban, Roxas, Cagayan','09356662233','2026-06-12 13:05:21'),(39,'102345678932','Timothy','John','Villafuerte',NULL,'Male','2007-07-22',18,'Tuguegarao','Filipino','Iglesia ni Cristo','Zone 1, Brgy. San Gabriel, Tuguegarao City, Cagayan','09458883344','2026-06-12 13:05:21'),(40,'102345678933','Nicole','Anne','Manalo',NULL,'Female','2009-09-02',16,'Roxas','Filipino','Roman Catholic','Zone 4, Brgy. San Pedro, Roxas, Cagayan','09264445511','2026-06-12 13:05:21'),(41,'102345678934','Patrick','Neil','Bautista','Jr.','Male','2008-02-28',18,'Manila','Filipino','Christian','Zone 2, Brgy. Holy Monday, Roxas, Cagayan','09773336644','2026-06-12 13:05:21'),(42,'102345678935','Erica','Louise','Javier',NULL,'Female','2008-12-05',17,'Ilagan','Filipino','Roman Catholic','Zone 3, Brgy. Masaya, Roxas, Cagayan','09157774433','2026-06-12 13:05:21'),(44,'0943588103','Russel Gio','Guerra','Briva','','Male','2018-08-26',7,'Yumena Hospital, Roxas','Filipino','Roman Catholic','Rizal, Roxas, Isabela','','2026-06-14 15:34:03'),(47,'10243435000','adasd','dasas','fasds','asa','Male','2003-10-09',22,'asdasda','Filipino','asad','dsa','fdsa','2026-06-25 06:58:03'),(69,'136000000001','Juan',NULL,'Dela Cruz',NULL,'Male','2015-06-12',11,'Manila','Filipino','Catholic','123 Rizal St, Manila','09171234561','2026-08-10 12:57:17'),(70,'136000000002','Maria',NULL,'Santos',NULL,'Female','2015-08-20',10,'Quezon City','Filipino','Catholic','45 Bonifacio Ave, QC','09171234562','2026-08-10 12:57:17'),(71,'136000000003','Jose',NULL,'Garcia',NULL,'Male','2015-03-05',11,'Cebu City','Filipino','Catholic','78 Mabini St, Cebu',NULL,'2026-08-10 12:57:17'),(72,'136000000004','Angela',NULL,'Reyes',NULL,'Female','2015-01-15',11,'Manila','Filipino','Catholic','12 Luna St, Manila',NULL,'2026-08-10 12:57:17'),(73,'136000000005','Mark',NULL,'Villanueva',NULL,'Male','2015-09-02',10,'Pasig','Filipino','Catholic','9 Ortigas Ave, Pasig',NULL,'2026-08-10 12:57:17'),(74,'136000000006','Krystal',NULL,'Aquino',NULL,'Female','2015-11-30',10,'Makati','Filipino','Catholic','5 Ayala Ave, Makati',NULL,'2026-08-10 12:57:17'),(75,'136000000007','Paolo',NULL,'Torres',NULL,'Male','2014-07-18',12,'Taguig','Filipino','Catholic','3 McKinley Rd, Taguig',NULL,'2026-08-10 12:57:17'),(76,'136000000008','Bea',NULL,'Fernandez',NULL,'Female','2014-04-25',12,'Manila','Filipino','Catholic','21 Taft Ave, Manila',NULL,'2026-08-10 12:57:17'),(77,'136000000009','Miguel',NULL,'Ramos',NULL,'Male','2010-02-10',16,'Manila','Filipino','Catholic','14 Espana Blvd, Manila',NULL,'2026-08-10 12:57:17'),(78,'136000000010','Samantha',NULL,'Lopez',NULL,'Female','2010-05-22',16,'Manila','Filipino','Catholic','30 Recto Ave, Manila',NULL,'2026-08-10 12:57:17');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin.edu.ph@gmail.com','$2y$10$ISff11xX1PgO7HpubtYmBO4ck8pKTKXAd9kn2aI/ZIJtIhOvfnpTy','admin','storage/profiles/pfp_1_1782008660.jpg','2026-06-03 05:10:02','2026-06-22 17:01:59'),(3,'Registrar','registrar@gmail.com','$2y$10$rrHlYbYy4H0aRnlHLfdYju3PpzK5Pr5dp6mr54VfVXIs2nbvr968q','registrar','storage/profiles/pfp_3_1782007787.jpg','2026-06-03 07:01:06','2026-06-21 10:09:47'),(7,'John Doe','teacher@gmail.com','$2y$10$LcAKY/X2C2t4Fu4LYdioBOu3hV1x8.bnc9R7pjfFSTQWY95mTY0WG','teacher',NULL,'2026-06-08 10:38:13','2026-06-08 10:38:13'),(8,'Mark Lester','marklester@gmail.com','$2y$10$Mr7XFswgt3TDt43QRe3CrO/tsc1IIMaguPegRKcH1WXazKUFEixNm','teacher',NULL,'2026-06-08 11:15:37','2026-06-08 11:15:37'),(9,'teacher 1','teacher1@gmail.com','$2y$10$g4RH0LdSsbNUeqILlHSC/eVa3BgmYkHyeVbxR6uI3oIYbwv.GYXpS','teacher','','2026-06-23 17:43:45','2026-06-23 17:43:45');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-10 21:03:18
