-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 17, 2025 at 06:50 PM
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
(40, '2025-12-17 22:58:55', 398000, 'paid');

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
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `category_id`, `image_url`, `is_active`) VALUES
(1, 'Cà phê Đen', 25000, 1, '../assets/img/drink/ca-phe-den.jpg', 1),
(2, 'Latte Đá', 45000, 1, '../assets/img/drink/latte-da.jpg', 1),
(3, 'Trà Đào Cam Sả', 39000, 1, '../assets/img/drink/tra-dao-cam-sa.jpg', 1),
(4, 'Nước Ép Cam', 40000, 1, '../assets/img/drink/nuoc-ep-cam.png', 1),
(5, 'Sinh Tố Bơ', 55000, 1, '../assets/img/drink/sinh-to-bo.avif', 1),
(6, 'Bánh Mousse Chanh Leo', 35000, 2, '../assets/img/food/banh-mousse-chanh-leo.png', 1),
(7, 'Bánh Mì Bơ Tỏi', 29000, 2, '../assets/img/food/banh-mi-bo-toi.webp', 1),
(8, 'Bánh Muffin Chocolate', 32000, 2, '../assets/img/food/banh-muffin-chocolate.jpg', 1),
(9, 'Khoai Tây Chiên', 45000, 2, '../assets/img/food/khoai-tay-chien.jpg', 1),
(10, 'Bánh Tiramisu', 30000, 2, '../assets/img/food/banh-tiramisu.png', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `category_id` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
