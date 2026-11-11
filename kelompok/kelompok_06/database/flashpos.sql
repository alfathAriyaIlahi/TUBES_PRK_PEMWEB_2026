-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 09, 2026 at 05:49 PM
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
-- Database: `db_flashpos`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Kopi & Espresso', 'Minuman berbasis espresso panas dan dingin'),
(2, 'Non-Kopi & Teh', 'Minuman segar tanpa kopi'),
(3, 'Makanan Utama', 'Hidangan berat pendamping'),
(4, 'Snack & Pastry', 'Camilan ringan');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `no_invoice` varchar(30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) DEFAULT 'Umum',
  `nomor_meja` varchar(10) DEFAULT '-',
  `total_tagihan` decimal(12,2) NOT NULL,
  `tipe_pembayaran` enum('full','split') DEFAULT 'full',
  `status_order` enum('pending','sebagian','lunas','batal') NOT NULL DEFAULT 'pending',
  `tanggal_transaksi` datetime DEFAULT current_timestamp(),
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `diskon` decimal(12,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `pajak` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `no_invoice`, `user_id`, `nama_pelanggan`, `nomor_meja`, `total_tagihan`, `tipe_pembayaran`, `status_order`, `tanggal_transaksi`, `subtotal`, `diskon`, `service_charge`, `pajak`, `created_at`) VALUES
(1, 'INV-20261109203242', 2, 'Umum', '-', 33000.00, 'split', 'lunas', '2026-11-09 20:32:42', 0.00, 0.00, 0.00, 0.00, '2026-11-09 16:48:28'),
(2, 'INV-20261109203834', 2, 'Mahiru', '11', 46000.00, 'split', 'lunas', '2026-11-09 20:38:34', 0.00, 0.00, 0.00, 0.00, '2026-11-09 16:48:28'),
(3, 'INV-20261109205358', 2, 'dihan', '12', 43000.00, 'split', 'lunas', '2026-11-09 20:53:58', 0.00, 0.00, 0.00, 0.00, '2026-11-09 16:48:28');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `status_bayar_item` enum('belum','lunas') DEFAULT 'belum'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `qty`, `harga_satuan`, `subtotal`, `status_bayar_item`) VALUES
(1, 1, 5, 1, 15000.00, 15000.00, 'lunas'),
(2, 1, 1, 1, 18000.00, 18000.00, 'lunas'),
(3, 2, 3, 1, 24000.00, 24000.00, 'lunas'),
(4, 2, 2, 1, 22000.00, 22000.00, 'lunas'),
(5, 3, 4, 1, 28000.00, 28000.00, 'lunas'),
(6, 3, 5, 1, 15000.00, 15000.00, 'lunas');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `urutan_split` int(11) DEFAULT 1,
  `metode_bayar` enum('tunai','qris','debit') NOT NULL DEFAULT 'tunai',
  `nominal_bayar` decimal(12,2) NOT NULL,
  `uang_diterima` decimal(12,2) NOT NULL,
  `kembalian` decimal(12,2) NOT NULL DEFAULT 0.00,
  `waktu_bayar` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `urutan_split`, `metode_bayar`, `nominal_bayar`, `uang_diterima`, `kembalian`, `waktu_bayar`, `created_at`) VALUES
(1, 1, 1, 'tunai', 15000.00, 15000.00, 0.00, '2026-11-09 20:36:45', '2026-11-09 16:46:11'),
(2, 1, 2, 'tunai', 18000.00, 18000.00, 0.00, '2026-11-09 20:38:02', '2026-11-09 16:46:11'),
(3, 2, 1, 'tunai', 24000.00, 26000.00, 2000.00, '2026-11-09 20:38:42', '2026-11-09 16:46:11'),
(4, 2, 2, 'tunai', 22000.00, 22000.00, 0.00, '2026-11-09 20:53:34', '2026-11-09 16:46:11'),
(5, 3, 1, 'tunai', 12000.00, 12000.00, 0.00, '2026-11-09 20:54:51', '2026-11-09 16:46:11'),
(6, 3, 2, 'tunai', 28000.00, 28000.00, 0.00, '2026-11-09 20:55:10', '2026-11-09 16:46:11'),
(7, 3, 3, 'tunai', 3000.00, 3000.00, 0.00, '2026-11-09 20:55:23', '2026-11-09 16:46:11');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `kode_produk` varchar(20) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `status` enum('tersedia','habis') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `kode_produk`, `nama_produk`, `harga`, `stok`, `status`) VALUES
(1, 1, 'KOP-001', 'Iced Americano', 18000.00, 49, 'tersedia'),
(2, 1, 'KOP-002', 'Kopi Susu Gula Aren', 22000.00, 44, 'tersedia'),
(3, 2, 'NON-001', 'Matcha Latte Ice', 24000.00, 29, 'tersedia'),
(4, 3, 'MKN-001', 'Nasi Goreng Spesial', 28000.00, 24, 'tersedia'),
(5, 4, 'SNK-001', 'French Fries Platter', 15000.00, 38, 'tersedia');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','kasir') NOT NULL DEFAULT 'kasir',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator Utama', 'admin', '$2y$10$D1dpF/9Pr4cYKjW.mW84w.UwwvW3TwvgDYh1qv21mSlhBlAuGA7IG', 'admin', '2026-11-02 12:00:02'),
(2, 'Kasir Shift Pagi', 'kasir1', '$2y$10$2vlG8YSrkOCbctcFyr0sEu/VmGJDTY2D5S//mWhuVBeQXDonF1gK2', 'kasir', '2026-11-02 12:00:02');

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_invoice` (`no_invoice`),
  ADD UNIQUE KEY `uniq_invoice` (`no_invoice`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`),
  ADD KEY `category_id` (`category_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

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
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
