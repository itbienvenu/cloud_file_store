-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 14, 2025 at 11:02 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `file`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_logs`
--

CREATE TABLE `access_logs` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `access_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `access_logs`
--

INSERT INTO `access_logs` (`id`, `file_id`, `ip_address`, `access_time`) VALUES
(1, 8, '::1', '2025-04-13 14:20:16'),
(2, 9, '::1', '2025-04-13 14:28:19');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `secure_path` varchar(255) NOT NULL,
  `download_token` varchar(64) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expire_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `file_name`, `secure_path`, `download_token`, `user_id`, `created_at`, `expire_time`) VALUES
(3, 'Screenshot (6).png', '1d2e1bc6/4f645544/c9aaa628/54e1b0b169bc618ae088cd431b15fac1', '8cc905ca6e70d097c35806fbfc823724429888d979b8566bdbdc13a532563ccc', 2, '2025-04-12 21:17:35', '2025-04-13 23:17:35'),
(4, 'Screenshot (2).png', '23987e9d/bd0a8892/66d1704f/1635fc8a720b5170f0247ad31696f2c1', '4f75da6d39d3e9201166a7886a839ace01202ae1674292166ddd7f33120d683d', 2, '2025-04-12 21:18:45', '2025-04-13 23:18:45'),
(5, 'Screenshot (6).png', '946e69fe/034c91a2/a7f7dca8/93455f18f122172d2f01af0390e90169', '505b7acabae7b571f7330518b882ae56b0527099845013414ffe4f30d5856f37', 2, '2025-04-12 21:19:25', '2025-04-13 23:19:25'),
(6, 'Screenshot (6).png', '96c3c25f/082a77ce/150597aa/8bd13e6e616872c6af5cc650d18deda4', 'f2d84ad2714a2fe0e1ed2b43bfa736043bab81a33e9a8e73321c655a16aa1755', 2, '2025-04-12 21:24:15', '2025-04-13 23:24:15'),
(7, 'Screenshot (7).png', '9c810050/a31a8a6f/42f8600e/4fb3ec55043f8ff9d3934ec39f5b646d', '429b50dfd7e665b61646aedd13380af37a443278dc0bc758f651b7fa38ad97f8', 2, '2025-04-12 21:26:25', '2025-04-13 23:26:25'),
(8, 'ResultSlipAdvancedLevel (1).pdf', 'e59df2bf/a0e93745/0e42f6c9/f63daf8a4ba6206a545b1fa7689bdf37', 'f218df46ddc2c46395fa68249ac0cd198a28472317a7d296ecda4b12c1badce0', 2, '2025-04-13 21:18:19', '2025-04-14 23:18:19'),
(9, 'Engineering Mathematics. A Foundation for Electronic, Electrical, Communications and Systems Engineers ( PDFDrive.com ) (2).pdf', '6c3a82b7/e02f0197/02ac8ab5/d18bc2c660062cb9303657ba6b7b4f0b', 'd71a208a0ec4a3da1f5b93e40e6d5a6ed71b928e021047a36953f9b3ce2b6f75', 2, '2025-04-13 21:28:05', '2025-04-14 00:28:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'bienvenu', 'bienvenugashema@gmail.com', '$2y$10$WK0SMP/Lz/fU2bBY6m1PI.A1cFzFQBnn5mPR5d7UY7bnZ/XJKNfQO', '2025-04-12 19:49:18'),
(2, 'mwimule', 'gashema@gmail.com', '$2y$10$SVEhth2YFg8.z.rrRN0bveMMsZPUoU3/V/RxeR2BagXPMBBEuM4hO', '2025-04-12 19:50:47');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `token`, `created_at`, `expires_at`) VALUES
(2, 2, 'db3888126e223f3a6d998e742f03560bb08410dd6ef49f27abf7c17993381ea1', '2025-04-12 20:13:53', '2025-04-19 13:13:53'),
(4, 2, '121206d263cc6997ffb17638aa7f7223dfd40ec944b7fa105940d39098280cca', '2025-04-13 21:17:59', '2025-04-20 14:17:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_logs`
--
ALTER TABLE `access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_id` (`file_id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `download_token` (`download_token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_logs`
--
ALTER TABLE `access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `access_logs`
--
ALTER TABLE `access_logs`
  ADD CONSTRAINT `access_logs_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
