-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2025 at 08:12 PM
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
-- Database: `pafinder`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `applied_date` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `user_id`, `job_id`, `applied_date`, `status`) VALUES
(1, 12, 4, '2025-05-16 23:12:50', 'Pending'),
(2, 12, 5, '2025-05-17 00:02:43', 'Interviewing');

-- --------------------------------------------------------

--
-- Table structure for table `education`
--

CREATE TABLE `education` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `degree` varchar(255) NOT NULL,
  `institution` varchar(255) NOT NULL,
  `year` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `education`
--

INSERT INTO `education` (`id`, `user_id`, `degree`, `institution`, `year`) VALUES
(1, 12, 'BSINFO', 'LSPU', '2025');

-- --------------------------------------------------------

--
-- Table structure for table `experiences`
--

CREATE TABLE `experiences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `position` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `experiences`
--

INSERT INTO `experiences` (`id`, `user_id`, `position`, `company`, `start_date`, `end_date`) VALUES
(1, 12, 'Navigator', 'Stawhat Crew', '2010-03-08', '2020-07-14');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `salary` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `employer_id`, `title`, `description`, `location`, `salary`, `created_at`) VALUES
(2, 2, 'waaaaaaaa', 'aaaaaaaaaaa', 'aaaaaaaaaaaa', '200billion pesos', '2025-05-15 19:48:35'),
(3, 2, 'crew ni blackbeard', 'ako budoy', 'east blue', '10000 beli', '2025-05-15 21:11:03'),
(4, 2, 'wow nagana ', 'nagana sya', 'lamao', 'sanpablo11', '2025-05-15 21:32:47'),
(5, 13, 'Matcho Dancer', 'Need Ka dito ', 'Tondo Manila', '1K-UP', '2025-05-16 23:34:05');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `user_id`, `name`) VALUES
(1, 12, 'CSS');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `resume` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','employer','client') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `resume`, `password`, `role`, `created_at`) VALUES
(1, 'delfin lorenz', 'lorenz@gmail.com', NULL, '$2y$10$RX7P1bz5lKFJGC7qTK3DLuY5PoW1unTZz6QndHBl26YxYJRRjnKAq', 'client', '2025-05-15 04:49:06'),
(2, 'lolrnez', 'lolrenz@gmail.com', NULL, '$2y$10$W/DOwXlOpvLl7alxU3JM.ORy8DTyi9uFRK.ZzezL/gJwqDK7o1Fce', 'employer', '2025-05-15 05:07:43'),
(3, 'lorenz2', 'start@gmail.com', NULL, '$2y$10$2zOynKd3QRRy/4H6PsFcmO97fJU1PAfOkFmzTn6pMIjsidBJ5i.X2', 'client', '2025-05-15 05:26:39'),
(4, 'lorenz trin', 'lmao@gmail.com', NULL, '$2y$10$zjNd.6ySmBwOyYuBkPMVnuunu5jYFJ3yZlBbDykZ28wT4I6hFIKri', 'admin', '2025-05-15 14:07:00'),
(8, 'waa', 'tungtung@gmail.com', NULL, '$2y$10$DTNwrScQSHXJ6Raby/Jxh./D4I4vEOjFKlwZrcLR2MgYkHfuUbV1a', 'admin', '2025-05-15 14:10:59'),
(12, 'user', 'user@example.com', '1747407470_abc.docx', '$2y$10$taon2gdZuqIFtb6ldtlYl.fSoig4amnhmBYk64R/4eTjLYUGopDFW', 'client', '2025-05-15 15:09:21'),
(13, 'employer ako', 'employer@ako.com', NULL, '$2y$10$TepdCRXlA6DiFfmB6nHvdehnJ3WK8j4LixtWyTtLIFbgzKLPu3LVq', 'employer', '2025-05-16 15:32:14'),
(14, 'Admin', 'admin@pathfinders.com', NULL, '$2y$10$YedqLN7MRc.Rsc8IkAIaOu/WuoQxcH7ebtZXJPlEwlSB1zrxMQly.', 'admin', '2025-05-16 16:07:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_job` (`user_id`,`job_id`);

--
-- Indexes for table `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `experiences`
--
ALTER TABLE `experiences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `education`
--
ALTER TABLE `education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `experiences`
--
ALTER TABLE `experiences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `education`
--
ALTER TABLE `education`
  ADD CONSTRAINT `education_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `experiences`
--
ALTER TABLE `experiences`
  ADD CONSTRAINT `experiences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
