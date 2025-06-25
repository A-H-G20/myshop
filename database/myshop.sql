-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 25, 2025 at 11:47 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `myshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `cart_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `created_at`) VALUES
(8, 11, '2025-06-25 10:52:16'),
(7, 11, '2025-06-25 10:32:09');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `cart_item_id` int NOT NULL AUTO_INCREMENT,
  `cart_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_item_id`),
  KEY `cart_id` (`cart_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `product_id`, `quantity`, `created_at`) VALUES
(12, 8, 11, 2, '2025-06-25 10:52:16');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `image` text NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `image`, `description`, `created_at`) VALUES
(1, 'Men', '../category/1750851213_d8cebdbdb5e8f135078121ba0e2c9aa5.jpg', 'Men\'s fashion and accessories', '2025-05-03 11:53:55'),
(2, 'Women', '', 'Women\'s fashion and accessories', '2025-05-03 11:53:55');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `subscribed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `email`, `subscribed_at`) VALUES
(1, 'ahmadghosen20@gmail.com', '2025-05-24 22:02:36'),
(2, '22130479@students.liu.edu.lb', '2025-05-24 22:29:50'),
(3, 'ee@gmail.com', '2025-06-25 11:26:56');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `billing_address` text NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `status`, `shipping_address`, `billing_address`, `payment_method`, `payment_status`, `created_at`, `updated_at`) VALUES
(1, 6, 261.00, 'processing', '{\"first_name\":\"Green\",\"last_name\":\"Customer\",\"address_line_1\":\"123 Market St\",\"address_line_2\":\"\",\"city\":\"GreenCity\",\"state\":\"GR\",\"zip_code\":\"12345\",\"country\":\"EcoLand\"}', '{\"first_name\":\"Green\",\"last_name\":\"Customer\",\"address_line_1\":\"123 Market St\",\"address_line_2\":\"\",\"city\":\"GreenCity\",\"state\":\"GR\",\"zip_code\":\"12345\",\"country\":\"EcoLand\"}', 'cash_on_delivery', '', '2025-05-24 21:27:41', '2025-05-24 21:27:41'),
(2, 6, 1305.00, 'processing', '{\"first_name\":\"Green\",\"last_name\":\"Customer\",\"address_line_1\":\"123 Market St\",\"address_line_2\":\"\",\"city\":\"GreenCity\",\"state\":\"GR\",\"zip_code\":\"12345\",\"country\":\"EcoLand\"}', '{\"first_name\":\"Green\",\"last_name\":\"Customer\",\"address_line_1\":\"123 Market St\",\"address_line_2\":\"\",\"city\":\"GreenCity\",\"state\":\"GR\",\"zip_code\":\"12345\",\"country\":\"EcoLand\"}', 'cash_on_delivery', '', '2025-05-24 21:28:09', '2025-05-24 21:28:09'),
(3, 6, 261.00, 'processing', '{\"first_name\":\"Green\",\"last_name\":\"Customer\",\"address_line_1\":\"123 Market St\",\"address_line_2\":\"\",\"city\":\"GreenCity\",\"state\":\"GR\",\"zip_code\":\"12345\",\"country\":\"EcoLand\"}', '{\"first_name\":\"Green\",\"last_name\":\"Customer\",\"address_line_1\":\"123 Market St\",\"address_line_2\":\"\",\"city\":\"GreenCity\",\"state\":\"GR\",\"zip_code\":\"12345\",\"country\":\"EcoLand\"}', 'cash_on_delivery', '', '2025-05-24 21:30:36', '2025-05-24 21:30:36'),
(4, 6, 261.00, 'completed', '{\"first_name\":\"Green\",\"last_name\":\"Customer\",\"address_line_1\":\"123 Market St\",\"address_line_2\":\"\",\"city\":\"GreenCity\",\"state\":\"GR\",\"zip_code\":\"12345\",\"country\":\"EcoLand\"}', '{\"first_name\":\"Green\",\"last_name\":\"Customer\",\"address_line_1\":\"123 Market St\",\"address_line_2\":\"\",\"city\":\"GreenCity\",\"state\":\"GR\",\"zip_code\":\"12345\",\"country\":\"EcoLand\"}', 'cash_on_delivery', '', '2025-05-24 21:32:00', '2025-06-25 08:15:45');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price_at_time` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price_at_time`, `created_at`) VALUES
(1, 1, 7, 1, 261.00, '2025-05-24 21:27:41'),
(2, 2, 7, 5, 261.00, '2025-05-24 21:28:09'),
(3, 3, 7, 1, 261.00, '2025-05-24 21:30:36'),
(4, 4, 7, 1, 261.00, '2025-05-24 21:32:00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `category_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `images` text,
  `sizes` text NOT NULL,
  PRIMARY KEY (`product_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `stock_quantity`, `category_id`, `created_at`, `images`, `sizes`) VALUES
(12, 'tseet', 'sf', 12.00, 212, 1, '2025-06-25 11:04:55', '../uploads/1750849495_R.png', 'q'),
(11, 'tsee', 'erertr', 345.00, 33, 1, '2025-06-25 10:52:11', '../uploads/1750848731_R.jpg', 's');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `verification_code` varchar(10) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `role` varchar(50) DEFAULT 'user',
  `address` text NOT NULL,
  `city` text NOT NULL,
  `date_of_birth` text NOT NULL,
  `gender` text NOT NULL,
  `verified` int NOT NULL,
  `reset_code` varchar(6) DEFAULT NULL,
  `reset_code_expires` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `phone`, `password`, `verification_code`, `email_verified_at`, `role`, `address`, `city`, `date_of_birth`, `gender`, `verified`, `reset_code`, `reset_code_expires`, `created_at`) VALUES
(11, 'Ahmad', 'Ghosen', 'ahmad.ghosen', 'ahmadghosen200@gmail.com', '79666666', '$2y$10$1Er1TiVPF0adCsvwnCLVBemf4b.S/YvAuvDDxU7cPvEH8NSJJP.r2', NULL, '2025-06-25 13:06:30', 'admin', 'bekaa', 'MARJ', '2025-06-23', 'male', 1, NULL, '0000-00-00 00:00:00', '2025-06-25 10:06:06');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
