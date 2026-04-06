-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 15, 2026 at 03:42 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fpl_cinemas`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'ADMIN', 'admin@cinema.local', '$2y$12$XLLCTGtQ6lESLmQlBoPUpe5sQ9teodlaVaobM8siSo20iObaY6.GK', '2026-03-03 00:58:29', '2026-03-03 00:59:03');

-- --------------------------------------------------------

--
-- Table structure for table `auditoriums`
--

CREATE TABLE `auditoriums` (
  `id` bigint UNSIGNED NOT NULL,
  `public_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cinema_id` bigint UNSIGNED NOT NULL,
  `auditorium_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `screen_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'STANDARD',
  `seat_map_version` int UNSIGNED NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `auditoriums`
--

INSERT INTO `auditoriums` (`id`, `public_id`, `cinema_id`, `auditorium_code`, `name`, `screen_type`, `seat_map_version`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '01KJR54DBG4Y9KH4SJZ9JW7VRM', 1, 'AUD1_1', 'Phòng 1', 'STANDARD', 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(2, '01KJR54DBJN07N3XYD260Y1PSJ', 1, 'AUD1_2', 'Phòng 2', 'STANDARD', 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(3, '01KJR54DBKTBE385HMYKFK2GB3', 1, 'AUD1_3', 'Phòng 3', 'IMAX', 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(4, '01KJR54DBM4Y8Y65Y827CQ9WCD', 2, 'AUD2_1', 'Phòng 1', 'STANDARD', 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(5, '01KJR54DBNQ7ZQWESF1NRFMJWX', 2, 'AUD2_2', 'Phòng 2', 'STANDARD', 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(6, '01KJR54DBQ206D5050K527SKDZ', 2, 'AUD2_3', 'Phòng 3', 'IMAX', 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `actor_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actor_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint UNSIGNED NOT NULL,
  `public_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `show_id` bigint UNSIGNED NOT NULL,
  `cinema_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `sales_channel_id` bigint UNSIGNED DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtotal_amount` bigint UNSIGNED NOT NULL DEFAULT '0',
  `discount_amount` bigint UNSIGNED NOT NULL DEFAULT '0',
  `total_amount` bigint UNSIGNED NOT NULL DEFAULT '0',
  `paid_amount` bigint UNSIGNED NOT NULL DEFAULT '0',
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VND',
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `public_id`, `booking_code`, `show_id`, `cinema_id`, `customer_id`, `sales_channel_id`, `status`, `contact_name`, `contact_phone`, `contact_email`, `subtotal_amount`, `discount_amount`, `total_amount`, `paid_amount`, `currency`, `notes`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, '01KJR54ERKZCSV3TMBEYX11BG5', 'BK26030200001', 13, 1, 8, 2, 'PAID', 'Khổng Vũ', '0907481655', 'hoa89@example.net', 398640, 0, 398640, 398640, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(2, '01KJR54ESDFNZ69DKDSFHMCC68', 'BK26030200002', 11, 1, 2, 2, 'PAID', 'Em. Bình Định', '0986202677', 'diep43@example.org', 174831, 0, 174831, 174831, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(3, '01KJR54ESS71NRM0S9EK1XHRAK', 'BK26030200003', 18, 1, 12, 1, 'PAID', 'Em. Đỗ Tài', '0979880240', 'qca@example.net', 201970, 0, 201970, 201970, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(4, '01KJR54ET4J7AQ8B2RSA5129V3', 'BK26030200004', 5, 1, 21, 2, 'PAID', 'Ông. Thào Hội', '0971325607', 'can.tong@example.com', 66590, 0, 66590, 66590, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(5, '01KJR54ETDRV31N2SYZ4J2YQZZ', 'BK26030200005', 7, 1, 7, 2, 'PAID', 'Em. Điền Phong', '0952035134', 'khoi54@example.com', 282483, 0, 282483, 282483, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(6, '01KJR54ETTAY38BDMV9515249K', 'BK26030200006', 9, 1, 8, 2, 'PAID', 'Khổng Vũ', '0907481655', 'hoa89@example.net', 225520, 0, 225520, 225520, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(7, '01KJR54EV7T7RSJ18ZGRYJWFVC', 'BK26030200007', 32, 2, 1, 1, 'PAID', 'Chị. Lô Du', '0918285714', 'nghiep.ngan@example.com', 440276, 0, 440276, 440276, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(8, '01KJR54EVV48B9X9JBP6SAFYXV', 'BK26030200008', 20, 2, 8, 1, 'PAID', 'Khổng Vũ', '0907481655', 'hoa89@example.net', 133804, 0, 133804, 133804, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(9, '01KJR54EW33SHBB494Q3H1NYJ0', 'BK26030200009', 16, 1, 11, 1, 'PAID', 'Cam Hiếu Tiến', '0941250273', 'nninh@example.net', 78182, 0, 78182, 78182, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(10, '01KJR54EWB3ZTQQD4T70HEBG43', 'BK26030200010', 29, 2, 19, 1, 'PAID', 'Anh. Hứa Cương Vượng', '0912781810', 'tra98@example.net', 437758, 0, 437758, 437758, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(11, '01KJR54EWVRKFCJJSR4K7PERPJ', 'BK26030200011', 16, 1, 21, 1, 'PAID', 'Ông. Thào Hội', '0971325607', 'can.tong@example.com', 294546, 0, 294546, 294546, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(12, '01KJR54EX8VCTRZ8XECJCJQ85V', 'BK26030200012', 13, 1, 29, 2, 'PAID', 'Tống Luận', '0994824368', 'huyen.tiep@example.com', 353894, 0, 353894, 353894, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(13, '01KJR54EXSD9YY3V53M7Q56FQA', 'BK26030200013', 16, 1, 5, 1, 'PAID', 'Chị. Mạch Tâm Lộc', '0975624090', 'ta.tuyen@example.org', 117274, 0, 117274, 117274, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(14, '01KJR54EY4Z31Q56WMD12K797A', 'BK26030200014', 26, 2, 23, 1, 'PAID', 'Khoa Trực', '0923466583', 'alai@example.net', 205097, 0, 205097, 205097, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(15, '01KJR54EYF287V6J7MKQGAEWRX', 'BK26030200015', 8, 1, 30, 1, 'PAID', 'Cụ. Hùng Kỳ Bình', '0985855455', 'bda@example.net', 312976, 0, 312976, 312976, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(16, '01KJR54EZ0HPAVA8489MS7CGFG', 'BK26030200016', 8, 1, 14, 1, 'PAID', 'Chị. Cù Nhiên', '0964315352', 'duong.bac@example.org', 331326, 0, 331326, 331326, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(17, '01KJR54EZGNNK3VCP58CZGJ3VC', 'BK26030200017', 14, 1, 24, 2, 'PAID', 'Uông Thy Ái', '0989690443', 'tue09@example.com', 57773, 0, 57773, 57773, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(18, '01KJR54EZR2Y9GPPQASZV0P2D9', 'BK26030200018', 28, 2, 3, 2, 'PAID', 'Ông. Dư Phong Bắc', '0925915142', 'khuat.nguyet@example.net', 288397, 0, 288397, 288397, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(19, '01KJR54F0AGC0V1BHKR87FBC6R', 'BK26030200019', 27, 2, 23, 1, 'PAID', 'Khoa Trực', '0923466583', 'alai@example.net', 253694, 0, 253694, 253694, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(20, '01KJR54F0QQFP584NYMCMEHDPM', 'BK26030200020', 30, 2, 13, 2, 'PAID', 'Chú. Khương Lâm Phong', '0976748044', 'vbien@example.net', 188088, 0, 188088, 188088, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(21, '01KJR54F12YVYYGYVKXZYSB8Z7', 'BK26030200021', 23, 2, 7, 2, 'PAID', 'Em. Điền Phong', '0952035134', 'khoi54@example.com', 125847, 0, 125847, 125847, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(22, '01KJR54F19KDJNNZFDD6279RMV', 'BK26030200022', 34, 2, 7, 2, 'PAID', 'Em. Điền Phong', '0952035134', 'khoi54@example.com', 308798, 0, 308798, 308798, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(23, '01KJR54F1RSFXTGMPVQ75V4W87', 'BK26030200023', 14, 1, 5, 1, 'PAID', 'Chị. Mạch Tâm Lộc', '0975624090', 'ta.tuyen@example.org', 311974, 0, 311974, 311974, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(24, '01KJR54F2BZ7BMMN6RMD63W0NF', 'BK26030200024', 33, 2, 19, 1, 'PAID', 'Anh. Hứa Cương Vượng', '0912781810', 'tra98@example.net', 302538, 0, 302538, 302538, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(25, '01KJR54F30G6ABKREEFJ8ZSKSA', 'BK26030200025', 22, 2, 3, 1, 'PAID', 'Ông. Dư Phong Bắc', '0925915142', 'khuat.nguyet@example.net', 420228, 0, 420228, 420228, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(26, '01KJR54F3JYRQRQM2CAACJKY6J', 'BK26030200026', 26, 2, 21, 2, 'PAID', 'Ông. Thào Hội', '0971325607', 'can.tong@example.com', 410552, 0, 410552, 410552, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(27, '01KJR54F471XKQ63SG3AD05M49', 'BK26030200027', 34, 2, 23, 1, 'PAID', 'Khoa Trực', '0923466583', 'alai@example.net', 325828, 0, 325828, 325828, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(28, '01KJR54F4VJFE3J9Y6N6GF5YCF', 'BK26030200028', 28, 2, 30, 2, 'PAID', 'Cụ. Hùng Kỳ Bình', '0985855455', 'bda@example.net', 231955, 0, 231955, 231955, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(29, '01KJR54F5FDJKYWMSV1EVCSFFS', 'BK26030200029', 11, 1, 3, 2, 'PAID', 'Ông. Dư Phong Bắc', '0925915142', 'khuat.nguyet@example.net', 258757, 0, 258757, 258757, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(30, '01KJR54F61YG610HAEGB8QF669', 'BK26030200030', 18, 1, 16, 2, 'PAID', 'Bà. Châu Thu Hoa', '0956498167', 'hoai97@example.org', 287470, 0, 287470, 287470, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(31, '01KJR54F6CX0K268ZDF5VAZDJN', 'BK26030200031', 9, 1, 27, 2, 'PAID', 'Em. Mang Khanh', '0980566514', 'gdiep@example.org', 60906, 0, 60906, 60906, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(32, '01KJR54F6NPMR2RVJWJBXYBHST', 'BK26030200032', 30, 2, 8, 1, 'PAID', 'Khổng Vũ', '0907481655', 'hoa89@example.net', 517344, 0, 517344, 517344, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(33, '01KJR54F7DTW4EXXZQ7B9F7AW5', 'BK26030200033', 9, 1, 5, 2, 'PAID', 'Chị. Mạch Tâm Lộc', '0975624090', 'ta.tuyen@example.org', 150235, 0, 150235, 150235, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(34, '01KJR54F7WZ21B05NSJ0ZYC1B4', 'BK26030200034', 15, 1, 17, 1, 'PAID', 'Nông Tân', '0925411823', 'cai.quoc@example.org', 445145, 0, 445145, 445145, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(35, '01KJR54F8WM66D2S6YPMD6HH4Q', 'BK26030200035', 23, 2, 12, 1, 'PAID', 'Em. Đỗ Tài', '0979880240', 'qca@example.net', 454512, 0, 454512, 454512, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(36, '01KJR54F9QC6D1QZF951JWE21P', 'BK26030200036', 1, 1, 15, 2, 'PAID', 'Hình Định', '0949735868', 'hoa.yen@example.org', 306705, 0, 306705, 306705, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(37, '01KJR54FAJA3Y9HTD90CA9DDFZ', 'BK26030200037', 17, 1, 7, 2, 'PAID', 'Em. Điền Phong', '0952035134', 'khoi54@example.com', 443111, 0, 443111, 443111, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(38, '01KJR54FBBAMMQ7CZ5E8G4WZ3Z', 'BK26030200038', 30, 2, 26, 2, 'PAID', 'Chú. Viên Tiền', '0903325888', 'trac.chuong@example.net', 172838, 0, 172838, 172838, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(39, '01KJR54FBT02GT0Q1GNFY22FDB', 'BK26030200039', 23, 2, 25, 1, 'PAID', 'Anh. Thi Đạo', '0990748097', 'nghiem.bang@example.net', 529025, 0, 529025, 529025, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(40, '01KJR54FCRBET90MCCFW494QYS', 'BK26030200040', 18, 1, 15, 2, 'PAID', 'Hình Định', '0949735868', 'hoa.yen@example.org', 545820, 0, 545820, 545820, 'VND', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45');

-- --------------------------------------------------------

--
-- Table structure for table `booking_discounts`
--

CREATE TABLE `booking_discounts` (
  `id` bigint UNSIGNED NOT NULL,
  `booking_id` bigint UNSIGNED NOT NULL,
  `promotion_id` bigint UNSIGNED DEFAULT NULL,
  `coupon_id` bigint UNSIGNED DEFAULT NULL,
  `applied_to` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ORDER',
  `discount_amount` bigint UNSIGNED NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `booking_products`
--

CREATE TABLE `booking_products` (
  `id` bigint UNSIGNED NOT NULL,
  `booking_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `qty` int UNSIGNED NOT NULL,
  `unit_price_amount` bigint UNSIGNED NOT NULL,
  `discount_amount` bigint UNSIGNED NOT NULL DEFAULT '0',
  `final_amount` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `booking_tickets`
--

