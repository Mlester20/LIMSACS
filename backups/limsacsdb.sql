-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2026 at 02:10 PM
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
  `enrollment_status` enum('Enrolled','Transferred','Graduated','Dropped') DEFAULT 'Enrolled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_history`
--

INSERT INTO `academic_history` (`id`, `student_id`, `enrolled_by`, `school_year_id`, `grade_level`, `section_id`, `enrollment_status`, `created_at`) VALUES
(11, 1, 3, 3, 'Grade 6', 3, 'Enrolled', '2026-10-13 14:39:17'),
(12, 2, 3, 3, 'Grade 6', 3, 'Enrolled', '2026-10-13 14:39:17'),
(13, 5, 3, 3, 'Grade 6', 3, 'Enrolled', '2026-10-13 14:39:17'),
(14, 3, 3, 3, 'Grade 5', 6, 'Enrolled', '2026-10-13 14:39:17'),
(15, 4, 3, 3, 'Grade 5', 6, 'Enrolled', '2026-10-13 14:39:17'),
(16, 6, 3, 3, 'Grade 5', 6, 'Enrolled', '2026-10-13 14:39:17'),
(19, 3, 3, 3, 'Grade 6', 3, 'Graduated', '2026-10-14 14:18:26'),
(20, 4, 3, 3, 'Grade 6', 3, 'Graduated', '2026-10-14 14:18:26'),
(21, 7, 3, 3, 'Grade 6', 3, 'Graduated', '2026-10-14 14:18:26'),
(22, 8, 3, 3, 'Grade 6', 3, 'Graduated', '2026-10-14 14:18:26'),
(32, 9, 3, 11, 'Grade 3', 10, 'Enrolled', '2026-10-20 13:42:39'),
(33, 10, 3, 11, 'Grade 3', 10, 'Enrolled', '2026-10-20 13:42:39'),
(34, 11, 3, 11, 'Grade 3', 10, 'Enrolled', '2026-10-20 13:42:39'),
(35, 10, 3, 3, 'Grade 4', 11, 'Enrolled', '2026-10-20 13:42:55'),
(36, 11, 3, 3, 'Grade 4', 11, 'Enrolled', '2026-10-20 13:42:55'),
(37, 9, 3, 3, 'Grade 4', 11, 'Enrolled', '2026-10-20 13:42:55'),
(39, 21, NULL, 3, 'Grade 4', 12, 'Enrolled', '2026-10-27 12:34:46'),
(40, 22, NULL, 3, 'Grade 4', 12, 'Enrolled', '2026-10-27 12:34:46'),
(41, 23, NULL, 3, 'Grade 4', 12, 'Enrolled', '2026-10-27 12:34:46'),
(42, 24, NULL, 3, 'Grade 4', 12, 'Enrolled', '2026-10-27 12:34:46');

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
(1, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-04 11:43:07'),
(2, 3, 'registrar', 'IMPORT STUDENTS', 'STUDENTS', NULL, 'students', '10 students inserted, 0 skipped, 0 failed; 3 parent/guardian records added; 0 academic history records added (0 skipped); 0 graduate records added (0 skipped)', '::1', 'success', '2026-10-04 11:49:27'),
(3, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 1', 'UNKNOWN', 'success', '2026-10-04 12:09:09'),
(5, 3, 'registrar', 'ADD STUDENT', 'STUDENT', NULL, 'students', 'Registrar added student record', '::1', 'success', '2026-10-04 12:11:02'),
(6, 3, 'registrar', 'CREATE SCHOOL YEAR', 'SCHOOL_YEAR', NULL, 'school_year', 'Registrar created school year: 2026-2027', '::1', 'success', '2026-10-04 12:11:50'),
(7, 3, 'registrar', 'CREATE SECTION', 'SECTIONS', NULL, 'sections', 'Registrar created section: Mahogani', '::1', 'success', '2026-10-04 12:12:46'),
(8, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 6', '::1', 'success', '2026-10-04 12:13:03'),
(9, 3, 'registrar', 'REVERT ENROLLMENT', 'ENROLLMENT', 4, 'academic_history', 'Reverted enrollment for Mark Lester  Raguindin', '::1', 'success', '2026-10-04 12:14:42'),
(10, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-10 11:35:45'),
(11, 3, 'registrar', 'ENROLL STUDENT', 'ENROLLMENT', NULL, 'academic_history', 'Student enrolled in Grade 6', '::1', 'success', '2026-10-10 11:42:31'),
(12, 3, 'registrar', 'REVERT ENROLLMENT', 'ENROLLMENT', 5, 'academic_history', 'Reverted enrollment for Mark Villanueva', '::1', 'success', '2026-10-10 11:42:36'),
(13, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-11 12:56:55'),
(14, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-10-13 02:20:40'),
(15, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 02:22:45'),
(16, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-10-13 02:29:02'),
(17, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 02:37:15'),
(18, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-10-13 02:39:43'),
(19, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 02:40:59'),
(20, 3, 'registrar', 'CREATE TEACHER', 'USERS', NULL, 'users', 'Test Registrar added teacher: ZZTESTREGADD Teacher', 'UNKNOWN', 'success', '2026-10-13 02:49:01'),
(21, 3, 'registrar', 'CREATE TEACHER', 'USERS', NULL, 'users', 'Registrar added teacher: Registrar', '::1', 'success', '2026-10-13 02:53:35'),
(22, 3, 'registrar', 'CREATE SECTION', 'SECTIONS', NULL, 'sections', 'Registrar created section: Mahogani', '::1', 'success', '2026-10-13 02:53:54'),
(23, 3, 'registrar', 'IMPORT STUDENTS', 'STUDENTS', NULL, 'students', '0 students inserted, 10 skipped, 0 failed; 0 parent/guardian records added; 0 academic history records added (0 skipped); 0 graduate records added (0 skipped)', '::1', 'success', '2026-10-13 03:05:28'),
(24, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-10-13 03:12:11'),
(25, 1, 'admin', 'CREATE USER', 'USER', NULL, 'users', 'admin created a new user record', '::1', 'success', '2026-10-13 03:12:52'),
(26, 1, 'admin', 'DELETED USER', 'USER', NULL, 'users', 'admin Deleted user record', '::1', 'success', '2026-10-13 03:12:59'),
(27, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 03:32:57'),
(28, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-10-13 03:34:37'),
(29, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-10-13 03:37:44'),
(30, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 03:38:48'),
(31, 1, 'registrar', 'CREATE SECTION', 'SECTIONS', NULL, 'sections', 'Test Runner created section: TEST_GRADE_GOOD', 'UNKNOWN', 'success', '2026-10-13 03:45:28'),
(32, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 03:46:23'),
(33, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 13:10:04'),
(34, 1, 'registrar', 'ADD STUDENT', 'STUDENT', NULL, 'students', 'Test Runner added student record', 'UNKNOWN', 'success', '2026-10-13 13:13:07'),
(35, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 13:22:02'),
(36, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 13:27:33'),
(37, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 13:32:10'),
(38, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 13:40:42'),
(39, 3, 'registrar', 'CREATE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar created a new document type: Birth Certificate.', '::1', 'success', '2026-10-13 13:49:49'),
(40, 3, 'registrar', 'CREATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar created a new student document for student ID: 11', '::1', 'success', '2026-10-13 13:50:15'),
(41, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 13:51:57'),
(42, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 14:01:58'),
(43, 3, 'registrar', 'UPDATE STUDENT', 'STUDENT', 3, 'students', 'Registrar updated student record with ID: 3', '::1', 'success', '2026-10-13 14:21:44'),
(44, 3, 'registrar', 'CREATE PARENT/GUARDIAN', 'PARENTS_GUARDIANS', NULL, 'parents_guardians', 'Registrar created a new parent/guardian record for student ID: 1', 'UNKNOWN', 'success', '2026-10-13 14:58:43'),
(45, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 15:05:38'),
(46, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 15:07:13'),
(47, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 15:35:18'),
(48, 3, 'registrar', 'EXPORT BACKUP', 'BACKUP', NULL, NULL, 'Registrar exported an Excel database snapshot', '::1', 'success', '2026-10-13 15:35:28'),
(49, 3, 'registrar', 'EXPORT BACKUP', 'BACKUP', NULL, NULL, 'Registrar exported an Excel database snapshot', '::1', 'success', '2026-10-13 15:39:20'),
(50, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-10-13 15:42:38'),
(51, 1, 'admin', 'RESET PASSWORD', 'USER', 3, 'users', 'admin reset the password for user ID: 3', '::1', 'success', '2026-10-13 15:42:58'),
(52, 1, 'admin', 'RESET PASSWORD', 'USER', 14, 'users', 'Admin Tester reset the password for user ID: 14', 'UNKNOWN', 'success', '2026-10-13 15:43:16'),
(53, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-13 15:43:27'),
(54, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-10-14 13:20:21'),
(55, 1, 'admin', 'RESET PASSWORD', 'USER', 3, 'users', 'admin reset the password for user ID: 3', '::1', 'success', '2026-10-14 13:20:48'),
(56, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-14 13:21:05'),
(57, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-14 14:05:13'),
(58, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-14 14:27:17'),
(59, 3, 'registrar', 'ARCHIVE STUDENTS', 'STUDENT', NULL, 'students', 'Registrar archived 2 student(s): 7, 8', 'UNKNOWN', 'success', '2026-10-14 14:45:15'),
(60, 3, 'registrar', 'UNARCHIVE STUDENTS', 'STUDENT', NULL, 'students', 'Registrar unarchived 2 student(s): 7, 8', 'UNKNOWN', 'success', '2026-10-14 14:45:15'),
(61, 3, 'registrar', 'ARCHIVE STUDENTS', 'STUDENT', NULL, 'students', 'Registrar archived 1 student(s): 10', '::1', 'success', '2026-10-14 14:46:18'),
(62, 3, 'registrar', 'UPDATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar updated student document with ID: 1', '::1', 'success', '2026-10-14 15:15:10'),
(63, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-15 11:31:59'),
(64, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-18 16:48:09'),
(65, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-18 16:59:16'),
(66, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-18 17:03:07'),
(67, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-19 11:45:14'),
(68, 3, 'registrar', 'ADD STUDENT', 'STUDENT', NULL, 'students', 'Registrar added student record', '::1', 'success', '2026-10-19 11:48:51'),
(69, 3, 'registrar', 'ADD STUDENT', 'STUDENT', NULL, 'students', 'Registrar added student record', '::1', 'success', '2026-10-19 11:51:28'),
(70, 3, 'registrar', 'DELETE STUDENT', 'STUDENT', 20, 'students', 'Registrar deleted student record with ID: 20', '::1', 'success', '2026-10-19 11:51:38'),
(71, 3, 'registrar', 'DELETE STUDENT', 'STUDENT', 19, 'students', 'Registrar deleted student record with ID: 19', '::1', 'success', '2026-10-19 11:51:41'),
(72, 3, 'registrar', 'CREATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar created a new student document for student ID: 11', '::1', 'success', '2026-10-19 12:06:22'),
(73, 3, 'registrar', 'DELETE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar deleted a student document with ID: 1', '::1', 'success', '2026-10-19 12:10:17'),
(74, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-19 12:26:27'),
(75, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-19 12:29:12'),
(76, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-19 12:29:58'),
(77, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-20 01:25:03'),
(78, 3, 'registrar', 'VERIFY DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar marked student document ID: 3 as Verified', '::1', 'success', '2026-10-20 01:33:59'),
(79, 3, 'registrar', 'UPDATE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar updated a document type with ID: 1.', '::1', 'success', '2026-10-20 01:34:42'),
(80, 3, 'registrar', 'CREATE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar created a new document type: Form-137.', '::1', 'success', '2026-10-20 01:35:04'),
(81, 3, 'registrar', 'CREATE DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'Registrar created a new student document for student ID: 11', '::1', 'success', '2026-10-20 01:36:11'),
(82, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-20 01:55:46'),
(83, 3, 'registrar', 'PASSWORD RESET REQUESTED', 'AUTH', NULL, NULL, 'Registrar requested a password reset', '::1', 'success', '2026-10-20 01:56:27'),
(84, 3, 'registrar', 'PASSWORD RESET COMPLETED', 'AUTH', NULL, NULL, 'Registrar reset their password via the forgot-password flow', '::1', 'success', '2026-10-20 01:58:29'),
(85, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-20 01:59:22'),
(86, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-10-20 02:00:40'),
(87, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-20 02:01:50'),
(88, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-10-20 02:03:07'),
(89, 1, 'admin', 'UPDATE USER', 'USER', NULL, 'users', 'admin updated user record', '::1', 'success', '2026-10-20 02:05:11'),
(90, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-20 13:19:47'),
(91, 3, 'registrar', 'PROMOTE STUDENTS', 'ENROLLMENT', NULL, 'academic_history', 'Promoted 1 student(s) to Grade 5 - Mahogani', 'UNKNOWN', 'success', '2026-10-20 13:31:53'),
(92, 3, 'registrar', 'CREATE SCHOOL YEAR', 'SCHOOL_YEAR', NULL, 'school_year', 'Registrar created school year: 2027-2028', '::1', 'success', '2026-10-20 13:36:56'),
(93, 3, 'registrar', 'UPDATE SCHOOL YEAR', 'SCHOOL_YEAR', 10, 'school_year', 'Registrar updated school year with ID: 10', '::1', 'success', '2026-10-20 13:37:04'),
(94, 3, 'registrar', 'UPDATE SCHOOL YEAR', 'SCHOOL_YEAR', 3, 'school_year', 'Registrar updated school year with ID: 3', '::1', 'success', '2026-10-20 13:37:19'),
(95, 3, 'registrar', 'PROMOTE STUDENTS', 'ENROLLMENT', NULL, 'academic_history', 'Promoted 3 student(s) to Grade 4 - Acacia', '::1', 'success', '2026-10-20 13:42:55'),
(96, 3, 'registrar', 'UPDATE REPORT SIGNATORIES', 'SCHOOL_SETTINGS', NULL, 'school_settings', 'Registrar updated the report signatory settings', '::1', 'success', '2026-10-20 14:07:47'),
(97, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-20 15:52:16'),
(98, 3, 'registrar', 'PASSWORD RESET REQUESTED', 'AUTH', NULL, NULL, 'Registrar requested a password reset', '::1', 'success', '2026-10-20 15:53:04'),
(99, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-20 16:27:19'),
(100, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-21 05:06:19'),
(101, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-21 12:28:07'),
(102, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-21 12:32:24'),
(103, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-21 12:48:28'),
(104, 15, 'teacher', 'LOGIN', 'AUTH', NULL, NULL, 'teacher logged in', '::1', 'success', '2026-10-27 11:54:07'),
(105, 15, 'teacher', 'LOGIN', 'AUTH', NULL, NULL, 'teacher logged in', '::1', 'success', '2026-10-27 12:06:44'),
(106, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-27 12:19:17'),
(107, 15, 'teacher', 'LOGIN', 'AUTH', NULL, NULL, 'teacher logged in', '::1', 'success', '2026-10-27 12:30:16'),
(108, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-27 12:31:08'),
(109, 15, 'teacher', 'LOGIN', 'AUTH', NULL, NULL, 'teacher logged in', '::1', 'success', '2026-10-27 12:35:51'),
(110, 15, 'teacher', 'LOGIN', 'AUTH', NULL, NULL, 'teacher logged in', '::1', 'success', '2026-10-27 12:37:34'),
(111, 15, 'teacher', 'UPLOAD DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'teacher uploaded a document for student ID: 21', '::1', 'success', '2026-10-27 12:39:00'),
(112, 15, 'teacher', 'LOGIN', 'AUTH', NULL, NULL, 'teacher logged in', '::1', 'success', '2026-10-27 12:49:02'),
(113, 15, 'teacher', 'UPLOAD DOCUMENT', 'STUDENTS_DOCUMENTS', NULL, 'students_documents', 'teacher uploaded a document for student ID: 22', '::1', 'success', '2026-10-27 12:49:02'),
(114, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-27 12:53:07'),
(115, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-27 12:54:44'),
(116, 7, 'teacher', 'LOGIN', 'AUTH', NULL, NULL, 'John Doe logged in', '::1', 'success', '2026-10-27 12:59:18'),
(117, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-10-27 13:01:45');

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
(1, 'Birth Certificate', 1, 1),
(2, 'Form-137', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `graduates`
--

CREATE TABLE `graduates` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `academic_history_id` int(11) NOT NULL,
  `graduation_date` date NOT NULL,
  `honors` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `graduates`
--

INSERT INTO `graduates` (`id`, `student_id`, `academic_history_id`, `graduation_date`, `honors`, `remarks`, `recorded_by`, `created_at`) VALUES
(2, 3, 19, '2027-03-31', 'With Honors', NULL, 3, '2026-10-14 14:18:26'),
(3, 4, 20, '2027-03-31', 'With High Honors', NULL, 3, '2026-10-14 14:18:26'),
(4, 7, 21, '2027-03-31', NULL, NULL, 3, '2026-10-14 14:18:26'),
(5, 8, 22, '2027-03-31', 'With Honors', NULL, 3, '2026-10-14 14:18:26');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'general',
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `reference_table` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `reference_table`, `reference_id`, `is_read`, `created_at`) VALUES
(1, 3, 'document_upload', 'New document uploaded', 'teacher uploaded \"Form-137\" for Mendoza, Carlo.', '/LIMSACS/resources/views/registrar/student-documents.php?status=Pending', 'students_documents', NULL, 1, '2026-10-27 12:49:02'),
(2, 13, 'document_upload', 'New document uploaded', 'teacher uploaded \"Form-137\" for Mendoza, Carlo.', '/LIMSACS/resources/views/registrar/student-documents.php?status=Pending', 'students_documents', NULL, 0, '2026-10-27 12:49:02');

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

--
-- Dumping data for table `parents_guardians`
--

INSERT INTO `parents_guardians` (`id`, `student_id`, `father_name`, `father_occupation`, `father_contact`, `mother_name`, `mother_occupation`, `mother_contact`, `guardian_name`, `guardian_relationship`, `guardian_contact`, `created_at`) VALUES
(1, 3, 'Pedro Garcia', 'Driver', '09181234563', 'Ana Garcia', 'Vendor', '09191234563', NULL, NULL, NULL, '2026-10-04'),
(2, 6, 'Ramon Aquino', NULL, '09201234566', 'Liza Aquino', NULL, '09211234566', NULL, NULL, NULL, '2026-10-04'),
(3, 10, 'Carlos Lopez', NULL, '09221234570', 'Grace Lopez', NULL, '09231234570', NULL, NULL, NULL, '2026-10-04'),
(4, 1, 'Ricardo Dela Cruz', 'Farmer', '09171112222', 'Elena Dela Cruz', 'Housewife', '09173334444', '', '', '', '2026-10-13');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(7, 3, '6c11aa852fd137da8733da257839e70501d04690cba2d1a92437d54fb9a1ee4b', '2026-10-21 00:52:59', NULL, '2026-10-20 15:52:59');

-- --------------------------------------------------------

--
-- Table structure for table `school_settings`
--

CREATE TABLE `school_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_settings`
--

INSERT INTO `school_settings` (`setting_key`, `setting_value`) VALUES
('principal_name', 'Maria Santos'),
('principal_title', 'School Principal'),
('registrar_name', 'Mark Lester Raguindin'),
('registrar_title', 'Registrar');

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
(3, '2026-2027', '2026-07-20', '2027-05-20', 'active', '2026-10-04 12:11:50', '2026-10-04 12:11:50'),
(10, '2027-2028', '2027-06-07', '2028-04-03', 'inactive', '2026-10-20 13:36:56', '2026-10-20 13:36:56'),
(11, '2025-2026', '2025-06-01', '2026-03-31', 'archived', '2026-10-20 13:38:18', '2026-10-20 13:38:18');

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
(3, 'Mahogani', 'Grade 6', 8, 3, 35, '2026-10-04 12:12:46'),
(6, 'Mahogani', 'Grade 5', 11, 3, 35, '2026-10-13 02:53:54'),
(10, 'Narra', 'Grade 3', NULL, 11, 35, '2026-10-20 13:38:23'),
(11, 'Acacia', 'Grade 4', 7, 3, 35, '2026-10-20 13:38:23'),
(12, 'Sampaguita', 'Grade 4', 15, 3, 35, '2026-10-27 12:34:46');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `lrn`, `first_name`, `middle_name`, `last_name`, `suffix`, `gender`, `birth_date`, `age`, `place_of_birth`, `nationality`, `religion`, `address`, `contact_number`, `created_at`, `archived`) VALUES
(1, '136000000001', 'Juan', NULL, 'Dela Cruz', NULL, 'Male', '2015-06-12', 11, 'Manila', 'Filipino', 'Catholic', '123 Rizal St, Manila', '09171234561', '2026-10-04 11:49:24', 0),
(2, '136000000002', 'Maria', NULL, 'Santos', NULL, 'Female', '2015-08-20', 11, 'Quezon City', 'Filipino', 'Catholic', '45 Bonifacio Ave, QC', '09171234562', '2026-10-04 11:49:24', 0),
(3, '136000000003', 'Jose', '', 'Garcia', '', 'Male', '2026-03-05', 11, 'Cebu City', 'Filipino', 'Catholic', '78 Mabini St, Cebu', '', '2026-10-04 11:49:24', 1),
(4, '136000000004', 'Angela', NULL, 'Reyes', NULL, 'Female', '2015-01-15', 11, 'Manila', 'Filipino', 'Catholic', '12 Luna St, Manila', NULL, '2026-10-04 11:49:25', 1),
(5, '136000000005', 'Mark', NULL, 'Villanueva', NULL, 'Male', '2015-09-02', 11, 'Pasig', 'Filipino', 'Catholic', '9 Ortigas Ave, Pasig', NULL, '2026-10-04 11:49:25', 0),
(6, '136000000006', 'Krystal', NULL, 'Aquino', NULL, 'Female', '2015-11-30', 10, 'Makati', 'Filipino', 'Catholic', '5 Ayala Ave, Makati', NULL, '2026-10-04 11:49:25', 0),
(7, '136000000007', 'Paolo', NULL, 'Torres', NULL, 'Male', '2014-07-18', 12, 'Taguig', 'Filipino', 'Catholic', '3 McKinley Rd, Taguig', NULL, '2026-10-04 11:49:25', 1),
(8, '136000000008', 'Bea', NULL, 'Fernandez', NULL, 'Female', '2014-04-25', 12, 'Manila', 'Filipino', 'Catholic', '21 Taft Ave, Manila', NULL, '2026-10-04 11:49:26', 1),
(9, '136000000009', 'Miguel', NULL, 'Ramos', NULL, 'Male', '1998-02-10', 28, 'Manila', 'Filipino', 'Catholic', '14 Espana Blvd, Manila', NULL, '2026-10-04 11:49:26', 0),
(10, '136000000010', 'Samantha', NULL, 'Lopez', NULL, 'Female', '2013-05-22', 13, 'Manila', 'Filipino', 'Catholic', '30 Recto Ave, Manila', NULL, '2026-10-04 11:49:26', 1),
(11, '20242110365', 'Mark Lester ', 'Suguitan', 'Raguindin', '', 'Male', '2002-12-20', 23, 'Ilagan City, Isabela', 'Filipino', 'Roman Catholic', 'Rizal, Roxas, Isabela', '', '2026-10-04 12:11:02', 0),
(21, '136000000101', 'Liza', NULL, 'Manalo', NULL, 'Female', NULL, 10, NULL, NULL, NULL, 'Aurora, Isabela', '09171234501', '2026-10-27 12:34:46', 0),
(22, '136000000102', 'Carlo', NULL, 'Mendoza', NULL, 'Male', NULL, 10, NULL, NULL, NULL, 'Aurora, Isabela', '09171234502', '2026-10-27 12:34:46', 0),
(23, '136000000103', 'Trisha', NULL, 'Bautista', NULL, 'Female', NULL, 9, NULL, NULL, NULL, 'Aurora, Isabela', '09171234503', '2026-10-27 12:34:46', 0),
(24, '136000000104', 'Ryan', NULL, 'Ocampo', NULL, 'Male', NULL, 10, NULL, NULL, NULL, 'Aurora, Isabela', '09171234504', '2026-10-27 12:34:46', 0);

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

--
-- Dumping data for table `student_documents`
--

INSERT INTO `student_documents` (`id`, `student_id`, `document_type_id`, `file_path`, `status`, `remarks`, `uploaded_by`, `uploaded_at`) VALUES
(3, 11, 1, 'storage/student_documents/doc_1792411582.docx', 'Verified', '', 3, '2026-10-19 12:06:22'),
(4, 11, 2, 'storage/student_documents/doc_1792460171.pdf', 'Submitted', 'To be verify and review', 3, '2026-10-20 01:36:11'),
(5, 21, 1, 'storage/student_documents/doc_1793104740.pdf', 'Pending', NULL, 15, '2026-10-27 12:39:00'),
(6, 22, 2, 'storage/student_documents/doc_1793105342.pdf', 'Pending', NULL, 15, '2026-10-27 12:49:02');

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
(1, 'admin', 'admin.edu.ph@gmail.com', '$2y$10$ISff11xX1PgO7HpubtYmBO4ck8pKTKXAd9kn2aI/ZIJtIhOvfnpTy', 'admin', 'storage/profiles/pfp_1_1782008660.jpg', '2026-06-03 05:10:02', '2026-06-22 17:01:59'),
(3, 'Registrar', 'registrar@gmail.com', '$2y$10$/kwAzcNmtatjc5H4ICXl4OZsvj33tYqFpgpbm5piGuypE8F5U6UTK', 'registrar', 'storage/profiles/pfp_3_1782007787.jpg', '2026-06-03 07:01:06', '2026-10-21 00:27:40'),
(7, 'John Doe', 'teacher@gmail.com', '$2y$10$LcAKY/X2C2t4Fu4LYdioBOu3hV1x8.bnc9R7pjfFSTQWY95mTY0WG', 'teacher', NULL, '2026-06-08 10:38:13', '2026-06-08 10:38:13'),
(8, 'Mark Lester', 'marklester@gmail.com', '$2y$10$Mr7XFswgt3TDt43QRe3CrO/tsc1IIMaguPegRKcH1WXazKUFEixNm', 'teacher', NULL, '2026-06-08 11:15:37', '2026-06-08 11:15:37'),
(11, 'Registrar', 'test@gmail.com', '$2y$10$EAI.O7VP5.o2mRjY9H7BIuoJQRxtpgHRgeS0C/9YPOV9Q2H8QD0Dy', 'teacher', '', '2026-10-13 10:53:35', '2026-10-13 10:53:35'),
(13, 'Reset Test User', 'resettest_1791906169@example.com', '$2y$10$OIFKaDrLGBhR3xr/DiaCKuWqJ2V/5zH01Oa15JmYh7TMqVQiej4rq', 'registrar', NULL, '2026-10-13 23:42:49', '2026-10-13 23:42:49'),
(15, 'teacher', 'teacher1@gmail.com', '$2y$10$Jg4lWG.mSwdbOHcRsBDEgeguLDm1xL58VRcbdPhuwwboXy4DC2ACu', 'teacher', NULL, '2026-10-27 12:46:48', '2026-10-27 12:46:48');

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
-- Indexes for table `graduates`
--
ALTER TABLE `graduates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_academic_history_id` (`academic_history_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `user_id_is_read` (`user_id`,`is_read`);

--
-- Indexes for table `parents_guardians`
--
ALTER TABLE `parents_guardians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token_hash` (`token_hash`);

--
-- Indexes for table `school_settings`
--
ALTER TABLE `school_settings`
  ADD PRIMARY KEY (`setting_key`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `graduates`
--
ALTER TABLE `graduates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `parents_guardians`
--
ALTER TABLE `parents_guardians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `school_year`
--
ALTER TABLE `school_year`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `student_documents`
--
ALTER TABLE `student_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
-- Constraints for table `graduates`
--
ALTER TABLE `graduates`
  ADD CONSTRAINT `graduates_academic_history_id_fk` FOREIGN KEY (`academic_history_id`) REFERENCES `academic_history` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `graduates_recorded_by_fk` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `graduates_student_id_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parents_guardians`
--
ALTER TABLE `parents_guardians`
  ADD CONSTRAINT `parents_guardians_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
