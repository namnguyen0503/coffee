-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 21, 2025 at 04:21 PM
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
(1, 'Hạt Cà phê', 'g', 5340, 500, '2025-12-21 05:12:26'),
(2, 'Sữa tươi', 'ml', 98650, 200, '2025-12-21 15:19:09'),
(3, 'Trà túi lọc (Đào)', 'túi', 1984, 10, '2025-12-21 08:39:16'),
(4, 'Cam tươi', 'quả', 19937.5, 20, '2025-12-21 10:00:45'),
(5, 'Bơ sáp', 'g', 16400, 500, '2025-12-21 15:19:09'),
(6, 'Bột mì', 'g', 50150, 1000, '2025-12-21 15:19:09'),
(7, 'Chocolate', 'g', 20020, 300, '2025-12-21 05:05:13'),
(8, 'Khoai tây đông lạnh', 'g', 14978, 1000, '2025-12-21 05:15:32'),
(9, 'Bánh mì ổ', 'ổ', 232, 10, '2025-12-21 08:53:18'),
(10, 'Bơ lạt', 'g', 4343430, 200, '2025-12-21 08:53:08'),
(11, 'Tỏi', 'g', 100, 50, '2025-12-21 05:15:32'),
(12, 'Lá Parsley khô', 'g', 60, 10, '2025-12-21 05:15:32'),
(13, 'Sữa đặc', 'ml', 400, 100, '2025-12-21 05:15:32');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_log`
--

CREATE TABLE `inventory_log` (
  `id` int(11) NOT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `type` enum('import','export') NOT NULL,
  `quantity` float NOT NULL,
  `cost` decimal(15,0) DEFAULT 0 COMMENT 'Tổng tiền nhập hàng',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_log`
--

