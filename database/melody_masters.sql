-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 28, 2026 at 04:43 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `melody_masters`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'music',
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `parent_id`, `icon`, `image`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'Guitars', 'guitars', 'Acoustic, electric, and bass guitars for all skill levels', NULL, '🎸', NULL, 1, 1, '2026-02-25 08:58:33'),
(2, 'Keyboards', 'keyboards-amp-pianos', 'Digital pianos, synthesizers, and MIDI controllers', NULL, '🎹', NULL, 2, 1, '2026-02-25 08:58:33'),
(3, 'Drums & Percussion', 'drums-percussion', 'Drum kits, electronic drums, and percussion instruments', NULL, '🥁', NULL, 3, 1, '2026-02-25 08:58:33'),
(4, 'Wind Instruments', 'wind-instruments', 'Brass and woodwind instruments', NULL, '🎺', NULL, 4, 1, '2026-02-25 08:58:33'),
(5, 'String Instruments', 'string-instruments', 'Violins, cellos, ukuleles and more', NULL, '🎻', NULL, 5, 1, '2026-02-25 08:58:33'),
(6, 'Accessories', 'accessories', 'Strings, picks, stands, cables, and more', NULL, '🎵', NULL, 6, 1, '2026-02-25 08:58:33'),
(7, 'Digital Sheet Music', 'digital-sheet-music', 'Downloadable sheet music for all instruments', NULL, '📄', NULL, 7, 1, '2026-02-25 08:58:33'),
(8, 'Acoustic Guitars', 'acoustic-guitars', 'Steel and nylon string acoustic guitars', 1, '🎸', NULL, 1, 0, '2026-02-25 08:58:33'),
(9, 'Electric Guitars', 'electric-guitars', 'Solid body and semi-hollow electric guitars', 1, '🎸', NULL, 2, 0, '2026-02-25 08:58:33'),
(10, 'Bass Guitars', 'bass-guitars', 'Electric and acoustic bass guitars', 1, '🎸', NULL, 3, 0, '2026-02-25 08:58:33'),
(11, 'Digital Pianos', 'digital-pianos', 'Weighted key digital pianos', 2, '🎹', NULL, 1, 0, '2026-02-25 08:58:33'),
(12, 'Synthesizers', 'synthesizers', 'Analog and digital synthesizers', 2, '🎹', NULL, 2, 0, '2026-02-25 08:58:33'),
(13, 'Snare Drums', 'snare-drums', 'Acoustic and electronic snare drums', 3, '🥁', NULL, 1, 0, '2026-02-25 08:58:33'),
(14, 'Drum Kits', 'drum-kits', 'Complete acoustic and electronic drum kits', 3, '🥁', NULL, 2, 0, '2026-02-25 08:58:33'),
(15, 'Flutes', 'flutes', 'Concert and student flutes', 4, '🎵', NULL, 1, 0, '2026-02-25 08:58:33'),
(16, 'Saxophones', 'saxophones', 'Alto, tenor and soprano saxophones', 4, '🎷', NULL, 2, 0, '2026-02-25 08:58:33'),
(17, 'Trumpets', 'trumpets', 'Bb and C trumpets', 4, '🎺', NULL, 3, 0, '2026-02-25 08:58:33'),
(18, 'Violins', 'violins', 'Student and professional violins', 5, '🎻', NULL, 1, 0, '2026-02-25 08:58:33'),
(19, 'Ukuleles', 'ukuleles', 'Soprano, concert and tenor ukuleles', 5, '🎵', NULL, 2, 0, '2026-02-25 08:58:33');

-- --------------------------------------------------------

--
-- Table structure for table `digital_products`
--

