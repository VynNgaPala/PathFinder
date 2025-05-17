-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2025 at 08:22 AM
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
(3, 15, 7, '2025-05-17 12:04:24', 'Under Review');

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
(4, 15, 'Bachelor of Science in Business Administration Major in Marketing Management', 'Laguna State University of San Pablo', '2020');

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
(2, 15, 'Business Manager', 'LF Studio', '2023-06-24', '2025-01-17');

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
(7, 13, 'Sales Admin', 'We are looking for a detail-oriented Sales Admin to support our sales team and ensure the smooth running of sales operations. The ideal candidate will assist in managing customer accounts, preparing sales reports, handling order processing, and coordinating communication between the sales department and clients.', 'Laguna', '₱18,000 – ₱25,000 per year', '2025-05-17 12:01:24'),
(8, 13, 'Web Designer', 'We are seeking a creative and detail-oriented Web Designer to join our team. The ideal candidate will design, maintain, and improve websites that are visually appealing, user-friendly, and responsive across all devices. You’ll collaborate with developers, marketers, and content creators to deliver high-quality digital experiences.\r\n\r\n', 'Manila', '₱20,000 – ₱30,000', '2025-05-17 13:06:09'),
(9, 13, 'Barista', 'We are looking for a friendly and energetic Barista to join our team. The Barista will be responsible for preparing high-quality coffee and beverages, providing excellent customer service, and maintaining a clean and welcoming café environment.\r\n\r\n', 'Quezon', '₱12,000 – ₱16,000 per year', '2025-05-17 13:08:39'),
(10, 13, 'Customer Service Representative (CSR)', 'A Customer Service Representative (CSR) serves as the main point of contact between a company and its customers. You will handle inquiries, complaints, order tracking, and provide solutions via phone, chat, or email.', 'San Francisco, USA', '$15 – $22/hour (plus bonuses, remote options)', '2025-05-17 13:10:44');

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

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(2, 'user@example.com', 'ba90dddd6ffeda44210dcb070a235a7cdc285fb4f37d73e20ad0a4e5c09e3abd', '2025-05-17 06:46:44', '2025-05-17 03:46:44');

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
(2, 15, 'Microsoft Office (Excel, Word, Outlook, PowerPoint)'),
(3, 15, 'Data entry and order processing systems'),
(4, 15, 'Quotation and invoice preparation'),
(5, 15, 'Sales reporting and documentation');

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
(4, 'lorenz trin', 'lmao@gmail.com', NULL, '$2y$10$zjNd.6ySmBwOyYuBkPMVnuunu5jYFJ3yZlBbDykZ28wT4I6hFIKri', 'admin', '2025-05-15 14:07:00'),
(13, 'employer ako', 'employer@ako.com', NULL, '$2y$10$TepdCRXlA6DiFfmB6nHvdehnJ3WK8j4LixtWyTtLIFbgzKLPu3LVq', 'employer', '2025-05-16 15:32:14'),
(14, 'Admin', 'admin@pathfinders.com', NULL, '$2y$10$YedqLN7MRc.Rsc8IkAIaOu/WuoQxcH7ebtZXJPlEwlSB1zrxMQly.', 'admin', '2025-05-16 16:07:43'),
(15, 'Marvin Andrei Almendral', 'marvinalmendral22@gmail.com', '1747458168_Beige___Black_Minimalist_Business_Manager_Professional_Resume.pdf', '$2y$10$TwKT8oehovgGFqjRNiKj1eVZ3TaBX5zmyzbMHAFh46dMex6DkYbSW', 'client', '2025-05-17 03:54:49');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `education`
--
ALTER TABLE `education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `experiences`
--
ALTER TABLE `experiences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