INSERT INTO `inventory_log` (`id`, `ingredient_id`, `type`, `quantity`, `cost`, `note`, `created_at`, `user_id`) VALUES
(1, 8, 'export', 200, 0, 'Bán đơn hàng #50', '2025-12-19 16:10:40', 1),
(2, 4, 'export', 2, 0, 'Bán đơn hàng #51', '2025-12-19 16:11:05', 1),
(3, 3, 'export', 1, 0, 'Bán đơn hàng #51', '2025-12-19 16:11:05', 1),
(4, 4, 'export', 0.5, 0, 'Bán đơn hàng #51', '2025-12-19 16:11:05', 1),
(5, 4, 'export', 2, 0, 'Bán đơn hàng #52', '2025-12-19 16:11:38', 1),
(6, 3, 'export', 1, 0, 'Bán đơn hàng #53', '2025-12-19 16:11:53', 1),
(7, 4, 'export', 0.5, 0, 'Bán đơn hàng #53', '2025-12-19 16:11:53', 1),
(8, 3, 'import', 5, 0, 'Cozy', '2025-12-20 12:51:02', 3),
(9, 4, 'import', 20, 0, 'Chợ trời', '2025-12-20 12:51:47', 3),
(10, 5, 'export', 3000, 0, 'Bán đơn hàng #54', '2025-12-20 13:42:45', 1),
(11, 2, 'export', 750, 0, 'Bán đơn hàng #54', '2025-12-20 13:42:45', 1),
(12, 6, 'export', 1250, 0, 'Bán đơn hàng #55', '2025-12-20 13:44:40', 1),
(13, 2, 'export', 1250, 0, 'Bán đơn hàng #55', '2025-12-20 13:44:40', 1),
(14, 1, 'export', 500, 0, 'Bán đơn hàng #55', '2025-12-20 13:44:40', 1),
(15, 2, 'import', 50, 0, 'Vinamilk', '2025-12-20 13:51:10', 1),
(16, 5, 'import', 200, 0, '', '2025-12-20 14:16:55', 1),
(17, 5, 'import', 200, 0, '', '2025-12-20 14:17:11', 1),
(18, 5, 'import', 2000, 0, '', '2025-12-20 14:19:13', 1),
(19, 2, 'import', 4000, 0, '', '2025-12-20 14:19:19', 1),
(20, 3, 'import', 20, 50000, 'Cozy', '2025-12-20 14:25:12', 1),
(21, 6, 'export', 50, 0, 'Bán đơn hàng #56', '2025-12-20 16:59:59', 1),
(22, 2, 'export', 100, 0, 'Bán đơn hàng #56', '2025-12-20 16:59:59', 1),
(23, 5, 'export', 400, 0, 'Bán đơn hàng #56', '2025-12-20 16:59:59', 1),
(24, 2, 'export', 100, 0, 'Bán đơn hàng #56', '2025-12-20 16:59:59', 1),
(25, 6, 'export', 300, 0, 'Bán đơn hàng #57', '2025-12-20 17:02:04', 1),
(26, 2, 'export', 600, 0, 'Bán đơn hàng #57', '2025-12-20 17:02:04', 1),
(27, 5, 'export', 200, 0, 'Bán đơn hàng #57', '2025-12-20 17:02:04', 1),
(28, 2, 'export', 50, 0, 'Bán đơn hàng #57', '2025-12-20 17:02:04', 1),
(29, 6, 'export', 50, 0, 'Bán đơn hàng #58', '2025-12-20 17:02:13', 1),
(30, 2, 'export', 50, 0, 'Bán đơn hàng #58', '2025-12-20 17:02:13', 1),
(31, 1, 'export', 20, 0, 'Bán đơn hàng #58', '2025-12-20 17:02:13', 1),
(32, 8, 'export', 400, 0, 'Bán đơn hàng #58', '2025-12-20 17:02:13', 1),
(33, 6, 'export', 150, 0, 'Bán đơn hàng #59', '2025-12-20 17:03:22', 1),
(34, 2, 'export', 300, 0, 'Bán đơn hàng #59', '2025-12-20 17:03:22', 1),
(35, 5, 'export', 1800, 0, 'Bán đơn hàng #60', '2025-12-20 17:04:33', 1),
(36, 2, 'export', 450, 0, 'Bán đơn hàng #60', '2025-12-20 17:04:33', 1),
(37, 6, 'export', 1200, 0, 'Bán đơn hàng #63', '2025-12-20 17:06:03', 1),
(38, 2, 'export', 2400, 0, 'Bán đơn hàng #63', '2025-12-20 17:06:03', 1),
(39, 3, 'export', 73, 0, 'Bán đơn hàng #64', '2025-12-20 17:13:14', 1),
(40, 4, 'export', 36.5, 0, 'Bán đơn hàng #64', '2025-12-20 17:13:14', 1),
(41, 4, 'export', 78, 0, 'Bán đơn hàng #66', '2025-12-20 17:13:38', 1),
(42, 8, 'export', 4400, 0, 'Bán đơn hàng #67', '2025-12-20 17:14:49', 1),
(43, 6, 'export', 6600, 0, 'Bán đơn hàng #68', '2025-12-20 17:15:31', 1),
(44, 7, 'export', 1980, 0, 'Bán đơn hàng #68', '2025-12-20 17:15:31', 1),
(45, 1, 'export', 20, 0, 'Bán đơn hàng #69', '2025-12-20 17:16:26', 1),
(46, 1, 'export', 4460, 0, 'Bán đơn hàng #70', '2025-12-20 17:16:33', 1),
(47, 1, 'import', 12000, 5000000, '', '2025-12-20 17:20:17', 1),
(48, 4, 'import', 515, 1000000, '', '2025-12-20 17:20:34', 1),
(49, 4, 'export', 6, 0, 'Bán đơn hàng #73', '2025-12-20 17:20:52', 1),
(50, 4, 'export', 24, 0, 'Bán đơn hàng #74', '2025-12-20 17:23:09', 1),
(51, 4, 'export', 6, 0, 'Bán đơn hàng #75', '2025-12-20 17:23:22', 1),
(52, 4, 'export', 8, 0, 'Bán đơn hàng #76', '2025-12-20 17:23:31', 1),
(53, 4, 'export', 470, 0, 'Bán đơn hàng #77', '2025-12-20 17:23:43', 1),
(54, 2, 'import', 50000, 50000, 'Vinamilk', '2025-12-21 05:03:19', 1),
(55, 6, 'import', 50000, 0, '', '2025-12-21 05:03:44', 1),
(56, 3, 'import', 2000, 0, '', '2025-12-21 05:03:59', 1),
(57, 5, 'import', 20000, 0, '', '2025-12-21 05:04:32', 1),
(58, 8, 'import', 15778, 0, '', '2025-12-21 05:04:39', 1),
(59, 4, 'import', 20000, 430000, '', '2025-12-21 05:04:59', 1),
(60, 7, 'import', 20000, 51000, '', '2025-12-21 05:05:13', 1),
(61, 3, 'export', 3, 0, 'Bán đơn hàng #78', '2025-12-21 05:06:04', 1),
(62, 4, 'export', 1.5, 0, 'Bán đơn hàng #78', '2025-12-21 05:06:04', 1),
(63, 4, 'export', 22, 0, 'Bán đơn hàng #79', '2025-12-21 05:12:26', 2),
(64, 1, 'export', 6660, 0, 'Bán đơn hàng #79', '2025-12-21 05:12:26', 2),
(65, 2, 'export', 49950, 0, 'Bán đơn hàng #79', '2025-12-21 05:12:26', 2),
(66, 5, 'export', 200, 0, 'Bán đơn hàng #80', '2025-12-21 05:15:04', 2),
(67, 2, 'export', 50, 0, 'Bán đơn hàng #80', '2025-12-21 05:15:04', 2),
(68, 8, 'export', 800, 0, 'Bán đơn hàng #81', '2025-12-21 05:15:32', 2),
(69, 9, 'export', 40, 0, 'Bán đơn hàng #81', '2025-12-21 05:15:32', 2),
(70, 10, 'export', 1000, 0, 'Bán đơn hàng #81', '2025-12-21 05:15:32', 2),
(71, 11, 'export', 400, 0, 'Bán đơn hàng #81', '2025-12-21 05:15:32', 2),
(72, 12, 'export', 40, 0, 'Bán đơn hàng #81', '2025-12-21 05:15:32', 2),
(73, 13, 'export', 600, 0, 'Bán đơn hàng #81', '2025-12-21 05:15:32', 2),
(74, 3, 'export', 1, 0, 'Bán đơn hàng #82', '2025-12-21 05:18:11', 2),
(75, 4, 'export', 0.5, 0, 'Bán đơn hàng #82', '2025-12-21 05:18:11', 2),
(76, 3, 'export', 2, 0, 'Bán đơn hàng #83', '2025-12-21 05:23:08', 2),
(77, 4, 'export', 1, 0, 'Bán đơn hàng #83', '2025-12-21 05:23:08', 2),
(78, 4, 'export', 8, 0, 'Bán đơn hàng #84', '2025-12-21 05:24:41', 1),
(79, 3, 'export', 1, 0, 'Bán đơn hàng #85', '2025-12-21 05:30:22', 2),
(80, 4, 'export', 0.5, 0, 'Bán đơn hàng #85', '2025-12-21 05:30:22', 2),
(81, 3, 'export', 4, 0, 'Bán đơn #89', '2025-12-21 08:22:27', 2),
(82, 4, 'export', 2, 0, 'Bán đơn #89', '2025-12-21 08:22:27', 2),
(83, 3, 'export', 1, 0, 'Bán đơn #90', '2025-12-21 08:28:53', 2),
(84, 4, 'export', 0.5, 0, 'Bán đơn #90', '2025-12-21 08:28:53', 2),
(85, 4, 'export', 2, 0, 'Bán đơn #91', '2025-12-21 08:29:30', 2),
(86, 3, 'export', 1, 0, 'Bán đơn #92', '2025-12-21 08:30:50', 2),
(87, 4, 'export', 0.5, 0, 'Bán đơn #92', '2025-12-21 08:30:50', 2),
(88, 4, 'export', 2, 0, 'Bán đơn #93', '2025-12-21 08:31:07', 2),
(89, 4, 'export', 2, 0, 'Bán đơn #94', '2025-12-21 08:33:48', 2),
(90, 4, 'export', 2, 0, 'Bán đơn #95', '2025-12-21 08:33:58', 2),
(91, 3, 'export', 1, 0, 'Bán đơn #96', '2025-12-21 08:34:17', 2),
(92, 4, 'export', 0.5, 0, 'Bán đơn #96', '2025-12-21 08:34:17', 2),
(93, 3, 'export', 1, 0, 'Bán đơn #97', '2025-12-21 08:38:59', 2),
(94, 4, 'export', 0.5, 0, 'Bán đơn #97', '2025-12-21 08:38:59', 2),
(95, 3, 'export', 1, 0, 'Bán đơn #98', '2025-12-21 08:39:16', 2),
(96, 4, 'export', 0.5, 0, 'Bán đơn #98', '2025-12-21 08:39:16', 2),
(97, 4, 'export', 2, 0, 'Bán đơn #99', '2025-12-21 08:44:38', 2),
(98, 4, 'export', 2, 0, 'Bán đơn #100', '2025-12-21 08:44:56', 2),
(99, 4, 'export', 2, 0, 'Bán đơn #101', '2025-12-21 08:48:33', 2),
(100, 4, 'export', 2, 0, 'Bán đơn #102', '2025-12-21 08:52:08', 2),
(101, 2, 'import', 100000, 5000000, 'Vinam', '2025-12-21 08:53:00', 1),
(102, 10, 'import', 4343430, 500000, 'NCC', '2025-12-21 08:53:08', 1),
(103, 9, 'import', 222, 50000000, '', '2025-12-21 08:53:18', 1),
(104, 5, 'export', 200, 0, 'Bán đơn #103', '2025-12-21 08:53:25', 1),
(105, 2, 'export', 50, 0, 'Bán đơn #103', '2025-12-21 08:53:25', 1),
(106, 4, 'export', 6, 0, 'Bán đơn #104', '2025-12-21 08:54:05', 1),
(107, 5, 'export', 200, 0, 'Bán đơn #104', '2025-12-21 08:54:05', 1),
(108, 2, 'export', 50, 0, 'Bán đơn #104', '2025-12-21 08:54:05', 1),
(109, 5, 'export', 600, 0, 'Bán đơn #105', '2025-12-21 09:09:00', 1),
(110, 2, 'export', 150, 0, 'Bán đơn #105', '2025-12-21 09:09:00', 1),
(111, 5, 'export', 1000, 0, 'Bán đơn #106', '2025-12-21 09:19:37', 1),
(112, 2, 'export', 250, 0, 'Bán đơn #106', '2025-12-21 09:19:37', 1),
(113, 4, 'export', 2, 0, 'Bán đơn #107', '2025-12-21 09:46:03', 1),
(114, 5, 'export', 200, 0, 'Bán đơn #108', '2025-12-21 09:48:02', 1),
(115, 2, 'export', 50, 0, 'Bán đơn #108', '2025-12-21 09:48:02', 1),
(116, 6, 'export', 150, 0, 'Bán đơn #109', '2025-12-21 09:48:44', 1),
(117, 2, 'export', 300, 0, 'Bán đơn #109', '2025-12-21 09:48:44', 1),
(118, 5, 'export', 800, 0, 'Bán đơn #110', '2025-12-21 09:55:33', 2),
(119, 2, 'export', 200, 0, 'Bán đơn #110', '2025-12-21 09:55:33', 2),
(120, 4, 'export', 2, 0, 'Bán đơn #111', '2025-12-21 10:00:45', 2),
(121, 6, 'export', 50, 0, 'Bán đơn #112', '2025-12-21 10:01:13', 2),
(122, 2, 'export', 100, 0, 'Bán đơn #112', '2025-12-21 10:01:13', 2),
(123, 6, 'export', 50, 0, 'Bán đơn #113', '2025-12-21 15:19:09', 1),
(124, 2, 'export', 100, 0, 'Bán đơn #113', '2025-12-21 15:19:09', 1),
(125, 5, 'export', 400, 0, 'Bán đơn #113', '2025-12-21 15:19:09', 1),
(126, 2, 'export', 100, 0, 'Bán đơn #113', '2025-12-21 15:19:09', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_price` int(11) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'not_paid',
  `session_id` int(11) DEFAULT NULL,
  `voucher_code` varchar(50) DEFAULT NULL,
  `discount_percent` decimal(5,2) DEFAULT 0.00,
  `final_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `total_price`, `status`, `session_id`, `voucher_code`, `discount_percent`, `final_amount`) VALUES
