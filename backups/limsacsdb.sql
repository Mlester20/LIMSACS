-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2026 at 08:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `limsacsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_history`
--

CREATE TABLE `academic_history` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrolled_by` int(11) DEFAULT NULL,
  `school_year_id` int(11) NOT NULL,
  `grade_level` varchar(50) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `enrollment_status` enum('Enrolled','Transferred','Graduated','Inactive') DEFAULT 'Enrolled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_history`
--

INSERT INTO `academic_history` (`id`, `student_id`, `enrolled_by`, `school_year_id`, `grade_level`, `section_id`, `enrollment_status`, `created_at`) VALUES
(2, 12, 3, 3, 'Grade 1', 4, 'Enrolled', '2026-06-17 06:51:37');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(100) NOT NULL,
  `reference_id` int(10) UNSIGNED DEFAULT NULL,
  `reference_table` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('success','failed') NOT NULL DEFAULT 'success',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `role`, `action`, `module`, `reference_id`, `reference_table`, `description`, `ip_address`, `status`, `created_at`) VALUES
(3, 1, 'admin', 'CREATE USER', 'USER', NULL, 'users', 'admin created a new user record', '::1', 'success', '2026-06-07 14:07:45'),
(4, 1, 'admin', 'DELETED USER', 'USER', NULL, 'users', 'admin Deleted user record', '::1', 'success', '2026-06-07 14:08:40'),
(5, 1, 'admin', 'CREATE USER', 'USER', NULL, 'users', 'admin created a new user record', '::1', 'success', '2026-06-07 14:11:52'),
(6, 1, 'admin', 'UPDATE USER', 'USER', NULL, 'users', 'admin updated user record', '::1', 'success', '2026-06-07 14:27:55'),
(7, 1, 'admin', 'DELETED USER', 'USER', NULL, 'users', 'admin Deleted user record', '::1', 'success', '2026-06-07 14:28:00'),
(8, 3, 'registrar', 'CREATE SECTION', 'SECTIONS', NULL, 'sections', 'registrar created section: Pine', '::1', 'success', '2026-06-08 12:36:01'),
(9, 3, 'registrar', 'DELETE SECTION', 'SECTIONS', NULL, 'sections', 'registrar deleted section', '::1', 'success', '2026-06-08 12:39:37'),
(10, 3, 'registrar', 'UPDATE SECTION', 'SECTIONS', NULL, 'sections', 'registrar updated section: Mahogani', '::1', 'success', '2026-06-08 12:41:30'),
(12, 3, 'registrar', 'UPDATE SECTION', 'SECTIONS', NULL, 'sections', 'registrar updated section: Mahogani', '::1', 'success', '2026-06-08 12:54:07'),
(13, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-08 15:31:12'),
(14, 3, 'registrar', 'DELETE SECTION', 'SECTIONS', NULL, 'sections', 'Registrar deleted section', '::1', 'success', '2026-06-08 15:38:29'),
(15, 3, 'registrar', 'CREATE SECTION', 'SECTIONS', NULL, 'sections', 'Registrar created section: Pine', '::1', 'success', '2026-06-08 15:38:40'),
(16, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-08 15:45:55'),
(17, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-08 15:46:16'),
(18, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-08 16:02:06'),
(19, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-08 16:44:36'),
(20, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-06-09 09:03:43'),
(21, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-09 09:52:16'),
(22, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-09 13:21:42'),
(23, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-06-09 13:27:43'),
(24, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-09 13:28:57'),
(25, 3, 'registrar', 'CREATE SECTION', 'SECTIONS', NULL, 'sections', 'Registrar created section: Mahogani', '::1', 'success', '2026-06-09 13:29:58'),
(26, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 2', '::1', 'success', '2026-06-09 13:30:27'),
(27, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-06-09 13:33:28'),
(28, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-09 13:34:01'),
(29, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-09 13:49:11'),
(30, 3, 'registrar', 'DELETE SECTION', 'SECTIONS', NULL, 'sections', 'Registrar deleted section', '::1', 'success', '2026-06-09 13:50:28'),
(31, 3, 'registrar', 'UPDATE SECTION', 'SECTIONS', NULL, 'sections', 'Registrar updated section: Mahogani', '::1', 'success', '2026-06-09 13:51:29'),
(32, 3, 'registrar', 'DELETE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar deleted a document type with ID: 9.', '::1', 'success', '2026-06-09 15:14:14'),
(33, 3, 'registrar', 'DELETE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar deleted a document type with ID: 1.', '::1', 'success', '2026-06-09 15:14:19'),
(34, 3, 'registrar', 'DELETE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar deleted a document type with ID: 8.', '::1', 'success', '2026-06-09 15:14:24'),
(35, 3, 'registrar', 'DELETE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar deleted a document type with ID: 7.', '::1', 'success', '2026-06-09 15:14:53'),
(36, 3, 'registrar', 'DELETE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar deleted a document type with ID: 6.', '::1', 'success', '2026-06-09 15:14:58'),
(37, 3, 'registrar', 'DELETE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar deleted a document type with ID: 5.', '::1', 'success', '2026-06-09 15:15:01'),
(38, 3, 'registrar', 'DELETE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar deleted a document type with ID: 4.', '::1', 'success', '2026-06-09 15:15:05'),
(39, 3, 'registrar', 'DELETE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar deleted a document type with ID: 3.', '::1', 'success', '2026-06-09 15:15:09'),
(40, 3, 'registrar', 'DELETE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar deleted a document type with ID: 2.', '::1', 'success', '2026-06-09 15:15:13'),
(41, 3, 'registrar', 'CREATE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar created a new document type: Birth Certificate.', '::1', 'success', '2026-06-09 15:16:56'),
(42, 3, 'registrar', 'DELETE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar deleted a document type with ID: 10.', '::1', 'success', '2026-06-09 15:17:00'),
(43, 3, 'registrar', 'DELETE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar deleted a document type with ID: 16.', '::1', 'success', '2026-06-09 15:18:54'),
(44, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-06-09 15:27:04'),
(45, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-09 15:31:52'),
(46, 3, 'registrar', 'UPDATE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar updated a document type with ID: 19.', '::1', 'success', '2026-06-09 15:34:06'),
(47, 3, 'registrar', 'DELETE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar deleted a document type with ID: 19.', '::1', 'success', '2026-06-09 15:34:26'),
(48, 3, 'registrar', 'UPDATE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar updated a document type with ID: 18.', '::1', 'success', '2026-06-09 15:35:27'),
(49, 3, 'registrar', 'DELETE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar deleted a document type with ID: 18.', '::1', 'success', '2026-06-09 15:35:59'),
(50, 3, 'registrar', 'UPDATE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar updated a document type with ID: 17.', '::1', 'success', '2026-06-09 15:36:59'),
(51, 3, 'registrar', 'UPDATE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar updated a document type with ID: 15.', '::1', 'success', '2026-06-09 15:37:05'),
(52, 3, 'registrar', 'UPDATE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar updated a document type with ID: 14.', '::1', 'success', '2026-06-09 15:37:11'),
(53, 3, 'registrar', 'UPDATE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar updated a document type with ID: 13.', '::1', 'success', '2026-06-09 15:37:18'),
(54, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-10 12:58:29'),
(55, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-10 14:13:26'),
(56, 3, 'registrar', 'DELETE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', NULL, 'parents_guardians', 'Registrar deleted parent/guardian record with ID: 1', '::1', 'success', '2026-06-10 15:22:42'),
(57, 3, 'registrar', 'DELETE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', NULL, 'parents_guardians', 'Registrar deleted parent/guardian record with ID: 2', '::1', 'success', '2026-06-10 15:25:48'),
(58, 3, 'registrar', 'DELETE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', NULL, 'parents_guardians', 'Registrar deleted parent/guardian record with ID: 3', '::1', 'success', '2026-06-10 16:14:17'),
(59, 3, 'registrar', 'CREATE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', NULL, 'parents_guardians', 'Registrar created a new parent/guardian record for student ID: 11', '::1', 'success', '2026-06-10 16:28:52'),
(60, 3, 'registrar', 'UPDATE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', 4, 'parents_guardians', 'Registrar updated parent/guardian record with ID: 4', '::1', 'success', '2026-06-10 16:51:50'),
(61, 3, 'registrar', 'DELETE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', NULL, 'parents_guardians', 'Registrar deleted parent/guardian record with ID: 4', '::1', 'success', '2026-06-10 16:51:59'),
(62, 3, 'registrar', 'CREATE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', NULL, 'parents_guardians', 'Registrar created a new parent/guardian record for student ID: 11', '::1', 'success', '2026-06-10 16:58:46'),
(63, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 2', '::1', 'success', '2026-06-10 16:59:30'),
(64, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-11 07:42:23'),
(65, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-12 08:54:57'),
(66, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 2', '::1', 'success', '2026-06-12 08:57:02'),
(67, 3, 'registrar', 'CREATE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', NULL, 'parents_guardians', 'Registrar created a new parent/guardian record for student ID: 11', '::1', 'success', '2026-06-12 08:57:21'),
(68, 3, 'registrar', 'UPDATE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', 5, 'parents_guardians', 'Registrar updated parent/guardian record with ID: 5', '::1', 'success', '2026-06-12 08:57:30'),
(69, 3, 'registrar', 'UPDATE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', 5, 'parents_guardians', 'Registrar updated parent/guardian record with ID: 5', '::1', 'success', '2026-06-12 08:57:58'),
(70, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-12 09:08:28'),
(71, 3, 'registrar', 'DELETE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', NULL, 'parents_guardians', 'Registrar deleted parent/guardian record with ID: 6', '::1', 'success', '2026-06-12 09:13:33'),
(72, 3, 'registrar', 'DELETE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', NULL, 'parents_guardians', 'Registrar deleted parent/guardian record with ID: 5', '::1', 'success', '2026-06-12 09:13:41'),
(73, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-12 11:45:13'),
(74, 3, 'registrar', 'UPDATE SECTION', 'SECTIONS', NULL, 'sections', 'Registrar updated section: Mahogani', '::1', 'success', '2026-06-12 12:51:11'),
(75, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:20:58'),
(76, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:32:54'),
(77, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:33:15'),
(78, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:33:41'),
(79, 3, 'registrar', 'CREATE SECTION', 'SECTIONS', NULL, 'sections', 'Registrar created section: Pine', '::1', 'success', '2026-06-12 13:34:47'),
(80, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:36:49'),
(81, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:47:42'),
(82, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:47:59'),
(83, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:48:14'),
(84, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:48:26'),
(85, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:51:46'),
(86, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:52:26'),
(87, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:53:22'),
(88, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:54:16'),
(89, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:54:32'),
(90, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:55:00'),
(91, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:55:19'),
(92, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 13:56:33'),
(93, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:00:02'),
(94, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:01:54'),
(95, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:02:34'),
(96, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:03:11'),
(97, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:04:05'),
(98, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:05:48'),
(99, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:07:53'),
(100, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:11:03'),
(101, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:12:33'),
(102, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:13:24'),
(103, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:14:33'),
(104, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:16:07'),
(105, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:17:37'),
(106, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:18:45'),
(107, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:20:48'),
(108, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-12 14:23:38'),
(109, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-14 14:46:36'),
(110, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-14 15:01:25'),
(111, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-14 15:03:32'),
(112, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-14 15:04:35'),
(113, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-14 15:06:31'),
(114, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-14 15:11:51'),
(115, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-14 15:34:53'),
(116, 3, 'registrar', 'ADD STUDENT', 'STUDENT', NULL, 'students', 'Registrar added student record', '::1', 'success', '2026-06-14 15:47:51'),
(117, 3, 'registrar', 'CREATE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', NULL, 'parents_guardians', 'Registrar created a new parent/guardian record for student ID: 11', '::1', 'success', '2026-06-14 16:03:17'),
(118, 3, 'registrar', 'UPDATE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', 7, 'parents_guardians', 'Registrar updated parent/guardian record with ID: 7', '::1', 'success', '2026-06-14 16:18:47'),
(119, 3, 'registrar', 'CREATE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', NULL, 'parents_guardians', 'Registrar created a new parent/guardian record for student ID: 11', '::1', 'success', '2026-06-14 16:19:19'),
(120, 3, 'registrar', 'DELETE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', NULL, 'parents_guardians', 'Registrar deleted parent/guardian record with ID: 8', '::1', 'success', '2026-06-14 16:19:47'),
(121, 3, 'registrar', 'DELETE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', NULL, 'parents_guardians', 'Registrar deleted parent/guardian record with ID: 7', '::1', 'success', '2026-06-14 16:19:51'),
(122, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Pogi si Lester logged in', '::1', 'success', '2026-06-14 16:30:40'),
(123, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-06-14 16:39:59'),
(124, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-15 06:30:31'),
(125, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-16 05:51:21'),
(126, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-16 11:53:30'),
(127, 3, 'registrar', 'UPLOAD DOCUMENT', 'STUDENT_DOCUMENTS', NULL, 'student_documents', 'Document uploaded for student ID 13', '::1', 'success', '2026-06-16 12:49:47'),
(128, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-16 13:35:40'),
(129, 3, 'registrar', 'CREATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar created a new student document for student ID: 12', '::1', 'success', '2026-06-16 14:10:28'),
(130, 3, 'registrar', 'DELETE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar deleted a student document with ID: 2', '::1', 'success', '2026-06-16 14:20:55'),
(131, 3, 'registrar', 'DELETE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar deleted a student document with ID: 1', '::1', 'success', '2026-06-16 14:20:58'),
(132, 3, 'registrar', 'CREATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar created a new student document for student ID: 11', '::1', 'success', '2026-06-16 14:22:09'),
(133, 3, 'registrar', 'CREATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar created a new student document for student ID: 11', '::1', 'success', '2026-06-16 14:23:24'),
(134, 3, 'registrar', 'DELETE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar deleted a student document with ID: 4', '::1', 'success', '2026-06-16 14:23:29'),
(135, 3, 'registrar', 'UPDATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar updated student document with ID: 3', '::1', 'success', '2026-06-16 14:24:23'),
(136, 3, 'registrar', 'UPDATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar updated student document with ID: 3', '::1', 'success', '2026-06-16 14:29:10'),
(137, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-16 14:31:53'),
(138, 3, 'registrar', 'ADD STUDENT', 'STUDENT', NULL, 'students', 'Registrar added student record', '::1', 'success', '2026-06-16 14:33:24'),
(139, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-16 14:33:50'),
(140, 3, 'registrar', 'DELETE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar deleted a student document with ID: 3', '::1', 'success', '2026-06-16 15:04:48'),
(141, 3, 'registrar', 'CREATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar created a new student document for student ID: 12', '::1', 'success', '2026-06-16 15:05:25'),
(142, 3, 'registrar', 'CREATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar created a new student document for student ID: 44', '::1', 'success', '2026-06-16 15:10:16'),
(143, 3, 'registrar', 'UPDATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar updated student document with ID: 5', '::1', 'success', '2026-06-16 15:14:42'),
(144, 3, 'registrar', 'DELETE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar deleted a student document with ID: 6', '::1', 'success', '2026-06-16 15:22:05'),
(145, 3, 'registrar', 'DELETE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar deleted a student document with ID: 5', '::1', 'success', '2026-06-16 15:22:08'),
(146, 3, 'registrar', 'CREATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar created a new student document for student ID: 43', '::1', 'success', '2026-06-16 15:23:08'),
(147, 3, 'registrar', 'UPDATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar updated student document with ID: 1', '::1', 'success', '2026-06-16 15:24:19'),
(148, 3, 'registrar', 'DELETE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar deleted a student document with ID: 1', '::1', 'success', '2026-06-16 15:25:32'),
(149, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-16 15:26:04'),
(150, 3, 'registrar', 'CREATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar created a new student document for student ID: 11', '::1', 'success', '2026-06-16 15:26:31'),
(151, 3, 'registrar', 'UPDATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar updated student document with ID: 2', '::1', 'success', '2026-06-16 15:31:43'),
(152, 3, 'registrar', 'DELETE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar deleted a student document with ID: 2', '::1', 'success', '2026-06-16 15:35:30'),
(153, 3, 'registrar', 'CREATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar created a new student document for student ID: 22', '::1', 'success', '2026-06-16 15:35:51'),
(154, 3, 'registrar', 'UPDATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar updated student document with ID: 3', '::1', 'success', '2026-06-16 15:36:15'),
(155, 3, 'registrar', 'UPDATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar updated student document with ID: 3', '::1', 'success', '2026-06-16 15:36:42'),
(156, 3, 'registrar', 'DELETE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar deleted a student document with ID: 3', '::1', 'success', '2026-06-16 15:36:50'),
(157, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-06-17 06:37:43'),
(158, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-17 06:38:04'),
(159, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-17 06:42:59'),
(160, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-17 06:44:10'),
(161, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-17 06:49:24'),
(162, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', '::1', 'success', '2026-06-17 06:51:37');

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
--

CREATE TABLE `document_types` (
  `id` int(11) NOT NULL,
  `document_name` varchar(100) NOT NULL,
  `is_required` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_types`
--

INSERT INTO `document_types` (`id`, `document_name`, `is_required`, `is_active`) VALUES
(11, 'Birth Certificate', 1, 1),
(12, 'Report Card', 1, 1),
(13, 'Form 137', 1, 1),
(14, 'Good Moral Certificate', 1, 1),
(15, 'Medical Certificate', 1, 1),
(17, 'Certificate of Completion', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `parents_guardians`
--

CREATE TABLE `parents_guardians` (
  `id` int(11) NOT NULL,
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
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_year`
--

CREATE TABLE `school_year` (
  `id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','inactive','archived') DEFAULT 'inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_year`
--

INSERT INTO `school_year` (`id`, `school_year`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(3, '2026-2027', '2026-06-08', '2027-04-05', 'active', '2026-06-06 15:01:38', '2026-06-09 13:33:49'),
(6, '2027-2028', '2027-06-07', '2028-04-03', 'inactive', '2026-06-09 13:28:47', '2026-06-09 13:33:40');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `grade_level` varchar(50) DEFAULT NULL,
  `adviser_id` int(11) DEFAULT NULL,
  `school_year_id` int(11) DEFAULT NULL,
  `max_students` int(11) NOT NULL DEFAULT 35,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `section_name`, `grade_level`, `adviser_id`, `school_year_id`, `max_students`, `created_at`) VALUES
(4, 'Mahogani', 'Grade 1', 8, 3, 35, '2026-06-09 13:29:58'),
(5, 'Pine', 'Grade 1', 7, 3, 35, '2026-06-12 13:34:47');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `lrn`, `first_name`, `middle_name`, `last_name`, `suffix`, `gender`, `birth_date`, `age`, `place_of_birth`, `nationality`, `religion`, `address`, `contact_number`, `created_at`) VALUES
(11, '20242110365', 'Mark Lester ', 'Suguitan', 'Raguindin', '', 'Male', '2002-12-20', 23, 'Ilagan City, Isabela', 'Filipino', 'Roman Catholic', 'Rizal, Roxas, Isabela', '', '2026-06-09 15:49:56'),
(12, '20242111365', 'Armando', 'Suguitan', 'Raguindin', 'Jr', 'Male', '2004-10-14', 22, 'Ilagan City, Isabela', 'Filipino', '', 'Rizal, Roxas, Isabela', '', '2026-06-12 08:56:37'),
(13, '102345678906', 'Angelo', 'Mercado', 'Aquino', NULL, 'Male', '2008-11-04', 17, 'Roxas', 'Filipino', 'Roman Catholic', 'Zone 1, Brgy. San Jose, Roxas, Cagayan', '09152223344', '2026-06-12 13:05:21'),
(14, '102345678907', 'Princess', 'Mae', 'Villanueva', NULL, 'Female', '2009-04-12', 17, 'Tuguegarao', 'Filipino', 'Roman Catholic', 'Blk 3 Lot 5, Stella Subdivision, Roxas, Cagayan', '09278889900', '2026-06-12 13:05:21'),
(15, '102345678908', 'Christian', 'Bautista', 'Reyes', NULL, 'Male', '2007-09-25', 18, 'Manila', 'Filipino', 'Christian', '14 Mabini St, Brgy. Centro, Roxas, Cagayan', '09061112233', '2026-06-12 13:05:21'),
(16, '102345678909', 'Samantha', 'Gomez', 'Santos', NULL, 'Female', '2008-07-19', 17, 'Ilagan', 'Filipino', 'Roman Catholic', 'Zone 3, Brgy. Muñoz, Roxas, Cagayan', '09174445566', '2026-06-12 13:05:21'),
(17, '102345678910', 'Alexander', 'Lim', 'Chua', 'III', 'Male', '2008-02-02', 18, 'Quezon City', 'Filipino', 'Christian', '88 Lotus St, Brgy. New Vargas, Roxas, Cagayan', '09193334455', '2026-06-12 13:05:21'),
(18, '102345678911', 'Chloe', 'Nicole', 'Dizon', NULL, 'Female', '2009-10-14', 16, 'Roxas', 'Filipino', 'Iglesia ni Cristo', 'Zone 5, Brgy. Bantug, Roxas, Cagayan', '09367778899', '2026-06-12 13:05:21'),
(19, '102345678912', 'Joshua', 'Gabriel', 'Torres', NULL, 'Male', '2008-06-30', 17, 'Santiago', 'Filipino', 'Roman Catholic', '22 Luna St, Brgy. Central, Roxas, Cagayan', '09985554433', '2026-06-12 13:05:21'),
(20, '102345678913', 'Beatriz', 'Amor', 'Castillo', NULL, 'Female', '2007-11-11', 18, 'Tuguegarao', 'Filipino', 'Roman Catholic', 'Zone 2, Brgy. San Jone, Roxas, Cagayan', '09452221100', '2026-06-12 13:05:21'),
(21, '102345678914', 'Gabriel', 'Jose', 'Mendoza', NULL, 'Male', '2009-03-08', 17, 'Roxas', 'Filipino', 'Aglipayan', 'Zone 6, Brgy. Matusalem, Roxas, Cagayan', '09263337788', '2026-06-12 13:05:21'),
(22, '102345678915', 'Hannah', 'Sophia', 'Ramos', NULL, 'Female', '2008-12-21', 17, 'Cauayan', 'Filipino', 'Born Again Christian', '19 Del Pilar St, Brgy. Centro, Roxas, Cagayan', '09774441122', '2026-06-12 13:05:21'),
(23, '102345678916', 'Nathaniel', 'David', 'Castro', NULL, 'Male', '2008-01-17', 18, 'Manila', 'Filipino', 'Roman Catholic', 'Zone 1, Brgy. Quibal, Peñablanca, Cagayan', '09178883344', '2026-06-12 13:05:21'),
(24, '102345678917', 'Sofia', 'Isabella', 'Fernandez', NULL, 'Female', '2009-07-04', 16, 'Tuguegarao', 'Filipino', 'Roman Catholic', '45 Caritan Norte, Tuguegarao City, Cagayan', '09184445533', '2026-06-12 13:05:21'),
(25, '102345678918', 'Ethan', 'Jacob', 'Soriano', NULL, 'Male', '2007-10-29', 18, 'Aparri', 'Filipino', 'Methodist', 'Poblacion, Aparri, Cagayan', '09226667788', '2026-06-12 13:05:21'),
(26, '102345678919', 'Althea', 'Rose', 'Guzman', NULL, 'Female', '2008-05-23', 18, 'Roxas', 'Filipino', 'Roman Catholic', 'Zone 4, Brgy. San Quirino, Roxas, Cagayan', '09351119900', '2026-06-12 13:05:21'),
(27, '102345678920', 'Michael', 'Kevin', 'Pascual', NULL, 'Male', '2009-02-11', 17, 'Lal-lo', 'Filipino', 'Iglesia ni Cristo', 'Brgy. Bagumbayan, Lal-lo, Cagayan', '09054443322', '2026-06-12 13:05:21'),
(28, '102345678921', 'Camila', 'Jane', 'Valdez', NULL, 'Female', '2008-08-08', 17, 'Tuguegarao', 'Filipino', 'Roman Catholic', 'Zone 2, Brgy. Carig Sur, Tuguegarao City, Cagayan', '09167772211', '2026-06-12 13:05:21'),
(29, '102345678922', 'Daniel', 'Luis', 'Bermudez', NULL, 'Male', '2007-12-15', 18, 'Roxas', 'Filipino', 'Roman Catholic', 'Zone 3, Brgy. Marcos, Roxas, Cagayan', '09991114455', '2026-06-12 13:05:21'),
(30, '102345678923', 'Janine', 'Marie', 'Salamat', NULL, 'Female', '2009-05-19', 17, 'Ilagan', 'Filipino', 'Christian', 'Zone 1, Brgy. Sotero, Roxas, Cagayan', '09473332211', '2026-06-12 13:05:21'),
(31, '102345678924', 'Elijah', 'Paul', 'Corpuz', NULL, 'Male', '2008-04-03', 18, 'Tuguegarao', 'Filipino', 'Aglipayan', 'Zone 7, Brgy. Pengue Ruyu, Tuguegarao City, Cagayan', '09362228833', '2026-06-12 13:05:21'),
(32, '102345678925', 'Alyssa', 'Faith', 'Domingo', NULL, 'Female', '2008-09-12', 17, 'Roxas', 'Filipino', 'Roman Catholic', 'Zone 2, Brgy. Vira, Roxas, Cagayan', '09154449988', '2026-06-12 13:05:21'),
(33, '102345678926', 'Justin', 'Mark', 'Santiago', NULL, 'Male', '2009-11-20', 16, 'Manila', 'Filipino', 'Roman Catholic', '12 Bonifacio St, Brgy. Centro, Roxas, Cagayan', '09273334411', '2026-06-12 13:05:21'),
(34, '102345678927', 'Maria', 'Theresa', 'Flores', NULL, 'Female', '2008-03-14', 18, 'Tuguegarao', 'Filipino', 'Roman Catholic', 'Zone 4, Brgy. Cataggaman Pardo, Tuguegarao City, Cagayan', '09192225588', '2026-06-12 13:05:21'),
(35, '102345678928', 'Matthew', 'James', 'Salvador', NULL, 'Male', '2007-08-27', 18, 'Roxas', 'Filipino', 'Jehovah\'s Witness', 'Zone 5, Brgy. Doña Concha, Roxas, Cagayan', '09063334499', '2026-06-12 13:05:21'),
(36, '102345678929', 'Samantha', 'Joy', 'Pineda', NULL, 'Female', '2009-01-05', 17, 'Santiago', 'Filipino', 'Christian', 'Zone 1, Brgy. Simimba, Roxas, Cagayan', '09172229900', '2026-06-12 13:05:21'),
(37, '102345678930', 'Kyle', 'Andrew', 'Gatbonton', NULL, 'Male', '2008-10-10', 17, 'Quezon City', 'Filipino', 'Roman Catholic', 'Zone 3, Brgy. Imelda, Roxas, Cagayan', '09984441122', '2026-06-12 13:05:21'),
(38, '102345678931', 'Angelica', 'Mae', 'De Leon', NULL, 'Female', '2008-06-17', 17, 'Roxas', 'Filipino', 'Roman Catholic', 'Zone 2, Brgy. Lucban, Roxas, Cagayan', '09356662233', '2026-06-12 13:05:21'),
(39, '102345678932', 'Timothy', 'John', 'Villafuerte', NULL, 'Male', '2007-07-22', 18, 'Tuguegarao', 'Filipino', 'Iglesia ni Cristo', 'Zone 1, Brgy. San Gabriel, Tuguegarao City, Cagayan', '09458883344', '2026-06-12 13:05:21'),
(40, '102345678933', 'Nicole', 'Anne', 'Manalo', NULL, 'Female', '2009-09-02', 16, 'Roxas', 'Filipino', 'Roman Catholic', 'Zone 4, Brgy. San Pedro, Roxas, Cagayan', '09264445511', '2026-06-12 13:05:21'),
(41, '102345678934', 'Patrick', 'Neil', 'Bautista', 'Jr.', 'Male', '2008-02-28', 18, 'Manila', 'Filipino', 'Christian', 'Zone 2, Brgy. Holy Monday, Roxas, Cagayan', '09773336644', '2026-06-12 13:05:21'),
(42, '102345678935', 'Erica', 'Louise', 'Javier', NULL, 'Female', '2008-12-05', 17, 'Ilagan', 'Filipino', 'Roman Catholic', 'Zone 3, Brgy. Masaya, Roxas, Cagayan', '09157774433', '2026-06-12 13:05:21'),
(43, '2026202705', 'Noriel John', 'Dolado', 'Vidal', '', 'Male', '2003-05-01', 23, 'Cagayan', 'Filipino', 'Roman Catholic', 'Sitio Karagsakan, Rizal', '', '2026-06-14 14:58:59'),
(44, '0943588103', 'Russel Gio', 'Guerra', 'Briva', '', 'Male', '2018-08-26', 7, 'Yumena Hospital, Roxas', 'Filipino', 'Roman Catholic', 'Rizal, Roxas, Isabela', '', '2026-06-14 15:34:03');

-- --------------------------------------------------------

--
-- Table structure for table `student_documents`
--

CREATE TABLE `student_documents` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `status` enum('Pending','Submitted','Verified','Rejected') DEFAULT 'Submitted',
  `remarks` text DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `profile_picture`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$ISff11xX1PgO7HpubtYmBO4ck8pKTKXAd9kn2aI/ZIJtIhOvfnpTy', 'admin', 'storage/profiles/pfp_1_1780663737.png', '2026-06-03 05:10:02', '2026-06-05 20:48:57'),
(3, 'Registrar', 'registrar@gmail.com', '$2y$10$rrHlYbYy4H0aRnlHLfdYju3PpzK5Pr5dp6mr54VfVXIs2nbvr968q', 'registrar', 'storage/profiles/pfp_3_1780558920.jpg', '2026-06-03 07:01:06', '2026-06-15 00:39:00'),
(7, 'John Doe', 'teacher@gmail.com', '$2y$10$LcAKY/X2C2t4Fu4LYdioBOu3hV1x8.bnc9R7pjfFSTQWY95mTY0WG', 'teacher', NULL, '2026-06-08 10:38:13', '2026-06-08 10:38:13'),
(8, 'Mark Lester', 'marklester@gmail.com', '$2y$10$Mr7XFswgt3TDt43QRe3CrO/tsc1IIMaguPegRKcH1WXazKUFEixNm', 'teacher', NULL, '2026-06-08 11:15:37', '2026-06-08 11:15:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_history`
--
ALTER TABLE `academic_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ah_school_year` (`school_year_id`),
  ADD KEY `fk_ah_section` (`section_id`),
  ADD KEY `fk_ah_student` (`student_id`),
  ADD KEY `fk_academic_history_enrolled_by` (`enrolled_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `parents_guardians`
--
ALTER TABLE `parents_guardians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `school_year`
--
ALTER TABLE `school_year`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sections_ibfk_1` (`adviser_id`),
  ADD KEY `sections_ibfk_2` (`school_year_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lrn` (`lrn`);

--
-- Indexes for table `student_documents`
--
ALTER TABLE `student_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `document_type_id` (`document_type_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_history`
--
ALTER TABLE `academic_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `parents_guardians`
--
ALTER TABLE `parents_guardians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `school_year`
--
ALTER TABLE `school_year`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `student_documents`
--
ALTER TABLE `student_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academic_history`
--
ALTER TABLE `academic_history`
  ADD CONSTRAINT `fk_academic_history_enrolled_by` FOREIGN KEY (`enrolled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ah_school_year` FOREIGN KEY (`school_year_id`) REFERENCES `school_year` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ah_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ah_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parents_guardians`
--
ALTER TABLE `parents_guardians`
  ADD CONSTRAINT `parents_guardians_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`adviser_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sections_ibfk_2` FOREIGN KEY (`school_year_id`) REFERENCES `school_year` (`id`);

--
-- Constraints for table `student_documents`
--
ALTER TABLE `student_documents`
  ADD CONSTRAINT `student_documents_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `student_documents_ibfk_2` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`),
  ADD CONSTRAINT `student_documents_ibfk_3` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
