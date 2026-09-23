-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2026 at 09:21 AM
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
(1, 4, 3, 1, 'Grade 1', NULL, 'Enrolled', '2026-08-15 14:51:41'),
(2, 5, 3, 1, 'Grade 1', NULL, 'Enrolled', '2026-08-15 14:51:41'),
(3, 6, 3, 1, 'Grade 1', NULL, 'Enrolled', '2026-08-15 14:51:41'),
(4, 7, 3, 1, 'Grade 1', NULL, 'Transferred', '2026-08-15 14:51:41'),
(5, 8, 3, 1, 'Grade 1', NULL, 'Dropped', '2026-08-15 14:51:41'),
(6, 9, 3, 2, 'Grade 6', NULL, 'Graduated', '2026-08-15 14:51:41'),
(7, 10, 3, 1, 'Grade 6', NULL, 'Graduated', '2026-08-15 14:51:41');

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
(1, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-08-15 14:50:33'),
(2, 3, 'registrar', 'IMPORT STUDENTS', 'STUDENTS', NULL, 'students', '10 students inserted, 0 skipped, 0 failed; 3 parent/guardian records added; 7 academic history records added (0 skipped); 2 graduate records added (0 skipped)', '::1', 'success', '2026-08-15 14:51:41'),
(3, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-08-20 16:06:04'),
(4, 1, 'admin', 'LOGIN', 'AUTH', NULL, NULL, 'admin logged in', '::1', 'success', '2026-08-20 16:25:32'),
(5, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-08-20 16:25:53'),
(6, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-08-22 14:41:07'),
(7, 3, 'registrar', 'UPDATE SCHOOL YEAR', 'SCHOOL_YEAR', 1, 'school_year', 'Registrar updated school year with ID: 1', '::1', 'success', '2026-08-22 14:41:33'),
(8, 3, 'registrar', 'UPDATE SCHOOL YEAR', 'SCHOOL_YEAR', 1, 'school_year', 'Registrar updated school year with ID: 1', '::1', 'success', '2026-08-22 14:41:47'),
(9, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-09-18 15:47:57'),
(10, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-09-18 17:25:34'),
(11, 3, 'registrar', 'LOGIN', 'AUTH', NULL, NULL, 'Registrar logged in', '::1', 'success', '2026-09-23 07:18:53');

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
(1, 9, 6, '2011-04-05', 'With Honors', 'Graduated in the oldest school year on record', 3, '2026-08-15 14:51:41'),
(2, 10, 7, '2026-04-06', 'With High Honors', 'Graduated in the current/latest school year', 3, '2026-08-15 14:51:41');

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
(1, 3, 'Pedro Garcia', 'Driver', '09181234563', 'Ana Garcia', 'Vendor', '09191234563', NULL, NULL, NULL, '2026-08-15'),
(2, 6, 'Ramon Aquino', NULL, '09201234566', 'Liza Aquino', NULL, '09211234566', NULL, NULL, NULL, '2026-08-15'),
(3, 10, 'Carlos Lopez', NULL, '09221234570', 'Grace Lopez', NULL, '09231234570', NULL, NULL, NULL, '2026-08-15');

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
(1, '2025-2026', '2025-06-09', '2026-04-06', 'active', '2026-08-10 13:09:56', '2026-08-22 14:41:47'),
(2, '2010-2011', '2010-06-01', '2011-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48'),
(3, '2011-2012', '2011-06-01', '2012-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48'),
(4, '2012-2013', '2012-06-01', '2013-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48'),
(5, '2013-2014', '2013-06-01', '2014-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48'),
(6, '2014-2015', '2014-06-01', '2015-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48'),
(7, '2015-2016', '2015-06-01', '2016-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48'),
(8, '2016-2017', '2016-06-01', '2017-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48'),
(9, '2017-2018', '2017-06-01', '2018-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48'),
(10, '2018-2019', '2018-06-01', '2019-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48'),
(11, '2019-2020', '2019-06-01', '2020-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48'),
(12, '2020-2021', '2020-06-01', '2021-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48'),
(13, '2021-2022', '2021-06-01', '2022-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48'),
(14, '2022-2023', '2022-06-01', '2023-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48'),
(15, '2023-2024', '2023-06-01', '2024-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48'),
(16, '2024-2025', '2024-06-01', '2025-04-05', 'archived', '2026-08-10 13:17:48', '2026-08-10 13:17:48');

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
(1, '136000000001', 'Juan', NULL, 'Dela Cruz', NULL, 'Male', '2015-06-12', 11, 'Manila', 'Filipino', 'Catholic', '123 Rizal St, Manila', '09171234561', '2026-08-15 14:51:41'),
(2, '136000000002', 'Maria', NULL, 'Santos', NULL, 'Female', '2015-08-20', 10, 'Quezon City', 'Filipino', 'Catholic', '45 Bonifacio Ave, QC', '09171234562', '2026-08-15 14:51:41'),
(3, '136000000003', 'Jose', NULL, 'Garcia', NULL, 'Male', '2015-03-05', 11, 'Cebu City', 'Filipino', 'Catholic', '78 Mabini St, Cebu', NULL, '2026-08-15 14:51:41'),
(4, '136000000004', 'Angela', NULL, 'Reyes', NULL, 'Female', '2015-01-15', 11, 'Manila', 'Filipino', 'Catholic', '12 Luna St, Manila', NULL, '2026-08-15 14:51:41'),
(5, '136000000005', 'Mark', NULL, 'Villanueva', NULL, 'Male', '2015-09-02', 10, 'Pasig', 'Filipino', 'Catholic', '9 Ortigas Ave, Pasig', NULL, '2026-08-15 14:51:41'),
(6, '136000000006', 'Krystal', NULL, 'Aquino', NULL, 'Female', '2015-11-30', 10, 'Makati', 'Filipino', 'Catholic', '5 Ayala Ave, Makati', NULL, '2026-08-15 14:51:41'),
(7, '136000000007', 'Paolo', NULL, 'Torres', NULL, 'Male', '2014-07-18', 12, 'Taguig', 'Filipino', 'Catholic', '3 McKinley Rd, Taguig', NULL, '2026-08-15 14:51:41'),
(8, '136000000008', 'Bea', NULL, 'Fernandez', NULL, 'Female', '2014-04-25', 12, 'Manila', 'Filipino', 'Catholic', '21 Taft Ave, Manila', NULL, '2026-08-15 14:51:41'),
(9, '136000000009', 'Miguel', NULL, 'Ramos', NULL, 'Male', '1998-02-10', 28, 'Manila', 'Filipino', 'Catholic', '14 Espana Blvd, Manila', NULL, '2026-08-15 14:51:41'),
(10, '136000000010', 'Samantha', NULL, 'Lopez', NULL, 'Female', '2013-05-22', 13, 'Manila', 'Filipino', 'Catholic', '30 Recto Ave, Manila', NULL, '2026-08-15 14:51:41');

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
(1, 'admin', 'admin.edu.ph@gmail.com', '$2y$10$ISff11xX1PgO7HpubtYmBO4ck8pKTKXAd9kn2aI/ZIJtIhOvfnpTy', 'admin', 'storage/profiles/pfp_1_1782008660.jpg', '2026-06-03 05:10:02', '2026-06-22 17:01:59'),
(3, 'Registrar', 'registrar@gmail.com', '$2y$10$rrHlYbYy4H0aRnlHLfdYju3PpzK5Pr5dp6mr54VfVXIs2nbvr968q', 'registrar', 'storage/profiles/pfp_3_1782007787.jpg', '2026-06-03 07:01:06', '2026-06-21 10:09:47'),
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
-- Indexes for table `graduates`
--
ALTER TABLE `graduates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_academic_history_id` (`academic_history_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `recorded_by` (`recorded_by`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `graduates`
--
ALTER TABLE `graduates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `parents_guardians`
--
ALTER TABLE `parents_guardians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `school_year`
--
ALTER TABLE `school_year`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