(1, 1, '2025-11-30 21:10:28', 0, 'not_paid', NULL, NULL, 0.00, 0.00),
(2, 1, '2025-12-07 14:17:59', 25000, 'not_paid', NULL, NULL, 0.00, 0.00),
(3, 1, '2025-12-07 14:20:16', 32000, 'not_paid', NULL, NULL, 0.00, 0.00),
(4, 1, '2025-12-07 14:27:34', 25000, 'not_paid', NULL, NULL, 0.00, 0.00),
(5, 1, '2025-12-07 14:30:47', 60000, 'not_paid', NULL, NULL, 0.00, 0.00),
(6, 1, '2025-12-11 18:15:18', 32000, 'paid', NULL, NULL, 0.00, 0.00),
(7, 1, '2025-12-11 18:39:01', 3748000, 'paid', NULL, NULL, 0.00, 0.00),
(8, 1, '2025-12-11 18:50:33', 90000, 'paid', NULL, NULL, 0.00, 0.00),
(9, 1, '2025-12-11 18:51:30', 450000, 'paid', NULL, NULL, 0.00, 0.00),
(10, 1, '2025-12-12 10:28:43', 2235000, 'paid', NULL, NULL, 0.00, 0.00),
(11, 1, '2025-12-12 10:29:44', 155000, 'paid', NULL, NULL, 0.00, 0.00),
(12, 1, '2025-12-12 10:30:16', 195000, 'paid', NULL, NULL, 0.00, 0.00),
(13, 1, '2025-12-12 10:30:33', 120000, 'paid', NULL, NULL, 0.00, 0.00),
(14, 1, '2025-12-12 10:39:33', 777000, 'paid', NULL, NULL, 0.00, 0.00),
(15, 1, '2025-12-12 10:47:35', 385000, 'paid', NULL, NULL, 0.00, 0.00),
(16, 1, '2025-12-12 11:07:07', 77000, 'paid', NULL, NULL, 0.00, 0.00),
(17, 1, '2025-12-12 11:25:10', 336000, 'paid', NULL, NULL, 0.00, 0.00),
(18, 1, '2025-12-17 20:52:56', 430000, 'paid', NULL, NULL, 0.00, 0.00),
(19, 1, '2025-12-17 20:53:28', 603000, 'paid', NULL, NULL, 0.00, 0.00),
(20, 1, '2025-12-17 22:37:26', 531000, 'paid', NULL, NULL, 0.00, 0.00),
(21, 1, '2025-12-17 22:38:00', 167000, 'paid', NULL, NULL, 0.00, 0.00),
(22, 1, '2025-12-17 22:44:08', 403000, 'paid', NULL, NULL, 0.00, 0.00),
(23, 1, '2025-12-17 22:44:21', 120000, 'paid', NULL, NULL, 0.00, 0.00),
(24, 1, '2025-12-17 22:44:26', 240000, 'paid', NULL, NULL, 0.00, 0.00),
(25, 1, '2025-12-17 22:44:37', 662000, 'paid', NULL, NULL, 0.00, 0.00),
(26, 1, '2025-12-17 22:44:43', 538000, 'paid', NULL, NULL, 0.00, 0.00),
(27, 1, '2025-12-17 22:44:50', 295000, 'paid', NULL, NULL, 0.00, 0.00),
(28, 1, '2025-12-17 22:46:45', 428000, 'paid', NULL, NULL, 0.00, 0.00),
(29, 1, '2025-12-17 22:47:22', 804000, 'paid', NULL, NULL, 0.00, 0.00),
(30, 1, '2025-12-17 22:47:30', 372000, 'paid', NULL, NULL, 0.00, 0.00),
(31, 1, '2025-12-17 22:49:29', 160000, 'paid', NULL, NULL, 0.00, 0.00),
(32, 1, '2025-12-17 22:49:50', 95000, 'paid', NULL, NULL, 0.00, 0.00),
(33, 1, '2025-12-17 22:50:10', 90000, 'paid', NULL, NULL, 0.00, 0.00),
(34, 1, '2025-12-17 22:50:40', 175000, 'paid', NULL, NULL, 0.00, 0.00),
(35, 1, '2025-12-17 22:51:28', 148000, 'paid', NULL, NULL, 0.00, 0.00),
(36, 1, '2025-12-17 22:51:39', 58000, 'paid', NULL, NULL, 0.00, 0.00),
(37, 1, '2025-12-17 22:51:50', 115000, 'paid', NULL, NULL, 0.00, 0.00),
(38, 1, '2025-12-17 22:52:08', 130000, 'paid', NULL, NULL, 0.00, 0.00),
(39, 1, '2025-12-17 22:54:56', 120000, 'paid', NULL, NULL, 0.00, 0.00),
(40, 1, '2025-12-17 22:58:55', 398000, 'paid', NULL, NULL, 0.00, 0.00),
(41, 1, '2025-12-18 08:55:24', 600000, 'paid', NULL, NULL, 0.00, 0.00),
(42, 1, '2025-12-18 09:12:43', 324000, 'paid', NULL, NULL, 0.00, 0.00),
(43, 1, '2025-12-18 09:12:47', 443000, 'paid', NULL, NULL, 0.00, 0.00),
(44, 1, '2025-12-18 09:32:34', 115000, 'paid', NULL, NULL, 0.00, 0.00),
(45, 1, '2025-12-18 03:39:06', 215000, 'paid', NULL, NULL, 0.00, 0.00),
(46, 1, '2025-12-18 03:44:01', 145000, 'paid', NULL, NULL, 0.00, 0.00),
(47, 1, '2025-12-18 03:53:57', 86000, 'paid', NULL, NULL, 0.00, 0.00),
(48, 1, '2025-12-18 04:09:32', 105000, 'paid', NULL, NULL, 0.00, 0.00),
(49, 1, '2025-12-19 16:31:16', 49000, 'paid', NULL, NULL, 0.00, 0.00),
(50, 1, '2025-12-19 17:10:40', 75000, 'paid', NULL, NULL, 0.00, 0.00),
(51, 1, '2025-12-19 17:11:05', 109000, 'paid', NULL, NULL, 0.00, 0.00),
(52, 1, '2025-12-19 17:11:38', 40000, 'paid', NULL, NULL, 0.00, 0.00),
(53, 1, '2025-12-19 17:11:53', 39000, 'paid', NULL, NULL, 0.00, 0.00),
(54, 1, '2025-12-20 14:42:45', 825000, 'paid', NULL, NULL, 0.00, 0.00),
(55, 1, '2025-12-20 14:44:40', 750000, 'paid', NULL, NULL, 0.00, 0.00),
(56, 1, '2025-12-20 17:59:59', 144000, 'paid', NULL, NULL, 0.00, 0.00),
(57, 1, '2025-12-20 18:02:04', 259000, 'paid', NULL, NULL, 0.00, 0.00),
(58, 1, '2025-12-20 18:02:13', 120000, 'paid', NULL, NULL, 0.00, 0.00),
(59, 1, '2025-12-20 18:03:22', 102000, 'paid', NULL, NULL, 0.00, 0.00),
(60, 1, '2025-12-20 18:04:33', 495000, 'paid', NULL, NULL, 0.00, 0.00),
(63, 1, '2025-12-20 18:06:03', 816000, 'paid', NULL, NULL, 0.00, 0.00),
(64, 1, '2025-12-20 18:13:14', 2847000, 'paid', NULL, NULL, 0.00, 0.00),
(66, 1, '2025-12-20 18:13:38', 1560000, 'paid', NULL, NULL, 0.00, 0.00),
(67, 1, '2025-12-20 18:14:49', 990000, 'paid', NULL, NULL, 0.00, 0.00),
(68, 1, '2025-12-20 18:15:31', 2112000, 'paid', NULL, NULL, 0.00, 0.00),
(69, 1, '2025-12-20 18:16:26', 28909000, 'paid', NULL, NULL, 0.00, 0.00),
(70, 1, '2025-12-20 18:16:33', 34459000, 'paid', NULL, NULL, 0.00, 0.00),
(72, 1, '2025-12-20 18:16:42', 28942000, 'paid', NULL, NULL, 0.00, 0.00),
(73, 1, '2025-12-20 18:20:52', 120000, 'paid', NULL, NULL, 0.00, 0.00),
(74, 1, '2025-12-20 18:23:09', 480000, 'paid', NULL, NULL, 0.00, 0.00),
(75, 1, '2025-12-20 18:23:22', 120000, 'paid', NULL, NULL, 0.00, 0.00),
(76, 1, '2025-12-20 18:23:31', 160000, 'paid', NULL, NULL, 0.00, 0.00),
(77, 1, '2025-12-20 18:23:43', 9400000, 'paid', NULL, NULL, 0.00, 0.00),
(78, 1, '2025-12-21 06:06:04', 117000, 'paid', NULL, NULL, 0.00, 0.00),
(79, 2, '2025-12-21 06:12:26', 15425000, 'paid', NULL, NULL, 0.00, 0.00),
(80, 2, '2025-12-21 06:15:04', 55000, 'paid', NULL, NULL, 0.00, 0.00),
(81, 2, '2025-12-21 06:15:32', 1340000, 'paid', NULL, NULL, 0.00, 0.00),
(82, 2, '2025-12-21 06:18:11', 39000, 'paid', NULL, NULL, 0.00, 0.00),
(83, 2, '2025-12-21 06:23:08', 78000, 'paid', NULL, NULL, 0.00, 0.00),
(84, 1, '2025-12-21 06:24:41', 160000, 'paid', NULL, NULL, 0.00, 0.00),
(85, 2, '2025-12-21 06:30:22', 39000, 'paid', NULL, NULL, 0.00, 0.00),
(89, 2, '2025-12-21 15:22:27', 156000, 'paid', NULL, NULL, 0.00, 0.00),
(90, 2, '2025-12-21 15:28:53', 39000, 'paid', NULL, NULL, 0.00, 0.00),
(91, 2, '2025-12-21 15:29:30', 40000, 'paid', NULL, NULL, 0.00, 0.00),
(92, 2, '2025-12-21 15:30:50', 39000, 'paid', NULL, NULL, 0.00, 0.00),
(93, 2, '2025-12-21 15:31:07', 40000, 'paid', NULL, NULL, 0.00, 0.00),
(94, 2, '2025-12-21 15:33:48', 40000, 'paid', NULL, NULL, 0.00, 0.00),
(95, 2, '2025-12-21 15:33:58', 40000, 'paid', NULL, NULL, 0.00, 0.00),
(96, 2, '2025-12-21 15:34:17', 39000, 'paid', NULL, NULL, 0.00, 0.00),
(97, 2, '2025-12-21 15:38:59', 39000, 'paid', NULL, NULL, 0.00, 0.00),
(98, 2, '2025-12-21 15:39:16', 39000, 'paid', NULL, NULL, 0.00, 0.00),
(99, 2, '2025-12-21 15:44:38', 40000, 'paid', NULL, NULL, 0.00, 0.00),
(100, 2, '2025-12-21 15:44:56', 40000, 'paid', NULL, NULL, 0.00, 0.00),
(101, 2, '2025-12-21 15:48:33', 40000, 'paid', NULL, NULL, 0.00, 0.00),
(102, 2, '2025-12-21 15:52:08', 40000, 'paid', NULL, NULL, 0.00, 0.00),
(103, 1, '2025-12-21 15:53:25', 55000, 'paid', NULL, NULL, 0.00, 0.00),
(104, 1, '2025-12-21 15:54:05', 175000, 'paid', NULL, NULL, 0.00, 0.00),
(105, 1, '2025-12-21 16:09:00', 165000, 'paid', NULL, NULL, 0.00, 0.00),
(106, 1, '2025-12-21 16:19:37', 275000, 'paid', 2, NULL, 0.00, 0.00),
(107, 1, '2025-12-21 16:46:03', 40000, 'paid', 3, 'ADMINVIP', 100.00, 0.00),
(108, 1, '2025-12-21 16:48:02', 55000, 'paid', 3, 'WELCOME', 10.00, 49500.00),
(109, 1, '2025-12-21 16:48:44', 102000, 'paid', 3, '', 0.00, 102000.00),
(110, 2, '2025-12-21 16:55:33', 220000, 'paid', 4, 'WELCOME', 10.00, 198000.00),
(111, 2, '2025-12-21 17:00:45', 40000, 'canceled', 4, '', 0.00, 40000.00),
(112, 2, '2025-12-21 17:01:13', 34000, 'paid', 4, 'WELCOME', 10.00, 30600.00),
(113, 1, '2025-12-21 22:19:09', 144000, 'paid', 6, '', 0.00, 144000.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `note`) VALUES
(1, 45, 5, 2, NULL),
(2, 45, 10, 1, NULL),
(3, 45, 4, 1, NULL),
(4, 45, 6, 1, NULL),
(5, 46, 6, 1, NULL),
(6, 46, 5, 2, NULL),
(7, 47, 1, 1, NULL),
(8, 47, 7, 1, NULL),
(9, 47, 8, 1, NULL),
(10, 48, 10, 2, NULL),
(11, 48, 2, 1, NULL),
(12, 49, 11, 1, NULL),
(13, 49, 6, 1, NULL),
(14, 50, 10, 1, NULL),
(15, 50, 9, 1, NULL),
(16, 51, 4, 1, NULL),
(17, 51, 3, 1, NULL),
(18, 51, 10, 1, NULL),
(19, 52, 4, 1, NULL),
(20, 53, 3, 1, NULL),
(21, 54, 5, 15, NULL),
(22, 55, 10, 25, NULL),
(23, 56, 6, 1, NULL),
(24, 56, 5, 2, NULL),
(25, 57, 6, 6, NULL),
(26, 57, 5, 1, NULL),
(27, 58, 10, 1, NULL),
(28, 58, 9, 2, NULL),
(29, 59, 6, 3, NULL),
(30, 60, 5, 9, NULL),
(33, 63, 6, 24, NULL),
(34, 64, 3, 73, NULL),
(36, 66, 4, 39, NULL),
(37, 67, 9, 22, NULL),
(38, 68, 8, 66, NULL),
(39, 69, 1, 1, NULL),
(40, 69, 7, 996, NULL),
(41, 70, 1, 223, NULL),
(42, 70, 7, 996, NULL),
(45, 72, 7, 998, NULL),
(46, 73, 4, 3, NULL),
(47, 74, 4, 12, NULL),
(48, 75, 4, 3, NULL),
(49, 76, 4, 4, NULL),
(50, 77, 4, 235, NULL),
(51, 78, 3, 3, NULL),
(52, 79, 4, 11, NULL),
(53, 79, 2, 333, NULL),
(54, 80, 5, 1, NULL),
(55, 81, 9, 4, NULL),
(56, 81, 7, 40, NULL),
(57, 82, 3, 1, NULL),
(58, 83, 3, 2, NULL),
(59, 84, 4, 4, NULL),
(60, 85, 3, 1, NULL),
(61, 89, 3, 4, 'Không đá'),
(62, 90, 3, 1, ''),
(63, 91, 4, 1, ''),
(64, 92, 3, 1, ''),
(65, 93, 4, 1, ''),
(66, 94, 4, 1, ''),
(67, 95, 4, 1, ''),
(68, 96, 3, 1, ''),
(69, 97, 3, 1, ''),
(70, 98, 3, 1, ''),
(71, 99, 4, 1, ''),
(72, 100, 4, 1, ''),
(73, 101, 4, 1, ''),
(74, 102, 4, 1, ''),
(75, 103, 5, 1, ''),
(76, 104, 4, 3, 'Ít đá, Mang về'),
(77, 104, 5, 1, ''),
(78, 105, 5, 3, ''),
(79, 106, 5, 5, ''),
(80, 107, 4, 1, ''),
(81, 108, 5, 1, ''),
(82, 109, 6, 3, ''),
(83, 110, 5, 4, ''),
(84, 111, 4, 1, ''),
(85, 112, 6, 1, ''),
(86, 113, 6, 1, ''),
(87, 113, 5, 2, '');

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
(11, 9, 8, 200),
(12, 6, 6, 50),
(13, 6, 2, 100),
(14, 10, 6, 50),
(15, 10, 2, 50),
(16, 10, 1, 20),
(17, 7, 9, 1),
(18, 7, 10, 25),
(19, 7, 11, 10),
(20, 7, 12, 1),
(21, 7, 13, 15);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','wh-staff') NOT NULL DEFAULT 'staff',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'Admin', 'admin', '$2y$10$DxWZmJ0OdBN7Yp.HPXAUmusmf76YcwzMJjWF/QcIrBpqQnJtKpkPu', 'admin', 1, '2025-12-18 03:32:02'),
(2, 'Staff test', 'staff', '$2y$10$DxWZmJ0OdBN7Yp.HPXAUmusmf76YcwzMJjWF/QcIrBpqQnJtKpkPu', 'staff', 1, '2025-12-18 03:32:02'),
(3, 'Thủ kho', 'thukho', '$2y$10$DxWZmJ0OdBN7Yp.HPXAUmusmf76YcwzMJjWF/QcIrBpqQnJtKpkPu', 'wh-staff', 1, '2025-12-20 11:06:00');

