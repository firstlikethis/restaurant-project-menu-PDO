-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 23, 2024 at 10:15 AM
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
-- Database: `restaurant_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Main Dishes'),
(2, 'Salads'),
(3, 'Burgers'),
(4, 'Sushi'),
(5, 'Desserts');

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `name`, `description`, `price`, `category_id`, `picture`, `stock`) VALUES
(1, 'Chicken Curry', NULL, 8.50, 1, NULL, 10),
(2, 'Vegan Salad', NULL, 7.00, 1, NULL, 15),
(3, 'Beef Burger', NULL, 2.50, 2, NULL, 20),
(4, 'Sushi Platter', NULL, 12.00, 3, NULL, 5),
(5, 'Tomato Pasta', NULL, 7.50, 1, NULL, 10),
(6, 'Chocolate Cake', NULL, 4.00, 4, NULL, 10),
(7, 'Mushroom Risotto', NULL, 9.00, 1, NULL, 8),
(8, 'Pulled Pork Sandwich', NULL, 8.00, 2, NULL, 10),
(9, 'Green Tea', NULL, 1.50, 5, NULL, 30),
(10, 'Apple Pie', NULL, 3.00, 4, NULL, 12);

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `link` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `order_index` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `icon`, `link`, `parent_id`, `order_index`, `active`) VALUES
(1, 'Dashboard', 'fas fa-home', '/admin/index.php', NULL, 1, 1),
(2, 'Foods Management', 'fas fa-utensils', '/admin/manage_foods.php', NULL, 2, 1),
(3, 'Category Management', 'fas fa-object-group', '/admin/manage_categories.php', NULL, 3, 1),
(4, 'Orders Menagement', 'fas fa-shopping-cart', '/admin/manage_orders.php', NULL, 4, 1),
(5, 'Tables Management', 'fas fa-chair', '/admin/manage_tables.php', NULL, 5, 1),
(6, 'Users management', 'fas fa-users', '/admin/manage_users.php', NULL, 6, 1),
(7, 'Menu Items Menagement', 'fas fa-bars', '/admin/manage_menu_items.php', NULL, 7, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `table_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Processing','Completed','Cancelled') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `table_id`, `order_date`, `total_price`, `status`) VALUES
(1, 1, '2024-07-23 12:00:00', 18.00, 'Pending'),
(2, 2, '2024-07-23 12:15:00', 29.50, 'Processing'),
(3, 3, '2024-07-23 12:30:00', 21.00, 'Completed'),
(4, 4, '2024-07-23 12:45:00', 15.50, 'Cancelled'),
(5, 5, '2024-07-23 13:00:00', 26.50, 'Pending'),
(6, 6, '2024-07-23 13:15:00', 13.50, 'Processing'),
(7, 7, '2024-07-23 13:30:00', 21.00, 'Completed'),
(8, 8, '2024-07-23 13:45:00', 9.00, 'Cancelled'),
(9, 9, '2024-07-23 14:00:00', 12.00, 'Pending'),
(10, 10, '2024-07-23 14:15:00', 14.00, 'Processing');

-- --------------------------------------------------------

--
-- Table structure for table `order_line_items`
--

CREATE TABLE `order_line_items` (
  `order_id` int(11) DEFAULT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `line_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `order_line_items`
--

INSERT INTO `order_line_items` (`order_id`, `menu_id`, `quantity`, `line_total`) VALUES
(1, 1, 2, 17.00),
(1, 2, 1, 1.00),
(2, 3, 3, 7.50),
(2, 4, 2, 24.00),
(3, 5, 1, 7.50),
(3, 6, 2, 8.00),
(3, 7, 1, 9.00),
(4, 8, 1, 8.00),
(4, 9, 1, 1.50),
(4, 10, 2, 6.00),
(5, 1, 1, 8.50),
(5, 3, 2, 5.00),
(5, 5, 1, 7.50),
(6, 2, 3, 21.00),
(7, 6, 3, 12.00),
(7, 7, 1, 9.00),
(8, 8, 2, 16.00),
(9, 9, 2, 3.00),
(9, 10, 1, 3.00),
(10, 1, 1, 8.50),
(10, 2, 1, 7.00),
(10, 3, 1, 2.50);

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `id` int(11) NOT NULL,
  `table_number` int(11) NOT NULL,
  `status` enum('free','occupied') NOT NULL DEFAULT 'free'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tables`
--

INSERT INTO `tables` (`id`, `table_number`, `status`) VALUES
(21, 1, 'free'),
(22, 2, 'free'),
(23, 3, 'free'),
(24, 4, 'free'),
(25, 5, 'free'),
(26, 6, 'free'),
(27, 7, 'free'),
(28, 8, 'free'),
(29, 9, 'free'),
(30, 10, 'free');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('manager','employee') NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `nick_name` varchar(255) NOT NULL,
  `tels` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `full_name`, `nick_name`, `tels`) VALUES
(1, 'admin', '$2y$10$zzHnYoqL5lwd66xU8TwnCeRsyGSGNQvhJG5zqN5rwMYS2LcsD6E/2', 'manager', '', '', ''),
(2, 'user', '$2y$10$f6mFvI4hLSt4JMLhs6k08ufFAOqr9s.O0o8gl7pvwk5lD8fjPbLoW', 'employee', '', '', ''),
(3, 'manager', '$2y$10$5/az51hBezNTlmfY47O5J.lB9OLYRRDWyOv88ePr8Fb2oPZ5OR18y', 'manager', '', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_line_items`
--
ALTER TABLE `order_line_items`
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `table_number` (`table_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_line_items`
--
ALTER TABLE `order_line_items`
  ADD CONSTRAINT `order_line_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_line_items_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