CREATE TABLE `digital_products` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` varchar(50) DEFAULT NULL,
  `file_format` varchar(20) DEFAULT NULL,
  `download_limit` int(11) DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `digital_products`
--

INSERT INTO `digital_products` (`id`, `product_id`, `file_name`, `file_path`, `file_size`, `file_format`, `download_limit`, `created_at`) VALUES
(1, 17, 'Beginner_Guitar_Chords_Product_Details.pdf', 'digital_17_1772290137.pdf', '1923', 'pdf', 5, '2026-02-28 14:48:57');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `shipping_name` varchar(200) DEFAULT NULL,
  `shipping_address` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_county` varchar(100) DEFAULT NULL,
  `shipping_postcode` varchar(20) DEFAULT NULL,
  `shipping_country` varchar(100) DEFAULT 'United Kingdom',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `tracking_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `status`, `subtotal`, `shipping_cost`, `total`, `shipping_name`, `shipping_address`, `shipping_city`, `shipping_county`, `shipping_postcode`, `shipping_country`, `payment_method`, `payment_status`, `tracking_number`, `notes`, `created_at`, `updated_at`) VALUES
(2, 5, 'MM-40AD97-20260228', 'delivered', 220.00, 0.00, 220.00, 'Imasha Kalpani', '567/B', 'Kadawatha', 'sri lanaka', '11850', 'United Kingdom', 'bank_transfer', 'paid', '', '', '2026-02-28 12:12:20', '2026-02-28 12:13:48'),
(3, 5, 'MM-740B99-20260228', 'processing', 220.00, 0.00, 220.00, 'Imasha Kalpani', '567/B', 'Kadawatha', '', '11850', 'United Kingdom', 'bank_transfer', 'paid', NULL, '', '2026-02-28 13:45:11', '2026-02-28 13:45:11'),
(4, 5, 'MM-2DB7CF-20260228', 'processing', 220.00, 0.00, 220.00, 'Imasha Kalpani', '567/B', 'Kadawatha', 'Sri Lanka', '11850', 'Sri Lanka', 'paypal', 'paid', NULL, '', '2026-02-28 13:46:58', '2026-02-28 13:46:58'),
(5, 5, 'MM-2030BF-20260228', 'delivered', 220.00, 0.00, 220.00, 'Imasha Kalpani', '567/B', 'Kadawatha', 'sri lanka', '11850', 'Sri Lanka', 'card', 'paid', '', '', '2026-02-28 13:53:38', '2026-02-28 13:54:38'),
(6, 7, 'MM-63C93D-20260228', 'delivered', 15.00, 0.00, 15.00, '', '', '', '', '', 'Sri Lanka', 'card', 'paid', '', 'quickly i want', '2026-02-28 15:20:54', '2026-02-28 15:25:10'),
(7, 7, 'MM-619E80-20260228', 'processing', 950.00, 0.00, 950.00, 'Hiruni Pallawela', '886/A', 'Kadawatha', 'UK', '11850', 'Sri Lanka', 'card', 'paid', NULL, '', '2026-02-28 15:40:06', '2026-02-28 15:40:06'),
(8, 7, 'MM-503532-20260228', 'processing', 830.00, 0.00, 830.00, 'Hiruni Pallawela', '886/A', 'Kadawatha', 'UK', '11850', 'Sri Lanka', 'card', 'paid', NULL, '', '2026-02-28 15:41:57', '2026-02-28 15:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `product_type` enum('physical','digital') DEFAULT 'physical',
  `download_count` int(11) DEFAULT 0,
  `download_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `total_price`, `product_type`, `download_count`, `download_token`, `created_at`) VALUES
