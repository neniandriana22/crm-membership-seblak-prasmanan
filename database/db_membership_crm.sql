-- =========================================================
-- DATABASE: db_membership_crm
-- Sistem Pendaftaran Membership & Kartu Anggota (CRM)
-- Import file ini melalui phpMyAdmin (Laragon) atau CLI:
--   mysql -u root -p < db_membership_crm.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS db_membership_crm
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE db_membership_crm;

-- ---------------------------------------------------------
-- Tabel admin (pengelola sistem / kasir / staff CRM)
-- ---------------------------------------------------------
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Password default: admin123 (sudah di-hash dengan password_hash BCRYPT)
INSERT INTO admins (username, password, nama_lengkap) VALUES
('admin', '$2y$10$J058JjUjixS5WAQsvFHlJe1UNgQ0Ga38DL0asyQQEaxOI5jDDWxam', 'Administrator');

-- ---------------------------------------------------------
-- Tabel tingkatan membership (tier)
-- ---------------------------------------------------------
CREATE TABLE membership_tiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_tier VARCHAR(50) NOT NULL,
    min_poin INT NOT NULL DEFAULT 0,
    diskon_persen DECIMAL(5,2) NOT NULL DEFAULT 0,
    keterangan VARCHAR(255) DEFAULT NULL,
    urutan INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO membership_tiers (nama_tier, min_poin, diskon_persen, keterangan, urutan) VALUES
('Silver',   0,    0.00, 'Tier awal setiap anggota baru', 1),
('Gold',     500,  5.00, 'Diskon 5% + prioritas info promo', 2),
('Platinum', 1500, 10.00,'Diskon 10% + benefit eksklusif', 3);

-- ---------------------------------------------------------
-- Tabel anggota / member
-- ---------------------------------------------------------
CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_code VARCHAR(30) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    no_hp VARCHAR(20) NOT NULL,
    alamat TEXT,
    tanggal_lahir DATE DEFAULT NULL,
    jenis_kelamin ENUM('L','P') DEFAULT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    qr_code VARCHAR(255) DEFAULT NULL,
    tier_id INT NOT NULL DEFAULT 1,
    total_poin INT NOT NULL DEFAULT 0,
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    tanggal_daftar DATE NOT NULL,
    berlaku_sampai DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tier_id) REFERENCES membership_tiers(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel riwayat poin (setiap kunjungan / transaksi / penukaran)
-- ---------------------------------------------------------
CREATE TABLE points_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    jenis ENUM('earn','redeem') NOT NULL,
    jumlah_poin INT NOT NULL,
    keterangan VARCHAR(255) DEFAULT NULL,
    admin_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabel feedback / masukan pelanggan (inti CRM)
-- ---------------------------------------------------------
CREATE TABLE feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    rating TINYINT NOT NULL,
    komentar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Selesai.
