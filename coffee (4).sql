-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2025 at 05:18 PM
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
-- Database: `coffee`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Đồ uống'),
(2, 'Đồ ăn');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `quantity` float DEFAULT 0,
  `min_quantity` float DEFAULT 10,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `name`, `unit`, `quantity`, `min_quantity`, `last_updated`) VALUES
(1, 'Hạt Cà phê', 'g', 5000, 500, '2025-12-19 16:06:01'),
(2, 'Sữa tươi', 'ml', 2000, 200, '2025-12-19 16:06:01'),
(3, 'Trà túi lọc (Đào)', 'túi', 48, 10, '2025-12-19 16:11:53'),
(4, 'Cam tươi', 'quả', 95, 20, '2025-12-19 16:11:53'),
(5, 'Bơ sáp', 'g', 3000, 500, '2025-12-19 16:06:01'),
(6, 'Bột mì', 'g', 10000, 1000, '2025-12-19 16:06:01'),
(7, 'Chocolate', 'g', 2000, 300, '2025-12-19 16:06:01'),
(8, 'Khoai tây đông lạnh', 'g', 4800, 1000, '2025-12-19 16:10:40');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_log`
--

CREATE TABLE `inventory_log` (
  `id` int(11) NOT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `type` enum('import','export') NOT NULL,
  `quantity` float NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_log`
--

