-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 05, 2026 at 05:43 PM
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
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `lrn` varchar(20) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `grade_level` varchar(50) NOT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `place_of_birth` varchar(150) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `enrollment_status` enum('Enrolled','Transferee','Transferred','Dropped','Graduated') DEFAULT 'Enrolled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `lrn`, `first_name`, `middle_name`, `last_name`, `suffix`, `grade_level`, `gender`, `birth_date`, `age`, `place_of_birth`, `nationality`, `religion`, `address`, `contact_number`, `enrollment_status`, `created_at`) VALUES
(3, '20242111365', 'Mark Lester ', 'Suguitan', 'Raguindin', '', 'Grade 1', 'Male', '2002-12-20', 23, 'Ilagan City, Isabela', 'Filipino', 'Roman Catholic', 'Rizal, Roxas, Isabela', '09349991034', 'Enrolled', '2026-06-05 14:05:55'),
(4, '123456789001', 'Juan', 'Santos', 'Dela Cruz', NULL, 'Grade 7', 'Male', '2012-05-15', 14, 'Manila City', 'Filipino', 'Roman Catholic', 'Brgy. San Isidro, Luna, Isabela', '09171234567', 'Enrolled', '2026-06-05 15:03:57'),
(5, '123456789002', 'Maria', 'Reyes', 'Garcia', NULL, 'Grade 8', 'Female', '2011-09-22', 15, 'Tuguegarao City', 'Filipino', 'Roman Catholic', 'Brgy. Centro, Luna, Isabela', '09181234567', 'Enrolled', '2026-06-05 15:03:57'),
(6, '123456789003', 'Mark', 'Villanueva', 'Ramos', 'Jr.', 'Grade 9', 'Male', '2010-02-10', 16, 'Santiago City', 'Filipino', 'Iglesia ni Cristo', 'Brgy. Lallayug, Luna, Isabela', '09191234567', '', '2026-06-05 15:03:57'),
(7, '123456789004', 'Angela', 'Lopez', 'Fernandez', NULL, 'Grade 10', 'Female', '2009-11-30', 17, 'Ilagan City', 'Filipino', 'Roman Catholic', 'Brgy. Victoria, Luna, Isabela', '09201234567', 'Transferred', '2026-06-05 15:03:57'),
(8, '123456789005', 'Joshua', 'Mendoza', 'Aquino', NULL, 'Grade 12', 'Male', '2007-08-18', 19, 'Cauayan City', 'Filipino', 'Born Again Christian', 'Brgy. Macatel, Luna, Isabela', '09211234567', 'Graduated', '2026-06-05 15:03:57');

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
(3, 'registrar', 'registrar@gmail.com', '$2y$10$/e3gO4nFgjXHxVvN1fvodeIlj60rF/IIvzWHhSUE2PfmWZG4KO8YC', 'registrar', 'storage/profiles/pfp_3_1780558920.jpg', '2026-06-03 07:01:06', '2026-06-04 15:42:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
