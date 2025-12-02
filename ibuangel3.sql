-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 30, 2025 at 02:38 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ibuangel`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `fullname`, `created_at`) VALUES
(1, 'IbuAngelAdmin', '$2y$10$t9p5MafFDK7TbYEKwDXOwOR1IoUOgBGfxXgjYaV.wHGRNAt1JVV7e', 'Ibu Angel', '2025-11-22 15:35:02');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(5, 'Hampers Lebaran'),
(2, 'Kue & Bolu'),
(1, 'Kue Kering'),
(4, 'Pernikahan'),
(3, 'Ulang Tahun Anak');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `items` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','processed','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `items`, `total_price`, `status`, `created_at`) VALUES
(1, 'Febrian', '[{\"name\":\"Fudgy Brownies\",\"price\":75000,\"ingredients\":\"Dark chocolate, butter, telur\",\"type\":\"regular\",\"qty\":1}]', 75000.00, 'completed', '2025-11-25 20:19:15'),
(2, 'Febrian', '[{\"name\":\"Rustic Wedding\",\"qty\":1,\"price\":750000,\"type\":\"custom\",\"category\":\"Pernikahan\",\"details\":\"aaaa\",\"date\":\"2222-02-22\"}]', 750000.00, 'completed', '2025-11-25 20:59:02'),
(4, 'Febrian', '[{\"name\":\"Fudgy Brownies\",\"price\":75000,\"ingredients\":\"Dark chocolate, butter, telur\",\"type\":\"regular\",\"qty\":1}]', 75000.00, 'processed', '2025-11-27 12:49:02');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('regular','custom') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'regular',
  `price` int DEFAULT '0',
  `price_min` int DEFAULT '0',
  `price_max` int DEFAULT '0',
  `ingredients` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_url` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `slug`, `name`, `category`, `type`, `price`, `price_min`, `price_max`, `ingredients`, `description`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'putri-salju', 'Putri Salju', 'Kue Kering', 'regular', 65000, 0, 0, 'Tepung maizena, mentega, gula halus, kacang mete', 'Lembut, lumer di mulut, taburan gula dingin.', 'https://img.freepik.com/premium-photo/kue-putri-salju-snow-white-cookies-with-crescent-shaped_583400-7112.jpg', '2025-11-22 15:33:59', '2025-11-25 18:13:17'),