INSERT INTO `inventory_log` (`id`, `ingredient_id`, `type`, `quantity`, `note`, `created_at`, `user_id`) VALUES
(1, 8, 'export', 200, 'Bán đơn hàng #50', '2025-12-19 16:10:40', 1),
(2, 4, 'export', 2, 'Bán đơn hàng #51', '2025-12-19 16:11:05', 1),
(3, 3, 'export', 1, 'Bán đơn hàng #51', '2025-12-19 16:11:05', 1),
(4, 4, 'export', 0.5, 'Bán đơn hàng #51', '2025-12-19 16:11:05', 1),
(5, 4, 'export', 2, 'Bán đơn hàng #52', '2025-12-19 16:11:38', 1),
(6, 3, 'export', 1, 'Bán đơn hàng #53', '2025-12-19 16:11:53', 1),
(7, 4, 'export', 0.5, 'Bán đơn hàng #53', '2025-12-19 16:11:53', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_price` int(11) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'not_paid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_date`, `total_price`, `status`) VALUES
(1, '2025-11-30 21:10:28', 0, 'not_paid'),
(2, '2025-12-07 14:17:59', 25000, 'not_paid'),
(3, '2025-12-07 14:20:16', 32000, 'not_paid'),
(4, '2025-12-07 14:27:34', 25000, 'not_paid'),
(5, '2025-12-07 14:30:47', 60000, 'not_paid'),
(6, '2025-12-11 18:15:18', 32000, 'paid'),
(7, '2025-12-11 18:39:01', 3748000, 'paid'),
(8, '2025-12-11 18:50:33', 90000, 'paid'),
(9, '2025-12-11 18:51:30', 450000, 'paid'),
(10, '2025-12-12 10:28:43', 2235000, 'paid'),
(11, '2025-12-12 10:29:44', 155000, 'paid'),
(12, '2025-12-12 10:30:16', 195000, 'paid'),
(13, '2025-12-12 10:30:33', 120000, 'paid'),
(14, '2025-12-12 10:39:33', 777000, 'paid'),
(15, '2025-12-12 10:47:35', 385000, 'paid'),
(16, '2025-12-12 11:07:07', 77000, 'paid'),
(17, '2025-12-12 11:25:10', 336000, 'paid'),
(18, '2025-12-17 20:52:56', 430000, 'paid'),
(19, '2025-12-17 20:53:28', 603000, 'paid'),
(20, '2025-12-17 22:37:26', 531000, 'paid'),
(21, '2025-12-17 22:38:00', 167000, 'paid'),
(22, '2025-12-17 22:44:08', 403000, 'paid'),
(23, '2025-12-17 22:44:21', 120000, 'paid'),
(24, '2025-12-17 22:44:26', 240000, 'paid'),
(25, '2025-12-17 22:44:37', 662000, 'paid'),
(26, '2025-12-17 22:44:43', 538000, 'paid'),
(27, '2025-12-17 22:44:50', 295000, 'paid'),
(28, '2025-12-17 22:46:45', 428000, 'paid'),
(29, '2025-12-17 22:47:22', 804000, 'paid'),
(30, '2025-12-17 22:47:30', 372000, 'paid'),
(31, '2025-12-17 22:49:29', 160000, 'paid'),
(32, '2025-12-17 22:49:50', 95000, 'paid'),
(33, '2025-12-17 22:50:10', 90000, 'paid'),
(34, '2025-12-17 22:50:40', 175000, 'paid'),
(35, '2025-12-17 22:51:28', 148000, 'paid'),
(36, '2025-12-17 22:51:39', 58000, 'paid'),
(37, '2025-12-17 22:51:50', 115000, 'paid'),
(38, '2025-12-17 22:52:08', 130000, 'paid'),
(39, '2025-12-17 22:54:56', 120000, 'paid'),
(40, '2025-12-17 22:58:55', 398000, 'paid'),
(41, '2025-12-18 08:55:24', 600000, 'paid'),
(42, '2025-12-18 09:12:43', 324000, 'paid'),
(43, '2025-12-18 09:12:47', 443000, 'paid'),
(44, '2025-12-18 09:32:34', 115000, 'paid'),
(45, '2025-12-18 03:39:06', 215000, 'paid'),
(46, '2025-12-18 03:44:01', 145000, 'paid'),
(47, '2025-12-18 03:53:57', 86000, 'paid'),
(48, '2025-12-18 04:09:32', 105000, 'paid'),
(49, '2025-12-19 16:31:16', 49000, 'paid'),
(50, '2025-12-19 17:10:40', 75000, 'paid'),
(51, '2025-12-19 17:11:05', 109000, 'paid'),
(52, '2025-12-19 17:11:38', 40000, 'paid'),
(53, '2025-12-19 17:11:53', 39000, 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`) VALUES
(1, 45, 5, 2),
(2, 45, 10, 1),
(3, 45, 4, 1),
(4, 45, 6, 1),
(5, 46, 6, 1),
(6, 46, 5, 2),
(7, 47, 1, 1),
(8, 47, 7, 1),
(9, 47, 8, 1),
(10, 48, 10, 2),
(11, 48, 2, 1),
(12, 49, 11, 1),
(13, 49, 6, 1),
(14, 50, 10, 1),
(15, 50, 9, 1),
(16, 51, 4, 1),
(17, 51, 3, 1),
(18, 51, 10, 1),
(19, 52, 4, 1),
(20, 53, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `category_id`, `image_url`, `is_active`, `status`) VALUES
(1, 'Cà phê Đen', 25000, 1, '../assets/img/drink/ca-phe-den.jpg', 1, 1),
(2, 'Latte Đá', 45000, 1, '../assets/img/drink/latte-da.jpg', 1, 1),
(3, 'Trà Đào Cam Sả', 39000, 1, '../assets/img/drink/tra-dao-cam-sa.jpg', 1, 1),
(4, 'Nước Ép Cam', 40000, 1, '../assets/img/drink/nuoc-ep-cam.png', 1, 1),
(5, 'Sinh Tố Bơ', 55000, 1, '../assets/img/drink/sinh-to-bo.avif', 1, 1),
(6, 'Bánh Mousse ', 34000, 2, '../assets/img/food/banh-mousse-chanh-leo.png', 1, 1),
(7, 'Bánh Mì Bơ Tỏi', 29000, 2, '../assets/img/food/banh-mi-bo-toi.webp', 1, 1),
(8, 'Bánh Muffin Chocolate', 32000, 2, '../assets/img/food/banh-muffin-chocolate.jpg', 1, 1),
(9, 'Khoai Tây Chiên', 45000, 2, '../assets/img/food/khoai-tay-chien.jpg', 1, 1),
(10, 'Bánh Tiramisu', 30000, 2, '../assets/img/food/banh-tiramisu.png', 1, 1),
(11, 'banhmi', 15000, 1, 'uploads/1766027605.webp', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity_required` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `product_id`, `ingredient_id`, `quantity_required`) VALUES
(1, 1, 1, 20),
(2, 2, 1, 20),
(3, 2, 2, 150),
(4, 3, 3, 1),
(5, 3, 4, 0.5),
(6, 4, 4, 2),
(7, 5, 5, 200),
(8, 5, 2, 50),
(9, 8, 6, 100),
(10, 8, 7, 30),
(11, 9, 8, 200);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'Admin', 'admin', '$2y$10$DxWZmJ0OdBN7Yp.HPXAUmusmf76YcwzMJjWF/QcIrBpqQnJtKpkPu', 'admin', 1, '2025-12-18 03:32:02'),
(2, 'Staff test', 'staff', '$2y$10$DxWZmJ0OdBN7Yp.HPXAUmusmf76YcwzMJjWF/QcIrBpqQnJtKpkPu', 'staff', 1, '2025-12-18 03:32:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `inventory_log`
--
ALTER TABLE `inventory_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD CONSTRAINT `inventory_log_ibfk_1` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_id` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `category_id` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