-- --------------------------------------------------------

--
-- Table structure for table `work_sessions`
--

CREATE TABLE `work_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_time` datetime DEFAULT current_timestamp(),
  `end_time` datetime DEFAULT NULL,
  `start_cash` decimal(10,2) DEFAULT 0.00,
  `end_cash` decimal(10,2) DEFAULT 0.00,
  `total_sales` decimal(10,2) DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_sessions`
--

INSERT INTO `work_sessions` (`id`, `user_id`, `start_time`, `end_time`, `start_cash`, `end_cash`, `total_sales`, `note`, `status`) VALUES
(1, 1, '2025-12-21 16:07:55', '2025-12-21 16:08:30', 500000.00, 50000.00, 0.00, '', 'closed'),
(2, 1, '2025-12-21 16:19:18', '2025-12-21 16:20:19', 500000.00, 1500000.00, 275000.00, '', 'closed'),
(3, 1, '2025-12-21 16:36:14', '2025-12-21 16:50:24', 500000.00, 50000.00, 197000.00, '', 'closed'),
(4, 2, '2025-12-21 16:50:38', '2025-12-21 21:44:33', 50000.00, 69000.00, 254000.00, 'Hệ thống tự chốt do nhân viên mới vào ca.', 'closed'),
(5, 1, '2025-12-21 21:44:33', '2025-12-21 22:09:31', 69000.00, 50000.00, 0.00, '', 'closed'),
(6, 1, '2025-12-21 22:19:05', NULL, 50000.00, 0.00, 0.00, NULL, 'open');

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_session` (`session_id`);

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
-- Indexes for table `work_sessions`
--
ALTER TABLE `work_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `inventory_log`
--
ALTER TABLE `inventory_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `work_sessions`
--
ALTER TABLE `work_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD CONSTRAINT `inventory_log_ibfk_1` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_session` FOREIGN KEY (`session_id`) REFERENCES `work_sessions` (`id`);

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

--
-- Constraints for table `work_sessions`
--
ALTER TABLE `work_sessions`
  ADD CONSTRAINT `work_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
