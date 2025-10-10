-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 10, 2025 at 10:21 AM
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
-- Database: `furniture_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`) VALUES
(2, 'Sofas & Couches', '2025-10-10 05:54:28'),
(3, 'Chairs & Recliners', '2025-10-10 05:54:28'),
(4, 'Tables & Desks', '2025-10-10 05:54:28'),
(5, 'Beds & Bed Frames', '2025-10-10 05:54:28'),
(6, 'Wardrobes & Cabinets', '2025-10-10 05:54:28'),
(7, 'Dressers & Drawers', '2025-10-10 05:54:28'),
(8, 'Bookshelves & Racks', '2025-10-10 05:54:28'),
(9, 'Outdoor Furniture', '2025-10-10 05:54:28'),
(10, 'Dining Sets', '2025-10-10 05:54:28'),
(11, 'Office Furniture', '2025-10-10 05:54:28');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `admin_reply` text DEFAULT NULL,
  `status` enum('pending','resolved','dismissed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `user_id`, `description`, `admin_reply`, `status`, `created_at`) VALUES
(1, 1, 'Dum', NULL, 'pending', '2025-10-10 07:10:12'),
(2, 1, 'Dum', NULL, 'pending', '2025-10-10 07:10:31'),
(3, 2, 'XY', 'dfas', 'resolved', '2025-10-10 07:11:09');

-- --------------------------------------------------------

--
-- Table structure for table `furniture`
--

CREATE TABLE `furniture` (
  `furniture_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `condition` enum('new','like_new','good','fair','poor') NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` enum('available','sold','blocked') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `furniture`
--

INSERT INTO `furniture` (`furniture_id`, `id`, `category_id`, `title`, `description`, `price`, `condition`, `location`, `status`, `created_at`, `updated_at`) VALUES
(12, 1, 2, 'Modern 3-Seater Sofa', 'Comfortable 3-seater sofa in good condition, perfect for living rooms.', 25000.00, 'good', 'Lahore', 'blocked', '2025-10-10 06:06:08', '2025-10-10 08:10:07'),
(13, 1, 2, 'Wooden Dining Chair', 'Elegant wooden chair with soft cushion, slightly used.', 4000.00, 'like_new', 'Karachi', 'available', '2025-10-10 06:06:08', '2025-10-10 06:06:08'),
(14, 1, 3, 'Study Table with Drawer', 'Spacious study table ideal for students, includes a side drawer.', 8000.00, 'good', 'Islamabad', 'available', '2025-10-10 06:06:08', '2025-10-10 06:06:08'),
(15, 1, 4, 'King Size Bed Frame', 'Strong wooden bed frame suitable for king-size mattress.', 15000.00, 'fair', 'Rawalpindi', 'available', '2025-10-10 06:06:08', '2025-10-10 06:06:08'),
(16, 1, 5, 'Double Door Wardrobe', 'Large wardrobe with mirror and hanging space, excellent condition.', 18000.00, 'like_new', 'Faisalabad', 'available', '2025-10-10 06:06:08', '2025-10-10 06:06:08'),
(17, 1, 6, 'Dresser with Mirror', 'Stylish dresser with 3 drawers and large mirror.', 12000.00, 'good', 'Lahore', 'available', '2025-10-10 06:06:08', '2025-10-10 06:06:08'),
(18, 1, 7, 'Bookshelf Rack', '5-shelf wooden rack ideal for books or decoration items.', 6000.00, 'good', 'Multan', 'available', '2025-10-10 06:06:08', '2025-10-10 06:06:08'),
(19, 1, 8, 'Outdoor Patio Set', 'Metal patio set with table and 4 chairs, weather-resistant.', 20000.00, 'like_new', 'Karachi', 'available', '2025-10-10 06:06:08', '2025-10-10 06:06:08'),
(20, 1, 9, 'Glass Dining Table Set', '6-seater glass-top dining table with matching chairs.', 35000.00, 'good', 'Lahore', 'available', '2025-10-10 06:06:08', '2025-10-10 06:06:08'),
(21, 1, 10, 'Office Chair Ergonomic', 'Comfortable office chair with adjustable height and wheels.', 10000.00, 'like_new', 'Islamabad', 'available', '2025-10-10 06:06:08', '2025-10-10 06:06:08');

-- --------------------------------------------------------

--
-- Table structure for table `furniture_images`
--

CREATE TABLE `furniture_images` (
  `image_id` int(11) NOT NULL,
  `furniture_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `furniture_images`
--

INSERT INTO `furniture_images` (`image_id`, `furniture_id`, `image_path`, `created_at`) VALUES
(2, 12, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(3, 12, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(4, 12, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(5, 12, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(6, 13, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(7, 13, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(8, 13, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(9, 13, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(10, 14, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(11, 14, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(12, 14, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(13, 14, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(14, 15, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(15, 15, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(16, 15, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(17, 15, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(18, 16, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(19, 16, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(20, 16, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(21, 16, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(22, 17, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(23, 17, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(24, 17, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(25, 17, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(26, 18, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(27, 18, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(28, 18, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(29, 18, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(30, 19, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(31, 19, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(32, 19, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(33, 19, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(34, 20, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(35, 20, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(36, 20, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(37, 20, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(38, 21, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(39, 21, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(40, 21, '1760076768_eximage.png', '2025-10-10 06:16:01'),
(41, 21, '1760076768_eximage.png', '2025-10-10 06:16:01');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `furniture_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `order_status` enum('pending','processing','shipped','completed','cancelled') DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_address` text NOT NULL,
  `payment_method` enum('cod','online') NOT NULL,
  `payment_status` enum('pending','completed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `buyer_id`, `furniture_id`, `seller_id`, `order_status`, `order_date`, `delivery_address`, `payment_method`, `payment_status`) VALUES
(1, 2, 12, 1, 'processing', '2025-10-10 06:43:00', 'XYZ', 'cod', 'pending'),
(2, 2, 12, 1, 'completed', '2025-10-10 06:43:07', 'XYZ', 'online', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `receipt` varchar(255) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `buyer_id`, `amount`, `receipt`, `payment_date`) VALUES
(1, 2, 2, 25000.00, '1760079281_Mrk_Spring 2025_CS619_10925_36206_S25PROJECTBBFB1.docx', '2025-10-10 06:54:41');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `furniture_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `furniture_id`, `buyer_id`, `seller_id`, `rating`, `review_text`, `created_at`) VALUES
(1, 14, 2, 1, 3, 'cxz', '2025-10-10 07:19:59'),
(2, 13, 2, 1, 5, 'dsa', '2025-10-10 07:44:59'),
(3, 12, 2, 1, 1, 'fdsa', '2025-10-10 07:46:07'),
(4, 12, 2, 1, 2, 'fds', '2025-10-10 07:48:41'),
(5, 12, 2, 1, 5, 'dfa', '2025-10-10 07:49:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `role` enum('buyer','seller') DEFAULT 'buyer',
  `status` enum('active','blocked','pending') DEFAULT 'active',
  `upload_status` enum('active','blocked') DEFAULT 'active',
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `address`, `city`, `state`, `country`, `role`, `status`, `upload_status`, `profile_image`, `created_at`) VALUES
(1, 'Nasir Seller', 'nasiryt.827@gmail.com', '$2y$10$ATOPTfF3jpLz/08aU7s8TeW.MoajlWCKVk.7DU9VPvUudgnGZwTs2', '03176526827', 'Street jeff xxxxAp04', 'Salvador', 'Lahore', 'Pakistan', 'seller', 'active', 'blocked', 'Blue Back.png', '2025-10-10 05:41:45'),
(2, 'Buyer', 'buyer@gmail.com', '$2y$10$.yXPPzWks6v9di1Sl2wjr.k0.yHkrGn1l5flyBpD9SESlVGtGyw7m', '03120558090', 'P/o Basti Chhina Tehsil jampur District RajanpurP/o Basti Chhina Tehsil jampur District Rajanpur', 'Jampur', 'dd', 'Pakistan', 'buyer', 'active', 'active', 'eximage.png', '2025-10-10 06:26:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `furniture`
--
ALTER TABLE `furniture`
  ADD PRIMARY KEY (`furniture_id`),
  ADD KEY `id` (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `furniture_images`
--
ALTER TABLE `furniture_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `furniture_id` (`furniture_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `furniture_id` (`furniture_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `furniture_id` (`furniture_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `seller_id` (`seller_id`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `furniture`
--
ALTER TABLE `furniture`
  MODIFY `furniture_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `furniture_images`
--
ALTER TABLE `furniture_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `furniture`
--
ALTER TABLE `furniture`
  ADD CONSTRAINT `furniture_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `furniture_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `furniture_images`
--
ALTER TABLE `furniture_images`
  ADD CONSTRAINT `furniture_images_ibfk_1` FOREIGN KEY (`furniture_id`) REFERENCES `furniture` (`furniture_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`furniture_id`) REFERENCES `furniture` (`furniture_id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`furniture_id`) REFERENCES `furniture` (`furniture_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
