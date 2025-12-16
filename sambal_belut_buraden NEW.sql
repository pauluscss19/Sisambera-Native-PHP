-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 04:53 AM
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
-- Database: `sambal_belut_buraden`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `shift` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `nama`, `username`, `password`, `shift`, `created_at`) VALUES
(1, 'Admin', 'admin', '0192023a7bbd73250516f069df18b500', 'pagi', '2025-11-15 11:41:52');

-- --------------------------------------------------------

--
-- Table structure for table `bahan`
--

CREATE TABLE `bahan` (
  `bahan_id` int(11) NOT NULL,
  `nama_bahan` varchar(100) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `status` enum('safe','low-stock','very-low') DEFAULT 'safe',
  `minimum_stok` decimal(10,2) DEFAULT 5.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bahan`
--

INSERT INTO `bahan` (`bahan_id`, `nama_bahan`, `jumlah`, `satuan`, `status`, `minimum_stok`, `created_at`) VALUES
(1, 'Belut', 12.00, 'Kg', 'safe', 5.00, '2025-11-15 11:41:52'),
(2, 'Beras', 50.00, 'Kg', 'safe', 10.00, '2025-11-15 11:41:52'),
(3, 'Cabai Rawit', 100.00, 'Kg', 'safe', 3.00, '2025-11-15 11:41:52'),
(4, 'Minyak Goreng', 9.00, 'liter', 'safe', 5.00, '2025-11-15 11:41:52'),
(5, 'Es Teh Manis', 10.00, 'pcs', 'safe', 5.00, '2025-11-15 11:41:52'),
(6, 'Ayam', 10000.00, 'Kg', 'safe', 5.00, '2025-11-15 11:41:52');

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `detail_id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`detail_id`, `pesanan_id`, `menu_id`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(10, 9, 19, 1, 25000.00, 25000.00),
(11, 9, 20, 1, 10000.00, 10000.00),
(12, 10, 20, 1, 10000.00, 10000.00),
(13, 11, 20, 1, 10000.00, 10000.00),
(14, 12, 20, 1, 10000.00, 10000.00),
(15, 13, 19, 1, 25000.00, 25000.00),
(16, 14, 20, 1, 10000.00, 10000.00),
(17, 15, 21, 1, 1000.00, 1000.00),
(18, 16, 19, 1, 25000.00, 25000.00),
(19, 17, 19, 1, 25000.00, 25000.00),
(20, 18, 19, 1, 25000.00, 25000.00),
(21, 18, 22, 1, 11.00, 11.00),
(22, 19, 19, 2, 25000.00, 50000.00),
(23, 20, 19, 1, 25000.00, 25000.00),
(24, 21, 19, 1, 25000.00, 25000.00),
(25, 22, 19, 1, 25000.00, 25000.00),
(26, 23, 19, 1, 25000.00, 25000.00);

-- --------------------------------------------------------

--
-- Table structure for table `layanan`
--

CREATE TABLE `layanan` (
  `layanan_id` int(11) NOT NULL,
  `jenis_layanan` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `layanan`
--

INSERT INTO `layanan` (`layanan_id`, `jenis_layanan`, `deskripsi`) VALUES
(1, 'Dine In', 'Makan di tempat'),
(2, 'Take Away', 'Bungkus dibawa pulang'),
(3, 'Delivery', 'Pesan antar');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `menu_id` int(11) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('tersedia','habis') DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`menu_id`, `nama_menu`, `kategori`, `deskripsi`, `harga`, `stok`, `foto`, `status`, `created_at`) VALUES
(19, 'Sambal Belut', 'Makanan Utama', 'Sambal Belut Dengan belut yang fresh', 25000.00, 1, '692f33f9b66b1_1764701177.jpeg', 'tersedia', '2025-12-02 18:46:17'),
(20, 'Ayam Goreng', 'Makanan', 'dhusbwdbw', 10000.00, 0, '692f36402003e_1764701760.png', 'habis', '2025-12-02 18:56:00'),
(21, 'ghgu', 'Makanan', 'hguh', 1000.00, 0, '692f36ef51dc8_1764701935.jpeg', 'habis', '2025-12-02 18:58:55'),
(22, 'es', 'Minuman', 'wdwdf', 11.00, 0, '692f378aec5c5_1764702090.jpg', 'habis', '2025-12-02 19:01:30');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `pembayaran_id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `metode` varchar(20) DEFAULT NULL,
  `bank` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','berhasil','gagal') DEFAULT 'pending',
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `tanggal_bayar` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`pembayaran_id`, `pesanan_id`, `metode`, `bank`, `total`, `status`, `bukti_bayar`, `tanggal_bayar`) VALUES
(9, 9, 'cash', NULL, 38500.00, 'berhasil', NULL, '2025-12-03 01:59:22'),
(10, 10, 'cash', NULL, 11000.00, 'berhasil', NULL, '2025-12-03 02:00:12'),
(11, 11, 'cash', NULL, 11000.00, 'berhasil', NULL, '2025-12-03 02:06:33'),
(12, 12, 'cash', NULL, 11000.00, 'berhasil', NULL, '2025-12-03 02:07:26'),
(13, 13, 'cash', NULL, 27500.00, 'berhasil', NULL, '2025-12-03 02:08:22'),
(14, 14, 'cash', NULL, 11000.00, 'berhasil', NULL, '2025-12-03 02:16:29'),
(15, 15, 'qris', NULL, 1100.00, 'pending', NULL, NULL),
(16, 16, 'qris', NULL, 27500.00, 'pending', NULL, NULL),
(17, 17, 'qris', NULL, 27500.00, 'berhasil', NULL, '2025-12-03 02:27:26'),
(18, 18, 'transfer', 'BCA', 27512.10, 'berhasil', 'bukti_18_1764703857.jpeg', '2025-12-03 02:31:15'),
(19, 19, 'transfer', 'BCA', 55000.00, 'berhasil', 'bukti_19_1764704405.jpeg', '2025-12-03 02:40:22'),
(20, 20, 'transfer', 'BCA', 27500.00, 'berhasil', 'bukti_20_1764704983.jpeg', '2025-12-03 03:04:48'),
(21, 21, 'cash', NULL, 27500.00, 'pending', NULL, NULL),
(22, 22, 'qris', NULL, 27500.00, 'berhasil', 'bukti_22_1764705092.jpeg', '2025-12-03 03:02:27'),
(23, 23, 'transfer', 'Mandiri', 27500.00, 'pending', 'bukti_23_1764705523.jpeg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `pesanan_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `layanan_id` int(11) NOT NULL,
  `tanggal` datetime NOT NULL,
  `status` enum('pending','dikonfirmasi','diproses','selesai','dibatalkan') DEFAULT 'pending',
  `total_harga` decimal(10,2) NOT NULL,
  `nomor_antrian` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`pesanan_id`, `user_id`, `admin_id`, `layanan_id`, `tanggal`, `status`, `total_harga`, `nomor_antrian`, `created_at`) VALUES
