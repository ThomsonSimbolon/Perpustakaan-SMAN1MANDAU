-- File: perpustakaan/db_perpustakaan.sql
-- Skrip SQL untuk Sistem Informasi Perpustakaan SMAN 1 Mandau

-- 1. Buat Database
CREATE DATABASE IF NOT EXISTS perpustakaan_sman1mandau;
USE perpustakaan_sman1mandau;

-- 2. TABEL users
CREATE TABLE users (
    id_user INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Di dunia nyata, gunakan HASH
    role ENUM('admin', 'pustakawan', 'siswa', 'guru') NOT NULL,
    PRIMARY KEY (id_user)
);

-- 3. TABEL anggota
CREATE TABLE anggota (
    id_anggota INT(11) NOT NULL AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    jenis_anggota ENUM('siswa', 'guru') NOT NULL,
    kelas VARCHAR(50) NULL, -- Hanya relevan untuk siswa
    PRIMARY KEY (id_anggota)
);

-- 4. TABEL buku
CREATE TABLE buku (
    kode_buku VARCHAR(20) NOT NULL, -- PK
    judul VARCHAR(255) NOT NULL,
    pengarang VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100) NOT NULL,
    tahun_terbit YEAR NOT NULL,
    stok INT(11) NOT NULL DEFAULT 1, -- Tambahkan stok untuk manajemen inventaris
    status ENUM('TERSEDIA', 'DIPINJAM') NOT NULL DEFAULT 'TERSEDIA',
    PRIMARY KEY (kode_buku)
);

-- 5. TABEL peminjaman
CREATE TABLE peminjaman (
    id_peminjaman INT(11) NOT NULL AUTO_INCREMENT,
    id_anggota INT(11) NOT NULL,
    kode_buku VARCHAR(20) NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali_harus DATE NOT NULL, -- Tambahkan tanggal harus kembali (misal 7 hari)
    qr_code VARCHAR(255) NOT NULL UNIQUE,
    status ENUM('AKTIF', 'SELESAI') NOT NULL DEFAULT 'AKTIF',
    PRIMARY KEY (id_peminjaman),
    FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE,
    FOREIGN KEY (kode_buku) REFERENCES buku(kode_buku) ON DELETE CASCADE
);

-- 6. TABEL pengembalian
CREATE TABLE pengembalian (
    id_pengembalian INT(11) NOT NULL AUTO_INCREMENT,
    id_peminjaman INT(11) NOT NULL UNIQUE, -- Unik karena 1 peminjaman hanya 1 pengembalian
    tanggal_kembali DATE NOT NULL,
    denda DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id_pengembalian),
    FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman) ON DELETE CASCADE
);

-- 7. Data Awal (Seed Data)
-- Password di sini adalah '123456' (plain text untuk kemudahan testing)
INSERT INTO users (username, password, role) VALUES
('admin', '123456', 'admin'),
('pustakawan', '123456', 'pustakawan');

-- Contoh Anggota (Siswa dan Guru)
INSERT INTO anggota (nama, jenis_anggota, kelas) VALUES
('Budi Santoso', 'siswa', 'XII IPA 1'),
('Ani Rahmawati', 'siswa', 'X IPS 2'),
('Pak Herman', 'guru', NULL);

-- Contoh Buku
INSERT INTO buku (kode_buku, judul, pengarang, penerbit, tahun_terbit, stok, status) VALUES
('B001', 'Fisika Dasar Jilid 1', 'Halliday & Resnick', 'Erlangga', 2018, 5, 'TERSEDIA'),
('B002', 'Sejarah Indonesia Modern', 'M.C. Ricklefs', 'Gramedia', 2015, 2, 'TERSEDIA'),
('B003', 'Kumpulan Puisi Senja', 'Chairil Anwar', 'Pustaka', 2020, 1, 'DIPINJAM');
