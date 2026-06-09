-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 09, 2026 at 05:37 PM
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
  `school_year_id` int(11) NOT NULL,
  `grade_level` varchar(50) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `enrollment_status` enum('Enrolled','Transferred','Graduated','Inactive') DEFAULT 'Enrolled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(53, 3, 'registrar', 'UPDATE DOCUMENT TYPE', 'DOCUMENT_TYPE', NULL, 'document_types', 'Registrar updated a document type with ID: 13.', '::1', 'success', '2026-06-09 15:37:18');

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
(4, 'Mahogani', 'Grade 2', 8, 3, 35, '2026-06-09 13:29:58');

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
(10, '20242111365', 'Mark Lester ', 'Suguitan', 'Raguindin', '', 'Male', '2002-12-20', 23, 'Ilagan City, Isabela', 'Filipino', 'Roman Catholic', 'Rizal, Roxas, Isabela', '', '2026-06-09 14:12:23');

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
(3, 'Registrar', 'registrar@gmail.com', '$2y$10$/e3gO4nFgjXHxVvN1fvodeIlj60rF/IIvzWHhSUE2PfmWZG4KO8YC', 'registrar', 'storage/profiles/pfp_3_1780558920.jpg', '2026-06-03 07:01:06', '2026-06-08 22:22:20'),
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
  ADD KEY `fk_ah_student` (`student_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `school_year`
--
ALTER TABLE `school_year`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `student_documents`
--
ALTER TABLE `student_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `fk_ah_school_year` FOREIGN KEY (`school_year_id`) REFERENCES `school_year` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ah_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ah_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

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