CREATE TABLE `booking_tickets` (
  `id` bigint UNSIGNED NOT NULL,
  `booking_id` bigint UNSIGNED NOT NULL,
  `show_id` bigint UNSIGNED NOT NULL,
  `seat_id` bigint UNSIGNED NOT NULL,
  `ticket_type_id` bigint UNSIGNED NOT NULL,
  `seat_type_id` bigint UNSIGNED NOT NULL,
  `unit_price_amount` bigint UNSIGNED NOT NULL,
  `discount_amount` bigint UNSIGNED NOT NULL DEFAULT '0',
  `final_price_amount` bigint UNSIGNED NOT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'RESERVED',
  `active_lock` tinyint GENERATED ALWAYS AS ((case when (`status` in (_utf8mb4'RESERVED',_utf8mb4'ISSUED')) then 1 else NULL end)) STORED,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `booking_tickets`
--

INSERT INTO `booking_tickets` (`id`, `booking_id`, `show_id`, `seat_id`, `ticket_type_id`, `seat_type_id`, `unit_price_amount`, `discount_amount`, `final_price_amount`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 13, 271, 1, 1, 98304, 0, 98304, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(2, 1, 13, 285, 1, 1, 98304, 0, 98304, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(3, 1, 13, 270, 3, 1, 73728, 0, 73728, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(4, 1, 13, 304, 1, 2, 128304, 0, 128304, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(5, 2, 11, 197, 1, 1, 94503, 0, 94503, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(6, 2, 11, 202, 2, 1, 80328, 0, 80328, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(7, 3, 18, 272, 2, 1, 92797, 0, 92797, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(8, 3, 18, 258, 1, 1, 109173, 0, 109173, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(9, 4, 5, 17, 3, 1, 66590, 0, 66590, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(10, 5, 7, 160, 1, 1, 99117, 0, 99117, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(11, 5, 7, 233, 2, 1, 84249, 0, 84249, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(12, 5, 7, 219, 1, 1, 99117, 0, 99117, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(13, 6, 9, 205, 3, 1, 60906, 0, 60906, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(14, 6, 9, 179, 3, 2, 83406, 0, 83406, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(15, 6, 9, 121, 1, 1, 81208, 0, 81208, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(16, 7, 32, 663, 3, 2, 88614, 0, 88614, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(17, 7, 32, 668, 2, 2, 100429, 0, 100429, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(18, 7, 32, 693, 1, 1, 88152, 0, 88152, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(19, 7, 32, 637, 2, 1, 74929, 0, 74929, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(20, 7, 32, 716, 1, 1, 88152, 0, 88152, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(21, 8, 20, 426, 1, 2, 133804, 0, 133804, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(22, 9, 16, 256, 1, 1, 78182, 0, 78182, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(23, 10, 29, 577, 2, 1, 78722, 0, 78722, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(24, 10, 29, 542, 3, 2, 91961, 0, 91961, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(25, 10, 29, 591, 3, 3, 114461, 0, 114461, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(26, 10, 29, 592, 1, 3, 152614, 0, 152614, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(27, 11, 16, 332, 1, 1, 78182, 0, 78182, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(28, 11, 16, 291, 1, 2, 108182, 0, 108182, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(29, 11, 16, 298, 1, 2, 108182, 0, 108182, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(30, 12, 13, 256, 2, 1, 83558, 0, 83558, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(31, 12, 13, 241, 3, 1, 73728, 0, 73728, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(32, 12, 13, 245, 1, 1, 98304, 0, 98304, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(33, 12, 13, 323, 1, 1, 98304, 0, 98304, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(34, 13, 16, 257, 3, 1, 58637, 0, 58637, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(35, 13, 16, 275, 3, 1, 58637, 0, 58637, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(36, 14, 26, 541, 1, 2, 124647, 0, 124647, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(37, 14, 26, 578, 2, 1, 80450, 0, 80450, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(38, 15, 8, 178, 3, 2, 84745, 0, 84745, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(39, 15, 8, 205, 3, 1, 62245, 0, 62245, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(40, 15, 8, 219, 1, 1, 82993, 0, 82993, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(41, 15, 8, 240, 1, 1, 82993, 0, 82993, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(42, 16, 8, 156, 1, 1, 82993, 0, 82993, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(43, 16, 8, 225, 2, 1, 70544, 0, 70544, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(44, 16, 8, 230, 3, 3, 107245, 0, 107245, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(45, 16, 8, 151, 2, 1, 70544, 0, 70544, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(46, 17, 14, 335, 3, 1, 57773, 0, 57773, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(47, 18, 28, 538, 2, 2, 85246, 0, 85246, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(48, 18, 28, 590, 3, 3, 97717, 0, 97717, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(49, 18, 28, 573, 3, 1, 52717, 0, 52717, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(50, 18, 28, 567, 3, 1, 52717, 0, 52717, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(51, 19, 27, 511, 2, 1, 71839, 0, 71839, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(52, 19, 27, 548, 2, 2, 97339, 0, 97339, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(53, 19, 27, 567, 1, 1, 84516, 0, 84516, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(54, 20, 30, 556, 2, 1, 86419, 0, 86419, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(55, 20, 30, 483, 1, 1, 101669, 0, 101669, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(56, 21, 23, 423, 2, 2, 125847, 0, 125847, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(57, 22, 34, 654, 2, 2, 98501, 0, 98501, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(58, 22, 34, 712, 1, 3, 145884, 0, 145884, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(59, 22, 34, 629, 3, 1, 64413, 0, 64413, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(60, 23, 14, 262, 3, 1, 57773, 0, 57773, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(61, 23, 14, 269, 3, 1, 57773, 0, 57773, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(62, 23, 14, 246, 2, 1, 65476, 0, 65476, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(63, 23, 14, 354, 2, 1, 65476, 0, 65476, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(64, 23, 14, 327, 2, 1, 65476, 0, 65476, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(65, 24, 33, 638, 2, 1, 63644, 0, 63644, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(66, 24, 33, 628, 1, 1, 74875, 0, 74875, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(67, 24, 33, 650, 2, 2, 89144, 0, 89144, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(68, 24, 33, 675, 1, 1, 74875, 0, 74875, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(69, 25, 22, 441, 1, 1, 97557, 0, 97557, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(70, 25, 22, 473, 1, 1, 97557, 0, 97557, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(71, 25, 22, 412, 1, 2, 127557, 0, 127557, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(72, 25, 22, 439, 1, 1, 97557, 0, 97557, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(73, 26, 26, 489, 1, 1, 94647, 0, 94647, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(74, 26, 26, 485, 3, 1, 70985, 0, 70985, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(75, 26, 26, 498, 3, 1, 70985, 0, 70985, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(76, 26, 26, 496, 2, 1, 80450, 0, 80450, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(77, 26, 26, 551, 3, 2, 93485, 0, 93485, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(78, 27, 34, 637, 3, 1, 64413, 0, 64413, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(79, 27, 34, 649, 2, 2, 98501, 0, 98501, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(80, 27, 34, 670, 2, 2, 98501, 0, 98501, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(81, 27, 34, 675, 3, 1, 64413, 0, 64413, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(82, 28, 28, 506, 2, 1, 59746, 0, 59746, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(83, 28, 28, 493, 3, 1, 52717, 0, 52717, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(84, 28, 28, 575, 2, 1, 59746, 0, 59746, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(85, 28, 28, 570, 2, 1, 59746, 0, 59746, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(86, 29, 11, 168, 1, 1, 94503, 0, 94503, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(87, 29, 11, 192, 3, 2, 93377, 0, 93377, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(88, 29, 11, 156, 3, 1, 70877, 0, 70877, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(89, 30, 18, 302, 2, 2, 118297, 0, 118297, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(90, 30, 18, 350, 1, 3, 169173, 0, 169173, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(91, 31, 9, 133, 3, 1, 60906, 0, 60906, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(92, 32, 30, 512, 3, 1, 76252, 0, 76252, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(93, 32, 30, 516, 3, 1, 76252, 0, 76252, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(94, 32, 30, 535, 2, 2, 111919, 0, 111919, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(95, 32, 30, 548, 1, 2, 131669, 0, 131669, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(96, 32, 30, 591, 3, 3, 121252, 0, 121252, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(97, 33, 9, 136, 2, 1, 69027, 0, 69027, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(98, 33, 9, 130, 1, 1, 81208, 0, 81208, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(99, 34, 15, 351, 3, 3, 110241, 0, 110241, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(100, 34, 15, 322, 2, 1, 73940, 0, 73940, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(101, 34, 15, 271, 1, 1, 86988, 0, 86988, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(102, 34, 15, 285, 1, 1, 86988, 0, 86988, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(103, 34, 15, 245, 1, 1, 86988, 0, 86988, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(104, 35, 23, 477, 2, 1, 100347, 0, 100347, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(105, 35, 23, 400, 1, 1, 118055, 0, 118055, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(106, 35, 23, 361, 1, 1, 118055, 0, 118055, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(107, 35, 23, 405, 1, 1, 118055, 0, 118055, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(108, 36, 1, 107, 3, 1, 70778, 0, 70778, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(109, 36, 1, 104, 3, 1, 70778, 0, 70778, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(110, 36, 1, 44, 3, 1, 70778, 0, 70778, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(111, 36, 1, 29, 1, 1, 94371, 0, 94371, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(112, 37, 17, 293, 2, 2, 115455, 0, 115455, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(113, 37, 17, 333, 2, 1, 89955, 0, 89955, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(114, 37, 17, 291, 3, 2, 101872, 0, 101872, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(115, 37, 17, 311, 1, 2, 135829, 0, 135829, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(116, 38, 30, 494, 2, 1, 86419, 0, 86419, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(117, 38, 30, 493, 2, 1, 86419, 0, 86419, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(118, 39, 23, 406, 1, 1, 118055, 0, 118055, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(119, 39, 23, 377, 3, 1, 88541, 0, 88541, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(120, 39, 23, 435, 3, 1, 88541, 0, 88541, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(121, 39, 23, 469, 3, 3, 133541, 0, 133541, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(122, 39, 23, 398, 2, 1, 100347, 0, 100347, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(123, 40, 18, 263, 2, 1, 92797, 0, 92797, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(124, 40, 18, 306, 1, 2, 139173, 0, 139173, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(125, 40, 18, 299, 1, 2, 139173, 0, 139173, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(126, 40, 18, 282, 2, 1, 92797, 0, 92797, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(127, 40, 18, 326, 3, 1, 81880, 0, 81880, 'ISSUED', '2026-03-02 20:51:45', '2026-03-02 20:51:45');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cinemas`
--

CREATE TABLE `cinemas` (
  `id` bigint UNSIGNED NOT NULL,
  `public_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chain_id` bigint UNSIGNED NOT NULL,
  `cinema_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Ho_Chi_Minh',
  `address_line` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ward` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VN',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `opening_hours` json DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `cinemas`
--

INSERT INTO `cinemas` (`id`, `public_id`, `chain_id`, `cinema_code`, `name`, `phone`, `email`, `timezone`, `address_line`, `ward`, `district`, `province`, `country_code`, `latitude`, `longitude`, `opening_hours`, `status`, `created_at`, `updated_at`) VALUES
(1, '01KJR54DAQ6C545MZ3D0PG0BDR', 1, 'HN02', 'BetaCinema - Xuân Thuỷ', '+84-60-315-1643', 'nhan99@example.org', 'Asia/Ha_Noi', 'Xuân Thuỷ', 'Xuân Thuỷ', 'Cầu Giấy', 'Hà Nội', 'VN', NULL, NULL, '{\"fri\": \"09:00-24:00\", \"mon\": \"09:00-23:00\", \"sat\": \"09:00-24:00\", \"sun\": \"09:00-23:00\", \"thu\": \"09:00-23:00\", \"tue\": \"09:00-23:00\", \"wed\": \"09:00-23:00\"}', 'ACTIVE', '2026-03-02 20:51:43', '2026-03-03 01:09:38'),
(2, '01KJR54DBEBZ8PGYBB5QMNAHWN', 1, 'HN01', 'BetaCinema - Giải phóng', '(0280)095-1665', 'ty.thoi@example.org', 'Asia/Ha_Noi', 'Giải phóng', 'Phương Liệt', 'Giải phóng', 'Hà Nội', 'VN', NULL, NULL, '{\"fri\": \"09:00-24:00\", \"mon\": \"09:00-23:00\", \"sat\": \"09:00-24:00\", \"sun\": \"09:00-23:00\", \"thu\": \"09:00-23:00\", \"tue\": \"09:00-23:00\", \"wed\": \"09:00-23:00\"}', 'ACTIVE', '2026-03-02 20:51:43', '2026-03-03 01:08:14');

-- --------------------------------------------------------

--
-- Table structure for table `cinema_chains`
--

CREATE TABLE `cinema_chains` (
  `id` bigint UNSIGNED NOT NULL,
  `public_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chain_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `legal_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hotline` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `cinema_chains`
--

INSERT INTO `cinema_chains` (`id`, `public_id`, `chain_code`, `name`, `legal_name`, `tax_code`, `hotline`, `email`, `website`, `status`, `created_at`, `updated_at`) VALUES
(1, '01KJR54DAMS7Z32RZ7XWD9N365', 'beta', 'BETA CINEMA', 'CÔNG TY BETA CINEMA', '0312345678', '1900 1234', 'support@cinevn.test', 'https://betacinema.test', 'ACTIVE', '2026-03-02 20:51:43', '2026-03-03 05:45:50'),
(2, '01KJSBPRJEVGB84RH9X8DK3Y0N', 'bhd', 'BHD CINEMA', 'CÔNG TY BHD CINEMA', NULL, '1900 6789', NULL, NULL, 'ACTIVE', '2026-03-03 01:05:50', '2026-03-03 01:06:06'),
(3, '01KJSBRYAYHAQ1Q4MFCGPSV5E8', 'cgv', 'CGV CINEMA', 'CÔNG TY CGV CINEMA', NULL, '1900 2307', NULL, NULL, 'ACTIVE', '2026-03-03 01:07:02', '2026-03-03 01:07:02');

-- --------------------------------------------------------

--
-- Table structure for table `content_ratings`
--

CREATE TABLE `content_ratings` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_age` int UNSIGNED DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `content_ratings`
--

INSERT INTO `content_ratings` (`id`, `code`, `name`, `min_age`, `description`, `created_at`, `updated_at`) VALUES
(1, 'P', 'P', NULL, 'Phù hợp mọi lứa tuổi', '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(2, 'T13', 'T13', 13, 'Từ 13 tuổi', '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(3, 'T16', 'T16', 16, 'Từ 16 tuổi', '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(4, 'T18', 'T18', 18, 'Từ 18 tuổi', '2026-03-02 20:51:44', '2026-03-02 20:51:44');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint UNSIGNED NOT NULL,
  `promotion_id` bigint UNSIGNED NOT NULL,
  `code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ISSUED',
  `issued_at` datetime DEFAULT NULL,
  `redeemed_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint UNSIGNED NOT NULL,
  `public_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `public_id`, `full_name`, `phone`, `email`, `dob`, `gender`, `city`, `created_at`, `updated_at`) VALUES
(1, '01KJR54EQ36KE7NFS5AMT1BRJP', 'Chị. Lô Du', '0918285714', 'nghiep.ngan@example.com', '1973-03-01', 'MALE', 'Cần Thơ', '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(2, '01KJR54EQ64R79HXS2SEJQPER9', 'Em. Bình Định', '0986202677', 'diep43@example.org', '1998-10-31', 'OTHER', 'Hải Phòng', '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(3, '01KJR54EQ84H0NC829X5R2F59Z', 'Ông. Dư Phong Bắc', '0925915142', 'khuat.nguyet@example.net', '2001-03-08', 'MALE', 'Hồ Chí Minh', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(4, '01KJR54EQ9YKTX49PJEYSSKRS6', 'Ngân Tố Chinh', '0919123575', 'yau@example.net', '1989-10-08', 'OTHER', 'Đà Nẵng', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(5, '01KJR54EQBTFK0MZEGF1NPFK3E', 'Chị. Mạch Tâm Lộc', '0975624090', 'ta.tuyen@example.org', '1980-06-19', 'MALE', 'Hải Phòng', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(6, '01KJR54EQCDVBQEMWXKTSWJCCA', 'Bác. Tiếp Thương', '0911927714', 'au.le@example.net', '1992-09-03', 'FEMALE', 'Hồ Chí Minh', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(7, '01KJR54EQET3CJNN8HQCFHDK9Y', 'Em. Điền Phong', '0952035134', 'khoi54@example.com', '1983-05-09', 'MALE', 'Hà Nội', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(8, '01KJR54EQG9397B6C34V3200MR', 'Khổng Vũ', '0907481655', 'hoa89@example.net', '2003-12-11', 'FEMALE', 'Đà Nẵng', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(9, '01KJR54EQHBN43M481X2GBBDHS', 'Bác. Cam Phụng Uyên', '0999660423', 'hieu06@example.org', '1991-09-13', 'FEMALE', 'Đà Nẵng', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(10, '01KJR54EQKXHB8S4EKH78N45FJ', 'Bác. Cung Nhu', '0973186460', 'loc.thai@example.net', '1971-01-19', 'MALE', 'Hải Phòng', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(11, '01KJR54EQM01GEPASHKNEZ46A8', 'Cam Hiếu Tiến', '0941250273', 'nninh@example.net', '1986-03-17', 'OTHER', 'Cần Thơ', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(12, '01KJR54EQPEP6WMBZ3B2WRYY0W', 'Em. Đỗ Tài', '0979880240', 'qca@example.net', '2010-12-22', 'OTHER', 'Hải Phòng', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(13, '01KJR54EQQ5GNB8JEKGSRYKYAW', 'Chú. Khương Lâm Phong', '0976748044', 'vbien@example.net', '1979-03-09', 'MALE', 'Hồ Chí Minh', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(14, '01KJR54EQR14MC0FNEZWY5S4JS', 'Chị. Cù Nhiên', '0964315352', 'duong.bac@example.org', '1999-08-23', 'OTHER', 'Cần Thơ', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(15, '01KJR54EQT2QPEJY3A0KD57TSJ', 'Hình Định', '0949735868', 'hoa.yen@example.org', '2001-02-26', 'MALE', 'Đà Nẵng', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(16, '01KJR54EQVSHHFDF002W77YFF6', 'Bà. Châu Thu Hoa', '0956498167', 'hoai97@example.org', '1988-05-12', 'FEMALE', 'Đà Nẵng', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(17, '01KJR54EQXHNSW2ZRCTCR47EJ6', 'Nông Tân', '0925411823', 'cai.quoc@example.org', '1972-01-23', 'OTHER', 'Đà Nẵng', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(18, '01KJR54EQYWFR4HYS7X4MP0JBR', 'Giao Sơn Nhạn', '0919800561', 'dan22@example.com', '1987-05-27', 'FEMALE', 'Đà Nẵng', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(19, '01KJR54ER0TCYR1HYQR8CYWWNP', 'Anh. Hứa Cương Vượng', '0912781810', 'tra98@example.net', '1970-09-14', 'FEMALE', 'Cần Thơ', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(20, '01KJR54ER1TJZ75XG02DQ4X0G0', 'Chị. Hạ Khánh Vũ', '0952346234', 'zlo@example.org', '1997-09-14', 'OTHER', 'Hồ Chí Minh', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(21, '01KJR54ER3S00QDEV80GQF73B6', 'Ông. Thào Hội', '0971325607', 'can.tong@example.com', '1997-02-10', 'OTHER', 'Hải Phòng', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(22, '01KJR54ER5V8DQPN2433FQ3Q77', 'Chiêm Phong', '0992097612', 'van.bang@example.org', '1978-04-13', 'MALE', 'Cần Thơ', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(23, '01KJR54ER61W7K681NC2G237XR', 'Khoa Trực', '0923466583', 'alai@example.net', '1983-05-11', 'FEMALE', 'Hải Phòng', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(24, '01KJR54ER81ZDW878EN0DSJX06', 'Uông Thy Ái', '0989690443', 'tue09@example.com', '1971-06-21', 'MALE', 'Hồ Chí Minh', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(25, '01KJR54ER9PRRVX7K0B1XC9T61', 'Anh. Thi Đạo', '0990748097', 'nghiem.bang@example.net', '1991-06-04', 'FEMALE', 'Đà Nẵng', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(26, '01KJR54ERBZ9GWTRMA5TTJ0F8V', 'Chú. Viên Tiền', '0903325888', 'trac.chuong@example.net', '2005-12-11', 'MALE', 'Hải Phòng', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(27, '01KJR54ERC246Q6XZM0X0MNQBH', 'Em. Mang Khanh', '0980566514', 'gdiep@example.org', '2008-07-16', 'FEMALE', 'Hồ Chí Minh', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(28, '01KJR54EREPEJARPVT3K2SD98S', 'Chị. Tăng Hảo Hiệp', '0915079277', 'trach.moc@example.com', '1978-07-17', 'OTHER', 'Hồ Chí Minh', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(29, '01KJR54ERF6ZEC7H3B9K0Z7DR0', 'Tống Luận', '0994824368', 'huyen.tiep@example.com', '1979-01-19', 'FEMALE', 'Hà Nội', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(30, '01KJR54ERHPK10EER9NK0NP043', 'Cụ. Hùng Kỳ Bình', '0985855455', 'bda@example.net', '1985-04-20', 'MALE', 'Hà Nội', '2026-03-02 20:51:45', '2026-03-02 20:51:45');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` bigint UNSIGNED NOT NULL,
  `cinema_id` bigint UNSIGNED NOT NULL,
  `auditorium_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `equipment_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `installed_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`id`, `code`, `name`, `created_at`, `updated_at`) VALUES
(1, 'GEN01', 'Hành động', '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(2, 'GEN02', 'Hài', '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(3, 'GEN03', 'Tình cảm', '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(4, 'GEN04', 'Kinh dị', '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(5, 'GEN05', 'Hoạt hình', '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(6, 'GEN06', 'Phiêu lưu', '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(7, 'GEN07', 'Khoa học viễn tưởng', '2026-03-02 20:51:44', '2026-03-02 20:51:44');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_balances`
--

CREATE TABLE `inventory_balances` (
  `id` bigint UNSIGNED NOT NULL,
  `stock_location_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `qty_on_hand` int NOT NULL DEFAULT '0',
  `reorder_level` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_accounts`
--

CREATE TABLE `loyalty_accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `tier_id` bigint UNSIGNED DEFAULT NULL,
  `points_balance` bigint NOT NULL DEFAULT '0',
  `lifetime_points` bigint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_tiers`
--

CREATE TABLE `loyalty_tiers` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_points` bigint UNSIGNED NOT NULL DEFAULT '0',
  `benefits` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_transactions`
--

CREATE TABLE `loyalty_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `loyalty_account_id` bigint UNSIGNED NOT NULL,
  `txn_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` bigint NOT NULL,
  `reference_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint UNSIGNED DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `cinema_id` bigint UNSIGNED NOT NULL,
  `auditorium_id` bigint UNSIGNED DEFAULT NULL,
  `equipment_id` bigint UNSIGNED DEFAULT NULL,
  `requested_by` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `priority` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'MEDIUM',
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `opened_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `closed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_03_03_000001_create_admin_users_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `id` bigint UNSIGNED NOT NULL,
  `public_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_rating_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_minutes` int UNSIGNED NOT NULL,
  `release_date` date DEFAULT NULL,
  `language_original` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `synopsis` text COLLATE utf8mb4_unicode_ci,
  `poster_url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trailer_url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `censorship_license_no` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`id`, `public_id`, `content_rating_id`, `title`, `original_title`, `duration_minutes`, `release_date`, `language_original`, `synopsis`, `poster_url`, `trailer_url`, `censorship_license_no`, `status`, `created_at`, `updated_at`) VALUES
(1, '01KJR54E6XZC03X058JH535JEZ', 1, 'Doraemon Movie 30', 'Doraemon Movie 30', 99, '2025-11-11', 'VI', 'Quod molestiae repellat iste id sed. Iste consequuntur amet autem. Aliquam dolores veritatis explicabo et ipsam assumenda. Accusamus est cupiditate beatae.', NULL, NULL, NULL, 'ACTIVE', '2026-03-02 20:51:44', '2026-03-09 07:47:02'),
(2, '01KJR54E793DPH83YTBQX9HTT5', 1, 'Nhà Ba Tôi Một Phòng - Trường Giang', 'Demo Movie 2', 132, '2026-02-27', 'VI', 'Et illum voluptatem aliquid voluptatem autem incidunt et. Est tempora velit cupiditate ipsam qui mollitia et. Possimus maiores est magnam saepe. Porro id et dolores pariatur voluptas ab id dolorum.', NULL, NULL, NULL, 'ACTIVE', '2026-03-02 20:51:44', '2026-03-09 07:45:31'),
(3, '01KJR54E7D4929FXB98VC5SR1J', 2, 'Đếm Ngày Xa Mẹ', 'Đếm Ngày Xa Mẹ', 89, '2025-12-17', 'VI', 'Voluptas at rerum dicta nostrum dolore molestiae minus. Ducimus amet et alias et sit culpa. Qui ipsum aut magnam dolorem aut quia consectetur fuga. Nisi enim quos deleniti at qui.', NULL, NULL, NULL, 'ACTIVE', '2026-03-02 20:51:44', '2026-03-09 07:44:56'),
(4, '01KJR54E7NNDR7JVHQHHH4WRBJ', 3, 'Quỷ Nhập Tràng 2 - POM NGUYỄN', 'Quỷ Nhập Tràng 2', 92, '2025-10-31', 'VI', 'Facilis voluptate qui vel corporis animi accusantium voluptate. Consequuntur nulla nihil minima omnis. Non eveniet dolores sed quasi.', NULL, NULL, NULL, 'ACTIVE', '2026-03-02 20:51:44', '2026-03-09 07:44:28'),
(5, '01KJR54E7TZENSCWJ6ZXR2Q5MK', 3, 'Tài - Mỹ Tâm', 'Tài', 115, '2026-01-24', 'VI', 'Harum vero nihil aut voluptates vitae reiciendis. Perferendis commodi rem quia voluptate minima et. Quasi rem sequi molestiae animi accusamus sit.', NULL, NULL, NULL, 'ACTIVE', '2026-03-02 20:51:44', '2026-03-09 07:43:59'),
(6, '01KJR54E7YXN7WCPQQD699X23Z', 3, 'Mùi Phở - Minh Beta', 'Mùi Phở', 129, '2026-01-26', 'VI', 'Consequatur quia voluptatem a explicabo nam quo amet. Ex enim perferendis exercitationem rerum et. Quos quibusdam esse beatae. Corrupti quis minus ipsum laboriosam odit eos doloribus.', NULL, NULL, NULL, 'ACTIVE', '2026-03-02 20:51:44', '2026-03-09 07:43:26'),
(7, '01KJR54E84E6D0X2QPH0KWEQ88', 4, 'Mưa Đỏ - Đặng Thái Huyền', 'Mưa Đỏ', 112, '2026-02-12', 'VI', 'Amet molestiae a placeat et vel dolorum cum. Voluptas perspiciatis officiis et debitis saepe.', NULL, NULL, NULL, 'ACTIVE', '2026-03-02 20:51:44', '2026-03-09 07:42:28'),
(8, '01KJR54E8ASHN084DDNPQ2H3NP', 4, 'Thỏ ơi - Trấn Thành', 'Thỏ ơi', 180, '2026-01-31', 'VI', 'Phim của trấn thành', NULL, NULL, NULL, 'ACTIVE', '2026-03-02 20:51:44', '2026-03-03 01:04:08');

-- --------------------------------------------------------

--
-- Table structure for table `movie_genres`
--

CREATE TABLE `movie_genres` (
  `movie_id` bigint UNSIGNED NOT NULL,
  `genre_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `movie_genres`
--

INSERT INTO `movie_genres` (`movie_id`, `genre_id`) VALUES
(1, 1),
(5, 1),
(2, 2),
(3, 3),
(1, 4),
(3, 4),
(6, 4),
(7, 4),
(8, 5),
(4, 6),
(7, 7);

-- --------------------------------------------------------

--
-- Table structure for table `movie_people`
--

CREATE TABLE `movie_people` (
  `movie_id` bigint UNSIGNED NOT NULL,
  `person_id` bigint UNSIGNED NOT NULL,
  `role_type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `character_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0'
) ;

-- --------------------------------------------------------

--
-- Table structure for table `movie_versions`
--

CREATE TABLE `movie_versions` (
  `id` bigint UNSIGNED NOT NULL,
  `movie_id` bigint UNSIGNED NOT NULL,
  `format` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2D',
  `audio_language` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VI',
  `subtitle_language` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `movie_versions`
--

INSERT INTO `movie_versions` (`id`, `movie_id`, `format`, `audio_language`, `subtitle_language`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, '2D', 'VI', NULL, NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(2, 1, '3D', 'VI', 'EN', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(3, 2, '2D', 'VI', NULL, NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(4, 3, '2D', 'VI', NULL, NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(5, 3, '3D', 'VI', 'EN', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(6, 4, '2D', 'VI', NULL, NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(7, 4, '3D', 'VI', 'EN', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(8, 5, '2D', 'VI', NULL, NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(9, 6, '2D', 'VI', NULL, NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(10, 6, '3D', 'VI', 'EN', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(11, 7, '2D', 'VI', NULL, NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(12, 7, '3D', 'VI', 'EN', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(13, 8, '2D', 'VI', NULL, NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `booking_id` bigint UNSIGNED NOT NULL,
  `provider` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INITIATED',
  `amount` bigint UNSIGNED NOT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VND',
  `external_txn_ref` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_payload` json DEFAULT NULL,
  `response_payload` json DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `provider`, `method`, `status`, `amount`, `currency`, `external_txn_ref`, `request_payload`, `response_payload`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'VNPAY', 'EWALLET', 'CAPTURED', 398640, 'VND', '01KJR54ESA1V691SNEAZP7S6H6', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(2, 2, 'VNPAY', 'CASH', 'CAPTURED', 174831, 'VND', '01KJR54ESRF2PGAZ3AZAD4Y1Y4', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(3, 3, 'CASH', 'CASH', 'CAPTURED', 201970, 'VND', '01KJR54ET3YSCRRHDKRN141XZY', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(4, 4, 'VNPAY', 'EWALLET', 'CAPTURED', 66590, 'VND', '01KJR54ETBQCEB9JTS5T4PTKC7', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(5, 5, 'CASH', 'CASH', 'CAPTURED', 282483, 'VND', '01KJR54ETR6X6P0V4M5QPNQ0S9', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(6, 6, 'VNPAY', 'EWALLET', 'CAPTURED', 225520, 'VND', '01KJR54EV6CNTFTT916K393383', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(7, 7, 'VNPAY', 'EWALLET', 'CAPTURED', 440276, 'VND', '01KJR54EVT10KW4R01G85Z917S', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(8, 8, 'CASH', 'CASH', 'CAPTURED', 133804, 'VND', '01KJR54EW24EX6AEYVNA3VMVST', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(9, 9, 'VNPAY', 'CASH', 'CAPTURED', 78182, 'VND', '01KJR54EW989NK7TTDJY126JQA', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(10, 10, 'CASH', 'CASH', 'CAPTURED', 437758, 'VND', '01KJR54EWSQJ67V5W8MRPSZK89', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(11, 11, 'CASH', 'CASH', 'CAPTURED', 294546, 'VND', '01KJR54EX7BV0VTC2FN6DXKBKB', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(12, 12, 'VNPAY', 'CASH', 'CAPTURED', 353894, 'VND', '01KJR54EXRXMTV6VAZKH9GPHTS', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(13, 13, 'CASH', 'EWALLET', 'CAPTURED', 117274, 'VND', '01KJR54EY3YRHAY8K666MD98ED', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(14, 14, 'VNPAY', 'EWALLET', 'CAPTURED', 205097, 'VND', '01KJR54EYE3VKCAF5EJZ54VQB9', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(15, 15, 'VNPAY', 'EWALLET', 'CAPTURED', 312976, 'VND', '01KJR54EYZN3DS9QWWJE4YXDTM', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(16, 16, 'CASH', 'EWALLET', 'CAPTURED', 331326, 'VND', '01KJR54EZFYVKKHGRHQEK5KEG8', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(17, 17, 'VNPAY', 'CASH', 'CAPTURED', 57773, 'VND', '01KJR54EZPKE61WGYM75GB97PR', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(18, 18, 'CASH', 'EWALLET', 'CAPTURED', 288397, 'VND', '01KJR54F08ZPYEVNZCEH2Y6KHJ', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(19, 19, 'CASH', 'EWALLET', 'CAPTURED', 253694, 'VND', '01KJR54F0PZMGS944V6GMXDAXJ', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(20, 20, 'CASH', 'CASH', 'CAPTURED', 188088, 'VND', '01KJR54F117BKF33WBZRA15487', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(21, 21, 'VNPAY', 'EWALLET', 'CAPTURED', 125847, 'VND', '01KJR54F18PJMKM1PQA0HZ9MG6', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(22, 22, 'VNPAY', 'EWALLET', 'CAPTURED', 308798, 'VND', '01KJR54F1QGPWR2FZEHY8W5MNS', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(23, 23, 'VNPAY', 'EWALLET', 'CAPTURED', 311974, 'VND', '01KJR54F2AA2RRC4BQKJ2MY5D1', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(24, 24, 'CASH', 'CASH', 'CAPTURED', 302538, 'VND', '01KJR54F2YZAMQCSN0JWF0K9AN', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(25, 25, 'VNPAY', 'EWALLET', 'CAPTURED', 420228, 'VND', '01KJR54F3G1VQ89CATR1JTTGMJ', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(26, 26, 'VNPAY', 'CASH', 'CAPTURED', 410552, 'VND', '01KJR54F460Q1N0NCAJRJKPE1X', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(27, 27, 'VNPAY', 'CASH', 'CAPTURED', 325828, 'VND', '01KJR54F4SM78XJDXJX72R830J', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(28, 28, 'VNPAY', 'EWALLET', 'CAPTURED', 231955, 'VND', '01KJR54F5DF524RJSEM4RVG73J', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(29, 29, 'CASH', 'EWALLET', 'CAPTURED', 258757, 'VND', '01KJR54F60T7J18CX9H9WRYAX4', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(30, 30, 'VNPAY', 'EWALLET', 'CAPTURED', 287470, 'VND', '01KJR54F6BWG706SBGW9M43JXC', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(31, 31, 'VNPAY', 'CASH', 'CAPTURED', 60906, 'VND', '01KJR54F6K24Y0EDS4PKFSE5ET', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(32, 32, 'CASH', 'CASH', 'CAPTURED', 517344, 'VND', '01KJR54F7BRC0FYXJ9ME60TKCQ', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(33, 33, 'CASH', 'CASH', 'CAPTURED', 150235, 'VND', '01KJR54F7V32KZMDASPS5TW18S', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(34, 34, 'VNPAY', 'CASH', 'CAPTURED', 445145, 'VND', '01KJR54F8TDPJRYY0VC0Y543FQ', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(35, 35, 'CASH', 'EWALLET', 'CAPTURED', 454512, 'VND', '01KJR54F9N65SPJ8BPTCTD2QD5', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(36, 36, 'CASH', 'CASH', 'CAPTURED', 306705, 'VND', '01KJR54FAGP24V9AWD3J24F11E', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(37, 37, 'CASH', 'CASH', 'CAPTURED', 443111, 'VND', '01KJR54FB9SA0P4FN8XK172FV1', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(38, 38, 'VNPAY', 'CASH', 'CAPTURED', 172838, 'VND', '01KJR54FBRHNRPYQMRJV9C03SB', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(39, 39, 'CASH', 'CASH', 'CAPTURED', 529025, 'VND', '01KJR54FCQ70ZV78Y80YB81K0F', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(40, 40, 'VNPAY', 'CASH', 'CAPTURED', 545820, 'VND', '01KJR54FDMRGC755ZZEBJKKSVY', NULL, NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45', '2026-03-02 20:51:45');

-- --------------------------------------------------------

--
-- Table structure for table `people`
--

CREATE TABLE `people` (
  `id` bigint UNSIGNED NOT NULL,
  `public_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dob` date DEFAULT NULL,
  `country_code` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `avatar_url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pricing_profiles`
--

CREATE TABLE `pricing_profiles` (
  `id` bigint UNSIGNED NOT NULL,
  `cinema_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pricing_rules`
--

CREATE TABLE `pricing_rules` (
  `id` bigint UNSIGNED NOT NULL,
  `pricing_profile_id` bigint UNSIGNED NOT NULL,
  `day_of_week` tinyint UNSIGNED DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `seat_type_id` bigint UNSIGNED NOT NULL,
  `ticket_type_id` bigint UNSIGNED NOT NULL,
  `price_amount` bigint UNSIGNED NOT NULL,
  `priority` int UNSIGNED NOT NULL DEFAULT '100',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `public_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `sku` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ITEM',
  `is_combo` tinyint(1) NOT NULL DEFAULT '0',
  `attributes` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_prices`
--

CREATE TABLE `product_prices` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `cinema_id` bigint UNSIGNED DEFAULT NULL,
  `price_amount` bigint UNSIGNED NOT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VND',
  `effective_from` datetime NOT NULL,
  `effective_to` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `promo_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_value` bigint UNSIGNED NOT NULL,
  `max_discount_amount` bigint UNSIGNED DEFAULT NULL,
  `min_order_amount` bigint UNSIGNED DEFAULT NULL,
  `applies_to` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ORDER',
  `is_stackable` tinyint(1) NOT NULL DEFAULT '0',
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `usage_limit_total` int UNSIGNED DEFAULT NULL,
  `usage_limit_per_customer` int UNSIGNED DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `promotion_cinemas`
--

CREATE TABLE `promotion_cinemas` (
  `promotion_id` bigint UNSIGNED NOT NULL,
  `cinema_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotion_movies`
--

CREATE TABLE `promotion_movies` (
  `promotion_id` bigint UNSIGNED NOT NULL,
  `movie_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `public_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` bigint UNSIGNED NOT NULL,
  `cinema_id` bigint UNSIGNED NOT NULL,
  `po_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `ordered_at` datetime DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `total_amount` bigint UNSIGNED NOT NULL DEFAULT '0',
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VND',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_lines`
--

CREATE TABLE `purchase_order_lines` (
  `id` bigint UNSIGNED NOT NULL,
  `purchase_order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `qty_ordered` int UNSIGNED NOT NULL,
  `qty_received` int UNSIGNED NOT NULL DEFAULT '0',
  `unit_cost_amount` bigint UNSIGNED NOT NULL,
  `line_amount` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `id` bigint UNSIGNED NOT NULL,
  `payment_id` bigint UNSIGNED NOT NULL,
  `amount` bigint UNSIGNED NOT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_ref` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_channels`
--

CREATE TABLE `sales_channels` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_channels`
--

INSERT INTO `sales_channels` (`id`, `code`, `name`, `created_at`, `updated_at`) VALUES
(1, 'WEB', 'Website', '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(2, 'POS', 'Quầy vé', '2026-03-02 20:51:44', '2026-03-02 20:51:44');

-- --------------------------------------------------------

--
-- Table structure for table `seats`
--

CREATE TABLE `seats` (
  `id` bigint UNSIGNED NOT NULL,
  `auditorium_id` bigint UNSIGNED NOT NULL,
  `seat_type_id` bigint UNSIGNED NOT NULL,
  `seat_code` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `row_label` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `col_number` int UNSIGNED NOT NULL,
  `x` int UNSIGNED DEFAULT NULL,
  `y` int UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seats`
--

INSERT INTO `seats` (`id`, `auditorium_id`, `seat_type_id`, `seat_code`, `row_label`, `col_number`, `x`, `y`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'A01', 'A', 1, 1, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(2, 1, 1, 'A02', 'A', 2, 2, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(3, 1, 1, 'A03', 'A', 3, 3, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(4, 1, 1, 'A04', 'A', 4, 4, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(5, 1, 1, 'A05', 'A', 5, 5, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(6, 1, 1, 'A06', 'A', 6, 6, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(7, 1, 1, 'A07', 'A', 7, 7, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(8, 1, 1, 'A08', 'A', 8, 8, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(9, 1, 1, 'A09', 'A', 9, 9, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(10, 1, 1, 'A10', 'A', 10, 10, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(11, 1, 1, 'A11', 'A', 11, 11, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(12, 1, 1, 'A12', 'A', 12, 12, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(13, 1, 1, 'B01', 'B', 1, 1, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(14, 1, 1, 'B02', 'B', 2, 2, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(15, 1, 1, 'B03', 'B', 3, 3, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(16, 1, 1, 'B04', 'B', 4, 4, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(17, 1, 1, 'B05', 'B', 5, 5, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(18, 1, 1, 'B06', 'B', 6, 6, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(19, 1, 1, 'B07', 'B', 7, 7, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(20, 1, 1, 'B08', 'B', 8, 8, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(21, 1, 1, 'B09', 'B', 9, 9, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(22, 1, 1, 'B10', 'B', 10, 10, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(23, 1, 1, 'B11', 'B', 11, 11, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(24, 1, 1, 'B12', 'B', 12, 12, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(25, 1, 1, 'C01', 'C', 1, 1, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(26, 1, 1, 'C02', 'C', 2, 2, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(27, 1, 1, 'C03', 'C', 3, 3, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(28, 1, 1, 'C04', 'C', 4, 4, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(29, 1, 1, 'C05', 'C', 5, 5, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(30, 1, 1, 'C06', 'C', 6, 6, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(31, 1, 1, 'C07', 'C', 7, 7, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(32, 1, 1, 'C08', 'C', 8, 8, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(33, 1, 1, 'C09', 'C', 9, 9, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(34, 1, 1, 'C10', 'C', 10, 10, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(35, 1, 1, 'C11', 'C', 11, 11, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(36, 1, 1, 'C12', 'C', 12, 12, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(37, 1, 1, 'D01', 'D', 1, 1, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(38, 1, 1, 'D02', 'D', 2, 2, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(39, 1, 1, 'D03', 'D', 3, 3, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(40, 1, 1, 'D04', 'D', 4, 4, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(41, 1, 1, 'D05', 'D', 5, 5, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(42, 1, 1, 'D06', 'D', 6, 6, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(43, 1, 1, 'D07', 'D', 7, 7, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(44, 1, 1, 'D08', 'D', 8, 8, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(45, 1, 1, 'D09', 'D', 9, 9, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(46, 1, 1, 'D10', 'D', 10, 10, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(47, 1, 1, 'D11', 'D', 11, 11, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(48, 1, 1, 'D12', 'D', 12, 12, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(49, 1, 2, 'E01', 'E', 1, 1, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(50, 1, 2, 'E02', 'E', 2, 2, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(51, 1, 2, 'E03', 'E', 3, 3, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(52, 1, 2, 'E04', 'E', 4, 4, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(53, 1, 2, 'E05', 'E', 5, 5, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(54, 1, 2, 'E06', 'E', 6, 6, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(55, 1, 2, 'E07', 'E', 7, 7, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(56, 1, 2, 'E08', 'E', 8, 8, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(57, 1, 2, 'E09', 'E', 9, 9, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(58, 1, 2, 'E10', 'E', 10, 10, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(59, 1, 2, 'E11', 'E', 11, 11, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(60, 1, 2, 'E12', 'E', 12, 12, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(61, 1, 2, 'F01', 'F', 1, 1, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(62, 1, 2, 'F02', 'F', 2, 2, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(63, 1, 2, 'F03', 'F', 3, 3, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(64, 1, 2, 'F04', 'F', 4, 4, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(65, 1, 2, 'F05', 'F', 5, 5, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(66, 1, 2, 'F06', 'F', 6, 6, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(67, 1, 2, 'F07', 'F', 7, 7, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(68, 1, 2, 'F08', 'F', 8, 8, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(69, 1, 2, 'F09', 'F', 9, 9, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(70, 1, 2, 'F10', 'F', 10, 10, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(71, 1, 2, 'F11', 'F', 11, 11, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(72, 1, 2, 'F12', 'F', 12, 12, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(73, 1, 1, 'G01', 'G', 1, 1, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(74, 1, 1, 'G02', 'G', 2, 2, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(75, 1, 1, 'G03', 'G', 3, 3, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(76, 1, 1, 'G04', 'G', 4, 4, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(77, 1, 1, 'G05', 'G', 5, 5, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(78, 1, 1, 'G06', 'G', 6, 6, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(79, 1, 1, 'G07', 'G', 7, 7, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(80, 1, 1, 'G08', 'G', 8, 8, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(81, 1, 1, 'G09', 'G', 9, 9, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(82, 1, 1, 'G10', 'G', 10, 10, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(83, 1, 1, 'G11', 'G', 11, 11, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(84, 1, 1, 'G12', 'G', 12, 12, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(85, 1, 1, 'H01', 'H', 1, 1, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(86, 1, 1, 'H02', 'H', 2, 2, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(87, 1, 1, 'H03', 'H', 3, 3, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(88, 1, 1, 'H04', 'H', 4, 4, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(89, 1, 1, 'H05', 'H', 5, 5, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(90, 1, 1, 'H06', 'H', 6, 6, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(91, 1, 1, 'H07', 'H', 7, 7, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(92, 1, 1, 'H08', 'H', 8, 8, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(93, 1, 1, 'H09', 'H', 9, 9, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(94, 1, 1, 'H10', 'H', 10, 10, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(95, 1, 1, 'H11', 'H', 11, 11, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(96, 1, 1, 'H12', 'H', 12, 12, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(97, 1, 1, 'I01', 'I', 1, 1, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(98, 1, 1, 'I02', 'I', 2, 2, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(99, 1, 1, 'I03', 'I', 3, 3, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(100, 1, 1, 'I04', 'I', 4, 4, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(101, 1, 1, 'I05', 'I', 5, 5, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(102, 1, 1, 'I06', 'I', 6, 6, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(103, 1, 1, 'I07', 'I', 7, 7, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(104, 1, 1, 'I08', 'I', 8, 8, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(105, 1, 1, 'I09', 'I', 9, 9, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(106, 1, 1, 'I10', 'I', 10, 10, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(107, 1, 1, 'I11', 'I', 11, 11, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(108, 1, 1, 'I12', 'I', 12, 12, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(109, 1, 3, 'J01', 'J', 1, 1, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(110, 1, 3, 'J02', 'J', 2, 2, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(111, 1, 3, 'J03', 'J', 3, 3, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(112, 1, 3, 'J04', 'J', 4, 4, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(113, 1, 1, 'J05', 'J', 5, 5, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(114, 1, 1, 'J06', 'J', 6, 6, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(115, 1, 1, 'J07', 'J', 7, 7, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(116, 1, 1, 'J08', 'J', 8, 8, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(117, 1, 1, 'J09', 'J', 9, 9, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(118, 1, 1, 'J10', 'J', 10, 10, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(119, 1, 1, 'J11', 'J', 11, 11, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(120, 1, 1, 'J12', 'J', 12, 12, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(121, 2, 1, 'A01', 'A', 1, 1, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(122, 2, 1, 'A02', 'A', 2, 2, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(123, 2, 1, 'A03', 'A', 3, 3, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(124, 2, 1, 'A04', 'A', 4, 4, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(125, 2, 1, 'A05', 'A', 5, 5, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(126, 2, 1, 'A06', 'A', 6, 6, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(127, 2, 1, 'A07', 'A', 7, 7, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(128, 2, 1, 'A08', 'A', 8, 8, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(129, 2, 1, 'A09', 'A', 9, 9, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(130, 2, 1, 'A10', 'A', 10, 10, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(131, 2, 1, 'A11', 'A', 11, 11, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(132, 2, 1, 'A12', 'A', 12, 12, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(133, 2, 1, 'B01', 'B', 1, 1, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(134, 2, 1, 'B02', 'B', 2, 2, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(135, 2, 1, 'B03', 'B', 3, 3, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(136, 2, 1, 'B04', 'B', 4, 4, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(137, 2, 1, 'B05', 'B', 5, 5, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(138, 2, 1, 'B06', 'B', 6, 6, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(139, 2, 1, 'B07', 'B', 7, 7, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(140, 2, 1, 'B08', 'B', 8, 8, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(141, 2, 1, 'B09', 'B', 9, 9, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(142, 2, 1, 'B10', 'B', 10, 10, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(143, 2, 1, 'B11', 'B', 11, 11, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(144, 2, 1, 'B12', 'B', 12, 12, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(145, 2, 1, 'C01', 'C', 1, 1, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(146, 2, 1, 'C02', 'C', 2, 2, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(147, 2, 1, 'C03', 'C', 3, 3, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(148, 2, 1, 'C04', 'C', 4, 4, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(149, 2, 1, 'C05', 'C', 5, 5, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(150, 2, 1, 'C06', 'C', 6, 6, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(151, 2, 1, 'C07', 'C', 7, 7, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(152, 2, 1, 'C08', 'C', 8, 8, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(153, 2, 1, 'C09', 'C', 9, 9, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(154, 2, 1, 'C10', 'C', 10, 10, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(155, 2, 1, 'C11', 'C', 11, 11, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(156, 2, 1, 'C12', 'C', 12, 12, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(157, 2, 1, 'D01', 'D', 1, 1, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(158, 2, 1, 'D02', 'D', 2, 2, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(159, 2, 1, 'D03', 'D', 3, 3, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(160, 2, 1, 'D04', 'D', 4, 4, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(161, 2, 1, 'D05', 'D', 5, 5, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(162, 2, 1, 'D06', 'D', 6, 6, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(163, 2, 1, 'D07', 'D', 7, 7, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(164, 2, 1, 'D08', 'D', 8, 8, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(165, 2, 1, 'D09', 'D', 9, 9, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(166, 2, 1, 'D10', 'D', 10, 10, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(167, 2, 1, 'D11', 'D', 11, 11, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(168, 2, 1, 'D12', 'D', 12, 12, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(169, 2, 2, 'E01', 'E', 1, 1, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(170, 2, 2, 'E02', 'E', 2, 2, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(171, 2, 2, 'E03', 'E', 3, 3, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(172, 2, 2, 'E04', 'E', 4, 4, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(173, 2, 2, 'E05', 'E', 5, 5, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(174, 2, 2, 'E06', 'E', 6, 6, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(175, 2, 2, 'E07', 'E', 7, 7, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(176, 2, 2, 'E08', 'E', 8, 8, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(177, 2, 2, 'E09', 'E', 9, 9, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(178, 2, 2, 'E10', 'E', 10, 10, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(179, 2, 2, 'E11', 'E', 11, 11, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(180, 2, 2, 'E12', 'E', 12, 12, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(181, 2, 2, 'F01', 'F', 1, 1, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(182, 2, 2, 'F02', 'F', 2, 2, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(183, 2, 2, 'F03', 'F', 3, 3, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(184, 2, 2, 'F04', 'F', 4, 4, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(185, 2, 2, 'F05', 'F', 5, 5, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(186, 2, 2, 'F06', 'F', 6, 6, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(187, 2, 2, 'F07', 'F', 7, 7, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(188, 2, 2, 'F08', 'F', 8, 8, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(189, 2, 2, 'F09', 'F', 9, 9, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(190, 2, 2, 'F10', 'F', 10, 10, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(191, 2, 2, 'F11', 'F', 11, 11, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(192, 2, 2, 'F12', 'F', 12, 12, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(193, 2, 1, 'G01', 'G', 1, 1, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(194, 2, 1, 'G02', 'G', 2, 2, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(195, 2, 1, 'G03', 'G', 3, 3, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(196, 2, 1, 'G04', 'G', 4, 4, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(197, 2, 1, 'G05', 'G', 5, 5, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(198, 2, 1, 'G06', 'G', 6, 6, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(199, 2, 1, 'G07', 'G', 7, 7, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(200, 2, 1, 'G08', 'G', 8, 8, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(201, 2, 1, 'G09', 'G', 9, 9, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(202, 2, 1, 'G10', 'G', 10, 10, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(203, 2, 1, 'G11', 'G', 11, 11, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(204, 2, 1, 'G12', 'G', 12, 12, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(205, 2, 1, 'H01', 'H', 1, 1, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(206, 2, 1, 'H02', 'H', 2, 2, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(207, 2, 1, 'H03', 'H', 3, 3, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(208, 2, 1, 'H04', 'H', 4, 4, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(209, 2, 1, 'H05', 'H', 5, 5, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(210, 2, 1, 'H06', 'H', 6, 6, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(211, 2, 1, 'H07', 'H', 7, 7, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(212, 2, 1, 'H08', 'H', 8, 8, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(213, 2, 1, 'H09', 'H', 9, 9, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(214, 2, 1, 'H10', 'H', 10, 10, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(215, 2, 1, 'H11', 'H', 11, 11, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(216, 2, 1, 'H12', 'H', 12, 12, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(217, 2, 1, 'I01', 'I', 1, 1, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(218, 2, 1, 'I02', 'I', 2, 2, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(219, 2, 1, 'I03', 'I', 3, 3, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(220, 2, 1, 'I04', 'I', 4, 4, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(221, 2, 1, 'I05', 'I', 5, 5, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(222, 2, 1, 'I06', 'I', 6, 6, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(223, 2, 1, 'I07', 'I', 7, 7, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(224, 2, 1, 'I08', 'I', 8, 8, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(225, 2, 1, 'I09', 'I', 9, 9, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(226, 2, 1, 'I10', 'I', 10, 10, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(227, 2, 1, 'I11', 'I', 11, 11, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(228, 2, 1, 'I12', 'I', 12, 12, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(229, 2, 3, 'J01', 'J', 1, 1, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(230, 2, 3, 'J02', 'J', 2, 2, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(231, 2, 3, 'J03', 'J', 3, 3, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(232, 2, 3, 'J04', 'J', 4, 4, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(233, 2, 1, 'J05', 'J', 5, 5, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(234, 2, 1, 'J06', 'J', 6, 6, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(235, 2, 1, 'J07', 'J', 7, 7, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(236, 2, 1, 'J08', 'J', 8, 8, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(237, 2, 1, 'J09', 'J', 9, 9, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(238, 2, 1, 'J10', 'J', 10, 10, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(239, 2, 1, 'J11', 'J', 11, 11, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(240, 2, 1, 'J12', 'J', 12, 12, 10, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(241, 3, 1, 'A01', 'A', 1, 1, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(242, 3, 1, 'A02', 'A', 2, 2, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(243, 3, 1, 'A03', 'A', 3, 3, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(244, 3, 1, 'A04', 'A', 4, 4, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(245, 3, 1, 'A05', 'A', 5, 5, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(246, 3, 1, 'A06', 'A', 6, 6, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(247, 3, 1, 'A07', 'A', 7, 7, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(248, 3, 1, 'A08', 'A', 8, 8, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(249, 3, 1, 'A09', 'A', 9, 9, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(250, 3, 1, 'A10', 'A', 10, 10, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(251, 3, 1, 'A11', 'A', 11, 11, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(252, 3, 1, 'A12', 'A', 12, 12, 1, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(253, 3, 1, 'B01', 'B', 1, 1, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(254, 3, 1, 'B02', 'B', 2, 2, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(255, 3, 1, 'B03', 'B', 3, 3, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(256, 3, 1, 'B04', 'B', 4, 4, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(257, 3, 1, 'B05', 'B', 5, 5, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(258, 3, 1, 'B06', 'B', 6, 6, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(259, 3, 1, 'B07', 'B', 7, 7, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(260, 3, 1, 'B08', 'B', 8, 8, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(261, 3, 1, 'B09', 'B', 9, 9, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(262, 3, 1, 'B10', 'B', 10, 10, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(263, 3, 1, 'B11', 'B', 11, 11, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(264, 3, 1, 'B12', 'B', 12, 12, 2, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(265, 3, 1, 'C01', 'C', 1, 1, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(266, 3, 1, 'C02', 'C', 2, 2, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(267, 3, 1, 'C03', 'C', 3, 3, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(268, 3, 1, 'C04', 'C', 4, 4, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(269, 3, 1, 'C05', 'C', 5, 5, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(270, 3, 1, 'C06', 'C', 6, 6, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(271, 3, 1, 'C07', 'C', 7, 7, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(272, 3, 1, 'C08', 'C', 8, 8, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(273, 3, 1, 'C09', 'C', 9, 9, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(274, 3, 1, 'C10', 'C', 10, 10, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(275, 3, 1, 'C11', 'C', 11, 11, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(276, 3, 1, 'C12', 'C', 12, 12, 3, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(277, 3, 1, 'D01', 'D', 1, 1, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(278, 3, 1, 'D02', 'D', 2, 2, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(279, 3, 1, 'D03', 'D', 3, 3, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(280, 3, 1, 'D04', 'D', 4, 4, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(281, 3, 1, 'D05', 'D', 5, 5, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(282, 3, 1, 'D06', 'D', 6, 6, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(283, 3, 1, 'D07', 'D', 7, 7, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(284, 3, 1, 'D08', 'D', 8, 8, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(285, 3, 1, 'D09', 'D', 9, 9, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(286, 3, 1, 'D10', 'D', 10, 10, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(287, 3, 1, 'D11', 'D', 11, 11, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(288, 3, 1, 'D12', 'D', 12, 12, 4, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(289, 3, 2, 'E01', 'E', 1, 1, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(290, 3, 2, 'E02', 'E', 2, 2, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(291, 3, 2, 'E03', 'E', 3, 3, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(292, 3, 2, 'E04', 'E', 4, 4, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(293, 3, 2, 'E05', 'E', 5, 5, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(294, 3, 2, 'E06', 'E', 6, 6, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(295, 3, 2, 'E07', 'E', 7, 7, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(296, 3, 2, 'E08', 'E', 8, 8, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(297, 3, 2, 'E09', 'E', 9, 9, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(298, 3, 2, 'E10', 'E', 10, 10, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(299, 3, 2, 'E11', 'E', 11, 11, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(300, 3, 2, 'E12', 'E', 12, 12, 5, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(301, 3, 2, 'F01', 'F', 1, 1, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(302, 3, 2, 'F02', 'F', 2, 2, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(303, 3, 2, 'F03', 'F', 3, 3, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(304, 3, 2, 'F04', 'F', 4, 4, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(305, 3, 2, 'F05', 'F', 5, 5, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(306, 3, 2, 'F06', 'F', 6, 6, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(307, 3, 2, 'F07', 'F', 7, 7, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(308, 3, 2, 'F08', 'F', 8, 8, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(309, 3, 2, 'F09', 'F', 9, 9, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(310, 3, 2, 'F10', 'F', 10, 10, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(311, 3, 2, 'F11', 'F', 11, 11, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(312, 3, 2, 'F12', 'F', 12, 12, 6, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(313, 3, 1, 'G01', 'G', 1, 1, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(314, 3, 1, 'G02', 'G', 2, 2, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(315, 3, 1, 'G03', 'G', 3, 3, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(316, 3, 1, 'G04', 'G', 4, 4, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(317, 3, 1, 'G05', 'G', 5, 5, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(318, 3, 1, 'G06', 'G', 6, 6, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(319, 3, 1, 'G07', 'G', 7, 7, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(320, 3, 1, 'G08', 'G', 8, 8, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(321, 3, 1, 'G09', 'G', 9, 9, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(322, 3, 1, 'G10', 'G', 10, 10, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(323, 3, 1, 'G11', 'G', 11, 11, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(324, 3, 1, 'G12', 'G', 12, 12, 7, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(325, 3, 1, 'H01', 'H', 1, 1, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(326, 3, 1, 'H02', 'H', 2, 2, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(327, 3, 1, 'H03', 'H', 3, 3, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(328, 3, 1, 'H04', 'H', 4, 4, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(329, 3, 1, 'H05', 'H', 5, 5, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(330, 3, 1, 'H06', 'H', 6, 6, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(331, 3, 1, 'H07', 'H', 7, 7, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(332, 3, 1, 'H08', 'H', 8, 8, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(333, 3, 1, 'H09', 'H', 9, 9, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(334, 3, 1, 'H10', 'H', 10, 10, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(335, 3, 1, 'H11', 'H', 11, 11, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(336, 3, 1, 'H12', 'H', 12, 12, 8, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(337, 3, 1, 'I01', 'I', 1, 1, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(338, 3, 1, 'I02', 'I', 2, 2, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(339, 3, 1, 'I03', 'I', 3, 3, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(340, 3, 1, 'I04', 'I', 4, 4, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(341, 3, 1, 'I05', 'I', 5, 5, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(342, 3, 1, 'I06', 'I', 6, 6, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(343, 3, 1, 'I07', 'I', 7, 7, 9, 1, '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(344, 3, 1, 'I08', 'I', 8, 8, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(345, 3, 1, 'I09', 'I', 9, 9, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(346, 3, 1, 'I10', 'I', 10, 10, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(347, 3, 1, 'I11', 'I', 11, 11, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(348, 3, 1, 'I12', 'I', 12, 12, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(349, 3, 3, 'J01', 'J', 1, 1, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(350, 3, 3, 'J02', 'J', 2, 2, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(351, 3, 3, 'J03', 'J', 3, 3, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(352, 3, 3, 'J04', 'J', 4, 4, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(353, 3, 1, 'J05', 'J', 5, 5, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(354, 3, 1, 'J06', 'J', 6, 6, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(355, 3, 1, 'J07', 'J', 7, 7, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(356, 3, 1, 'J08', 'J', 8, 8, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(357, 3, 1, 'J09', 'J', 9, 9, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(358, 3, 1, 'J10', 'J', 10, 10, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(359, 3, 1, 'J11', 'J', 11, 11, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(360, 3, 1, 'J12', 'J', 12, 12, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(361, 4, 1, 'A01', 'A', 1, 1, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(362, 4, 1, 'A02', 'A', 2, 2, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(363, 4, 1, 'A03', 'A', 3, 3, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(364, 4, 1, 'A04', 'A', 4, 4, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(365, 4, 1, 'A05', 'A', 5, 5, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(366, 4, 1, 'A06', 'A', 6, 6, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(367, 4, 1, 'A07', 'A', 7, 7, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(368, 4, 1, 'A08', 'A', 8, 8, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(369, 4, 1, 'A09', 'A', 9, 9, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(370, 4, 1, 'A10', 'A', 10, 10, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(371, 4, 1, 'A11', 'A', 11, 11, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(372, 4, 1, 'A12', 'A', 12, 12, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(373, 4, 1, 'B01', 'B', 1, 1, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(374, 4, 1, 'B02', 'B', 2, 2, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(375, 4, 1, 'B03', 'B', 3, 3, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(376, 4, 1, 'B04', 'B', 4, 4, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(377, 4, 1, 'B05', 'B', 5, 5, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(378, 4, 1, 'B06', 'B', 6, 6, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(379, 4, 1, 'B07', 'B', 7, 7, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(380, 4, 1, 'B08', 'B', 8, 8, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(381, 4, 1, 'B09', 'B', 9, 9, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(382, 4, 1, 'B10', 'B', 10, 10, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(383, 4, 1, 'B11', 'B', 11, 11, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(384, 4, 1, 'B12', 'B', 12, 12, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(385, 4, 1, 'C01', 'C', 1, 1, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(386, 4, 1, 'C02', 'C', 2, 2, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(387, 4, 1, 'C03', 'C', 3, 3, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(388, 4, 1, 'C04', 'C', 4, 4, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(389, 4, 1, 'C05', 'C', 5, 5, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(390, 4, 1, 'C06', 'C', 6, 6, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(391, 4, 1, 'C07', 'C', 7, 7, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(392, 4, 1, 'C08', 'C', 8, 8, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(393, 4, 1, 'C09', 'C', 9, 9, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(394, 4, 1, 'C10', 'C', 10, 10, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(395, 4, 1, 'C11', 'C', 11, 11, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(396, 4, 1, 'C12', 'C', 12, 12, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(397, 4, 1, 'D01', 'D', 1, 1, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(398, 4, 1, 'D02', 'D', 2, 2, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(399, 4, 1, 'D03', 'D', 3, 3, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(400, 4, 1, 'D04', 'D', 4, 4, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(401, 4, 1, 'D05', 'D', 5, 5, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(402, 4, 1, 'D06', 'D', 6, 6, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(403, 4, 1, 'D07', 'D', 7, 7, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(404, 4, 1, 'D08', 'D', 8, 8, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(405, 4, 1, 'D09', 'D', 9, 9, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(406, 4, 1, 'D10', 'D', 10, 10, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(407, 4, 1, 'D11', 'D', 11, 11, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(408, 4, 1, 'D12', 'D', 12, 12, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(409, 4, 2, 'E01', 'E', 1, 1, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(410, 4, 2, 'E02', 'E', 2, 2, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(411, 4, 2, 'E03', 'E', 3, 3, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(412, 4, 2, 'E04', 'E', 4, 4, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(413, 4, 2, 'E05', 'E', 5, 5, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(414, 4, 2, 'E06', 'E', 6, 6, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(415, 4, 2, 'E07', 'E', 7, 7, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(416, 4, 2, 'E08', 'E', 8, 8, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(417, 4, 2, 'E09', 'E', 9, 9, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(418, 4, 2, 'E10', 'E', 10, 10, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(419, 4, 2, 'E11', 'E', 11, 11, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(420, 4, 2, 'E12', 'E', 12, 12, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(421, 4, 2, 'F01', 'F', 1, 1, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(422, 4, 2, 'F02', 'F', 2, 2, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(423, 4, 2, 'F03', 'F', 3, 3, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(424, 4, 2, 'F04', 'F', 4, 4, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(425, 4, 2, 'F05', 'F', 5, 5, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(426, 4, 2, 'F06', 'F', 6, 6, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(427, 4, 2, 'F07', 'F', 7, 7, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(428, 4, 2, 'F08', 'F', 8, 8, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(429, 4, 2, 'F09', 'F', 9, 9, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(430, 4, 2, 'F10', 'F', 10, 10, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(431, 4, 2, 'F11', 'F', 11, 11, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(432, 4, 2, 'F12', 'F', 12, 12, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(433, 4, 1, 'G01', 'G', 1, 1, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(434, 4, 1, 'G02', 'G', 2, 2, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(435, 4, 1, 'G03', 'G', 3, 3, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(436, 4, 1, 'G04', 'G', 4, 4, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(437, 4, 1, 'G05', 'G', 5, 5, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(438, 4, 1, 'G06', 'G', 6, 6, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(439, 4, 1, 'G07', 'G', 7, 7, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(440, 4, 1, 'G08', 'G', 8, 8, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(441, 4, 1, 'G09', 'G', 9, 9, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(442, 4, 1, 'G10', 'G', 10, 10, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(443, 4, 1, 'G11', 'G', 11, 11, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(444, 4, 1, 'G12', 'G', 12, 12, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(445, 4, 1, 'H01', 'H', 1, 1, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(446, 4, 1, 'H02', 'H', 2, 2, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(447, 4, 1, 'H03', 'H', 3, 3, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(448, 4, 1, 'H04', 'H', 4, 4, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(449, 4, 1, 'H05', 'H', 5, 5, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(450, 4, 1, 'H06', 'H', 6, 6, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(451, 4, 1, 'H07', 'H', 7, 7, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(452, 4, 1, 'H08', 'H', 8, 8, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(453, 4, 1, 'H09', 'H', 9, 9, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(454, 4, 1, 'H10', 'H', 10, 10, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(455, 4, 1, 'H11', 'H', 11, 11, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(456, 4, 1, 'H12', 'H', 12, 12, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(457, 4, 1, 'I01', 'I', 1, 1, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(458, 4, 1, 'I02', 'I', 2, 2, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(459, 4, 1, 'I03', 'I', 3, 3, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(460, 4, 1, 'I04', 'I', 4, 4, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(461, 4, 1, 'I05', 'I', 5, 5, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(462, 4, 1, 'I06', 'I', 6, 6, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(463, 4, 1, 'I07', 'I', 7, 7, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(464, 4, 1, 'I08', 'I', 8, 8, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(465, 4, 1, 'I09', 'I', 9, 9, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(466, 4, 1, 'I10', 'I', 10, 10, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(467, 4, 1, 'I11', 'I', 11, 11, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(468, 4, 1, 'I12', 'I', 12, 12, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(469, 4, 3, 'J01', 'J', 1, 1, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(470, 4, 3, 'J02', 'J', 2, 2, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(471, 4, 3, 'J03', 'J', 3, 3, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(472, 4, 3, 'J04', 'J', 4, 4, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(473, 4, 1, 'J05', 'J', 5, 5, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(474, 4, 1, 'J06', 'J', 6, 6, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(475, 4, 1, 'J07', 'J', 7, 7, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(476, 4, 1, 'J08', 'J', 8, 8, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(477, 4, 1, 'J09', 'J', 9, 9, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(478, 4, 1, 'J10', 'J', 10, 10, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(479, 4, 1, 'J11', 'J', 11, 11, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(480, 4, 1, 'J12', 'J', 12, 12, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(481, 5, 1, 'A01', 'A', 1, 1, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(482, 5, 1, 'A02', 'A', 2, 2, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(483, 5, 1, 'A03', 'A', 3, 3, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(484, 5, 1, 'A04', 'A', 4, 4, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(485, 5, 1, 'A05', 'A', 5, 5, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(486, 5, 1, 'A06', 'A', 6, 6, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(487, 5, 1, 'A07', 'A', 7, 7, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(488, 5, 1, 'A08', 'A', 8, 8, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(489, 5, 1, 'A09', 'A', 9, 9, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(490, 5, 1, 'A10', 'A', 10, 10, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(491, 5, 1, 'A11', 'A', 11, 11, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(492, 5, 1, 'A12', 'A', 12, 12, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(493, 5, 1, 'B01', 'B', 1, 1, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(494, 5, 1, 'B02', 'B', 2, 2, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(495, 5, 1, 'B03', 'B', 3, 3, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(496, 5, 1, 'B04', 'B', 4, 4, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(497, 5, 1, 'B05', 'B', 5, 5, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(498, 5, 1, 'B06', 'B', 6, 6, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(499, 5, 1, 'B07', 'B', 7, 7, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(500, 5, 1, 'B08', 'B', 8, 8, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(501, 5, 1, 'B09', 'B', 9, 9, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(502, 5, 1, 'B10', 'B', 10, 10, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(503, 5, 1, 'B11', 'B', 11, 11, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(504, 5, 1, 'B12', 'B', 12, 12, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(505, 5, 1, 'C01', 'C', 1, 1, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(506, 5, 1, 'C02', 'C', 2, 2, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(507, 5, 1, 'C03', 'C', 3, 3, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(508, 5, 1, 'C04', 'C', 4, 4, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(509, 5, 1, 'C05', 'C', 5, 5, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(510, 5, 1, 'C06', 'C', 6, 6, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(511, 5, 1, 'C07', 'C', 7, 7, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(512, 5, 1, 'C08', 'C', 8, 8, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(513, 5, 1, 'C09', 'C', 9, 9, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(514, 5, 1, 'C10', 'C', 10, 10, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(515, 5, 1, 'C11', 'C', 11, 11, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(516, 5, 1, 'C12', 'C', 12, 12, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(517, 5, 1, 'D01', 'D', 1, 1, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(518, 5, 1, 'D02', 'D', 2, 2, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(519, 5, 1, 'D03', 'D', 3, 3, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(520, 5, 1, 'D04', 'D', 4, 4, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(521, 5, 1, 'D05', 'D', 5, 5, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(522, 5, 1, 'D06', 'D', 6, 6, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(523, 5, 1, 'D07', 'D', 7, 7, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(524, 5, 1, 'D08', 'D', 8, 8, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(525, 5, 1, 'D09', 'D', 9, 9, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(526, 5, 1, 'D10', 'D', 10, 10, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(527, 5, 1, 'D11', 'D', 11, 11, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(528, 5, 1, 'D12', 'D', 12, 12, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(529, 5, 2, 'E01', 'E', 1, 1, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(530, 5, 2, 'E02', 'E', 2, 2, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(531, 5, 2, 'E03', 'E', 3, 3, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(532, 5, 2, 'E04', 'E', 4, 4, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(533, 5, 2, 'E05', 'E', 5, 5, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(534, 5, 2, 'E06', 'E', 6, 6, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(535, 5, 2, 'E07', 'E', 7, 7, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(536, 5, 2, 'E08', 'E', 8, 8, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(537, 5, 2, 'E09', 'E', 9, 9, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(538, 5, 2, 'E10', 'E', 10, 10, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(539, 5, 2, 'E11', 'E', 11, 11, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(540, 5, 2, 'E12', 'E', 12, 12, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(541, 5, 2, 'F01', 'F', 1, 1, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(542, 5, 2, 'F02', 'F', 2, 2, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(543, 5, 2, 'F03', 'F', 3, 3, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(544, 5, 2, 'F04', 'F', 4, 4, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(545, 5, 2, 'F05', 'F', 5, 5, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(546, 5, 2, 'F06', 'F', 6, 6, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(547, 5, 2, 'F07', 'F', 7, 7, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(548, 5, 2, 'F08', 'F', 8, 8, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(549, 5, 2, 'F09', 'F', 9, 9, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(550, 5, 2, 'F10', 'F', 10, 10, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(551, 5, 2, 'F11', 'F', 11, 11, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(552, 5, 2, 'F12', 'F', 12, 12, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(553, 5, 1, 'G01', 'G', 1, 1, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(554, 5, 1, 'G02', 'G', 2, 2, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(555, 5, 1, 'G03', 'G', 3, 3, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(556, 5, 1, 'G04', 'G', 4, 4, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(557, 5, 1, 'G05', 'G', 5, 5, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(558, 5, 1, 'G06', 'G', 6, 6, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(559, 5, 1, 'G07', 'G', 7, 7, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(560, 5, 1, 'G08', 'G', 8, 8, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(561, 5, 1, 'G09', 'G', 9, 9, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(562, 5, 1, 'G10', 'G', 10, 10, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(563, 5, 1, 'G11', 'G', 11, 11, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(564, 5, 1, 'G12', 'G', 12, 12, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(565, 5, 1, 'H01', 'H', 1, 1, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(566, 5, 1, 'H02', 'H', 2, 2, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(567, 5, 1, 'H03', 'H', 3, 3, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(568, 5, 1, 'H04', 'H', 4, 4, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(569, 5, 1, 'H05', 'H', 5, 5, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(570, 5, 1, 'H06', 'H', 6, 6, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(571, 5, 1, 'H07', 'H', 7, 7, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(572, 5, 1, 'H08', 'H', 8, 8, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(573, 5, 1, 'H09', 'H', 9, 9, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(574, 5, 1, 'H10', 'H', 10, 10, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(575, 5, 1, 'H11', 'H', 11, 11, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(576, 5, 1, 'H12', 'H', 12, 12, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(577, 5, 1, 'I01', 'I', 1, 1, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(578, 5, 1, 'I02', 'I', 2, 2, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(579, 5, 1, 'I03', 'I', 3, 3, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(580, 5, 1, 'I04', 'I', 4, 4, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(581, 5, 1, 'I05', 'I', 5, 5, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(582, 5, 1, 'I06', 'I', 6, 6, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(583, 5, 1, 'I07', 'I', 7, 7, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(584, 5, 1, 'I08', 'I', 8, 8, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(585, 5, 1, 'I09', 'I', 9, 9, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(586, 5, 1, 'I10', 'I', 10, 10, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(587, 5, 1, 'I11', 'I', 11, 11, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(588, 5, 1, 'I12', 'I', 12, 12, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(589, 5, 3, 'J01', 'J', 1, 1, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(590, 5, 3, 'J02', 'J', 2, 2, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(591, 5, 3, 'J03', 'J', 3, 3, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(592, 5, 3, 'J04', 'J', 4, 4, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(593, 5, 1, 'J05', 'J', 5, 5, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(594, 5, 1, 'J06', 'J', 6, 6, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(595, 5, 1, 'J07', 'J', 7, 7, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(596, 5, 1, 'J08', 'J', 8, 8, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(597, 5, 1, 'J09', 'J', 9, 9, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(598, 5, 1, 'J10', 'J', 10, 10, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(599, 5, 1, 'J11', 'J', 11, 11, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(600, 5, 1, 'J12', 'J', 12, 12, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(601, 6, 1, 'A01', 'A', 1, 1, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(602, 6, 1, 'A02', 'A', 2, 2, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(603, 6, 1, 'A03', 'A', 3, 3, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(604, 6, 1, 'A04', 'A', 4, 4, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(605, 6, 1, 'A05', 'A', 5, 5, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(606, 6, 1, 'A06', 'A', 6, 6, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(607, 6, 1, 'A07', 'A', 7, 7, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(608, 6, 1, 'A08', 'A', 8, 8, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(609, 6, 1, 'A09', 'A', 9, 9, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(610, 6, 1, 'A10', 'A', 10, 10, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(611, 6, 1, 'A11', 'A', 11, 11, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(612, 6, 1, 'A12', 'A', 12, 12, 1, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44');
INSERT INTO `seats` (`id`, `auditorium_id`, `seat_type_id`, `seat_code`, `row_label`, `col_number`, `x`, `y`, `is_active`, `created_at`, `updated_at`) VALUES
(613, 6, 1, 'B01', 'B', 1, 1, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(614, 6, 1, 'B02', 'B', 2, 2, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(615, 6, 1, 'B03', 'B', 3, 3, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(616, 6, 1, 'B04', 'B', 4, 4, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(617, 6, 1, 'B05', 'B', 5, 5, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(618, 6, 1, 'B06', 'B', 6, 6, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(619, 6, 1, 'B07', 'B', 7, 7, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(620, 6, 1, 'B08', 'B', 8, 8, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(621, 6, 1, 'B09', 'B', 9, 9, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(622, 6, 1, 'B10', 'B', 10, 10, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(623, 6, 1, 'B11', 'B', 11, 11, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(624, 6, 1, 'B12', 'B', 12, 12, 2, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(625, 6, 1, 'C01', 'C', 1, 1, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(626, 6, 1, 'C02', 'C', 2, 2, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(627, 6, 1, 'C03', 'C', 3, 3, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(628, 6, 1, 'C04', 'C', 4, 4, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(629, 6, 1, 'C05', 'C', 5, 5, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(630, 6, 1, 'C06', 'C', 6, 6, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(631, 6, 1, 'C07', 'C', 7, 7, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(632, 6, 1, 'C08', 'C', 8, 8, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(633, 6, 1, 'C09', 'C', 9, 9, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(634, 6, 1, 'C10', 'C', 10, 10, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(635, 6, 1, 'C11', 'C', 11, 11, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(636, 6, 1, 'C12', 'C', 12, 12, 3, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(637, 6, 1, 'D01', 'D', 1, 1, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(638, 6, 1, 'D02', 'D', 2, 2, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(639, 6, 1, 'D03', 'D', 3, 3, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(640, 6, 1, 'D04', 'D', 4, 4, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(641, 6, 1, 'D05', 'D', 5, 5, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(642, 6, 1, 'D06', 'D', 6, 6, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(643, 6, 1, 'D07', 'D', 7, 7, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(644, 6, 1, 'D08', 'D', 8, 8, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(645, 6, 1, 'D09', 'D', 9, 9, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(646, 6, 1, 'D10', 'D', 10, 10, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(647, 6, 1, 'D11', 'D', 11, 11, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(648, 6, 1, 'D12', 'D', 12, 12, 4, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(649, 6, 2, 'E01', 'E', 1, 1, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(650, 6, 2, 'E02', 'E', 2, 2, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(651, 6, 2, 'E03', 'E', 3, 3, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(652, 6, 2, 'E04', 'E', 4, 4, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(653, 6, 2, 'E05', 'E', 5, 5, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(654, 6, 2, 'E06', 'E', 6, 6, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(655, 6, 2, 'E07', 'E', 7, 7, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(656, 6, 2, 'E08', 'E', 8, 8, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(657, 6, 2, 'E09', 'E', 9, 9, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(658, 6, 2, 'E10', 'E', 10, 10, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(659, 6, 2, 'E11', 'E', 11, 11, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(660, 6, 2, 'E12', 'E', 12, 12, 5, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(661, 6, 2, 'F01', 'F', 1, 1, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(662, 6, 2, 'F02', 'F', 2, 2, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(663, 6, 2, 'F03', 'F', 3, 3, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(664, 6, 2, 'F04', 'F', 4, 4, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(665, 6, 2, 'F05', 'F', 5, 5, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(666, 6, 2, 'F06', 'F', 6, 6, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(667, 6, 2, 'F07', 'F', 7, 7, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(668, 6, 2, 'F08', 'F', 8, 8, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(669, 6, 2, 'F09', 'F', 9, 9, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(670, 6, 2, 'F10', 'F', 10, 10, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(671, 6, 2, 'F11', 'F', 11, 11, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(672, 6, 2, 'F12', 'F', 12, 12, 6, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(673, 6, 1, 'G01', 'G', 1, 1, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(674, 6, 1, 'G02', 'G', 2, 2, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(675, 6, 1, 'G03', 'G', 3, 3, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(676, 6, 1, 'G04', 'G', 4, 4, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(677, 6, 1, 'G05', 'G', 5, 5, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(678, 6, 1, 'G06', 'G', 6, 6, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(679, 6, 1, 'G07', 'G', 7, 7, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(680, 6, 1, 'G08', 'G', 8, 8, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(681, 6, 1, 'G09', 'G', 9, 9, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(682, 6, 1, 'G10', 'G', 10, 10, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(683, 6, 1, 'G11', 'G', 11, 11, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(684, 6, 1, 'G12', 'G', 12, 12, 7, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(685, 6, 1, 'H01', 'H', 1, 1, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(686, 6, 1, 'H02', 'H', 2, 2, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(687, 6, 1, 'H03', 'H', 3, 3, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(688, 6, 1, 'H04', 'H', 4, 4, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(689, 6, 1, 'H05', 'H', 5, 5, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(690, 6, 1, 'H06', 'H', 6, 6, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(691, 6, 1, 'H07', 'H', 7, 7, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(692, 6, 1, 'H08', 'H', 8, 8, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(693, 6, 1, 'H09', 'H', 9, 9, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(694, 6, 1, 'H10', 'H', 10, 10, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(695, 6, 1, 'H11', 'H', 11, 11, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(696, 6, 1, 'H12', 'H', 12, 12, 8, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(697, 6, 1, 'I01', 'I', 1, 1, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(698, 6, 1, 'I02', 'I', 2, 2, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(699, 6, 1, 'I03', 'I', 3, 3, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(700, 6, 1, 'I04', 'I', 4, 4, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(701, 6, 1, 'I05', 'I', 5, 5, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(702, 6, 1, 'I06', 'I', 6, 6, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(703, 6, 1, 'I07', 'I', 7, 7, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(704, 6, 1, 'I08', 'I', 8, 8, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(705, 6, 1, 'I09', 'I', 9, 9, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(706, 6, 1, 'I10', 'I', 10, 10, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(707, 6, 1, 'I11', 'I', 11, 11, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(708, 6, 1, 'I12', 'I', 12, 12, 9, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(709, 6, 3, 'J01', 'J', 1, 1, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(710, 6, 3, 'J02', 'J', 2, 2, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(711, 6, 3, 'J03', 'J', 3, 3, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(712, 6, 3, 'J04', 'J', 4, 4, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(713, 6, 1, 'J05', 'J', 5, 5, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(714, 6, 1, 'J06', 'J', 6, 6, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(715, 6, 1, 'J07', 'J', 7, 7, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(716, 6, 1, 'J08', 'J', 8, 8, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(717, 6, 1, 'J09', 'J', 9, 9, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(718, 6, 1, 'J10', 'J', 10, 10, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(719, 6, 1, 'J11', 'J', 11, 11, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(720, 6, 1, 'J12', 'J', 12, 12, 10, 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44');

-- --------------------------------------------------------

--
-- Table structure for table `seat_blocks`
--

CREATE TABLE `seat_blocks` (
  `id` bigint UNSIGNED NOT NULL,
  `auditorium_id` bigint UNSIGNED NOT NULL,
  `seat_id` bigint UNSIGNED DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `seat_holds`
--

CREATE TABLE `seat_holds` (
  `id` bigint UNSIGNED NOT NULL,
  `show_id` bigint UNSIGNED NOT NULL,
  `seat_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `hold_token` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'HELD',
  `expires_at` datetime NOT NULL,
  `active_lock` tinyint GENERATED ALWAYS AS ((case when (`status` in (_utf8mb4'HELD',_utf8mb4'CONFIRMED')) then 1 else NULL end)) STORED,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `seat_types`
--

CREATE TABLE `seat_types` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seat_types`
--

INSERT INTO `seat_types` (`id`, `code`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'REGULAR', 'Ghế thường', 'Ghế tiêu chuẩn', '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(2, 'VIP', 'Ghế VIP', 'Ghế VIP (hàng đẹp)', '2026-03-02 20:51:43', '2026-03-02 20:51:43'),
(3, 'COUPLE', 'Ghế đôi', 'Ghế đôi (sweetbox)', '2026-03-02 20:51:43', '2026-03-02 20:51:43');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('1zeYDfXNyunoUaavDXuAbsZ5LAqZRa37485Diia0', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiS2RtNmk0RGVvaHlJQzF1SXFTSlJza09rdlRRY2lUejFPZGE3dlkyWiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbiI7czo1OiJyb3V0ZSI7czoxNToiYWRtaW4uZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czoxMzoiYWRtaW5fdXNlcl9pZCI7aToxO30=', 1773589242),
('j8vYvizoq9SlMF60sSS7iuawQ6F5mdR3Od1ZsPhi', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTXhtU001RmRtdHl6U0tERlBTTnN5dUE0RXVIUUlmVUNqelExMUVndiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbiI7czo1OiJyb3V0ZSI7czoxNToiYWRtaW4uZGFzaGJvYXJkIjt9czoxMzoiYWRtaW5fdXNlcl9pZCI7aToxO30=', 1773068311),
('sEifaM3CRjIPLDkiSLge7yTVEIlYW92SVAuL4cZR', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUUc2d1BZb3RXV25ySWJEWGdpZWNJdU10ZXJ0QmhlYUJ0bGlZM3J3aSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czoxMToiYWRtaW4ubG9naW4iO319', 1773067910),
('UhQvhX57787nrJut2vvoMM40sdLYLDglyFIXr24j', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUllxa3FMWWdCOTRpM2VlMTMyRzVsU3BVeFFMUldVOWVqZGZmcmZLaSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czoxMToiYWRtaW4ubG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1773588965),
('VkKgSR6xBZl64uOkxnq6L5tUzo0anpD1vHu0Ynzt', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNU1rbHF1bFFvbVo0dHl0WlNaSEFna2FrVmFRNTZOM1BtdlJMQkZzMCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czoxMToiYWRtaW4ubG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1773067092);

-- --------------------------------------------------------

--
-- Table structure for table `shift_assignments`
--

CREATE TABLE `shift_assignments` (
  `shift_id` bigint UNSIGNED NOT NULL,
  `staff_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shows`
--

CREATE TABLE `shows` (
  `id` bigint UNSIGNED NOT NULL,
  `public_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditorium_id` bigint UNSIGNED NOT NULL,
  `movie_version_id` bigint UNSIGNED NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `on_sale_from` datetime DEFAULT NULL,
  `on_sale_until` datetime DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SCHEDULED',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `shows`
--

INSERT INTO `shows` (`id`, `public_id`, `auditorium_id`, `movie_version_id`, `start_time`, `end_time`, `on_sale_from`, `on_sale_until`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '01KJR54E8Q9BPY94JR1NYYB9F7', 1, 11, '2026-03-02 13:00:00', '2026-03-02 15:07:00', '2026-03-01 00:00:00', '2026-03-02 14:07:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(2, '01KJR54E8V9CV7TE0TJEGA4SBQ', 1, 6, '2026-03-02 19:00:00', '2026-03-02 20:47:00', '2026-03-01 00:00:00', '2026-03-02 19:47:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(3, '01KJR54E8X00TNYZ45HG833ACW', 1, 4, '2026-03-02 21:30:00', '2026-03-02 23:14:00', '2026-03-01 00:00:00', '2026-03-02 22:14:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(4, '01KJR54E8ZCE0QYFFK3QZCG1GX', 1, 1, '2026-03-03 13:00:00', '2026-03-03 14:54:00', '2026-03-01 00:00:00', '2026-03-03 13:54:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(5, '01KJR54E927HQ6JDEDST8SY53T', 1, 8, '2026-03-03 19:00:00', '2026-03-03 21:10:00', '2026-03-01 00:00:00', '2026-03-03 20:10:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(6, '01KJR54E94K7TAE94WK85AKBK7', 1, 1, '2026-03-03 21:30:00', '2026-03-03 23:24:00', '2026-03-01 00:00:00', '2026-03-03 22:24:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(7, '01KJR54E9672QB6MPHZ1S9E0P5', 2, 9, '2026-03-02 10:00:00', '2026-03-02 12:24:00', '2026-03-01 00:00:00', '2026-03-02 11:24:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(8, '01KJR54E98VT0ZYMK50JDBHWXG', 2, 6, '2026-03-02 13:00:00', '2026-03-02 14:47:00', '2026-03-01 00:00:00', '2026-03-02 13:47:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(9, '01KJR54E9A104KC2S2CVR52745', 2, 10, '2026-03-02 21:30:00', '2026-03-02 23:54:00', '2026-03-01 00:00:00', '2026-03-02 22:54:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(10, '01KJR54E9C3WFXEKHQA9SH0NER', 2, 9, '2026-03-03 13:00:00', '2026-03-03 15:24:00', '2026-03-01 00:00:00', '2026-03-03 14:24:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(11, '01KJR54E9F8A5NGZQNXZT7DPHX', 2, 12, '2026-03-03 19:00:00', '2026-03-03 21:07:00', '2026-03-01 00:00:00', '2026-03-03 20:07:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(12, '01KJR54E9HDMWMS9C1XMT38Q61', 2, 7, '2026-03-03 21:30:00', '2026-03-03 23:17:00', '2026-03-01 00:00:00', '2026-03-03 22:17:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(13, '01KJR54E9M1SWK3VAVMXK4RQXM', 3, 5, '2026-03-02 10:00:00', '2026-03-02 11:44:00', '2026-03-01 00:00:00', '2026-03-02 10:44:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(14, '01KJR54E9PGRR6FCKCDPCVD5JJ', 3, 8, '2026-03-02 13:00:00', '2026-03-02 15:10:00', '2026-03-01 00:00:00', '2026-03-02 14:10:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(15, '01KJR54E9RETYXR28NVG8EME1S', 3, 11, '2026-03-02 21:30:00', '2026-03-02 23:37:00', '2026-03-01 00:00:00', '2026-03-02 22:37:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(16, '01KJR54E9TAAHQG2YTGGSSZZK8', 3, 9, '2026-03-03 13:00:00', '2026-03-03 15:24:00', '2026-03-01 00:00:00', '2026-03-03 14:24:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(17, '01KJR54E9WV9HN1PP6JBE5Y8CQ', 3, 11, '2026-03-03 16:00:00', '2026-03-03 18:07:00', '2026-03-01 00:00:00', '2026-03-03 17:07:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(18, '01KJR54E9YSM2S5ZC8QBRX1BH9', 3, 11, '2026-03-03 19:00:00', '2026-03-03 21:07:00', '2026-03-01 00:00:00', '2026-03-03 20:07:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(19, '01KJR54E9ZK95914KCERNH636N', 4, 10, '2026-03-02 10:00:00', '2026-03-02 12:24:00', '2026-03-01 00:00:00', '2026-03-02 11:24:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(20, '01KJR54EA2NBMMFB4DNQWR493V', 4, 2, '2026-03-02 13:00:00', '2026-03-02 14:54:00', '2026-03-01 00:00:00', '2026-03-02 13:54:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(21, '01KJR54EA5QG10WX9FE5S72JS4', 4, 2, '2026-03-02 16:00:00', '2026-03-02 17:54:00', '2026-03-01 00:00:00', '2026-03-02 16:54:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(22, '01KJR54EA7YT51KPR5KVBPNKXR', 4, 6, '2026-03-03 16:00:00', '2026-03-03 17:47:00', '2026-03-01 00:00:00', '2026-03-03 16:47:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(23, '01KJR54EA9KP0MD1YWWXEBNX4S', 4, 10, '2026-03-03 19:00:00', '2026-03-03 21:24:00', '2026-03-01 00:00:00', '2026-03-03 20:24:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(24, '01KJR54EAB9G3QXNR1JE8GHYEC', 4, 5, '2026-03-03 21:30:00', '2026-03-03 23:14:00', '2026-03-01 00:00:00', '2026-03-03 22:14:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(25, '01KJR54EADQ8AXVTF7CEXK1CG0', 5, 2, '2026-03-02 10:00:00', '2026-03-02 11:54:00', '2026-03-01 00:00:00', '2026-03-02 10:54:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(26, '01KJR54EAE4Z66H2JWJ5FQ53ET', 5, 11, '2026-03-02 16:00:00', '2026-03-02 18:07:00', '2026-03-01 00:00:00', '2026-03-02 17:07:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(27, '01KJR54EAHWKX0NXD3JZFKW5QE', 5, 11, '2026-03-02 21:30:00', '2026-03-02 23:37:00', '2026-03-01 00:00:00', '2026-03-02 22:37:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(28, '01KJR54EAKD9WSWAWTQXCNDPZE', 5, 3, '2026-03-03 16:00:00', '2026-03-03 18:27:00', '2026-03-01 00:00:00', '2026-03-03 17:27:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(29, '01KJR54EAPRS420EVZ3V980BHW', 5, 12, '2026-03-03 19:00:00', '2026-03-03 21:07:00', '2026-03-01 00:00:00', '2026-03-03 20:07:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(30, '01KJR54EAR195V2H9T937CEGNG', 5, 1, '2026-03-03 21:30:00', '2026-03-03 23:24:00', '2026-03-01 00:00:00', '2026-03-03 22:24:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(31, '01KJR54EAT63M9DF9F4NNGJNYG', 6, 5, '2026-03-02 10:00:00', '2026-03-02 11:44:00', '2026-03-01 00:00:00', '2026-03-02 10:44:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(32, '01KJR54EAWV67KB3JWCJDZGJEW', 6, 11, '2026-03-02 13:00:00', '2026-03-02 15:07:00', '2026-03-01 00:00:00', '2026-03-02 14:07:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(33, '01KJR54EAY08WKA81KPYNKQEK6', 6, 6, '2026-03-02 19:00:00', '2026-03-02 20:47:00', '2026-03-01 00:00:00', '2026-03-02 19:47:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(34, '01KJR54EB0A3BQZNRFE4KS9YAW', 6, 11, '2026-03-03 13:00:00', '2026-03-03 15:07:00', '2026-03-01 00:00:00', '2026-03-03 14:07:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(35, '01KJR54EB2562GXRYJR48VVBJ2', 6, 4, '2026-03-03 16:00:00', '2026-03-03 17:44:00', '2026-03-01 00:00:00', '2026-03-03 16:44:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(36, '01KJR54EB5A23XEFN1VCBE7P2Y', 6, 11, '2026-03-03 21:30:00', '2026-03-03 23:37:00', '2026-03-01 00:00:00', '2026-03-03 22:37:00', 'ON_SALE', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44');

-- --------------------------------------------------------

--
-- Table structure for table `show_prices`
--

CREATE TABLE `show_prices` (
  `id` bigint UNSIGNED NOT NULL,
  `show_id` bigint UNSIGNED NOT NULL,
  `seat_type_id` bigint UNSIGNED NOT NULL,
  `ticket_type_id` bigint UNSIGNED NOT NULL,
  `price_amount` bigint UNSIGNED NOT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VND',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `show_prices`
--

INSERT INTO `show_prices` (`id`, `show_id`, `seat_type_id`, `ticket_type_id`, `price_amount`, `currency`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 94371, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(2, 1, 1, 2, 80215, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(3, 1, 1, 3, 70778, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(4, 1, 2, 1, 124371, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(5, 1, 2, 2, 105715, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(6, 1, 2, 3, 93278, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(7, 1, 3, 1, 154371, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(8, 1, 3, 2, 131215, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(9, 1, 3, 3, 115778, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(10, 2, 1, 1, 99793, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(11, 2, 1, 2, 84824, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(12, 2, 1, 3, 74845, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(13, 2, 2, 1, 129793, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(14, 2, 2, 2, 110324, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(15, 2, 2, 3, 97345, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(16, 2, 3, 1, 159793, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(17, 2, 3, 2, 135824, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(18, 2, 3, 3, 119845, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(19, 3, 1, 1, 76401, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(20, 3, 1, 2, 64941, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(21, 3, 1, 3, 57301, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(22, 3, 2, 1, 106401, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(23, 3, 2, 2, 90441, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(24, 3, 2, 3, 79801, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(25, 3, 3, 1, 136401, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(26, 3, 3, 2, 115941, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(27, 3, 3, 3, 102301, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(28, 4, 1, 1, 112814, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(29, 4, 1, 2, 95892, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(30, 4, 1, 3, 84611, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(31, 4, 2, 1, 142814, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(32, 4, 2, 2, 121392, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(33, 4, 2, 3, 107111, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(34, 4, 3, 1, 172814, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(35, 4, 3, 2, 146892, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(36, 4, 3, 3, 129611, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(37, 5, 1, 1, 88787, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(38, 5, 1, 2, 75469, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(39, 5, 1, 3, 66590, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(40, 5, 2, 1, 118787, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(41, 5, 2, 2, 100969, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(42, 5, 2, 3, 89090, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(43, 5, 3, 1, 148787, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(44, 5, 3, 2, 126469, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(45, 5, 3, 3, 111590, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(46, 6, 1, 1, 91214, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(47, 6, 1, 2, 77532, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(48, 6, 1, 3, 68411, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(49, 6, 2, 1, 121214, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(50, 6, 2, 2, 103032, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(51, 6, 2, 3, 90911, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(52, 6, 3, 1, 151214, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(53, 6, 3, 2, 128532, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(54, 6, 3, 3, 113411, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(55, 7, 1, 1, 99117, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(56, 7, 1, 2, 84249, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(57, 7, 1, 3, 74338, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(58, 7, 2, 1, 129117, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(59, 7, 2, 2, 109749, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(60, 7, 2, 3, 96838, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(61, 7, 3, 1, 159117, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(62, 7, 3, 2, 135249, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(63, 7, 3, 3, 119338, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(64, 8, 1, 1, 82993, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(65, 8, 1, 2, 70544, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(66, 8, 1, 3, 62245, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(67, 8, 2, 1, 112993, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(68, 8, 2, 2, 96044, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(69, 8, 2, 3, 84745, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(70, 8, 3, 1, 142993, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(71, 8, 3, 2, 121544, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(72, 8, 3, 3, 107245, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(73, 9, 1, 1, 81208, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(74, 9, 1, 2, 69027, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(75, 9, 1, 3, 60906, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(76, 9, 2, 1, 111208, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(77, 9, 2, 2, 94527, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(78, 9, 2, 3, 83406, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(79, 9, 3, 1, 141208, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(80, 9, 3, 2, 120027, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(81, 9, 3, 3, 105906, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(82, 10, 1, 1, 117079, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(83, 10, 1, 2, 99517, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(84, 10, 1, 3, 87809, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(85, 10, 2, 1, 147079, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(86, 10, 2, 2, 125017, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(87, 10, 2, 3, 110309, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(88, 10, 3, 1, 177079, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(89, 10, 3, 2, 150517, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(90, 10, 3, 3, 132809, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(91, 11, 1, 1, 94503, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(92, 11, 1, 2, 80328, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(93, 11, 1, 3, 70877, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(94, 11, 2, 1, 124503, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(95, 11, 2, 2, 105828, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(96, 11, 2, 3, 93377, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(97, 11, 3, 1, 154503, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(98, 11, 3, 2, 131328, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(99, 11, 3, 3, 115877, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(100, 12, 1, 1, 85388, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(101, 12, 1, 2, 72580, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(102, 12, 1, 3, 64041, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(103, 12, 2, 1, 115388, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(104, 12, 2, 2, 98080, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(105, 12, 2, 3, 86541, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(106, 12, 3, 1, 145388, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(107, 12, 3, 2, 123580, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(108, 12, 3, 3, 109041, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(109, 13, 1, 1, 98304, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(110, 13, 1, 2, 83558, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(111, 13, 1, 3, 73728, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(112, 13, 2, 1, 128304, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(113, 13, 2, 2, 109058, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(114, 13, 2, 3, 96228, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(115, 13, 3, 1, 158304, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(116, 13, 3, 2, 134558, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(117, 13, 3, 3, 118728, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(118, 14, 1, 1, 77031, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(119, 14, 1, 2, 65476, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(120, 14, 1, 3, 57773, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(121, 14, 2, 1, 107031, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(122, 14, 2, 2, 90976, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(123, 14, 2, 3, 80273, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(124, 14, 3, 1, 137031, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(125, 14, 3, 2, 116476, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(126, 14, 3, 3, 102773, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(127, 15, 1, 1, 86988, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(128, 15, 1, 2, 73940, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(129, 15, 1, 3, 65241, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(130, 15, 2, 1, 116988, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(131, 15, 2, 2, 99440, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(132, 15, 2, 3, 87741, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(133, 15, 3, 1, 146988, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(134, 15, 3, 2, 124940, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(135, 15, 3, 3, 110241, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(136, 16, 1, 1, 78182, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(137, 16, 1, 2, 66455, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(138, 16, 1, 3, 58637, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(139, 16, 2, 1, 108182, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(140, 16, 2, 2, 91955, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(141, 16, 2, 3, 81137, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(142, 16, 3, 1, 138182, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(143, 16, 3, 2, 117455, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(144, 16, 3, 3, 103637, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(145, 17, 1, 1, 105829, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(146, 17, 1, 2, 89955, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(147, 17, 1, 3, 79372, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(148, 17, 2, 1, 135829, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(149, 17, 2, 2, 115455, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(150, 17, 2, 3, 101872, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(151, 17, 3, 1, 165829, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(152, 17, 3, 2, 140955, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(153, 17, 3, 3, 124372, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(154, 18, 1, 1, 109173, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(155, 18, 1, 2, 92797, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(156, 18, 1, 3, 81880, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(157, 18, 2, 1, 139173, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(158, 18, 2, 2, 118297, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(159, 18, 2, 3, 104380, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(160, 18, 3, 1, 169173, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(161, 18, 3, 2, 143797, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(162, 18, 3, 3, 126880, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(163, 19, 1, 1, 89094, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(164, 19, 1, 2, 75730, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(165, 19, 1, 3, 66821, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(166, 19, 2, 1, 119094, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(167, 19, 2, 2, 101230, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(168, 19, 2, 3, 89321, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(169, 19, 3, 1, 149094, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(170, 19, 3, 2, 126730, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(171, 19, 3, 3, 111821, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(172, 20, 1, 1, 103804, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(173, 20, 1, 2, 88233, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(174, 20, 1, 3, 77853, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(175, 20, 2, 1, 133804, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(176, 20, 2, 2, 113733, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(177, 20, 2, 3, 100353, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(178, 20, 3, 1, 163804, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(179, 20, 3, 2, 139233, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(180, 20, 3, 3, 122853, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(181, 21, 1, 1, 104144, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(182, 21, 1, 2, 88522, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(183, 21, 1, 3, 78108, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(184, 21, 2, 1, 134144, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(185, 21, 2, 2, 114022, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(186, 21, 2, 3, 100608, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(187, 21, 3, 1, 164144, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(188, 21, 3, 2, 139522, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(189, 21, 3, 3, 123108, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(190, 22, 1, 1, 97557, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(191, 22, 1, 2, 82923, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(192, 22, 1, 3, 73168, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(193, 22, 2, 1, 127557, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(194, 22, 2, 2, 108423, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(195, 22, 2, 3, 95668, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(196, 22, 3, 1, 157557, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(197, 22, 3, 2, 133923, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(198, 22, 3, 3, 118168, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(199, 23, 1, 1, 118055, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(200, 23, 1, 2, 100347, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(201, 23, 1, 3, 88541, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(202, 23, 2, 1, 148055, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(203, 23, 2, 2, 125847, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(204, 23, 2, 3, 111041, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(205, 23, 3, 1, 178055, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(206, 23, 3, 2, 151347, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(207, 23, 3, 3, 133541, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(208, 24, 1, 1, 74269, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(209, 24, 1, 2, 63129, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(210, 24, 1, 3, 55702, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(211, 24, 2, 1, 104269, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(212, 24, 2, 2, 88629, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(213, 24, 2, 3, 78202, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(214, 24, 3, 1, 134269, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(215, 24, 3, 2, 114129, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(216, 24, 3, 3, 100702, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(217, 25, 1, 1, 107020, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(218, 25, 1, 2, 90967, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(219, 25, 1, 3, 80265, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(220, 25, 2, 1, 137020, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(221, 25, 2, 2, 116467, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(222, 25, 2, 3, 102765, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(223, 25, 3, 1, 167020, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(224, 25, 3, 2, 141967, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(225, 25, 3, 3, 125265, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(226, 26, 1, 1, 94647, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(227, 26, 1, 2, 80450, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(228, 26, 1, 3, 70985, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(229, 26, 2, 1, 124647, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(230, 26, 2, 2, 105950, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(231, 26, 2, 3, 93485, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(232, 26, 3, 1, 154647, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(233, 26, 3, 2, 131450, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(234, 26, 3, 3, 115985, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(235, 27, 1, 1, 84516, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(236, 27, 1, 2, 71839, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(237, 27, 1, 3, 63387, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(238, 27, 2, 1, 114516, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(239, 27, 2, 2, 97339, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(240, 27, 2, 3, 85887, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(241, 27, 3, 1, 144516, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(242, 27, 3, 2, 122839, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(243, 27, 3, 3, 108387, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(244, 28, 1, 1, 70289, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(245, 28, 1, 2, 59746, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(246, 28, 1, 3, 52717, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(247, 28, 2, 1, 100289, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(248, 28, 2, 2, 85246, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(249, 28, 2, 3, 75217, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(250, 28, 3, 1, 130289, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(251, 28, 3, 2, 110746, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(252, 28, 3, 3, 97717, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(253, 29, 1, 1, 92614, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(254, 29, 1, 2, 78722, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(255, 29, 1, 3, 69461, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(256, 29, 2, 1, 122614, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(257, 29, 2, 2, 104222, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(258, 29, 2, 3, 91961, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(259, 29, 3, 1, 152614, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(260, 29, 3, 2, 129722, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(261, 29, 3, 3, 114461, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(262, 30, 1, 1, 101669, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(263, 30, 1, 2, 86419, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(264, 30, 1, 3, 76252, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(265, 30, 2, 1, 131669, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(266, 30, 2, 2, 111919, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(267, 30, 2, 3, 98752, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(268, 30, 3, 1, 161669, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(269, 30, 3, 2, 137419, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(270, 30, 3, 3, 121252, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(271, 31, 1, 1, 70669, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(272, 31, 1, 2, 60069, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(273, 31, 1, 3, 53002, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(274, 31, 2, 1, 100669, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(275, 31, 2, 2, 85569, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(276, 31, 2, 3, 75502, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(277, 31, 3, 1, 130669, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(278, 31, 3, 2, 111069, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(279, 31, 3, 3, 98002, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(280, 32, 1, 1, 88152, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(281, 32, 1, 2, 74929, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(282, 32, 1, 3, 66114, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(283, 32, 2, 1, 118152, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(284, 32, 2, 2, 100429, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(285, 32, 2, 3, 88614, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(286, 32, 3, 1, 148152, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(287, 32, 3, 2, 125929, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(288, 32, 3, 3, 111114, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(289, 33, 1, 1, 74875, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(290, 33, 1, 2, 63644, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(291, 33, 1, 3, 56156, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(292, 33, 2, 1, 104875, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(293, 33, 2, 2, 89144, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(294, 33, 2, 3, 78656, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(295, 33, 3, 1, 134875, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(296, 33, 3, 2, 114644, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(297, 33, 3, 3, 101156, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(298, 34, 1, 1, 85884, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(299, 34, 1, 2, 73001, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(300, 34, 1, 3, 64413, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(301, 34, 2, 1, 115884, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(302, 34, 2, 2, 98501, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(303, 34, 2, 3, 86913, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(304, 34, 3, 1, 145884, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(305, 34, 3, 2, 124001, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(306, 34, 3, 3, 109413, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(307, 35, 1, 1, 96189, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(308, 35, 1, 2, 81761, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(309, 35, 1, 3, 72142, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(310, 35, 2, 1, 126189, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(311, 35, 2, 2, 107261, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(312, 35, 2, 3, 94642, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(313, 35, 3, 1, 156189, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(314, 35, 3, 2, 132761, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(315, 35, 3, 3, 117142, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(316, 36, 1, 1, 96440, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(317, 36, 1, 2, 81974, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(318, 36, 1, 3, 72330, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(319, 36, 2, 1, 126440, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(320, 36, 2, 2, 107474, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(321, 36, 2, 3, 94830, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(322, 36, 3, 1, 156440, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(323, 36, 3, 2, 132974, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(324, 36, 3, 3, 117330, 'VND', 1, '2026-03-02 20:51:44', '2026-03-02 20:51:44');

-- --------------------------------------------------------

--
-- Table structure for table `show_status_histories`
--

CREATE TABLE `show_status_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `show_id` bigint UNSIGNED NOT NULL,
  `from_status` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changed_by` bigint UNSIGNED DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` bigint UNSIGNED NOT NULL,
  `public_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cinema_id` bigint UNSIGNED NOT NULL,
  `staff_code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `hired_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `staff_roles`
--

CREATE TABLE `staff_roles` (
  `staff_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_shifts`
--

CREATE TABLE `staff_shifts` (
  `id` bigint UNSIGNED NOT NULL,
  `cinema_id` bigint UNSIGNED NOT NULL,
  `shift_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `stock_locations`
--

CREATE TABLE `stock_locations` (
  `id` bigint UNSIGNED NOT NULL,
  `cinema_id` bigint UNSIGNED NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'WAREHOUSE',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` bigint UNSIGNED NOT NULL,
  `stock_location_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `movement_type` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty_delta` int NOT NULL,
  `unit_cost_amount` bigint UNSIGNED DEFAULT NULL,
  `reference_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint UNSIGNED DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint UNSIGNED NOT NULL,
  `public_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ward` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint UNSIGNED NOT NULL,
  `booking_ticket_id` bigint UNSIGNED NOT NULL,
  `ticket_code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qr_payload` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ISSUED',
  `issued_at` datetime DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `booking_ticket_id`, `ticket_code`, `qr_payload`, `status`, `issued_at`, `used_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'T6TUUSY069C1', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(2, 2, 'TDVFUXUEG4T2', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(3, 3, 'TGGCAJLVCXC3', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(4, 4, 'TTR6E9GG3FT4', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(5, 5, 'TAVOHFWTLQL5', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(6, 6, 'TXEMCXYEPRB6', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(7, 7, 'T7GNEDI0TBE7', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(8, 8, 'TLQUWO2OXXT8', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(9, 9, 'TBNJZWB9B4H9', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(10, 10, 'TIINZVCSFBE10', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(11, 11, 'TC2VKZIPHAM11', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(12, 12, 'TN2DR8LEVV212', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(13, 13, 'TWG88QLMPS913', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(14, 14, 'TSRIFQSA0FG14', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(15, 15, 'TJNMI9XCMPM15', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(16, 16, 'TBRGKXIGLPR16', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(17, 17, 'TJTKZWPMW8U17', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(18, 18, 'T1IWWBDRITT18', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(19, 19, 'THVRZNLES7S19', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(20, 20, 'TYZMUCOGS6A20', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(21, 21, 'T6RVWBDZS4821', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(22, 22, 'TQXGQAXU4FY22', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(23, 23, 'TG7AKDOTTVD23', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(24, 24, 'TPHDLJWQOLD24', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(25, 25, 'T2QQPQVHGMB25', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(26, 26, 'TXJ5O085OON26', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(27, 27, 'TDFEUIEGSIP27', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(28, 28, 'TRDFDR0LYPD28', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(29, 29, 'TEMPQHANOHA29', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(30, 30, 'TK2F22FLAWN30', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(31, 31, 'TFRHYFTKNWE31', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(32, 32, 'TXJTP7PXWBD32', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(33, 33, 'TAQ7RWTYFRP33', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(34, 34, 'TU03F1HORRR34', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(35, 35, 'TW9AN3B0ZVY35', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(36, 36, 'TL2JBNE2QYK36', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(37, 37, 'TGOA3W2MODX37', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(38, 38, 'T01PTSEOWWD38', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(39, 39, 'TT9OBYUEOLF39', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(40, 40, 'TMJBBDJ3ATN40', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(41, 41, 'TSJ8STOEZVV41', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(42, 42, 'TOFAEPVWXMN42', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(43, 43, 'TPPNORXAXI243', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(44, 44, 'TUXK41EZ4AF44', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(45, 45, 'T46ANSLP5GW45', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(46, 46, 'TYD4SH91QAY46', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(47, 47, 'TTQXM3WWC6O47', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(48, 48, 'TYW0EWH1JNQ48', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(49, 49, 'TTNWXBWYSED49', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(50, 50, 'TWNU91GL7OW50', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(51, 51, 'TJ8BW9GOXVH51', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(52, 52, 'TN1KPPGFIMB52', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(53, 53, 'TFG85K3ZK4853', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(54, 54, 'TDQTATQSO6D54', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(55, 55, 'TJGLDLBY5BX55', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(56, 56, 'TF4AHVE8JPI56', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(57, 57, 'TAIHQ2W39VP57', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(58, 58, 'TVMFDTFQGS458', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(59, 59, 'TUCVV30XPYW59', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(60, 60, 'TZSWBDOUM9F60', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(61, 61, 'TFESBIPB5EL61', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(62, 62, 'TAWRCNYOMML62', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(63, 63, 'TNEHANKYIKV63', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(64, 64, 'TAQTUWFTNNH64', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(65, 65, 'TPCGVBNUGGR65', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(66, 66, 'TQNWOYY47HM66', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(67, 67, 'TCIGHQJPKWI67', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(68, 68, 'TDV4AJGRZ1N68', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(69, 69, 'TA14D8AQQVE69', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(70, 70, 'TKGUFTMBZFM70', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(71, 71, 'TKOFUNNEJ6P71', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(72, 72, 'TOTSAHTLBNI72', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(73, 73, 'TT1FIC0GSBJ73', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(74, 74, 'TLKXUBETPPJ74', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(75, 75, 'TGBOXJ7DUGA75', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(76, 76, 'TUEUJRODCZ376', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(77, 77, 'TFSUMCRITBC77', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(78, 78, 'T5WT9AFQ2ES78', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(79, 79, 'TNZ02NJ3QDG79', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(80, 80, 'TA0ODXVAD5X80', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(81, 81, 'TBNTPKTG3HR81', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(82, 82, 'TJMY3VY818G82', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(83, 83, 'TQ75UHNDATI83', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(84, 84, 'TSRZ45RG8QW84', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(85, 85, 'TUBEYIETXCL85', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(86, 86, 'TYXSIMJ90AP86', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(87, 87, 'TGKOSXFN3BH87', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(88, 88, 'TRR4VBGURHZ88', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(89, 89, 'TM4IRCYRETH89', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(90, 90, 'TMNN4CMVMXQ90', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(91, 91, 'TEVZZBP2NXE91', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(92, 92, 'TS97K9YDMKW92', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(93, 93, 'T0GEBBYMKYJ93', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(94, 94, 'T3ISMO543GH94', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(95, 95, 'TLMMSAHUPPA95', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(96, 96, 'TCYV6UFHJO396', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(97, 97, 'TRU1OMETIJG97', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(98, 98, 'TKGMKGYW8YY98', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(99, 99, 'TYNHQAMUD6H99', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(100, 100, 'TRHKIQAET5G100', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(101, 101, 'TGTMHMRT4Q1101', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(102, 102, 'TCGFI344AW1102', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(103, 103, 'TVOVZ2PHUIA103', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(104, 104, 'TZBUNN40ON9104', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(105, 105, 'TS9ZXXGN1AW105', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(106, 106, 'TQRVLXVB5UZ106', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(107, 107, 'TQFZAORTVRA107', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(108, 108, 'TICV7GHMPTQ108', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(109, 109, 'TN91KEOUXRJ109', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(110, 110, 'TH7EBUP0CQD110', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(111, 111, 'T0FKDCWEIAV111', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(112, 112, 'TVL1X5NNPXM112', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(113, 113, 'T9G1PZEL5SL113', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(114, 114, 'TSLDEYPXERP114', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(115, 115, 'TBEBE4XVHN9115', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(116, 116, 'TEXROUQ1TSO116', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(117, 117, 'T9RWMIP3POC117', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(118, 118, 'TROAXQV4W0M118', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(119, 119, 'TWYVHIN9RQV119', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(120, 120, 'TZV44WETRTS120', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(121, 121, 'T9HRIU7KPYQ121', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(122, 122, 'T3EDOOY4EWU122', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(123, 123, 'TXV2DYKPKFV123', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(124, 124, 'TFY6W0XOUK1124', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(125, 125, 'TQLAE5AZ1OL125', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(126, 126, 'TGKONB290RC126', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45'),
(127, 127, 'TPJ23RNW7NZ127', NULL, 'ISSUED', '2026-03-02 20:51:45', NULL, '2026-03-02 20:51:45', '2026-03-02 20:51:45');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_types`
--

CREATE TABLE `ticket_types` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_types`
--

INSERT INTO `ticket_types` (`id`, `code`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'ADULT', 'Người lớn', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(2, 'STUDENT', 'HSSV', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44'),
(3, 'CHILD', 'Trẻ em', NULL, '2026-03-02 20:51:44', '2026-03-02 20:51:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_users_email_unique` (`email`);

--
-- Indexes for table `auditoriums`
--
ALTER TABLE `auditoriums`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_auditorium_public_id` (`public_id`),
  ADD UNIQUE KEY `uq_auditorium_code_per_cinema` (`cinema_id`,`auditorium_code`),
  ADD KEY `idx_auditorium_cinema` (`cinema_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_action_time` (`action`,`created_at`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_booking_public_id` (`public_id`),
  ADD UNIQUE KEY `uq_booking_code` (`booking_code`),
  ADD KEY `idx_booking_show` (`show_id`),
  ADD KEY `idx_booking_cinema` (`cinema_id`),
  ADD KEY `idx_booking_customer` (`customer_id`),
  ADD KEY `idx_booking_status_created` (`status`,`created_at`),
  ADD KEY `fk_booking_channel` (`sales_channel_id`);

--
-- Indexes for table `booking_discounts`
--
ALTER TABLE `booking_discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking_discount_booking` (`booking_id`),
  ADD KEY `fk_booking_discount_promo` (`promotion_id`),
  ADD KEY `fk_booking_discount_coupon` (`coupon_id`);

--
-- Indexes for table `booking_products`
--
ALTER TABLE `booking_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking_product_booking` (`booking_id`),
  ADD KEY `idx_booking_product_product` (`product_id`);

--
-- Indexes for table `booking_tickets`
--
ALTER TABLE `booking_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_booking_ticket_active` (`show_id`,`seat_id`,`active_lock`),
  ADD KEY `idx_booking_ticket_booking` (`booking_id`),
  ADD KEY `idx_booking_ticket_show` (`show_id`),
  ADD KEY `idx_booking_ticket_seat` (`seat_id`),
  ADD KEY `idx_booking_ticket_type` (`ticket_type_id`),
  ADD KEY `fk_booking_ticket_seat_type` (`seat_type_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `cinemas`
--
ALTER TABLE `cinemas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cinema_public_id` (`public_id`),
  ADD UNIQUE KEY `uq_cinema_code` (`cinema_code`),
  ADD KEY `idx_cinema_chain` (`chain_id`),
  ADD KEY `idx_cinema_location` (`province`,`district`);

--
-- Indexes for table `cinema_chains`
--
ALTER TABLE `cinema_chains`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_chain_public_id` (`public_id`),
  ADD UNIQUE KEY `uq_chain_code` (`chain_code`);

--
-- Indexes for table `content_ratings`
--
ALTER TABLE `content_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rating_code` (`code`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_coupon_code` (`code`),
  ADD KEY `idx_coupon_promo` (`promotion_id`),
  ADD KEY `idx_coupon_customer` (`customer_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_customer_public_id` (`public_id`),
  ADD UNIQUE KEY `uq_customer_phone` (`phone`),
  ADD UNIQUE KEY `uq_customer_email` (`email`),
  ADD KEY `idx_customer_name` (`full_name`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_equipment_code_per_cinema` (`cinema_id`,`code`),
  ADD KEY `idx_equipment_cinema` (`cinema_id`),
  ADD KEY `idx_equipment_auditorium` (`auditorium_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_genre_code` (`code`);

--
-- Indexes for table `inventory_balances`
--
ALTER TABLE `inventory_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_inventory_balance_unique` (`stock_location_id`,`product_id`),
  ADD KEY `idx_inventory_balance_product` (`product_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loyalty_accounts`
--
ALTER TABLE `loyalty_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_loyalty_account_customer` (`customer_id`),
  ADD KEY `idx_loyalty_account_tier` (`tier_id`);

--
-- Indexes for table `loyalty_tiers`
--
ALTER TABLE `loyalty_tiers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_loyalty_tier_code` (`code`);

--
-- Indexes for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loyalty_txn_account` (`loyalty_account_id`,`created_at`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_maintenance_status` (`status`,`opened_at`),
  ADD KEY `idx_maintenance_cinema` (`cinema_id`),
  ADD KEY `fk_maintenance_auditorium` (`auditorium_id`),
  ADD KEY `fk_maintenance_equipment` (`equipment_id`),
  ADD KEY `fk_maintenance_staff` (`requested_by`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_movie_public_id` (`public_id`),
  ADD KEY `idx_movie_title` (`title`),
  ADD KEY `idx_movie_release` (`release_date`),
  ADD KEY `idx_movie_rating` (`content_rating_id`);

--
-- Indexes for table `movie_genres`
--
ALTER TABLE `movie_genres`
  ADD PRIMARY KEY (`movie_id`,`genre_id`),
  ADD KEY `fk_movie_genre_genre` (`genre_id`);

--
-- Indexes for table `movie_people`
--
ALTER TABLE `movie_people`
  ADD PRIMARY KEY (`movie_id`,`person_id`,`role_type`),
  ADD KEY `idx_movie_people_person` (`person_id`);

--
-- Indexes for table `movie_versions`
--
ALTER TABLE `movie_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_movie_version_unique` (`movie_id`,`format`,`audio_language`,`subtitle_language`),
  ADD KEY `idx_movie_version_movie` (`movie_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_booking` (`booking_id`),
  ADD KEY `idx_payment_status_created` (`status`,`created_at`),
  ADD KEY `idx_payment_external_ref` (`external_txn_ref`);

--
-- Indexes for table `people`
--
ALTER TABLE `people`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_person_public_id` (`public_id`),
  ADD KEY `idx_person_name` (`full_name`);

--
-- Indexes for table `pricing_profiles`
--
ALTER TABLE `pricing_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pricing_profile_code` (`code`),
  ADD KEY `idx_pricing_profile_cinema` (`cinema_id`);

--
-- Indexes for table `pricing_rules`
--
ALTER TABLE `pricing_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pricing_rule_profile` (`pricing_profile_id`,`is_active`,`priority`),
  ADD KEY `idx_pricing_rule_dow` (`day_of_week`),
  ADD KEY `fk_pricing_rule_seat_type` (`seat_type_id`),
  ADD KEY `fk_pricing_rule_ticket_type` (`ticket_type_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_product_public_id` (`public_id`),
  ADD UNIQUE KEY `uq_product_sku` (`sku`),
  ADD KEY `idx_product_category` (`category_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_product_category_code` (`code`);

--
-- Indexes for table `product_prices`
--
ALTER TABLE `product_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_price_product` (`product_id`,`is_active`),
  ADD KEY `idx_product_price_cinema` (`cinema_id`,`is_active`),
  ADD KEY `idx_product_price_time` (`effective_from`,`effective_to`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_promo_code` (`code`),
  ADD KEY `idx_promo_time` (`start_at`,`end_at`);

--
-- Indexes for table `promotion_cinemas`
--
ALTER TABLE `promotion_cinemas`
  ADD PRIMARY KEY (`promotion_id`,`cinema_id`),
  ADD KEY `fk_promo_cinema_cinema` (`cinema_id`);

--
-- Indexes for table `promotion_movies`
--
ALTER TABLE `promotion_movies`
  ADD PRIMARY KEY (`promotion_id`,`movie_id`),
  ADD KEY `fk_promo_movie_movie` (`movie_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_po_public_id` (`public_id`),
  ADD UNIQUE KEY `uq_po_code` (`po_code`),
  ADD KEY `idx_po_supplier` (`supplier_id`),
  ADD KEY `idx_po_cinema` (`cinema_id`);

--
-- Indexes for table `purchase_order_lines`
--
ALTER TABLE `purchase_order_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_po_line_po` (`purchase_order_id`),
  ADD KEY `idx_po_line_product` (`product_id`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_refund_payment` (`payment_id`),
  ADD KEY `idx_refund_status` (`status`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_role_code` (`code`);

--
-- Indexes for table `sales_channels`
--
ALTER TABLE `sales_channels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sales_channel_code` (`code`);

--
-- Indexes for table `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_seat_code_per_auditorium` (`auditorium_id`,`seat_code`),
  ADD KEY `idx_seat_auditorium` (`auditorium_id`),
  ADD KEY `idx_seat_type` (`seat_type_id`);

--
-- Indexes for table `seat_blocks`
--
ALTER TABLE `seat_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_seat_block_auditorium` (`auditorium_id`,`start_at`,`end_at`),
  ADD KEY `idx_seat_block_seat` (`seat_id`,`start_at`,`end_at`);

--
-- Indexes for table `seat_holds`
--
ALTER TABLE `seat_holds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_seat_hold_token` (`hold_token`),
  ADD UNIQUE KEY `uq_seat_hold_active` (`show_id`,`seat_id`,`active_lock`),
  ADD KEY `idx_seat_hold_show` (`show_id`,`expires_at`),
  ADD KEY `idx_seat_hold_customer` (`customer_id`),
  ADD KEY `fk_seat_hold_seat` (`seat_id`);

--
-- Indexes for table `seat_types`
--
ALTER TABLE `seat_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_seat_type_code` (`code`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shift_assignments`
--
ALTER TABLE `shift_assignments`
  ADD PRIMARY KEY (`shift_id`,`staff_id`),
  ADD KEY `fk_shift_assignment_staff` (`staff_id`);

--
-- Indexes for table `shows`
--
ALTER TABLE `shows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_show_public_id` (`public_id`),
  ADD KEY `idx_show_time` (`start_time`,`end_time`),
  ADD KEY `idx_show_auditorium_time` (`auditorium_id`,`start_time`),
  ADD KEY `idx_show_movie_time` (`movie_version_id`,`start_time`);

--
-- Indexes for table `show_prices`
--
ALTER TABLE `show_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_show_price_unique` (`show_id`,`seat_type_id`,`ticket_type_id`),
  ADD KEY `idx_show_price_show` (`show_id`),
  ADD KEY `idx_show_price_seat_type` (`seat_type_id`),
  ADD KEY `idx_show_price_ticket_type` (`ticket_type_id`);

--
-- Indexes for table `show_status_histories`
--
ALTER TABLE `show_status_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_show_status_history_show` (`show_id`,`changed_at`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_staff_public_id` (`public_id`),
  ADD UNIQUE KEY `uq_staff_code_per_cinema` (`cinema_id`,`staff_code`),
  ADD KEY `idx_staff_cinema` (`cinema_id`);

--
-- Indexes for table `staff_roles`
--
ALTER TABLE `staff_roles`
  ADD PRIMARY KEY (`staff_id`,`role_id`),
  ADD KEY `fk_staff_roles_role` (`role_id`);

--
-- Indexes for table `staff_shifts`
--
ALTER TABLE `staff_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shift_cinema_date` (`cinema_id`,`shift_date`);

--
-- Indexes for table `stock_locations`
--
ALTER TABLE `stock_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_stock_location_code_per_cinema` (`cinema_id`,`code`),
  ADD KEY `idx_stock_location_cinema` (`cinema_id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_stock_movement_location` (`stock_location_id`,`created_at`),
  ADD KEY `idx_stock_movement_product` (`product_id`,`created_at`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_supplier_public_id` (`public_id`),
  ADD KEY `idx_supplier_name` (`name`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ticket_code` (`ticket_code`),
  ADD UNIQUE KEY `uq_ticket_booking_ticket` (`booking_ticket_id`),
  ADD KEY `idx_ticket_status` (`status`);

--
-- Indexes for table `ticket_types`
--
ALTER TABLE `ticket_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ticket_type_code` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `auditoriums`
--
ALTER TABLE `auditoriums`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_discounts`
--
ALTER TABLE `booking_discounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_products`
--
ALTER TABLE `booking_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_tickets`
--
ALTER TABLE `booking_tickets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cinemas`
--
ALTER TABLE `cinemas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cinema_chains`
--
ALTER TABLE `cinema_chains`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `content_ratings`
--
ALTER TABLE `content_ratings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `inventory_balances`
--
ALTER TABLE `inventory_balances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyalty_accounts`
--
ALTER TABLE `loyalty_accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyalty_tiers`
--
ALTER TABLE `loyalty_tiers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `movie_versions`
--
ALTER TABLE `movie_versions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `people`
--
ALTER TABLE `people`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pricing_profiles`
--
ALTER TABLE `pricing_profiles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pricing_rules`
--
ALTER TABLE `pricing_rules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_prices`
--
ALTER TABLE `product_prices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_lines`
--
ALTER TABLE `purchase_order_lines`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_channels`
--
ALTER TABLE `sales_channels`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=721;

--
-- AUTO_INCREMENT for table `seat_blocks`
--
ALTER TABLE `seat_blocks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seat_holds`
--
ALTER TABLE `seat_holds`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seat_types`
--
ALTER TABLE `seat_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shows`
--
ALTER TABLE `shows`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `show_prices`
--
ALTER TABLE `show_prices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=325;

--
-- AUTO_INCREMENT for table `show_status_histories`
--
ALTER TABLE `show_status_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_shifts`
--
ALTER TABLE `staff_shifts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_locations`
--
ALTER TABLE `stock_locations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_types`
--
ALTER TABLE `ticket_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auditoriums`
--
ALTER TABLE `auditoriums`
  ADD CONSTRAINT `fk_auditorium_cinema` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`id`);

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_booking_channel` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channels` (`id`),
  ADD CONSTRAINT `fk_booking_cinema` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`id`),
  ADD CONSTRAINT `fk_booking_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `fk_booking_show` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`);

--
-- Constraints for table `booking_discounts`
--
ALTER TABLE `booking_discounts`
  ADD CONSTRAINT `fk_booking_discount_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `fk_booking_discount_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`),
  ADD CONSTRAINT `fk_booking_discount_promo` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`);

--
-- Constraints for table `booking_products`
--
ALTER TABLE `booking_products`
  ADD CONSTRAINT `fk_booking_product_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `fk_booking_product_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `booking_tickets`
--
ALTER TABLE `booking_tickets`
  ADD CONSTRAINT `fk_booking_ticket_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `fk_booking_ticket_seat` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`id`),
  ADD CONSTRAINT `fk_booking_ticket_seat_type` FOREIGN KEY (`seat_type_id`) REFERENCES `seat_types` (`id`),
  ADD CONSTRAINT `fk_booking_ticket_show` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`),
  ADD CONSTRAINT `fk_booking_ticket_ticket_type` FOREIGN KEY (`ticket_type_id`) REFERENCES `ticket_types` (`id`);

--
-- Constraints for table `cinemas`
--
ALTER TABLE `cinemas`
  ADD CONSTRAINT `fk_cinema_chain` FOREIGN KEY (`chain_id`) REFERENCES `cinema_chains` (`id`);

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `fk_coupon_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `fk_coupon_promo` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`);

--
-- Constraints for table `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `fk_equipment_auditorium` FOREIGN KEY (`auditorium_id`) REFERENCES `auditoriums` (`id`),
  ADD CONSTRAINT `fk_equipment_cinema` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`id`);

--
-- Constraints for table `inventory_balances`
--
ALTER TABLE `inventory_balances`
  ADD CONSTRAINT `fk_inventory_balance_location` FOREIGN KEY (`stock_location_id`) REFERENCES `stock_locations` (`id`),
  ADD CONSTRAINT `fk_inventory_balance_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `loyalty_accounts`
--
ALTER TABLE `loyalty_accounts`
  ADD CONSTRAINT `fk_loyalty_account_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `fk_loyalty_account_tier` FOREIGN KEY (`tier_id`) REFERENCES `loyalty_tiers` (`id`);

--
-- Constraints for table `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD CONSTRAINT `fk_loyalty_txn_account` FOREIGN KEY (`loyalty_account_id`) REFERENCES `loyalty_accounts` (`id`);

--
-- Constraints for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `fk_maintenance_auditorium` FOREIGN KEY (`auditorium_id`) REFERENCES `auditoriums` (`id`),
  ADD CONSTRAINT `fk_maintenance_cinema` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`id`),
  ADD CONSTRAINT `fk_maintenance_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`),
  ADD CONSTRAINT `fk_maintenance_staff` FOREIGN KEY (`requested_by`) REFERENCES `staff` (`id`);

--
-- Constraints for table `movies`
--
ALTER TABLE `movies`
  ADD CONSTRAINT `fk_movie_rating` FOREIGN KEY (`content_rating_id`) REFERENCES `content_ratings` (`id`);

--
-- Constraints for table `movie_genres`
--
ALTER TABLE `movie_genres`
  ADD CONSTRAINT `fk_movie_genre_genre` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`),
  ADD CONSTRAINT `fk_movie_genre_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`);

--
-- Constraints for table `movie_people`
--
ALTER TABLE `movie_people`
  ADD CONSTRAINT `fk_movie_people_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`),
  ADD CONSTRAINT `fk_movie_people_person` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`);

--
-- Constraints for table `movie_versions`
--
ALTER TABLE `movie_versions`
  ADD CONSTRAINT `fk_movie_version_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Constraints for table `pricing_profiles`
--
ALTER TABLE `pricing_profiles`
  ADD CONSTRAINT `fk_pricing_profile_cinema` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`id`);

--
-- Constraints for table `pricing_rules`
--
ALTER TABLE `pricing_rules`
  ADD CONSTRAINT `fk_pricing_rule_profile` FOREIGN KEY (`pricing_profile_id`) REFERENCES `pricing_profiles` (`id`),
  ADD CONSTRAINT `fk_pricing_rule_seat_type` FOREIGN KEY (`seat_type_id`) REFERENCES `seat_types` (`id`),
  ADD CONSTRAINT `fk_pricing_rule_ticket_type` FOREIGN KEY (`ticket_type_id`) REFERENCES `ticket_types` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`);

--
-- Constraints for table `product_prices`
--
ALTER TABLE `product_prices`
  ADD CONSTRAINT `fk_product_price_cinema` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`id`),
  ADD CONSTRAINT `fk_product_price_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `promotion_cinemas`
--
ALTER TABLE `promotion_cinemas`
  ADD CONSTRAINT `fk_promo_cinema_cinema` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`id`),
  ADD CONSTRAINT `fk_promo_cinema_promo` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`);

--
-- Constraints for table `promotion_movies`
--
ALTER TABLE `promotion_movies`
  ADD CONSTRAINT `fk_promo_movie_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`),
  ADD CONSTRAINT `fk_promo_movie_promo` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`);

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `fk_po_cinema` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`id`),
  ADD CONSTRAINT `fk_po_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `purchase_order_lines`
--
ALTER TABLE `purchase_order_lines`
  ADD CONSTRAINT `fk_po_line_po` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`),
  ADD CONSTRAINT `fk_po_line_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `refunds`
--
ALTER TABLE `refunds`
  ADD CONSTRAINT `fk_refund_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`);

--
-- Constraints for table `seats`
--
ALTER TABLE `seats`
  ADD CONSTRAINT `fk_seat_auditorium` FOREIGN KEY (`auditorium_id`) REFERENCES `auditoriums` (`id`),
  ADD CONSTRAINT `fk_seat_type` FOREIGN KEY (`seat_type_id`) REFERENCES `seat_types` (`id`);

--
-- Constraints for table `seat_blocks`
--
ALTER TABLE `seat_blocks`
  ADD CONSTRAINT `fk_seat_block_auditorium` FOREIGN KEY (`auditorium_id`) REFERENCES `auditoriums` (`id`),
  ADD CONSTRAINT `fk_seat_block_seat` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`id`);

--
-- Constraints for table `seat_holds`
--
ALTER TABLE `seat_holds`
  ADD CONSTRAINT `fk_seat_hold_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `fk_seat_hold_seat` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`id`),
  ADD CONSTRAINT `fk_seat_hold_show` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`);

--
-- Constraints for table `shift_assignments`
--
ALTER TABLE `shift_assignments`
  ADD CONSTRAINT `fk_shift_assignment_shift` FOREIGN KEY (`shift_id`) REFERENCES `staff_shifts` (`id`),
  ADD CONSTRAINT `fk_shift_assignment_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`);

--
-- Constraints for table `shows`
--
ALTER TABLE `shows`
  ADD CONSTRAINT `fk_show_auditorium` FOREIGN KEY (`auditorium_id`) REFERENCES `auditoriums` (`id`),
  ADD CONSTRAINT `fk_show_movie_version` FOREIGN KEY (`movie_version_id`) REFERENCES `movie_versions` (`id`);

--
-- Constraints for table `show_prices`
--
ALTER TABLE `show_prices`
  ADD CONSTRAINT `fk_show_price_seat_type` FOREIGN KEY (`seat_type_id`) REFERENCES `seat_types` (`id`),
  ADD CONSTRAINT `fk_show_price_show` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`),
  ADD CONSTRAINT `fk_show_price_ticket_type` FOREIGN KEY (`ticket_type_id`) REFERENCES `ticket_types` (`id`);

--
-- Constraints for table `show_status_histories`
--
ALTER TABLE `show_status_histories`
  ADD CONSTRAINT `fk_show_status_history_show` FOREIGN KEY (`show_id`) REFERENCES `shows` (`id`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `fk_staff_cinema` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`id`);

--
-- Constraints for table `staff_roles`
--
ALTER TABLE `staff_roles`
  ADD CONSTRAINT `fk_staff_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `fk_staff_roles_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`);

--
-- Constraints for table `staff_shifts`
--
ALTER TABLE `staff_shifts`
  ADD CONSTRAINT `fk_shift_cinema` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`id`);

--
-- Constraints for table `stock_locations`
--
ALTER TABLE `stock_locations`
  ADD CONSTRAINT `fk_stock_location_cinema` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`id`);

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `fk_stock_movement_location` FOREIGN KEY (`stock_location_id`) REFERENCES `stock_locations` (`id`),
  ADD CONSTRAINT `fk_stock_movement_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_ticket_booking_ticket` FOREIGN KEY (`booking_ticket_id`) REFERENCES `booking_tickets` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
