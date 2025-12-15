-- Database SmartNote
-- Dibuat oleh saya sendiri (bukan AI) untuk keperluan manajemen notulen rapat.

CREATE DATABASE IF NOT EXISTS notulen;
USE notulen;

-- 1. Tabel Users
-- Nyatuin Admin sama Peserta disini biar simpel. Bedanya cuma di kolom 'role'.
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nik` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nomor_whatsapp` varchar(20) NULL DEFAULT NULL,
  `foto` varchar(255) NULL DEFAULT NULL,
  `role` enum('admin','peserta') NOT NULL DEFAULT 'peserta',
  `password_updated` tinyint(1) NOT NULL DEFAULT 0, -- Cek udah ganti password bawaan apa belum
  `is_first_login` tinyint(1) NOT NULL DEFAULT 1, -- Buat maksa ganti password pas pertama login
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  UNIQUE KEY `uk_nik` (`nik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Notulen
-- Ini tabel inti buat nyimpen data rapatnya.
CREATE TABLE IF NOT EXISTS `tambah_notulen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL, -- Siapa yang bikin notulen (biasanya admin)
  `judul` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `tempat` varchar(255) NOT NULL,
  `peserta` longtext NOT NULL, -- List peserta string (legacy)
  `hasil` longtext NOT NULL, -- Isi rapatnya
  `tindak_lanjut` longtext NOT NULL, -- Kolom lama buat file, sekarang pake tabel terpisah tapi tetep disimpen buat jaga-jaga
  `status` enum('draft','final','revisi','selesai') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `fk_notulen_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Peserta Notulen
-- Buat nyatet siapa aja yang diundang dan hadir di rapat tertentu.
CREATE TABLE IF NOT EXISTS `peserta_notulen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_notulen` int(11) NOT NULL,
  `id_peserta` int(11) NOT NULL,
  `status_hadir` enum('hadir','tidak_hadir','izin') NOT NULL DEFAULT 'hadir',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_notulen` (`id_notulen`),
  KEY `id_peserta` (`id_peserta`),
  CONSTRAINT `fk_peserta_notulen` FOREIGN KEY (`id_notulen`) REFERENCES `tambah_notulen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_peserta_user` FOREIGN KEY (`id_peserta`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Lampiran
-- Nah ini tabel baru buat nyimpen file-file lampiran biar bisa banyak.
CREATE TABLE IF NOT EXISTS `tb_lampiran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_notulen` int(11) NOT NULL,
  `judul_lampiran` varchar(255) NOT NULL,
  `file_lampiran` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_notulen` (`id_notulen`),
  CONSTRAINT `fk_lampiran_notulen` FOREIGN KEY (`id_notulen`) REFERENCES `tambah_notulen` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Data Default (Admin & Contoh Peserta)
-- Password default: lopolo9090
INSERT IGNORE INTO `users` (`id`, `nama`, `email`, `nik`, `password`, `role`, `password_updated`, `is_first_login`) VALUES 
(1, 'Admin', 'admin@gmail.com', 123456, 'lopolo9090', 'admin', 1, 0),
(2, 'Peserta Satu', 'peserta@gmail.com', 654321, 'lopolo9090', 'peserta', 1, 0);
