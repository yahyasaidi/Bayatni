-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2025 at 03:46 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bayatni_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `guests` int(2) NOT NULL,
  `room_type` varchar(30) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('confirmed','pending','cancelled','completed','no_show') NOT NULL DEFAULT 'confirmed',
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `hotel_id`, `check_in`, `check_out`, `guests`, `room_type`, `total_price`, `booking_date`, `status`, `completed_at`) VALUES
(41, 3, 1, '2025-04-25', '2025-04-27', 1, 'standard', 770.00, '2025-04-24 01:51:45', 'confirmed', NULL),
(42, 3, 1, '2025-04-25', '2025-04-27', 1, 'standard', 770.00, '2025-04-24 01:51:54', 'confirmed', NULL),
(43, 3, 3, '2025-04-27', '2025-04-28', 2, 'standard', 192.60, '2025-04-26 17:33:08', 'confirmed', NULL),
(44, 3, 3, '2025-04-27', '2025-04-28', 2, 'standard', 192.60, '2025-04-26 17:33:17', 'pending', NULL),
(45, 3, 3, '2025-04-27', '2025-04-28', 2, 'standard', 192.60, '2025-04-26 17:34:19', 'pending', NULL),
(46, 3, 3, '2025-04-27', '2025-04-28', 2, 'standard', 192.60, '2025-04-26 17:34:39', 'pending', NULL),
(47, 3, 1, '2025-04-27', '2025-04-29', 1, 'standard', 770.00, '2025-04-26 17:38:35', 'confirmed', NULL),
(48, 3, 3, '2025-04-27', '2025-04-28', 2, 'standard', 192.60, '2025-04-26 17:44:33', 'confirmed', NULL),
(49, 3, 3, '2025-04-27', '2025-04-28', 2, 'standard', 192.60, '2025-04-26 17:44:45', 'confirmed', NULL),
(50, 3, 1, '2025-04-27', '2025-04-29', 1, 'standard', 770.00, '2025-04-26 17:44:59', 'confirmed', NULL),
(51, 3, 1, '2025-04-27', '2025-04-29', 1, 'standard', 770.00, '2025-04-26 17:56:30', 'confirmed', NULL),
(52, 3, 3, '2025-04-27', '2025-04-28', 2, 'standard', 192.60, '2025-04-26 18:00:28', 'confirmed', NULL),
(53, 3, 16, '2025-04-27', '2025-04-28', 2, 'standard', 128.40, '2025-04-26 18:29:57', 'confirmed', NULL),
(54, 3, 16, '2025-04-27', '2025-04-28', 2, 'standard', 128.40, '2025-04-26 18:30:27', 'confirmed', NULL),
(55, 3, 16, '2025-04-27', '2025-04-28', 2, 'standard', 120.00, '2025-04-26 18:46:44', 'confirmed', NULL),
(56, 3, 16, '2025-04-27', '2025-04-28', 1, 'standard', 120.00, '2025-04-26 19:03:57', 'confirmed', NULL),
(57, 3, 16, '2025-04-27', '2025-04-28', 1, 'standard', 120.00, '2025-04-26 19:11:40', 'confirmed', NULL),
(58, 3, 1, '2025-04-27', '2025-04-29', 1, 'standard', 770.00, '2025-04-26 19:19:06', 'confirmed', NULL),
(61, 3, 10, '2025-04-29', '2025-04-30', 1, 'standard', 186.00, '2025-04-28 09:13:29', 'confirmed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `rating` int(1) NOT NULL,
  `reviews_count` int(11) NOT NULL DEFAULT 0,
  `region` enum('tunis','hammamet','sousse','djerba','tabarka') NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `features` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `title`, `location`, `price`, `rating`, `reviews_count`, `region`, `image_url`, `features`, `created_at`) VALUES
