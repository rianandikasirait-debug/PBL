-- ====================================================================
-- SMARTNOTE NOTULEN - COMPLETE DATABASE SCHEMA v2.0
-- ====================================================================
-- Database lengkap dengan WhatsApp Integration
-- Bisa langsung pakai untuk fresh install atau update existing
-- ====================================================================

CREATE DATABASE IF NOT EXISTS notulen;
USE notulen;

-- ====================================================================
-- TABLE: users (Admin & Peserta)
-- ====================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nik` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nomor_whatsapp` varchar(20) NULL DEFAULT NULL,
  `foto` varchar(255) NULL DEFAULT NULL,
  `role` enum('admin','peserta') NOT NULL DEFAULT 'peserta',
  `password_updated` tinyint(1) NOT NULL DEFAULT 0,
  `is_first_login` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  UNIQUE KEY `uk_nik` (`nik`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- TABLE: tambah_notulen (Meeting Notes)
-- ====================================================================
CREATE TABLE IF NOT EXISTS `tambah_notulen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `tempat` varchar(255) NOT NULL,
  `peserta` longtext NOT NULL,
  `hasil` longtext NOT NULL,
  `tindak_lanjut` longtext NOT NULL,
  `status` enum('draft','final','revisi','selesai') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `status` (`status`),
  CONSTRAINT `fk_notulen_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- TABLE: peserta_notulen (Peserta yang hadir)
-- ====================================================================
CREATE TABLE IF NOT EXISTS `peserta_notulen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_notulen` int(11) NOT NULL,
  `id_peserta` int(11) NOT NULL,
  `status_hadir` enum('hadir','tidak_hadir','izin') NOT NULL DEFAULT 'hadir',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_notulen` (`id_notulen`),
  KEY `id_peserta` (`id_peserta`),
  CONSTRAINT `fk_peserta_notulen_notulen` FOREIGN KEY (`id_notulen`) REFERENCES `tambah_notulen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_peserta_notulen_user` FOREIGN KEY (`id_peserta`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- TABLE: log_whatsapp (WhatsApp Message Logging)
-- ====================================================================
CREATE TABLE IF NOT EXISTS `log_whatsapp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nomor_whatsapp` varchar(20) NOT NULL,
  `pesan` longtext NOT NULL,
  `status` enum('sent','pending','failed') NOT NULL DEFAULT 'pending',
  `error_message` longtext NULL DEFAULT NULL,
  `wa_link` varchar(255) NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `fk_log_whatsapp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- TABLE: tambah_pengguna (Legacy - Backup table)
-- ====================================================================
CREATE TABLE IF NOT EXISTS `tambah_pengguna` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nik` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- INSERT DATA DEFAULT: ADMIN & PESERTA
-- ====================================================================
-- Password: lopolo9090
-- Catatan: Password tersimpan PLAIN TEXT, akan otomatis di-hash saat login pertama

INSERT IGNORE INTO `users` 
(`id`, `nama`, `email`, `nik`, `password`, `role`, `password_updated`, `is_first_login`) 
VALUES 
(1, 'Admin', 'admin@gmail.com', 123456, 'lopolo9090', 'admin', 1, 0),
(2, 'Peserta Satu', 'peserta@gmail.com', 654321, 'lopolo9090', 'peserta', 1, 0);

-- ====================================================================
-- NOTES:
-- ====================================================================
-- 1. PASSWORD DEFAULT untuk peserta baru = NIK mereka
--    Admin input NIK, password otomatis = NIK
--    Peserta HARUS ubah password saat login pertama
--
-- 2. nomor_whatsapp: Format 62xxxxxxxxxx atau 08xxxxxxxxxx
--    Akan di-normalize menjadi 62xxxxxxxxxx di backend
--
-- 3. log_whatsapp: Tracking setiap pengiriman WhatsApp
--    Status: sent (berhasil), pending (antri), failed (gagal)
--
-- 4. password_updated: Flag untuk tahu apakah sudah ganti password
--    0 = belum ganti, 1 = sudah ganti
--
-- 5. is_first_login: Flag untuk force ubah password
--    0 = bukan login pertama, 1 = login pertama (FORCE ubah password)
--
-- 6. READY TO USE: Database ini bisa langsung dipakai untuk:
--    - Fresh install (buat database baru)
--    - Update existing (jika kolom belum ada)
--
-- ====================================================================