(2, 'nastar-premium', 'Nastar Premium', 'Kue Kering', 'regular', 70000, 0, 0, 'Selai nanas homemade, wijsman, tepung', 'Isian nanas legit melimpah, kulit buttery.', 'https://cdn.idntimes.com/content-images/community/2021/03/fromandroid-78c7d9d0c3a3acda7593f32f72a98780_600x400.jpg', '2025-11-22 15:33:59', '2025-11-25 18:13:39'),
(3, 'kue-kacang', 'Kue Kacang', 'Kue Kering', 'regular', 55000, 0, 0, 'Kacang tanah sangrai, gula, minyak nabati', 'Gurih kacang asli, renyah dan wangi.', 'https://travistory.com/wp-content/uploads/2022/06/Resep-Kue-Kacang.jpg', '2025-11-22 15:33:59', '2025-11-25 18:14:52'),
(4, 'soft-choco-chips', 'Soft Choco Chips', 'Kue Kering', 'regular', 50000, 0, 0, 'Coklat, butter, brown sugar', 'Chewy di dalam, renyah di luar.', 'https://sallysbakingaddiction.com/wp-content/uploads/2012/08/soft-baked-chocolate-chip-cookies-5.jpg', '2025-11-22 15:33:59', '2025-11-25 18:15:06'),
(5, 'fudgy-brownies', 'Fudgy Brownies', 'Kue & Bolu', 'regular', 75000, 0, 0, 'Dark chocolate, butter, telur', 'Nyoklat banget, shiny crust di atas.', 'https://bakewithzoha.com/wp-content/uploads/2023/01/fudgy-brownies-overhead-partial.jpg', '2025-11-22 15:33:59', '2025-11-25 18:16:10'),
(6, 'lapis-surabaya', 'Lapis Surabaya', 'Kue & Bolu', 'regular', 85000, 0, 0, 'Kuning telur, butter, selai strawberry', 'Lembut, moist, resep kuno.', 'https://asset-a.grid.id/crop/0x0:0x0/700x465/photo/sasefoto/original/23276-lapis-surabaya.jpg', '2025-11-22 15:33:59', '2025-11-25 18:16:31'),
(7, 'bolu-pisang', 'Bolu Pisang', 'Kue & Bolu', 'regular', 60000, 0, 0, 'Pisang ambon matang, gula palem, kayu manis', 'Wangi pisang alami, tekstur empuk.', 'https://asset.kompas.com/crops/8LUtfT4ZT9RP6rZm7oV6XwcYYnA=/0x0:1000x667/1200x800/data/photo/2023/09/27/6513a25580a9b.jpg', '2025-11-22 15:33:59', '2025-11-25 18:16:46'),
(11, 'kue-tema-spiderman-ulang-tahun-anak', 'Kue Tema Spiderman', 'Ulang Tahun Anak', 'custom', 0, 150000, 300000, NULL, 'Hero favorit anak dengan detail icing keren.', 'https://i.pinimg.com/736x/c3/b9/d1/c3b9d158ebb0cc1f3f300a40e4c00b92.jpg', '2025-11-22 15:33:59', '2025-11-22 15:33:59'),
(12, 'kue-tema-princess-ulang-tahun-anak', 'Kue Tema Princess', 'Ulang Tahun Anak', 'custom', 0, 200000, 400000, NULL, 'Kastil megah untuk putri kecil Anda.', 'https://cdn.shopify.com/s/files/1/1175/4972/products/Princess_20Cake_20-_20118.jpg?v=1666101697', '2025-11-22 15:33:59', '2025-11-22 15:33:59'),
(13, 'kue-tema-doraemon-ulang-tahun-anak', 'Kue Tema Doraemon', 'Ulang Tahun Anak', 'custom', 0, 150000, 300000, NULL, 'Warna biru ceria dengan figur lucu.', 'https://cdn.bestcakedesign.com/wp-content/uploads/doraemon-cake-design-02.png', '2025-11-22 15:33:59', '2025-11-22 15:33:59'),
(14, 'kue-tema-make-up-sweet-seventeen', 'Kue Tema Make Up', 'Sweet Seventeen', 'custom', 0, 250000, 450000, NULL, 'Dekorasi lipstik & bedak fondant yang chic.', 'https://down-id.img.susercontent.com/file/id-11134207-7rask-m19g6tfbxxr2ad', '2025-11-22 15:33:59', '2025-11-22 15:33:59'),
(15, 'korean-minimalist-sweet-seventeen', 'Korean Minimalist', 'Sweet Seventeen', 'custom', 0, 180000, 350000, NULL, 'Desain simpel estetik dengan warna pastel.', 'https://caketogether.com/cdn/shop/products/CopyofIMG_1005_3450x3450.jpg?v=1619858063', '2025-11-22 15:33:59', '2025-11-22 15:33:59'),
(16, 'rustic-wedding-pernikahan', 'Rustic Wedding', 'Pernikahan', 'custom', 0, 750000, 2500000, NULL, 'Naked cake dengan hiasan bunga segar.', 'https://clweddings.com/wp-content/uploads/2024/09/Rustic-Wedding-Cake-Ideas-15.png', '2025-11-22 15:33:59', '2025-11-22 15:33:59'),
(17, 'elegant-white-pernikahan', 'Elegant White', 'Pernikahan', 'custom', 0, 1000000, 3000000, NULL, 'Putih bersih dengan aksen gold mewah.', 'https://cdn-image.hipwee.com/wp-content/uploads/2021/10/hipwee-Gold-Wedding-Theme-_-Wedding-Ideas-By-Colour-_-CHWV-500x750.jpg', '2025-11-22 15:33:59', '2025-11-22 15:33:59');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int NOT NULL,
  `setting_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_group` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_group`) VALUES
(1, 'hero_title', 'Baked with love,<br>Served with Joy.', 'home'),
(2, 'hero_desc', 'Kue klasik dengan sentuhan modern. Dibuat segar setiap hari dari dapur rumah kami untuk momen istimewa Anda.', 'home'),
(3, 'marquee_text', 'Fresh Tiap Hari • Bahan Premium • 100% Halal • Buatan Rumahan • Tanpa Bahan Pengawet', 'home'),
(4, 'about_title', 'Dari Dapur Sederhana, Penuh Rasa Cinta.', 'home'),
(5, 'about_desc', 'Berawal dari hobi membuat kue untuk keluarga, Ibu Angel kini hadir untuk berbagi kebahagiaan yang sama dengan Anda.', 'home'),
(6, 'about_img', 'dapur.png', 'home'),
(7, 'custom_title', 'Custom Cakes', 'custom'),
(8, 'custom_desc', 'Wujudkan kue impian untuk momen spesial Anda. Mulai dari Ulang Tahun Anak, Sweet Seventeen, hingga Pernikahan.', 'custom'),
(9, 'cta_title', 'Punya Desain Sendiri?', 'custom'),
(10, 'cta_desc', 'Konsultasikan ide kue impian Anda langsung dengan Ibu Angel.', 'custom'),
(81, 'contact_phone', '6281351966722', NULL),
(82, 'contact_address', 'Keluang Paser Jaya, Kecamatan Kuaro, Kabupaten Paser, Kalimantan Timur', NULL),
(83, 'gmaps_url', 'https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d4413.852771392167!2d116.12304782079818!3d-1.8775696006154787!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zMcKwNTInMzUuOCJTIDExNsKwMDcnMzIuNSJF!5e1!3m2!1sen!2sid!4v1764154336432!5m2!1sen!2sid', NULL),
(123, 'style_font_preset', 'default', 'style'),
(124, 'style_base_size', '16', 'style'),
(125, 'color_bg_cream', '#fdfbf7', 'style'),
(126, 'color_card', '#FFFFFF', 'style'),
(127, 'color_text_dark', '#2c1810', 'style'),
(128, 'color_accent', '#d37757', 'style');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

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
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