(1, 'Hôtel La Marsa Resort & Spa', 'La Marsa, Tunis', 350.00, 5, 332, 'tunis', 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80', 'piscine, restaurant, spa', '2025-04-20 16:15:53'),
(3, 'Sousse Marhaba Beach', 'Sousse', 180.00, 3, 215, 'sousse', 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=80', 'piscine,plage', '2025-04-20 16:15:53'),
(6, 'Sidi Bou Said Maison Bleue', 'Sidi Bou Said, Tunis', 320.00, 4, 95, 'tunis', 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80', 'restaurant,vue mer', '2025-04-20 16:15:53'),
(10, 'Riadh Palms Resort & Spa', 'RJRH+8M Sousse', 186.00, 3, 1, 'sousse', 'https://lh3.googleusercontent.com/p/AF1QipNKMh8br5zDd4RQk52dQgBxQXRmWTePYY2nbo0w=s1360-w1360-h1020', 'piscine,plage,restaurant,spa', '2025-04-22 20:45:45'),
(11, 'Hôtel Marhaba Palace', 'VHWV+JC Hammam Sousse', 242.00, 4, 0, 'sousse', 'https://lh3.googleusercontent.com/p/AF1QipOvko8sKKUTYL5OoEYpa_DIA-Ts21E3I9ud3397=s1360-w1360-h1020', 'piscine,plage,restaurant,spa,wifi', '2025-04-22 20:50:40'),
(12, 'Mövenpick Hotel du Lac Tunis', 'R6PX+59 Tunis', 138.00, 4, 0, 'tunis', 'https://lh3.googleusercontent.com/p/AF1QipOb7Sa9X6kYk0EAPxH_NzRep8Kcnm_nEZ-jYNIa=s1360-w1360-h1020', 'piscine,restaurant,wifi', '2025-04-22 20:55:43'),
(13, 'Cap Bon Kelibia Beach Hotel & Spa', 'V44G+JC Kelibia', 293.00, 4, 0, 'tunis', 'https://lh3.googleusercontent.com/p/AF1QipOdZyei5JMkJhdwd4w-QHLvmad1RvS0Xf24ZbHE=s1360-w1360-h1020', 'piscine,plage,restaurant,wifi', '2025-04-22 20:59:58'),
(14, 'Radisson Blu Resort & Thalasso, Hammamet', 'CJ2Q+WG Hammamet', 414.00, 4, 0, 'hammamet', 'https://lh3.googleusercontent.com/p/AF1QipPawVc0aFZYwaBl8AR14KNH2rZQc6Y7N-To7NCS=s1360-w1360-h1020', 'piscine,plage,restaurant,spa,wifi', '2025-04-22 21:04:55'),
(15, 'Golden Tulip President Hammamet', 'CMFF+64 Hammamet', 246.00, 3, 0, 'hammamet', 'https://lh3.googleusercontent.com/p/AF1QipP4OKq22CIff11LUyPF_Fvmm-0ZjmGwa9XpF4Co=s1360-w1360-h1020', 'piscine,restaurant,wifi', '2025-04-22 21:07:42'),
(16, 'Marina Palace', '9GGR+HW Hammamet', 120.00, 4, 0, 'hammamet', 'https://lh3.googleusercontent.com/p/AF1QipOk6q2aQPu32rf3DH9QTG0gHl2Bqf65iF60eaZ4=s1360-w1360-h1020', 'piscine,restaurant,wifi', '2025-04-22 21:12:41'),
(17, 'Résidence Royal - Deluxe', 'CH3H+CW Hammamet', 92.00, 4, 0, 'hammamet', 'https://lh3.googleusercontent.com/p/AF1QipOxxIz9ofJaDJ0uU6kdQtIZxESr6VTpPuU1Df6S=s1360-w1360-h1020', 'piscine,restaurant,spa,wifi', '2025-04-22 21:16:43');

-- --------------------------------------------------------

--
-- Table structure for table `hotels_coordinates`
--

CREATE TABLE `hotels_coordinates` (
  `id` int(11) NOT NULL,
  `x` decimal(9,6) NOT NULL,
  `y` decimal(9,6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotels_coordinates`
--

INSERT INTO `hotels_coordinates` (`id`, `x`, `y`) VALUES
(1, 35.369828, 10.847062),
(3, 35.848310, 10.623020),
(6, 33.855221, 10.694081),
(10, 35.841747, 10.628713),
(11, 35.899838, 10.594444),
(12, 36.841779, 10.247541),
(13, 36.931578, 11.121286),
(14, 36.405711, 10.636754),
(15, 36.425189, 10.675034),
(16, 36.378765, 10.544400),
(17, 36.406461, 10.580318);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `rating` int(1) NOT NULL,
  `comment` text DEFAULT NULL,
  `review_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `hotel_id`, `booking_id`, `rating`, `comment`, `review_date`) VALUES
(10, 3, 10, 61, 5, 'cool', '2025-04-28 09:14:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `birthday` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('active','suspended') NOT NULL,
  `password` varchar(255) NOT NULL,
  `card_number` varchar(16) NOT NULL,
  `card_name` varchar(100) NOT NULL,
  `card_expire` varchar(5) NOT NULL,
  `card_cvc` varchar(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(64) NOT NULL,
  `token_expire` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `fullname`, `birthday`, `email`, `status`, `password`, `card_number`, `card_name`, `card_expire`, `card_cvc`, `created_at`, `reset_token`, `token_expire`) VALUES
(3, 'Dehech', 'Furat Dehech', 'Furat', '2005-01-04', 'fdehech@outlook.com', 'suspended', '$2y$10$VaCs7ZLbVfexP4ad2iilhuaLzQmpua6ADPdgOrZNUamQt3v9Js8l6', '0000000000000000', 'Furat', '12/25', '123', '2025-04-20 22:42:07', 'bd1e2d9603bceda35b7fa8787a2524485e797b8375927bc3a6692ce34e14b2cc', '2025-04-27 21:36:21'),
(8, 'Achref', 'Achour Achref', 'Achour', '2000-01-01', 'achref@achour.tn', 'suspended', '$2y$10$WWkPTq3g95rKYEmJZawTnuDV0cxCn1h/si106D7dTOK1vt8g2V9wC', '0000000000000000', 'Achref', '11/22', '123', '2025-04-22 22:59:17', '', '2025-04-23 00:15:17'),
(9, 'Larguech', 'Arije', 'Larguech Arije', '2000-01-01', 'arije@gmail.com', 'active', '$2y$10$BWdBTOUkTeFyShFCbKSqpODfFiH2gQIEs0tlOH8lZ7RwvSPUxAEIq', '0000000000000000', 'arije', '11/22', '123', '2025-04-26 19:12:58', '', '2025-04-26 20:12:58'),
(16, 'admin', 'admin', 'admin admin', '2000-01-02', 'admin@admin.tn', 'active', '$2y$10$Z4f7QgIP4o7HdI7j82PyNOJcw/2oS6GC746agiRhyGV7MLaVIJu.W', '0000000000000000', 'Furat', '11/22', '123', '2025-04-27 18:25:07', '', '0000-00-00 00:00:00'),
(17, 'Furat', 'Dahesh', 'Furat Dahesh', '2000-01-01', 'fdehech@gmail.com', 'active', '$2y$10$JdY5sGlsNrdcrcgBebZxyupW8sUWLbPUC7OvwN4VeoXjbYMo5l9/O', '0000000000000000', 'Furat', '11/22', '113', '2025-04-27 18:35:21', '64fb67249310cb190bf160aee212d8bc75be998c22ba40ebe6087912937ecae1', '2025-04-27 21:35:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_ibfk_1` (`user_id`),
  ADD KEY `bookings_ibfk_2` (`hotel_id`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hotels_coordinates`
--
ALTER TABLE `hotels_coordinates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `hotel_id` (`hotel_id`),
  ADD KEY `booking_id` (`booking_id`);

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
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hotels_coordinates`
--
ALTER TABLE `hotels_coordinates`
  ADD CONSTRAINT `hotels_coordinates_ibfk_1` FOREIGN KEY (`id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
