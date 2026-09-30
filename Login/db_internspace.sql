-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 17, 2026 at 02:03 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_internspace`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_info`
--

CREATE TABLE `about_info` (
  `id` int NOT NULL DEFAULT '1',
  `kedayweb_title` varchar(255) NOT NULL,
  `kedayweb_description` text NOT NULL,
  `kedayweb_vision` text NOT NULL,
  `kedayweb_mission` text NOT NULL,
  `kedayweb_image` varchar(500) NOT NULL,
  `intern_title` varchar(255) NOT NULL,
  `intern_description` text NOT NULL,
  `intern_benefits` text NOT NULL,
  `intern_workflow` text NOT NULL,
  `intern_image` varchar(500) NOT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT '',
  `email` varchar(150) NOT NULL,
  `position` varchar(150) NOT NULL,
  `portfolio` varchar(255) DEFAULT '',
  `cv_file` varchar(255) DEFAULT '',
  `status` varchar(50) DEFAULT 'review',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `first_name`, `last_name`, `email`, `position`, `portfolio`, `cv_file`, `status`, `created_at`) VALUES
(1, 'Akshay', 'Mirza', 'akshaymirza5@gmail.com', 'Magang Web Developer', 'a', '', 'offer', '2026-09-15 06:38:26');

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'Aktivitas Harian',
  `author` varchar(100) NOT NULL,
  `excerpt` text,
  `content` text NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'present',
  `clock_in` varchar(5) DEFAULT NULL,
  `clock_out` varchar(5) DEFAULT NULL,
  `photo_in` varchar(255) DEFAULT NULL,
  `photo_out` varchar(255) DEFAULT NULL,
  `location_in` varchar(255) DEFAULT NULL,
  `location_out` varchar(255) DEFAULT NULL,
  `lat_in` decimal(10,7) DEFAULT NULL,
  `lng_in` decimal(10,7) DEFAULT NULL,
  `lat_out` decimal(10,7) DEFAULT NULL,
  `lng_out` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `reason` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `username`, `date`, `status`, `clock_in`, `clock_out`, `photo_in`, `photo_out`, `location_in`, `location_out`, `lat_in`, `lng_in`, `lat_out`, `lng_out`, `created_at`, `updated_at`, `reason`) VALUES
