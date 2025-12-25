-- File: perpustakaan/assets/database/alter_anggota.sql
-- Script SQL untuk menambahkan kolom data diri ke tabel anggota
-- Script ini memperluas tabel anggota dengan data diri lengkap untuk siswa dan guru

USE perpustakaan_sman1mandau;

-- 1. Tambahkan kolom id_user (NULLABLE untuk backward compatibility)
-- Kolom ini akan di-link dengan tabel users setelah migrasi
ALTER TABLE anggota 
ADD COLUMN id_user INT(11) NULL,
ADD INDEX idx_id_user (id_user);

-- 2. Tambahkan kolom nama_lengkap (copy data dari nama nanti)
ALTER TABLE anggota 
ADD COLUMN nama_lengkap VARCHAR(100) NULL;

-- Copy data dari nama ke nama_lengkap untuk data existing
UPDATE anggota SET nama_lengkap = nama WHERE nama_lengkap IS NULL;

-- 3. Tambahkan kolom data diri siswa (NIS, Jurusan)
ALTER TABLE anggota 
ADD COLUMN nis VARCHAR(30) NULL UNIQUE,
ADD COLUMN jurusan VARCHAR(50) NULL;

-- 4. Tambahkan kolom data diri guru (NIP, Mata Pelajaran)
ALTER TABLE anggota 
ADD COLUMN nip VARCHAR(30) NULL UNIQUE,
ADD COLUMN mata_pelajaran VARCHAR(100) NULL;

-- 5. Tambahkan kolom data umum
ALTER TABLE anggota 
ADD COLUMN jenis_kelamin ENUM('L','P') NULL,
ADD COLUMN no_hp VARCHAR(20) NULL,
ADD COLUMN email VARCHAR(100) NULL,
ADD COLUMN status_aktif ENUM('AKTIF','NONAKTIF') NOT NULL DEFAULT 'AKTIF';

-- 6. Tambahkan foreign key constraint untuk id_user (setelah migrasi data selesai)
-- Uncomment baris di bawah setelah semua data existing sudah di-link dengan user
-- ALTER TABLE anggota 
-- ADD CONSTRAINT fk_anggota_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE;

-- CATATAN PENTING:
-- 1. Kolom 'nama' tetap ada untuk backward compatibility selama masa transisi
-- 2. Setelah semua data sudah dipindah ke nama_lengkap dan sudah yakin tidak ada masalah,
--    kolom 'nama' bisa di-drop dengan perintah:
--    ALTER TABLE anggota DROP COLUMN nama;
-- 3. Foreign key constraint untuk id_user bisa di-enable setelah migrasi data selesai
-- 4. Semua kolom baru dibuat NULLABLE agar data existing tetap berfungsi
