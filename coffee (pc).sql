-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2026 at 07:04 PM
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
(1, 'Hạt Cà phê', 'g', 5260, 500, '2026-01-05 17:27:51'),
(2, 'Sữa tươi', 'ml', 97850, 200, '2026-01-05 17:32:00'),
(3, 'Trà túi lọc (Đào)', 'túi', 1978, 10, '2025-12-24 14:46:17'),
(4, 'Cam tươi', 'quả', 19534.5, 20, '2026-01-05 17:43:10'),
(5, 'Bơ sáp', 'g', 44334, 500, '2026-01-05 18:04:13'),
(6, 'Bột mì', 'g', 49650, 1000, '2026-01-05 17:32:00'),
(7, 'Chocolate', 'g', 20020, 300, '2025-12-21 05:05:13'),
(8, 'Khoai tây đông lạnh', 'g', 14978, 1000, '2025-12-21 05:15:32'),
(9, 'Bánh mì ổ', 'ổ', 232, 10, '2025-12-21 08:53:18'),
(10, 'Bơ lạt', 'g', 4343430, 200, '2025-12-21 08:53:08'),
(11, 'Tỏi', 'g', 100, 50, '2025-12-21 05:15:32'),
(12, 'Lá Parsley khô', 'g', 50, 10, '2025-12-23 14:48:53'),
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
(1, 1, 'import', 5000, 1000000, 'Nhập cafe đợt 1', '2025-12-01 01:00:00', 1),
(2, 1, 'import', 5000, 1200000, 'Nhập cafe đợt 2 (giá tăng)', '2025-12-15 01:00:00', 1),
(3, 2, 'import', 20000, 600000, 'Sữa Vinamilk thùng', '2025-12-01 01:30:00', 1),
(4, 2, 'import', 30000, 900000, 'Sữa Vinamilk thùng đợt 2', '2025-12-10 02:00:00', 1),
(5, 3, 'import', 200, 100000, 'Trà Cozy Đào', '2025-12-02 03:00:00', 1),
(6, 4, 'import', 50, 250000, 'Cam Vinh', '2025-12-02 03:15:00', 1),
(7, 4, 'import', 50, 250000, 'Cam Vinh đợt 2', '2025-12-20 00:00:00', 1),
(8, 5, 'import', 5000, 300000, 'Bơ sáp Daklak', '2025-12-05 01:00:00', 1),
(9, 5, 'import', 5000, 300000, 'Bơ sáp đợt 2', '2025-12-18 01:00:00', 1),
(10, 6, 'import', 10000, 200000, 'Bột mì đa dụng', '2025-12-01 07:00:00', 1),
(11, 7, 'import', 2000, 400000, 'Chocolate đen nguyên chất', '2025-12-03 04:00:00', 1),
(12, 8, 'import', 5000, 250000, 'Khoai tây đông lạnh', '2025-12-03 04:30:00', 1),
(13, 9, 'import', 100, 200000, 'Bánh mì tươi trong ngày', '2025-12-19 23:00:00', 1),
(14, 10, 'import', 1000, 200000, 'Bơ lạt Anchor', '2025-12-05 02:00:00', 1),
(15, 11, 'import', 500, 25000, 'Tỏi xay', '2025-12-05 02:00:00', 1),
(16, 12, 'import', 100, 50000, 'Lá gia vị khô', '2025-12-05 02:00:00', 1),
(17, 13, 'import', 5000, 250000, 'Sữa đặc Ngôi sao', '2025-12-01 01:30:00', 1),
(18, 6, 'export', 50, 0, 'Bán đơn #129', '2025-12-24 14:46:17', 1),
(19, 2, 'export', 50, 0, 'Bán đơn #129', '2025-12-24 14:46:17', 1),
(20, 1, 'export', 20, 0, 'Bán đơn #129', '2025-12-24 14:46:17', 1),
(21, 3, 'export', 2, 0, 'Bán đơn #129', '2025-12-24 14:46:17', 1),
(22, 4, 'export', 1, 0, 'Bán đơn #129', '2025-12-24 14:46:17', 1),
(23, 6, 'export', 50, 0, 'Bán đơn #130', '2026-01-05 17:26:44', 1),
(24, 2, 'export', 100, 0, 'Bán đơn #130', '2026-01-05 17:26:44', 1),
(25, 5, 'export', 2006, 0, 'Bán đơn #130', '2026-01-05 17:26:44', 1),
(26, 6, 'export', 50, 0, 'Bán đơn #131', '2026-01-05 17:27:51', 1),
(27, 2, 'export', 50, 0, 'Bán đơn #131', '2026-01-05 17:27:51', 1),
(28, 1, 'export', 20, 0, 'Bán đơn #131', '2026-01-05 17:27:51', 1),
(29, 4, 'export', 100, 0, 'Bán đơn #132', '2026-01-05 17:31:37', 1),
(30, 6, 'export', 50, 0, 'Bán đơn #133', '2026-01-05 17:32:00', 1),
(31, 2, 'export', 100, 0, 'Bán đơn #133', '2026-01-05 17:32:00', 1),
(32, 5, 'export', 12036, 0, 'Bán đơn #134', '2026-01-05 17:32:27', 1),
(33, 5, 'export', 6018, 0, 'Bán đơn #135', '2026-01-05 17:33:13', 1),
(34, 4, 'export', 100, 0, 'Bán đơn #136', '2026-01-05 17:43:10', 1),
(35, 5, 'import', 50000, 500000, '', '2026-01-05 18:04:04', 1),
(36, 5, 'export', 6018, 0, 'Bán đơn #137', '2026-01-05 18:04:13', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_price` int(11) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'cash' COMMENT 'cash hoặc transfer',
  `status` varchar(255) NOT NULL DEFAULT 'not_paid',
  `session_id` int(11) DEFAULT NULL,
  `voucher_code` varchar(50) DEFAULT NULL,
  `discount_percent` decimal(5,2) DEFAULT 0.00,
  `final_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `total_price`, `payment_method`, `status`, `session_id`, `voucher_code`, `discount_percent`, `final_amount`) VALUES