(1, 'fil', '2026-09-16', 'late', '13:57', NULL, 'uploads/attendance/fil_2026-09-16_in_1789541836.jpg', NULL, 'Setendo, Tamansari, Tukangkayu, Banyuwangi, East Java, 68416, Indonesia', NULL, -8.2244851, 114.3702103, NULL, NULL, '2026-09-16 06:47:17', '2026-09-16 06:57:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `attendance_settings`
--

CREATE TABLE `attendance_settings` (
  `id` int NOT NULL,
  `office_name` varchar(255) NOT NULL DEFAULT 'Kantor Kedayweb',
  `address` text,
  `latitude` decimal(11,8) NOT NULL DEFAULT '-8.21923300',
  `longitude` decimal(11,8) NOT NULL DEFAULT '114.36922200',
  `radius_meters` int NOT NULL DEFAULT '100',
  `is_strict` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attendance_settings`
--

INSERT INTO `attendance_settings` (`id`, `office_name`, `address`, `latitude`, `longitude`, `radius_meters`, `is_strict`, `updated_at`) VALUES
(1, 'Kantor Kedayweb Banyuwangi', 'Jl. Tamansari, Tukangkayu, Banyuwangi, Jawa Timur', -8.21932100, 114.36945800, 100, 1, '2026-09-16 14:15:34');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int NOT NULL,
  `certificate_id` varchar(50) NOT NULL,
  `user_id` int DEFAULT NULL,
  `intern_name` varchar(150) NOT NULL,
  `intern_position` varchar(150) NOT NULL,
  `university` varchar(200) DEFAULT '',
  `major` varchar(200) DEFAULT '',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `issue_date` date NOT NULL,
  `score_technical` tinyint UNSIGNED DEFAULT '0',
  `score_discipline` tinyint UNSIGNED DEFAULT '0',
  `score_attitude` tinyint UNSIGNED DEFAULT '0',
  `final_grade` varchar(5) DEFAULT '',
  `supervisor_name` varchar(150) DEFAULT '',
  `status` enum('active','revoked') NOT NULL DEFAULT 'active',
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `certificate_id`, `user_id`, `intern_name`, `intern_position`, `university`, `major`, `start_date`, `end_date`, `issue_date`, `score_technical`, `score_discipline`, `score_attitude`, `final_grade`, `supervisor_name`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'IS-2024-001', 1, 'INT-2024-001', 'Web Developer Intern', 'Universitas Indonesia', 'Ilmu Komputer', '2024-07-01', '2024-10-01', '2024-10-05', 88, 90, 92, 'A', 'Shaliza Mirza', 'active', NULL, '2026-09-16 12:35:41', '2026-09-16 12:35:41'),
(2, 'IS-2024-002', 4, 'Fairuz', 'UI/UX Design Intern', 'Institut Teknologi Bandung', 'Desain Komunikasi Visual', '2024-07-01', '2024-10-01', '2024-10-05', 85, 88, 95, 'A', 'Shaliza Mirza', 'active', NULL, '2026-09-16 12:35:41', '2026-09-16 12:35:41'),
(3, 'IS-2024-003', 5, 'Filbert', 'Backend Engineer Intern', 'Universitas Gadjah Mada', 'Teknik Informatika', '2024-07-01', '2024-10-01', '2024-10-05', 92, 85, 88, 'A', 'Shaliza Mirza', 'active', NULL, '2026-09-16 12:35:41', '2026-09-16 12:35:41');

-- --------------------------------------------------------

--
-- Table structure for table `events_history`
--

CREATE TABLE `events_history` (
  `id` int NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `event_type` varchar(100) NOT NULL DEFAULT 'Event Magang',
  `event_date` date NOT NULL,
  `location` varchar(255) NOT NULL,
  `organizer` varchar(100) NOT NULL,
  `summary` text NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_activities`
--

CREATE TABLE `gallery_activities` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'Kegiatan Magang',
  `author` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `gallery_activities`
--

INSERT INTO `gallery_activities` (`id`, `title`, `category`, `author`, `description`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'gf', 'Belajar & Workshop', 'INT-20242re-001', 'vwewv', 'uploads/gallery/1789461639_6aa90487c5544.jpg', '2026-09-15 08:40:39', '2026-09-15 15:40:39'),
(2, 'ss', 'Aktivitas Harian', 'shaliza', 'cssc', 'uploads/gallery/1789503873_6aa9a9814d979.jpeg', '2026-09-15 20:24:33', '2026-09-16 03:24:33');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','in_progress','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `user_id`, `title`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 0, 'Human Instrumentality', '', 'pending', '2026-09-14 11:52:11', '2026-09-15 08:00:44'),
(2, 0, 'cek 3', '', 'pending', '2026-09-14 12:58:14', '2026-09-14 12:58:14');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `priority` enum('Low','Medium','High') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Medium',
  `status` enum('todo','inprogress','underreview','done') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'todo',
  `assignee` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Alex Doe',
  `due_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `title`, `description`, `priority`, `status`, `assignee`, `due_date`, `created_at`, `updated_at`) VALUES
(2, 2, 'Make shinji ikari suffer', 'First step', 'High', 'done', 'Gendo ikari', '2008-03-12', '2026-09-15 07:49:17', '2026-09-16 01:46:43'),
(4, 1, 'Make shinji ikari suffer', '', 'Medium', 'todo', 'Alex Doe', NULL, '2026-09-15 07:54:49', '2026-09-15 08:01:04'),
(5, 2, 'makan', '', 'High', 'done', 'INT-2024-001', NULL, '2026-09-16 01:46:56', '2026-09-16 01:46:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `role` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `intern_position` varchar(150) DEFAULT NULL,
  `university` varchar(150) DEFAULT NULL,
  `major` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `username`, `password`) VALUES
(1, 'intern', 'INT-2024-001', '12345678'),
(2, 'admin', 'admin@internspace.com', 'admin123'),
(3, 'superadmin', 'shaliza', 'mirza'),
(4, 'intern', 'fairuz', 'a'),
(5, 'intern', 'filbert', 'a'),
(6, 'intern', 'fil', 'liem'),
(7, 'admin', 'fil2', 'liem'),
(8, 'superadmin', 'fil3', 'lem');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_info`
--
ALTER TABLE `about_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_date` (`username`,`date`);

--
-- Indexes for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_id` (`certificate_id`),
  ADD KEY `fk_cert_user` (`user_id`);

--
-- Indexes for table `events_history`
--
ALTER TABLE `events_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_activities`
--
ALTER TABLE `gallery_activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project_id` (`project_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `events_history`
--
ALTER TABLE `events_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery_activities`
--
ALTER TABLE `gallery_activities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `fk_cert_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