(2, 2, 13, 'Fender CD-60S Acoustic Guitar', 1, 220.00, 220.00, 'physical', 0, NULL, '2026-02-28 12:12:20'),
(3, 3, 13, 'Fender CD-60S Acoustic Guitar', 1, 220.00, 220.00, 'physical', 0, NULL, '2026-02-28 13:45:11'),
(4, 4, 13, 'Fender CD-60S Acoustic Guitar', 1, 220.00, 220.00, 'physical', 0, NULL, '2026-02-28 13:46:58'),
(5, 5, 13, 'Fender CD-60S Acoustic Guitar', 1, 220.00, 220.00, 'physical', 0, NULL, '2026-02-28 13:53:38'),
(6, 6, 17, 'Beginner Guitar Chords PDF', 1, 15.00, 15.00, 'digital', 1, '3c772d2b3272494f373f58c14a6eeced', '2026-02-28 15:20:54'),
(7, 7, 23, 'Yamaha YAS-280 Alto Saxophone', 1, 950.00, 950.00, 'physical', 0, NULL, '2026-02-28 15:40:06'),
(8, 8, 21, 'Pearl Roadshow 5-Piece Drum Kit', 1, 550.00, 550.00, 'physical', 0, NULL, '2026-02-28 15:41:57'),
(9, 8, 18, 'Yamaha PSR-E373 Portable Keyboard', 1, 280.00, 280.00, 'physical', 0, NULL, '2026-02-28 15:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `sku` varchar(100) DEFAULT NULL,
  `product_type` enum('physical','digital') NOT NULL DEFAULT 'physical',
  `specifications` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `brand`, `description`, `short_description`, `price`, `sale_price`, `stock_quantity`, `sku`, `product_type`, `specifications`, `image`, `gallery`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(13, 1, 'Fender CD-60S Acoustic Guitar', 'fender-cd-60s-acoustic-guitar', 'Yamaha', 'The Fender FA-125 is a high-quality dreadnought acoustic guitar featuring a spruce top and laminated basswood body. It delivers rich tone and excellent projection, making it ideal for beginners and intermediate players.', '6-string dreadnought acoustic guitar for beginners.', 250.00, 220.00, 96, 'FEN-AC-001', 'physical', NULL, 'fender-cd-60s-acoustic-guitar.png', NULL, 1, 1, '2026-02-28 08:40:20', '2026-02-28 14:52:22'),
(14, 1, 'Yamaha Pacifica 012 Electric Guitar', 'yamaha-pacifica-012-electric-guitar', 'Yamaha', 'The Yamaha Pacifica 012 features a comfortable body design, smooth maple neck, and versatile HSS pickup configuration. Perfect for rock, blues, and pop styles.', 'Versatile electric guitar with HSS pickup configuration.', 450.00, NULL, 100, 'YAM-EL-002', 'physical', NULL, 'yamaha-pacifica-012-electric-guitar.webp', NULL, 0, 1, '2026-02-28 14:22:37', '2026-02-28 14:52:15'),
(15, 1, 'Ibanez GSR200 Bass Guitar', 'ibanez-gsr200-bass-guitar', 'Ibanez', 'The Ibanez GSR200 offers powerful low-end sound, slim neck profile, and active bass boost. Ideal for beginners and gigging musicians.', '4-string electric bass guitar with dynamic tone control.', 320.00, NULL, 100, 'IBA-BS-003', 'physical', NULL, 'ibanez-gsr200-bass-guitar.png', NULL, 0, 1, '2026-02-28 14:25:42', '2026-02-28 14:52:19'),
(16, 1, 'Cordoba C5 Classical Guitar', 'cordoba-c5-classical-guitar', 'Cordoba', 'The Cordoba C5 classical guitar features a solid Canadian cedar top and mahogany back and sides, producing warm tones suitable for classical and flamenco styles.', 'Full-size classical guitar with nylon strings.  Full Description', 380.00, 350.00, 100, 'COR-CL-004', 'physical', NULL, 'cordoba-c5-classical-guitar.webp', NULL, 0, 1, '2026-02-28 14:29:20', '2026-02-28 14:29:20'),
(17, 7, 'Beginner Guitar Chords PDF', 'beginner-guitar-chords-pdf', 'Melody Publications', 'A downloadable PDF containing all essential guitar chords with diagrams and finger positioning. Perfect for self-learning beginners.', 'Digital PDF with essential beginner guitar chords.', 15.00, NULL, 100, 'DIG-GTR-005', 'digital', NULL, 'beginner-guitar-chords-pdf.webp', NULL, 1, 1, '2026-02-28 14:48:57', '2026-02-28 14:52:28'),
(18, 2, 'Yamaha PSR-E373 Portable Keyboard', 'yamaha-psr-e373-portable-keyboard', 'Yamaha', 'The Yamaha PSR-E373 offers 622 instrument voices, touch-sensitive keys, and built-in lesson functions, making it ideal for beginners and hobby musicians.', '61-key portable keyboard with built-in learning features.', 280.00, NULL, 99, 'YAM-KB-001', 'physical', NULL, 'yamaha-psr-e373-portable-keyboard.jpg', NULL, 1, 1, '2026-02-28 14:51:59', '2026-02-28 15:41:57'),
(19, 2, 'Casio Privia PX-770 Digital Piano', 'casio-privia-px-770-digital-piano', 'Casio', 'The Casio Privia PX-770 features scaled hammer action keys, 19 instrument tones, and a powerful sound engine delivering authentic acoustic piano experience.', '88-key weighted digital piano with realistic grand piano sound.', 650.00, 599.00, 100, 'CAS-DP-002', 'physical', NULL, 'casio-privia-px-770-digital-piano.webp', NULL, 0, 1, '2026-02-28 14:54:22', '2026-02-28 14:54:22'),
(20, 2, 'Korg KROSS 2 Synthesizer', 'korg-kross-2-synthesizer', 'Korg', 'The Korg KROSS 2 provides over 1000 preset sounds, sequencer functionality, and sampling capabilities. Perfect for live performances and music production.', 'Lightweight workstation synthesizer for stage and studio use.', 750.00, NULL, 100, 'KOR-SY-003', 'physical', NULL, 'korg-kross-2-synthesizer.webp', NULL, 0, 1, '2026-02-28 14:55:42', '2026-02-28 14:55:42'),
(21, 3, 'Pearl Roadshow 5-Piece Drum Kit', 'pearl-roadshow-5-piece-drum-kit', 'Pearl', 'The Pearl Roadshow drum kit includes bass drum, snare, toms, cymbals, and hardware. Designed for beginners and intermediate drummers seeking quality sound and durability.', 'Complete 5-piece acoustic drum kit for beginners.', 550.00, NULL, 99, 'PEA-DR-001', 'physical', NULL, 'pearl-roadshow-5-piece-drum-kit.webp', NULL, 1, 1, '2026-02-28 14:58:21', '2026-02-28 15:41:57'),
(22, 3, 'Roland TD-07KV Electronic Drum Kit', 'roland-td-07kv-electronic-drum-kit', 'Roland', 'The Roland TD-07KV features realistic mesh drum heads, Bluetooth connectivity, and multiple drum kits. Ideal for home practice and studio recording.', 'Compact electronic drum kit with mesh heads.', 899.00, NULL, 100, 'ROL-ED-002', 'physical', NULL, 'roland-td-07kv-electronic-drum-kit.png', NULL, 0, 1, '2026-02-28 15:00:12', '2026-02-28 15:00:12'),
(23, 4, 'Yamaha YAS-280 Alto Saxophone', 'yamaha-yas-280-alto-saxophone', 'Yamaha', 'The Yamaha YAS-280 alto saxophone delivers smooth playability and accurate intonation. Designed for beginners and intermediate players seeking professional quality sound.', 'Student-level alto saxophone with rich tone.', 950.00, NULL, 99, 'YAM-WI-001', 'physical', NULL, 'yamaha-yas-280-alto-saxophone.png', NULL, 1, 1, '2026-02-28 15:01:57', '2026-02-28 15:40:06'),
(24, 4, 'Bach TR300H2 Trumpet', 'bach-tr300h2-trumpet', 'Bach', 'The Bach TR300H2 trumpet offers excellent projection, responsive valves, and durable construction. Ideal for school bands and live performances.', 'Bb trumpet suitable for students and performers.', 720.00, NULL, 100, 'BAC-BR-002', 'physical', NULL, 'bach-tr300h2-trumpet.webp', NULL, 0, 1, '2026-02-28 15:03:48', '2026-02-28 15:03:48'),
(25, 5, 'Stentor Student II 4/4 Violin Outfit', 'stentor-student-ii-4-4-violin-outfit', 'Stentor', 'The Stentor Student II violin is crafted from solid tonewoods and includes a bow, rosin, and protective case. Ideal for beginners and school students.', 'Full-size student violin with bow and case.', 180.00, NULL, 100, 'STE-ST-001', 'physical', NULL, 'stentor-student-ii-4-4-violin-outfit.webp', NULL, 0, 1, '2026-02-28 15:06:25', '2026-02-28 15:06:25'),
(26, 5, 'Stagg 4/4 Cello Outfit', 'stagg-4-4-cello-outfit', 'Stagg', 'The Stagg 4/4 cello includes bow and padded gig bag. Designed to provide warm sound and comfortable playability for learners.', 'Complete cello outfit for beginners.', 650.00, NULL, 100, 'STA-CE-003', 'physical', NULL, 'stagg-4-4-cello-outfit.png', NULL, 0, 1, '2026-02-28 15:08:43', '2026-02-28 15:08:43'),
(27, 6, 'D&#039;Addario Acoustic Guitar Strings', 'd-addario-acoustic-guitar-strings', 'D&#039;Addario', 'High-quality phosphor bronze acoustic guitar strings delivering warm tone and long-lasting durability. Suitable for all 6-string acoustic guitars.', 'Light gauge acoustic guitar string set.', 12.00, NULL, 100, 'DAD-AC-001', 'physical', NULL, 'd-addario-acoustic-guitar-strings.jpg', NULL, 0, 1, '2026-02-28 15:14:03', '2026-02-28 15:14:03'),
(28, 6, 'Vic Firth 5A Drumsticks', 'vic-firth-5a-drumsticks', 'Vic Firth', 'The Vic Firth 5A drumsticks are ideal for all musical styles. Made from premium hickory for durability and balanced feel.', 'Classic 5A wooden drumsticks pair.', 14.00, NULL, 100, 'VIC-DR-003', 'physical', NULL, 'vic-firth-5a-drumsticks.webp', NULL, 0, 1, '2026-02-28 15:16:20', '2026-02-28 15:16:20');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` between 1 and 5),
  `title` varchar(200) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `order_id`, `rating`, `title`, `body`, `is_approved`, `created_at`) VALUES
(1, 13, 5, 2, 5, 'feedback this product', 'good product', 1, '2026-02-28 13:16:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('customer','staff','admin') NOT NULL DEFAULT 'customer',
  `address_line1` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `role`, `address_line1`, `city`, `postcode`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'User', 'admin@melodymasters.com', '$2y$10$PaVRPezTAbLIjFhLpyfxGue.d.4xVP0tfKpNUA.jbQcGNvo4bFjaW', '+94 12 345 6789', 'admin', '234/B', 'Kadawatha', '11850', 1, '2026-02-25 08:58:33', '2026-02-27 18:50:03'),
(2, 'Imasha', 'Kalpani', 'staff@melodymasters.com', '$2y$10$xA7v8i.EkX/eWm9slqHHRepVg2PNUI4rMUgEmqHNKOjVtxEdE4lKW', '+94 12 345 6789', 'staff', '123/A', 'Kadawatha', '11850', 1, '2026-02-25 08:58:33', '2026-02-27 18:46:34'),
(5, 'Imasha', 'Kalpani', 'kalpani@gmail.com', '$2y$10$HUs7JB2Eu7iQBdfF2hG1ROGbOWIe6SI0C2uu/XQ.eztCrLFgOcp4u', '+94725312022', 'customer', '567/B', 'Kadawatha', '11850', 1, '2026-02-27 07:11:16', '2026-02-28 07:13:56'),
(6, 'Imasha', 'Pallawela', 'Pallawela@gmail.com', '$2y$10$ku06r3c3Xunr5lNVHpLaFuEXPQ6xz7y7h.vkaKJgshhI6jnyvBVOy', '+94725312022', 'staff', 'Gonahena', 'Kadawatha', '11850', 1, '2026-02-28 14:10:40', '2026-02-28 14:16:37'),
(7, 'Hiruni', 'Pallawela', 'hiruni@gmail.com', '$2y$10$sQaxXxlN7hZjsVvPXUoAR.KrBy0BPbOjwg/rQ3t1FhPG1tBmc4vsy', '+94725312022', 'customer', '886/A', 'Kadawatha', '11850', 1, '2026-02-28 15:19:11', '2026-02-28 15:19:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `digital_products`
--
ALTER TABLE `digital_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

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
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review` (`product_id`,`user_id`,`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `digital_products`
--
ALTER TABLE `digital_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `digital_products`
--
ALTER TABLE `digital_products`
  ADD CONSTRAINT `digital_products_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