(1, 1, '2025-11-30 21:10:28', 0, 'cash', 'not_paid', NULL, NULL, 0.00, 0.00),
(2, 1, '2025-12-07 14:17:59', 25000, 'cash', 'not_paid', NULL, NULL, 0.00, 0.00),
(3, 1, '2025-12-07 14:20:16', 32000, 'cash', 'not_paid', NULL, NULL, 0.00, 0.00),
(4, 1, '2025-12-07 14:27:34', 25000, 'cash', 'not_paid', NULL, NULL, 0.00, 0.00),
(5, 1, '2025-12-07 14:30:47', 60000, 'cash', 'not_paid', NULL, NULL, 0.00, 0.00),
(6, 1, '2025-12-11 18:15:18', 32000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(7, 1, '2025-12-11 18:39:01', 3748000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(8, 1, '2025-12-11 18:50:33', 90000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(9, 1, '2025-12-11 18:51:30', 450000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(10, 1, '2025-12-12 10:28:43', 2235000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(11, 1, '2025-12-12 10:29:44', 155000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(12, 1, '2025-12-12 10:30:16', 195000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(13, 1, '2025-12-12 10:30:33', 120000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(14, 1, '2025-12-12 10:39:33', 777000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(15, 1, '2025-12-12 10:47:35', 385000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(16, 1, '2025-12-12 11:07:07', 77000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(17, 1, '2025-12-12 11:25:10', 336000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(18, 1, '2025-12-17 20:52:56', 430000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(19, 1, '2025-12-17 20:53:28', 603000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(20, 1, '2025-12-17 22:37:26', 531000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(21, 1, '2025-12-17 22:38:00', 167000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(22, 1, '2025-12-17 22:44:08', 403000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(23, 1, '2025-12-17 22:44:21', 120000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(24, 1, '2025-12-17 22:44:26', 240000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(25, 1, '2025-12-17 22:44:37', 662000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(26, 1, '2025-12-17 22:44:43', 538000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(27, 1, '2025-12-17 22:44:50', 295000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(28, 1, '2025-12-17 22:46:45', 428000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(29, 1, '2025-12-17 22:47:22', 804000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(30, 1, '2025-12-17 22:47:30', 372000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(31, 1, '2025-12-17 22:49:29', 160000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(32, 1, '2025-12-17 22:49:50', 95000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(33, 1, '2025-12-17 22:50:10', 90000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(34, 1, '2025-12-17 22:50:40', 175000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(35, 1, '2025-12-17 22:51:28', 148000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(36, 1, '2025-12-17 22:51:39', 58000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(37, 1, '2025-12-17 22:51:50', 115000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(38, 1, '2025-12-17 22:52:08', 130000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(39, 1, '2025-12-17 22:54:56', 120000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(40, 1, '2025-12-17 22:58:55', 398000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(41, 1, '2025-12-18 08:55:24', 600000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(42, 1, '2025-12-18 09:12:43', 324000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(43, 1, '2025-12-18 09:12:47', 443000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(44, 1, '2025-12-18 09:32:34', 115000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(45, 1, '2025-12-18 03:39:06', 215000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(46, 1, '2025-12-18 03:44:01', 145000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(47, 1, '2025-12-18 03:53:57', 86000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(48, 1, '2025-12-18 04:09:32', 105000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(49, 1, '2025-12-19 16:31:16', 49000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(50, 1, '2025-12-19 17:10:40', 75000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(51, 1, '2025-12-19 17:11:05', 109000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(52, 1, '2025-12-19 17:11:38', 40000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(53, 1, '2025-12-19 17:11:53', 39000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(54, 1, '2025-12-20 14:42:45', 825000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(55, 1, '2025-12-20 14:44:40', 750000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(56, 1, '2025-12-20 17:59:59', 144000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(57, 1, '2025-12-20 18:02:04', 259000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(58, 1, '2025-12-20 18:02:13', 120000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(59, 1, '2025-12-20 18:03:22', 102000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(60, 1, '2025-12-20 18:04:33', 495000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(63, 1, '2025-12-20 18:06:03', 816000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(64, 1, '2025-12-20 18:13:14', 2847000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(66, 1, '2025-12-20 18:13:38', 1560000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(67, 1, '2025-12-20 18:14:49', 990000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(68, 1, '2025-12-20 18:15:31', 2112000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(69, 1, '2025-12-20 18:16:26', 28909000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(70, 1, '2025-12-20 18:16:33', 34459000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(72, 1, '2025-12-20 18:16:42', 28942000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(73, 1, '2025-12-20 18:20:52', 120000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(74, 1, '2025-12-20 18:23:09', 480000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(75, 1, '2025-12-20 18:23:22', 120000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(76, 1, '2025-12-20 18:23:31', 160000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(77, 1, '2025-12-20 18:23:43', 9400000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(78, 1, '2025-12-21 06:06:04', 117000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(79, 2, '2025-12-21 06:12:26', 15425000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(80, 2, '2025-12-21 06:15:04', 55000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(81, 2, '2025-12-21 06:15:32', 1340000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(82, 2, '2025-12-21 06:18:11', 39000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(83, 2, '2025-12-21 06:23:08', 78000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(84, 1, '2025-12-21 06:24:41', 160000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(85, 2, '2025-12-21 06:30:22', 39000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(89, 2, '2025-12-21 15:22:27', 156000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(90, 2, '2025-12-21 15:28:53', 39000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(91, 2, '2025-12-21 15:29:30', 40000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(92, 2, '2025-12-21 15:30:50', 39000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(93, 2, '2025-12-21 15:31:07', 40000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(94, 2, '2025-12-21 15:33:48', 40000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(95, 2, '2025-12-21 15:33:58', 40000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(96, 2, '2025-12-21 15:34:17', 39000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(97, 2, '2025-12-21 15:38:59', 39000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(98, 2, '2025-12-21 15:39:16', 39000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(99, 2, '2025-12-21 15:44:38', 40000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(100, 2, '2025-12-21 15:44:56', 40000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(101, 2, '2025-12-21 15:48:33', 40000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(102, 2, '2025-12-21 15:52:08', 40000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(103, 1, '2025-12-21 15:53:25', 55000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(104, 1, '2025-12-21 15:54:05', 175000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(105, 1, '2025-12-21 16:09:00', 165000, 'cash', 'paid', NULL, NULL, 0.00, 0.00),
(106, 1, '2025-12-21 16:19:37', 275000, 'cash', 'paid', 2, NULL, 0.00, 0.00),
(107, 1, '2025-12-21 16:46:03', 40000, 'cash', 'paid', 3, 'ADMINVIP', 100.00, 0.00),
(108, 1, '2025-12-21 16:48:02', 55000, 'cash', 'paid', 3, 'WELCOME', 10.00, 49500.00),
(109, 1, '2025-12-21 16:48:44', 102000, 'cash', 'paid', 3, '', 0.00, 102000.00),
(110, 2, '2025-12-21 16:55:33', 220000, 'cash', 'paid', 4, 'WELCOME', 10.00, 198000.00),
(111, 2, '2025-12-21 17:00:45', 40000, 'cash', 'canceled', 4, '', 0.00, 40000.00),
(112, 2, '2025-12-21 17:01:13', 34000, 'cash', 'paid', 4, 'WELCOME', 10.00, 30600.00),
(113, 1, '2025-12-21 22:19:09', 144000, 'cash', 'canceled', 6, '', 0.00, 144000.00),
(116, 1, '2025-12-22 17:29:54', 78000, 'cash', 'paid', 7, '', 0.00, 78000.00),
(117, 1, '2025-12-22 19:13:49', 78000, 'cash', 'paid', 7, '', 0.00, 78000.00),
(118, 1, '2025-12-22 19:27:27', 60000, 'cash', 'paid', 7, '', 0.00, 60000.00),
(119, 1, '2025-12-23 14:45:02', 39000, 'cash', 'canceled', 13, '', 0.00, 39000.00),
(120, 1, '2025-12-23 15:59:06', 160000, 'cash', 'canceled', 14, '', 0.00, 160000.00),
(121, 1, '2025-12-23 16:01:00', 7800000, 'cash', 'canceled', 14, '', 0.00, 7800000.00),
(122, 1, '2025-12-23 16:01:23', 2769000, 'cash', 'canceled', 14, '', 0.00, 2769000.00),
(123, 1, '2025-12-23 16:02:22', 495000, 'cash', 'canceled', 14, '', 0.00, 495000.00),
(124, 1, '2025-12-23 16:02:52', 55000, 'cash', 'canceled', 14, '', 0.00, 55000.00),
(125, 1, '2025-12-23 16:16:26', 40000, 'cash', 'paid', 14, '', 0.00, 40000.00),
(126, 1, '2025-12-23 21:11:41', 40000, 'cash', 'paid', 15, 'ADMINVIP', 50.00, 20000.00),
(127, 1, '2025-12-23 21:30:18', 34000, 'cash', 'paid', 15, 'VIP69', 69.00, 10540.00),
(128, 1, '2025-12-23 21:30:41', 136000, 'cash', 'paid', 15, 'VIP69', 69.00, 42160.00),
(129, 1, '2025-12-24 21:46:17', 108000, 'transfer', 'paid', 16, '', 0.00, 108000.00),
(130, 1, '2026-01-06 00:26:44', 89000, 'cash', 'paid', 18, '', 0.00, 89000.00),
(131, 1, '2026-01-06 00:27:51', 30000, 'cash', 'paid', 19, '', 0.00, 30000.00),
(132, 1, '2026-01-06 00:31:37', 40000, 'cash', 'paid', 20, '', 0.00, 40000.00),
(133, 1, '2026-01-06 00:32:00', 34000, 'cash', 'paid', 20, '', 0.00, 34000.00),
(134, 1, '2026-01-06 00:32:27', 330000, 'cash', 'paid', 20, '', 0.00, 330000.00),
(135, 1, '2026-01-06 00:33:13', 165000, 'cash', 'paid', 20, '', 0.00, 165000.00),
(136, 1, '2026-01-06 00:43:10', 40000, 'cash', 'paid', 21, '', 0.00, 40000.00),
(137, 1, '2026-01-06 01:04:13', 165000, 'cash', 'paid', 21, '', 0.00, 165000.00);

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
(87, 113, 5, 2, ''),
(88, 116, 3, 2, ''),
(89, 117, 3, 2, ''),
(90, 118, 10, 2, ''),
(91, 119, 3, 1, ''),
(92, 120, 4, 4, ''),
(93, 121, 4, 195, ''),
(94, 122, 3, 71, ''),
(95, 123, 5, 9, ''),
(96, 124, 5, 1, ''),
(97, 125, 4, 1, ''),
(98, 126, 4, 1, ''),
(99, 127, 6, 1, ''),
(100, 128, 6, 4, ''),
(101, 129, 10, 1, ''),
(102, 129, 3, 2, ''),
(103, 130, 6, 1, ''),
(104, 130, 5, 1, ''),
(105, 131, 10, 1, ''),
(106, 132, 4, 1, ''),
(107, 133, 6, 1, ''),
(108, 134, 5, 6, ''),
(109, 135, 5, 3, ''),
(110, 136, 4, 1, ''),
(111, 137, 5, 3, '');

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
(5, 'Sinh Tố Bơ', 55000, 1, 'assets/img/drink/Biểu_trưng_Trường_đại_học_Hàng_hải_Việt_Nam.svg.png', 1, 1),
(6, 'Bánh Mousse ', 34000, 2, '../assets/img/food/banh-mousse-chanh-leo.png', 1, 1),
(7, 'Bánh Mì Bơ Tỏi', 29000, 2, '../assets/img/food/banh-mi-bo-toi.webp', 1, 1),
(8, 'Bánh Muffin Chocolate', 32000, 2, '../assets/img/food/banh-muffin-chocolate.jpg', 1, 1),
(9, 'Khoai Tây Chiên', 45000, 2, '../assets/img/food/khoai-tay-chien.jpg', 1, 1),
(10, 'Bánh Tiramisu', 30000, 2, '../assets/img/food/banh-tiramisu.png', 1, 1),
(11, 'banhmi', 15000, 1, 'uploads/1766027605.webp', 0, 0),
(12, 'coffe ', 30000, 1, 'uploads/1766337405_ca-phe-den.jpg', 0, 1),
(13, 'bánh mì bơ', 100000, 2, 'uploads/1766337497_pexels-photo-3915906.jpeg', 0, 1),
(14, 'vu hoang nghia', 1, 1, 'uploads/1766339255_hinh-cafe-kem-banh-quy.jpg', 0, 1),
(15, 'Test', 50000, 1, 'uploads/1766476283_images.jpg', 0, 1),
(16, 'TEst 2', 36000, 1, '/assets/img/drink/thumb_1766476678_images.jpg', 0, 1),
(17, 'abc test2', 53446, 1, '/assets/img/drink/thumb_1766476814_images.jpg', 0, 1),
(18, '312', 436, 1, '../../assets/img/drink/thumb_1766476924_images.jpg', 0, 1),
(19, 'r4feqa', 412, 1, 'C:\\xampp\\htdocs\\coffee/assets/img/drink/thumb_1766477190_images.jpg', 0, 1),
(20, 'fasd', 3123, 1, 'assets/img/drink/thumb_1766477234_images.jpg', 0, 1),
(21, 'đấ', 50000, 1, 'assets/img/drink/thumb_1766478190_images.jpg', 0, 1),
(22, 'da', 46777, 1, 'assets/img/no-image_url.png', 0, 1),
(23, 'ad', 43, 1, 'assets/img/no-image_url.png', 0, 1),
(24, '3213', 321332, 1, 'assets/img/drink/thumb_1766478903_ad.jpg', 0, 1),
(25, '312', 4214, 1, 'assets/img/drink/thumb_1766479105_ad.jpg', 0, 1),
(26, '43', 434, 1, 'assets/img/drink/thumb_1766479199_ad.jpg', 0, 1),
(27, '434', 4343, 1, 'assets/img/drink/1766479536_ad.jpg', 0, 0),
(28, '23', 323, 1, 'assets/img/drink/1766479597_ad.jpg', 0, 1),
(29, '55', 55, 2, 'assets/img/food/1766479673_ad.jpg', 0, 1),
(30, '324', 43, 1, 'assets/img/drink/1766479775_ad.jpg', 0, 1);

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
(21, 7, 13, 15),
(24, 12, 1, 100),
(25, 12, 2, 151),
(26, 13, 10, 100),
(27, 13, 9, 1),
(35, 14, 1, 5340),
(36, 14, 10, 3),
(37, 14, 5, 3),
(39, 4, 4, 100),
(40, 15, 4, 50),
(41, 16, 9, 6),
(42, 17, 9, 2),
(43, 18, 9, 245),
(44, 19, 9, 4),
(45, 20, 9, 434),
(46, 21, 9, 5),
(47, 22, 9, 3),
(48, 23, 9, 5),
(49, 24, 9, 45),
(50, 25, 10, 42),
(51, 26, 9, 4),
(53, 28, 9, 23),
(54, 29, 9, 6),
(55, 30, 9, 32),
(56, 27, 9, 32),
(57, 5, 5, 2006);

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
  `status_work` tinyint(1) DEFAULT 0 COMMENT '0: Off ca, 1: Đang trong ca',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`, `status`, `status_work`, `created_at`) VALUES
(1, 'Admin', 'admin', '$2y$10$DxWZmJ0OdBN7Yp.HPXAUmusmf76YcwzMJjWF/QcIrBpqQnJtKpkPu', 'admin', 1, 0, '2025-12-18 03:32:02'),
(2, 'Staff test', 'staff', '$2y$10$DxWZmJ0OdBN7Yp.HPXAUmusmf76YcwzMJjWF/QcIrBpqQnJtKpkPu', 'staff', 1, 0, '2025-12-18 03:32:02'),
(3, 'Thủ kho', 'thukho', '$2y$10$DxWZmJ0OdBN7Yp.HPXAUmusmf76YcwzMJjWF/QcIrBpqQnJtKpkPu', 'wh-staff', 1, 0, '2025-12-20 11:06:00'),
(4, 'abc', 'test_admin', '$2y$10$BawJvubcJKWMB0MTj36TVeKdRCvl/CWXL17DqCq4WPDs4Ob0GJW4C', 'admin', 1, 0, '2025-12-23 07:48:30');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_percent` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `discount_percent`, `description`, `created_at`) VALUES
(1, 'WELCOME', 10, 'Giảm 10% cho khách mới', '2025-12-21 18:17:12'),
(2, 'VIP20', 20, 'Giảm 20% cho khách VIP', '2025-12-21 18:17:12'),
(3, 'ADMINVIP', 100, 'Miễn phí cho chủ quán', '2025-12-21 18:17:12'),
(4, 'VIP50', 50, '', '2025-12-23 14:22:36'),
(5, 'VIP69', 69, '', '2025-12-23 14:25:25');

-- --------------------------------------------------------

--
-- Table structure for table `work_schedules`
--

CREATE TABLE `work_schedules` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shift_date` date NOT NULL,
  `shift_type` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_schedules`
--

INSERT INTO `work_schedules` (`id`, `user_id`, `shift_date`, `shift_type`, `created_at`) VALUES
(3, 2, '2025-12-21', 'morning', '2025-12-23 09:52:11'),
(4, 3, '2025-12-22', 'morning', '2025-12-23 09:54:35'),
(6, 1, '2025-12-23', 'morning', '2025-12-23 09:57:21'),
(7, 3, '2025-12-23', 'evening', '2025-12-24 12:41:27'),
(8, 2, '2025-12-23', 'evening', '2025-12-24 12:45:34'),
(9, 2, '2025-12-24', 'afternoon', '2025-12-24 12:45:39'),
(10, 2, '2025-12-24', 'evening', '2025-12-24 12:45:42'),
(11, 3, '2025-12-24', 'evening', '2025-12-24 12:50:06');

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
(6, 1, '2025-12-21 22:19:05', '2025-12-22 17:19:46', 50000.00, 50000.00, 0.00, '[CHỐT CA]: TM:0 | CK:0 | Lệch:0. ', 'closed'),
(7, 1, '2025-12-22 17:28:57', '2025-12-22 19:34:26', 50000.00, 50000.00, 216000.00, '', 'closed'),
(8, 1, '2025-12-22 21:13:41', '2025-12-22 21:50:24', 50000.00, 50000.00, 0.00, '', 'closed'),
(9, 1, '2025-12-22 21:54:50', '2025-12-22 22:07:25', 50000.00, 50000.00, 0.00, '', 'closed'),
(10, 1, '2025-12-22 22:09:22', '2025-12-22 22:16:12', 50000.00, 50000.00, 0.00, 'Hệ thống tự chốt do nhân viên mới vào ca.', 'closed'),
(11, 2, '2025-12-22 22:16:12', '2025-12-22 22:25:24', 50000.00, 50000.00, 0.00, 'Hệ thống tự chốt do nhân viên mới vào ca.', 'closed'),
(12, 1, '2025-12-22 22:25:24', '2025-12-23 14:39:32', 50000.00, 50000.00, 0.00, '', 'closed'),
(13, 1, '2025-12-23 14:44:28', '2025-12-23 14:48:52', 50000.00, 50000.00, 0.00, '', 'closed'),
(14, 1, '2025-12-23 14:50:10', '2025-12-23 16:49:36', 50000.00, 0.00, 40000.00, '', 'closed'),
(15, 1, '2025-12-23 21:11:27', '2025-12-24 19:40:11', 50000.00, 50000.00, 210000.00, '', 'closed'),
(16, 1, '2025-12-24 19:54:41', '2025-12-24 21:50:09', 50000.00, 50000.00, 108000.00, '', 'closed'),
(17, 2, '2025-12-24 22:45:19', '2026-01-04 23:44:22', 50000.00, 25000.00, 0.00, 'Hệ thống tự chốt do nhân viên mới vào ca.', 'closed'),
(18, 1, '2026-01-04 23:44:22', '2026-01-06 00:27:08', 25000.00, 50000.00, 89000.00, '', 'closed'),
(19, 1, '2026-01-06 00:27:39', '2026-01-06 00:28:08', 50000.00, 150000.00, 30000.00, '', 'closed'),
(20, 1, '2026-01-06 00:31:33', '2026-01-06 00:34:40', 50000.00, 150000.00, 569000.00, '', 'closed'),
(21, 1, '2026-01-06 00:43:05', NULL, 50000.00, 0.00, 0.00, NULL, 'open');

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
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_shift` (`user_id`,`shift_date`,`shift_type`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `work_schedules`
--
ALTER TABLE `work_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `work_sessions`
--
ALTER TABLE `work_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
-- Constraints for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD CONSTRAINT `work_schedules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `work_sessions`
--
ALTER TABLE `work_sessions`
  ADD CONSTRAINT `work_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
