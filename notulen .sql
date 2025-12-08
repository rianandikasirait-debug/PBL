-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 19, 2025 at 04:35 AM
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
-- Database: `notulen`
--

-- --------------------------------------------------------

--
-- Table structure for table `tambah_notulen`
--

CREATE TABLE `tambah_notulen` (
  `judul_raoat` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_rapat` date NOT NULL,
  `isi_rapat` text COLLATE utf8mb4_general_ci NOT NULL,
  `Lampiran` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `peserta` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tambah_pengguna`
--

CREATE TABLE `tambah_pengguna` (
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','peserta') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'peserta'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'user.jpg',
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nik` int NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','peserta') COLLATE utf8mb4_general_ci DEFAULT 'peserta',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `foto`, `nama`, `email`, `nik`, `password`, `role`, `created_at`) VALUES
(1, 'user.jpg', 'admin', 'admin@gmail.com', 123456, '$2y$10$hOd/rJAASQBcZdrzURsCZODO4XvM7jXq61E2RT3VX9EIj73kjvJl2', 'admin', '2025-11-17 08:23:59'),
(15, 'user.jpg', 'yohana', 'yohana@gmail.com', 213456, '$2y$10$SoNQ7cQX1MdohIjWs3f6nOZPGja9Ew7bixQ6HhvNC/bgRqHDUSbC6', 'peserta', '2025-11-17 08:27:36'),
(17, 'user.jpg', 'rian', 'rian@gmail.com', 908765, '$2y$10$XV5rqbg2wbZtx4empI3PQ..DzPCjdN264396USI320l4lAPuNh8h.', 'peserta', '2025-11-19 04:17:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`nik`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