(9, 5, NULL, 1, '2025-12-02 19:59:11', 'selesai', 38500.00, 'A001', '2025-12-02 18:59:11'),
(10, 5, NULL, 1, '2025-12-02 20:00:03', 'selesai', 11000.00, 'A002', '2025-12-02 19:00:03'),
(11, 5, NULL, 1, '2025-12-02 20:06:26', 'selesai', 11000.00, 'A003', '2025-12-02 19:06:26'),
(12, 5, NULL, 1, '2025-12-02 20:07:14', 'selesai', 11000.00, 'A004', '2025-12-02 19:07:14'),
(13, 5, NULL, 1, '2025-12-02 20:08:16', 'selesai', 27500.00, 'A005', '2025-12-02 19:08:16'),
(14, 5, NULL, 2, '2025-12-02 20:16:17', 'selesai', 11000.00, 'A006', '2025-12-02 19:16:17'),
(15, 5, NULL, 1, '2025-12-02 20:17:29', 'dibatalkan', 1100.00, 'A007', '2025-12-02 19:17:29'),
(16, 5, NULL, 1, '2025-12-02 20:26:08', 'dibatalkan', 27500.00, 'A008', '2025-12-02 19:26:08'),
(17, 5, NULL, 1, '2025-12-02 20:26:47', 'selesai', 27500.00, 'A009', '2025-12-02 19:26:47'),
(18, 5, NULL, 1, '2025-12-02 20:30:49', 'selesai', 27512.10, 'A010', '2025-12-02 19:30:49'),
(19, 5, NULL, 1, '2025-12-02 20:39:59', 'selesai', 55000.00, 'A011', '2025-12-02 19:39:59'),
(20, 5, NULL, 2, '2025-12-02 20:49:40', 'selesai', 27500.00, 'A012', '2025-12-02 19:49:40'),
(21, 5, NULL, 1, '2025-12-02 20:50:23', 'pending', 27500.00, 'A013', '2025-12-02 19:50:23'),
(22, 5, NULL, 1, '2025-12-02 20:51:27', 'selesai', 27500.00, 'A014', '2025-12-02 19:51:27'),
(23, 5, NULL, 1, '2025-12-02 20:58:39', 'pending', 27500.00, 'A015', '2025-12-02 19:58:39');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `nama`, `no_hp`, `created_at`) VALUES
(5, 'ggyh', '0812212012812', '2025-12-02 18:59:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bahan`
--
ALTER TABLE `bahan`
  ADD PRIMARY KEY (`bahan_id`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `detail_pesanan_ibfk_2` (`menu_id`);

--
-- Indexes for table `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`layanan_id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`menu_id`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`pembayaran_id`),
  ADD KEY `pesanan_id` (`pesanan_id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`pesanan_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `layanan_id` (`layanan_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bahan`
--
ALTER TABLE `bahan`
  MODIFY `bahan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `layanan`
--
ALTER TABLE `layanan`
  MODIFY `layanan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `pembayaran_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `pesanan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`pesanan_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`) ON DELETE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`pesanan_id`) ON DELETE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pesanan_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pesanan_ibfk_3` FOREIGN KEY (`layanan_id`) REFERENCES `layanan` (`layanan_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
