-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2026 at 05:51 PM
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
-- Database: `universitygrades`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `credits` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `credits`) VALUES
(101, 'Introduction to PHP', 4),
(102, 'database', 5),
(103, 'php', 2);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `grade` varchar(3) DEFAULT 'N/A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `course_id`, `instructor_id`, `grade`) VALUES
(1, 3, 101, 2, 'N/A'),
(2, 17, 102, 18, '12'),
(3, 24, 102, 25, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `instructor_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `department` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`instructor_id`, `name`, `department`) VALUES
(2, 'Dr. John Smith', 'Computer Science'),
(4, 'Dr. Jean Paul', 'BIT'),
(6, 'agape TI', 'cs'),
(14, 'mr og', 'information'),
(18, 'm. gervais', 'software'),
(25, 'gonzlo', 'information');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `major` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `first_name`, `last_name`, `major`) VALUES
(1, 'Kevine', 'Mugisha', 'Computer Science'),
(2, 'Eric', 'Manzi', 'Information Technology'),
(3, 'Alice', 'Green', 'Software Engineering'),
(4, 'Janvier', 'MPANO', 'IT'),
(5, 'syve', 'Tha', '?????'),
(13, 'yves', 'emmy', 'maso'),
(15, 'sele', 'bts', 'software'),
(16, 'gg', 'bet', 'software'),
(17, 'pp', 'ppp', 'software'),
(23, 'kjhbkjn', 'kjkjh', 'jkuhb'),
(24, 'isingizwe', 'gonzalezi', 'ed of computer science');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','instructor','student') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `role`) VALUES
(1, 'admin@gmail.com', '$2y$10$kBnGnSjLO4oqZ9C53RCeAeA1PtQVqP9jpmy6eLHYNyDI80/FyGm4C', 'admin'),
(2, 'j.smith@university.com', 'smith123', 'instructor'),
(3, 'alice.green@student.com', 'green123', 'student'),
(4, 'impjanvier20@gmail.com', '$2y$10$ucwRxkKVijKg4aIzoAuHD.V4F1dWk/87vzZCEmFAE0LNuifrQLIci', 'student'),
(5, 'syvetha@gmail.com', '$2y$10$HPOIcMLc8XiSWO5lsng2zOz8Jc68uwka72P/33pM4S5p/7LaYbbwm', 'student'),
(6, 'agape@gmail.com', '$2y$10$NFd6WZ3XGDILXnT0mhyh5eUYsffwAHr44rjtWU1s6Q0fJFq4rNE/a', 'instructor'),
(10, 'instructor@gmail.com', '$2y$10$8v86yCgN.VjIeWj7S.6Nbe3XbL4q.V.rD5DOmG4P7Q7eK796S38vG', 'instructor'),
(12, 'instructoor@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'instructor'),
(13, 'yves@gmail.com', '$2y$10$AzumGyPqwm/Jyx6qCxwkgeOtKI3wbmVrf8b2PVL7fvcfsoIHhNDdi', 'student'),
(14, 'mpano@gmail.com', '$2y$10$YqC.Dhd6FHGU4jH9I8.z0.Coi67QlYkO9JVy788t4MA8ZkbCbP9H6', 'instructor'),
(15, 'slmn@gmail.com', '$2y$10$Dz2eaFPXpt/Aqw.nHgQpaecQIufbWwMiakcgae/S1HlWCOFA0O5rO', 'student'),
(16, 'gonza@gmail.com', '$2y$10$BGUnw.vWOJMUguWEwu5ydOGlq0RjIhl/bzPTl86NaZFtRRD2Fvcp2', 'student'),
(17, 'pacy@gmail.com', '$2y$10$sizCtCa0sRmOsGHNh3elCuAjPv/A9c/CbOLqVxF8Qect9ut8o0DWG', 'student'),
(18, 'minani@gmail.com', '$2y$10$8v86yCgN.VjIeWj7S.6Nbe3XbL4q.V.rD5DOmG4P7Q7eK796S38vG', 'instructor'),
(23, 'thgfb@gmail.com', '$2y$10$LnNZmoMNrgQV6Al2Cpmmsu/GesPkQzyh4EhV/u99oZomdawu59p8C', 'student'),
(24, 'gonzalezi@gmail.com', '$2y$10$WCKE.luA07o4jZ3XXL.fYuqlUYwA5s3w.8/.M/9Bs9u9.x19LdVgu', 'student'),
(25, 'gogo@gmail.com', '$2y$10$nft44hegItx9vOfOEr1OTOsCCPe7jTzJhl4hopUi7Hg.gEj6MEogW', 'instructor');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`instructor_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_3` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE;

--
-- Constraints for table `instructors`
--
ALTER TABLE `instructors`
  ADD CONSTRAINT `instructors_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
