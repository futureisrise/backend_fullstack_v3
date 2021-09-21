-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: frozeneon-mysql
-- Generation Time: Sep 21, 2021 at 08:13 PM
-- Server version: 8.0.26
-- PHP Version: 7.4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test_task`
--

-- --------------------------------------------------------

--
-- Table structure for table `analytics`
--

CREATE TABLE `analytics` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `object` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `object_id` int DEFAULT NULL,
  `amount` int DEFAULT NULL,
  `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `boosterpack`
--

CREATE TABLE `boosterpack` (
  `id` int NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bank` decimal(10,2) NOT NULL DEFAULT '0.00',
  `us` int NOT NULL,
  `time_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `time_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `boosterpack`
--

INSERT INTO `boosterpack` (`id`, `price`, `bank`, `us`, `time_created`, `time_updated`) VALUES
(1, '5.00', '30.00', 1, '2021-09-17 13:24:28', '2021-09-21 19:40:23'),
(2, '20.00', '0.00', 2, '2021-09-17 13:24:28', '2021-09-17 13:24:28'),
(3, '50.00', '0.00', 5, '2021-09-17 13:24:28', '2021-09-17 13:24:28');

-- --------------------------------------------------------

--
-- Table structure for table `boosterpack_info`
--

CREATE TABLE `boosterpack_info` (
  `id` int NOT NULL,
  `boosterpack_id` int NOT NULL,
  `item_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `boosterpack_info`
--

INSERT INTO `boosterpack_info` (`id`, `boosterpack_id`, `item_id`) VALUES
(1, 1, 1),
(2, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `id` int NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `assign_id` int UNSIGNED NOT NULL,
  `reply_id` int DEFAULT NULL,
  `text` text NOT NULL,
  `likes` int DEFAULT NULL,
  `time_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `time_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`id`, `user_id`, `assign_id`, `reply_id`, `text`, `likes`, `time_created`, `time_updated`) VALUES
(1, 1, 1, 0, 'Comment #1', 0, '2021-09-17 13:24:29', '2021-09-19 22:47:04'),
(3, 1, 1, 0, 'sdfs', 1, '2021-09-19 21:51:25', '2021-09-20 07:01:41'),
(4, 1, 1, 3, 'sdfs', 0, '2021-09-19 21:54:22', '2021-09-20 07:01:23'),
(5, 1, 1, 0, 'sdfs', 1, '2021-09-19 21:55:22', '2021-09-20 07:06:57'),
(6, 1, 1, 0, 'sdfs', 0, '2021-09-19 22:03:23', '2021-09-20 07:01:20'),
(7, 1, 1, 0, 'sdfs', 0, '2021-09-19 22:03:23', '2021-09-20 07:01:27'),
(8, 1, 1, 0, 'sdfs', 0, '2021-09-19 22:03:24', '2021-09-20 07:01:25'),
(9, 1, 1, 0, 'sdfs', 0, '2021-09-19 22:03:38', '2021-09-20 07:01:30'),
(10, 1, 1, 0, 'sdf', 0, '2021-09-20 07:03:16', '2021-09-20 07:03:16');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int NOT NULL,
  `name` varchar(20) NOT NULL,
  `price` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `price`) VALUES
(1, '1 Likes', 1),
(2, '2 Likes', 2),
(3, '3 Likes', 3),
(4, '5 Likes', 5),
(5, '10 Likes', 10),
(6, '15 Likes', 15),
(7, '20 Likes', 20),
(8, '30 Likes', 30),
(9, '50 Likes', 50),
(10, '100 Likes', 100),
(11, '200 Likes', 200),
(12, '500 Likes', 500);

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `id` int NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `text` text NOT NULL,
  `img` varchar(1024) DEFAULT NULL,
  `likes` int DEFAULT NULL,
  `time_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `time_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `post`
--

INSERT INTO `post` (`id`, `user_id`, `text`, `img`, `likes`, `time_created`, `time_updated`) VALUES
(1, 1, 'Post 1', '/images/posts/1.png', 1, '2021-09-17 13:24:29', '2021-09-20 07:07:12'),
(2, 1, 'Post 2', '/images/posts/2.png', 0, '2021-09-17 13:24:29', '2021-09-17 13:24:29'),
(3, 1, 'Post 1', '/images/posts/1.png', 0, '2021-09-19 23:42:51', '2021-09-19 23:42:51');

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `amount` int NOT NULL,
  `external_id` varchar(255) NOT NULL,
  `type` enum('INCOME','WITHDROW','LIKES','BOOSTERPACK') NOT NULL,
  `info` text NOT NULL,
  `time_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_info`
--

CREATE TABLE `transaction_info` (
  `id` int NOT NULL,
  `type` enum('INCOME','WITHDROW','LIKES','BOOSTERPACK') NOT NULL,
  `info` text NOT NULL,
  `transaction_id` int NOT NULL,
  `boosterpack_id` int DEFAULT NULL,
  `boosterpack_price` int DEFAULT NULL,
  `item_id` int DEFAULT NULL,
  `item_price` int DEFAULT NULL,
  `time_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int UNSIGNED NOT NULL,
  `email` varchar(60) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  `personaname` varchar(50) NOT NULL DEFAULT '',
  `avatarfull` varchar(150) NOT NULL DEFAULT '',
  `rights` tinyint NOT NULL DEFAULT '0',
  `likes_balance` int DEFAULT '0',
  `wallet_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `wallet_total_refilled` decimal(10,2) NOT NULL DEFAULT '0.00',
  `wallet_total_withdrawn` decimal(10,2) NOT NULL DEFAULT '0.00',
  `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `email`, `password`, `personaname`, `avatarfull`, `rights`, `likes_balance`, `wallet_balance`, `wallet_total_refilled`, `wallet_total_withdrawn`, `time_created`, `time_updated`) VALUES
(1, 'admin@admin.pl', '12345', 'Admin User', 'https://steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/96/967871835afdb29f131325125d4395d55386c07a_full.jpg', 0, 5, '999870.00', '1000110.00', '240.00', '2021-09-17 13:24:29', '2021-09-21 19:40:23'),
(2, 'user@user.pl', '123', 'User #1', 'https://steamcdn-a.akamaihd.net/steamcommunity/public/images/avatars/86/86a0c845038332896455a566a1f805660a13609b_full.jpg', 0, 0, '0.00', '0.00', '0.00', '2021-09-17 13:24:29', '2021-09-17 13:24:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `analytics`
--
ALTER TABLE `analytics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `boosterpack`
--
ALTER TABLE `boosterpack`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `boosterpack_info`
--
ALTER TABLE `boosterpack_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaction_info`
--
ALTER TABLE `transaction_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `analytics`
--
ALTER TABLE `analytics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `boosterpack`
--
ALTER TABLE `boosterpack`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `boosterpack_info`
--
ALTER TABLE `boosterpack_info`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaction_info`
--
ALTER TABLE `transaction_info`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
