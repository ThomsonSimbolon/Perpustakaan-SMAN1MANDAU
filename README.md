# Sistem Informasi Perpustakaan SMAN 1 Mandau (PHP Native)

Sistem Informasi Perpustakaan berbasis web yang dikembangkan menggunakan PHP Native (tanpa framework), MySQL, CSS, dan JavaScript. Sistem ini menggantikan proses manual perpustakaan menjadi terkomputerisasi, terstruktur, minim human error, dan user friendly.

## 📋 Daftar Isi

1. [Fitur Utama](#fitur-utama)
2. [Struktur Proyek](#struktur-proyek)
3. [Instalasi](#instalasi)
4. [Flow Bisnis Sistem](#flow-bisnis-sistem)
5. [Role & Hak Akses](#role--hak-akses)
6. [Panduan Pengujian](#panduan-pengujian)
7. [UI/UX Design & Color Scheme](#uiux-design--color-scheme)
8. [Troubleshooting](#troubleshooting)

---

## 🎯 Fitur Utama

1. **Multi-Role Access:** Admin, Pustakawan, Siswa, dan Guru dengan hak akses berbeda
2. **Manajemen Data:** Kelola Pengguna, Anggota, dan Buku
3. **Transaksi Peminjaman:** Mencatat peminjaman dan **Generate QR Code** untuk setiap transaksi
4. **Transaksi Pengembalian:** Memproses pengembalian dengan **Scan QR Code (Kamera)** atau Input Manual dan menghitung denda otomatis
5. **Katalog Online:** Siswa/Guru dapat melihat katalog, mencari buku, dan mengecek ketersediaan
6. **Laporan:** Laporan Peminjaman dan Pengembalian yang dapat dicetak

---

## 📁 Struktur Proyek

```
/perpustakaan
|
|-- config/
|   |-- database.php      (Konfigurasi koneksi DB & fungsi helper)
|   |-- functions.php     (Fungsi umum: sanitize, generate QR Code, denda)
|
|-- auth/
|   |-- login.php         (Halaman Login - semua role wajib login)
|   |-- logout.php        (Logout & destroy session)
|
|-- admin/
|   |-- dashboard.php     (Dashboard Admin dengan statistik)
|   |-- users.php         (CRUD Kelola User)
|
|-- pustakawan/
|   |-- dashboard.php     (Dashboard Pustakawan dengan statistik)
|   |-- buku.php          (CRUD Kelola Data Buku)
|   |-- anggota.php       (CRUD Kelola Data Anggota)
|   |-- peminjaman.php    (Transaksi Peminjaman & Generate QR Code)
|   |-- pengembalian.php  (Transaksi Pengembalian & Scan QR Code)
|   |-- laporan.php       (Laporan Peminjaman & Pengembalian + Cetak)
|
|-- katalog/
|   |-- index.php         (Katalog Buku untuk Siswa/Guru + Pencarian)
|
|-- assets/
|   |-- css/
|   |   |-- style.css     (Styling UI/UX: modern, sidebar, card, modal)
|   |-- js/
|   |   |-- script.js     (Interaksi Modal & UI)
|   |-- img/
|   |   |-- qr_codes/     (Folder penyimpanan QR Code yang di-generate)
|
|-- vendor/
|   |-- qrlib.php         (Library QR Code - entry point)
|   |-- qr*.php           (13 file library QR Code)
|   |-- cache/            (Cache folder untuk QR Code)
|
|-- index.php             (Redirect ke Login)
|-- db_perpustakaan.sql   (Skrip Database & Data Awal)
|-- README.md             (Dokumentasi ini)
```

---

## 🚀 Instalasi

### 1. Persiapan Lingkungan

**Persyaratan Sistem:**

- Web Server: Apache/Nginx
- PHP: 7.4+ (disarankan PHP 8.1+)
- MySQL/MariaDB: 5.7+
- Ekstensi PHP yang dibutuhkan:
  - `mysqli` (untuk koneksi database)
  - `gd` (untuk generate QR Code image)

**Cek Ekstensi PHP:**

```bash
php -m | grep -i mysqli
php -m | grep -i gd
```

### 2. Konfigurasi Database

**A. Buat Database:**

```sql
CREATE DATABASE perpustakaan_sman1mandau;
```

**B. Impor Skrip Database:**
Impor file `db_perpustakaan.sql` ke database yang sudah dibuat.

**C. Konfigurasi Koneksi:**
Edit file `config/database.php` dan sesuaikan konfigurasi:

```php
private $host = "localhost";
private $db_name = "perpustakaan_sman1mandau";
private $username = "root";      // Sesuaikan
private $password = "";          // Sesuaikan
```

### 3. Library QR Code

**Status:** Library PHP QR Code sudah terinstall di folder `vendor/`

Library yang digunakan: **PHP QR Code** (by Dominik Dzienia)

- File utama: `vendor/qrlib.php`
- Total 13 file library (qr\*.php)
- Folder cache: `vendor/cache/` (untuk optimize performance)

**Catatan:** Jika library belum terinstall, download dari:

- GitHub: https://github.com/t0k4rt/phpqrcode
- Copy semua file qr\*.php ke folder `vendor/`

### 4. Permission Folder

Pastikan folder berikut memiliki permission write:

- `assets/img/qr_codes/` (untuk menyimpan QR Code yang di-generate)
- `vendor/cache/` (untuk cache QR Code)

```bash
chmod 777 assets/img/qr_codes/
chmod 777 vendor/cache/
```

### 5. Akses Aplikasi

Akses aplikasi melalui browser:

```
http://localhost/perpustakaan/
```

Atau sesuaikan dengan path Anda:

```
http://localhost/perpustakaan_sman1mandau/perpustakaan/
```

---

## 🔐 Role & Hak Akses

### 1. ADMIN

**Akses:**

- ✅ Login
- ✅ Dashboard Admin (statistik)
- ✅ Kelola User (CRUD: Tambah, Edit, Hapus)
- ✅ Atur Role dan Hak Akses
- ❌ Tidak melakukan transaksi buku

**Menu:**

- Dashboard
- Kelola User
- Logout

### 2. PUSTAKAWAN

**Akses:**

- ✅ Login
- ✅ Dashboard Pustakawan (statistik)
- ✅ Kelola Data Buku (CRUD)
- ✅ Kelola Data Anggota (CRUD)
- ✅ Transaksi Peminjaman (dengan Generate QR Code)
- ✅ Transaksi Pengembalian (dengan Scan QR Code)
- ✅ Laporan Peminjaman & Pengembalian (dapat dicetak)

**Menu:**

- Dashboard
- Data Buku
- Data Anggota
- Peminjaman
- Pengembalian
- Laporan
- Logout

### 3. SISWA

**Akses:**

- ✅ Login
- ✅ Katalog Buku
- ✅ Cari Buku (judul/pengarang)
- ✅ Cek Ketersediaan Buku (status TERSEDIA/DIPINJAM)
- ❌ Tidak boleh melakukan transaksi

**Menu:**

- Katalog
- Logout

### 4. GURU

**Akses:**

- ✅ Login
- ✅ Katalog Buku
- ✅ Cari Buku (judul/pengarang)
- ✅ Cek Ketersediaan Buku (status TERSEDIA/DIPINJAM)
- ❌ Tidak boleh melakukan transaksi

**Menu:**

- Katalog
- Logout

---

## 📊 Flow Bisnis Sistem

### Flow 1: Login

```
1. User mengakses halaman login
2. Input username dan password
3. Sistem validasi username & password
4. Sistem cek role user
5. Redirect ke dashboard sesuai role:
   - Admin → /admin/dashboard.php
   - Pustakawan → /pustakawan/dashboard.php
   - Siswa/Guru → /katalog/index.php
```

### Flow 2: Manajemen Buku (Pustakawan)

```
1. Pustakawan login
2. Pilih menu "Data Buku"
3. Tambah/Edit/Hapus buku:
   - Input: Kode Buku (unik), Judul, Pengarang, Penerbit, Tahun Terbit, Status
   - Validasi: Kode buku harus unik
   - Status hanya: TERSEDIA atau DIPINJAM
4. Sistem menyimpan data buku
```

### Flow 3: Manajemen Anggota (Pustakawan)

```
1. Pustakawan login
2. Pilih menu "Data Anggota"
3. Tambah/Edit/Hapus anggota:
   - Input: Nama, Jenis Anggota (Siswa/Guru), Kelas (jika Siswa)
   - Validasi: Kelas hanya untuk Siswa
4. Sistem menyimpan data anggota
```

### Flow 4: Peminjaman Buku (Generate QR Code)

```
1. Pustakawan login
2. Pilih menu "Peminjaman"
3. Klik "Peminjaman Baru"
4. Input data:
   - Pilih Anggota (dropdown)
   - Pilih Buku dengan status TERSEDIA (dropdown)
5. Sistem validasi:
   - ✅ Anggota valid
   - ✅ Buku status = TERSEDIA
6. Sistem menyimpan data peminjaman:
   - Tanggal pinjam = hari ini
   - Tanggal kembali harus = hari ini + 7 hari
   - Status = AKTIF
7. Sistem generate QR Code berisi:
   - Format: ID_PINJAM:{id}|ID_ANGGOTA:{id}|KODE_BUKU:{kode}
   - Simpan sebagai gambar PNG di assets/img/qr_codes/
8. Sistem update status buku menjadi DIPINJAM
9. QR Code ditampilkan di UI dan dapat dicetak
10. Transaksi peminjaman selesai
```

### Flow 5: Pengembalian Buku (Scan QR Code)

```
1. Pustakawan login
2. Pilih menu "Pengembalian"
3. Scan QR Code atau Input Manual:

   A. Scan dengan Kamera:
      - Klik tab "Scan dengan Kamera"
      - Sistem akses kamera device
      - Scan QR Code dari transaksi peminjaman
      - Sistem membaca data QR Code

   B. Input Manual:
      - Klik tab "Input Manual"
      - Input data QR Code secara manual
      - Format: ID_PINJAM:x|ID_ANGGOTA:y|KODE_BUKU:z
4. Sistem membaca data QR Code
5. Sistem validasi:
   - ✅ Transaksi peminjaman masih AKTIF
   - ✅ Data QR Code valid
6. Sistem menampilkan detail peminjaman:
   - ID Peminjaman
   - Data Anggota
   - Data Buku
   - Tanggal Pinjam
   - Tanggal Harus Kembali
   - Potensi Denda (jika terlambat)
7. Pustakawan klik "Selesaikan Pengembalian"
8. Sistem update:
   - Tanggal kembali = hari ini
   - Status peminjaman = SELESAI
   - Status buku = TERSEDIA
   - Hitung denda jika terlambat (Rp 1000/hari)
9. Sistem menyimpan data pengembalian
10. Transaksi pengembalian selesai
```

### Flow 6: Katalog Buku (Siswa/Guru)

```
1. Siswa/Guru login
2. Sistem redirect ke /katalog/index.php
3. Lihat katalog buku:
   - Daftar buku dalam format card grid
   - Informasi: Judul, Pengarang, Penerbit, Tahun, Status
   - Status badge: Hijau (TERSEDIA) / Merah (DIPINJAM)
4. Pencarian buku:
   - Input keyword
   - Pilih: Cari berdasarkan Judul atau Pengarang
   - Klik "Cari"
5. Sistem menampilkan hasil pencarian
6. Lihat statistik:
   - Total buku
   - Buku tersedia
   - Buku dipinjam
```

### Flow 7: Laporan (Pustakawan)

```
1. Pustakawan login
2. Pilih menu "Laporan"
3. Filter (opsional):
   - Filter berdasarkan tanggal peminjaman
   - Filter berdasarkan tanggal pengembalian
4. Sistem menampilkan:

   A. Laporan Peminjaman:
      - ID Peminjaman
      - Tanggal Pinjam
      - Harus Kembali
      - Data Anggota
      - Data Buku
      - Status (AKTIF/SELESAI)

   B. Laporan Pengembalian:
      - ID Pengembalian
      - ID Peminjaman
      - Tanggal Kembali
      - Data Anggota
      - Data Buku
      - Denda
      - Total denda
5. Klik "Cetak Laporan" untuk print
```

---

## 🧪 Panduan Pengujian (Black Box Testing)

### Skenario 1: Login dan Hak Akses

1. **Test Login Admin:**

   - Username: `admin`, Password: `123456`
   - Verifikasi: Hanya bisa akses menu Admin (Dashboard, Kelola User)
   - Verifikasi: Tidak bisa akses menu Pustakawan

2. **Test Login Pustakawan:**

   - Username: `pustakawan`, Password: `123456`
   - Verifikasi: Bisa akses semua menu Pustakawan
   - Verifikasi: Tidak bisa akses menu Admin

3. **Test Login Siswa/Guru:**

   - Buat user dengan role `siswa` atau `guru` (via Admin)
   - Login dengan user tersebut
   - Verifikasi: Redirect ke halaman Katalog
   - Verifikasi: Tidak bisa akses menu Admin/Pustakawan

4. **Test Access Control:**

   - Login sebagai pustakawan
   - Coba akses `/admin/users.php` langsung via URL
   - Verifikasi: Sistem redirect ke Dashboard Pustakawan

5. **Test Login Requirement:**
   - Akses `/katalog/index.php` tanpa login
   - Verifikasi: Sistem redirect ke halaman login

### Skenario 2: Manajemen Data Buku

1. **Test Tambah Buku:**

   - Login sebagai pustakawan
   - Menu: Data Buku → Tambah Buku
   - Input: Kode: B004, Judul: "Buku Test", Pengarang: "Author", Penerbit: "Publisher", Tahun: 2024
   - Verifikasi: Buku berhasil ditambahkan
   - Verifikasi: Status default = TERSEDIA

2. **Test Validasi Kode Buku Unik:**

   - Coba tambah buku dengan kode yang sudah ada
   - Verifikasi: Error message muncul "Kode buku sudah digunakan"

3. **Test Edit Buku:**

   - Klik Edit pada buku yang sudah ada
   - Ubah data buku
   - Verifikasi: Data berhasil diupdate

4. **Test Hapus Buku:**
   - Coba hapus buku yang statusnya DIPINJAM
   - Verifikasi: Error "Buku tidak dapat dihapus karena sedang dipinjam"
   - Hapus buku yang statusnya TERSEDIA
   - Verifikasi: Buku berhasil dihapus

### Skenario 3: Flow Peminjaman (Generate QR Code)

1. **Test Peminjaman Valid:**

   - Login sebagai pustakawan
   - Menu: Peminjaman → Peminjaman Baru
   - Pilih Anggota (misal: Budi Santoso)
   - Pilih Buku dengan status TERSEDIA (misal: Fisika Dasar Jilid 1)
   - Klik "Proses Peminjaman"
   - Verifikasi: Pesan sukses muncul
   - Verifikasi: QR Code ditampilkan di layar
   - Verifikasi: Status buku di database berubah menjadi DIPINJAM
   - Verifikasi: File QR Code tersimpan di `assets/img/qr_codes/`

2. **Test Validasi Buku Tersedia:**

   - Coba pinjam buku yang statusnya DIPINJAM
   - Verifikasi: Error "Buku tidak tersedia untuk dipinjam"

3. **Test QR Code Content:**

   - Verifikasi: QR Code berisi format: `ID_PINJAM:x|ID_ANGGOTA:y|KODE_BUKU:z`
   - Scan QR Code (atau lihat teks) untuk memastikan format benar

4. **Test Cetak QR Code:**
   - Klik tombol "Cetak QR Code"
   - Verifikasi: Halaman siap untuk print

### Skenario 4: Flow Pengembalian (Scan QR Code)

1. **Test Pengembalian via Input Manual:**

   - Login sebagai pustakawan
   - Menu: Pengembalian
   - Tab: Input Manual
   - Input data QR Code dari transaksi peminjaman (format: `ID_PINJAM:x|ID_ANGGOTA:y|KODE_BUKU:z`)
   - Klik "Cari Transaksi"
   - Verifikasi: Detail peminjaman muncul
   - Verifikasi: Potensi denda terhitung jika terlambat
   - Klik "Selesaikan Pengembalian"
   - Verifikasi: Pesan sukses muncul
   - Verifikasi: Status peminjaman = SELESAI
   - Verifikasi: Status buku = TERSEDIA
   - Verifikasi: Data pengembalian tersimpan

2. **Test Pengembalian via Camera Scan:**

   - Tab: Scan dengan Kamera
   - Verifikasi: Kamera aktif (jika browser support)
   - Scan QR Code dari transaksi
   - Verifikasi: Sistem membaca QR Code dan redirect ke detail
   - (Note: Camera scan memerlukan HTTPS atau localhost)

3. **Test Validasi Transaksi Aktif:**

   - Coba scan/input QR Code dari transaksi yang sudah SELESAI
   - Verifikasi: Error "Transaksi tidak aktif atau sudah selesai"

4. **Test Perhitungan Denda:**
   - Buat peminjaman dengan tanggal harus kembali = 7 hari yang lalu
   - Proses pengembalian hari ini
   - Verifikasi: Denda = Rp 7.000 (7 hari × Rp 1.000/hari)

### Skenario 5: Katalog dan Pencarian

1. **Test Lihat Katalog:**

   - Login sebagai siswa/guru
   - Verifikasi: Katalog buku ditampilkan
   - Verifikasi: Status buku ditampilkan dengan badge (Hijau/Merah)
   - Verifikasi: Statistik ditampilkan (Total, Tersedia, Dipinjam)

2. **Test Pencarian Buku:**

   - Input keyword di form pencarian
   - Pilih: Cari berdasarkan Judul
   - Klik "Cari"
   - Verifikasi: Hasil pencarian sesuai keyword
   - Test juga pencarian berdasarkan Pengarang

3. **Test Filter Status:**
   - Verifikasi: Buku dengan status TERSEDIA ditampilkan dengan badge hijau
   - Verifikasi: Buku dengan status DIPINJAM ditampilkan dengan badge merah

### Skenario 6: Laporan

1. **Test Laporan Peminjaman:**

   - Login sebagai pustakawan
   - Menu: Laporan
   - Verifikasi: Semua data peminjaman ditampilkan
   - Test filter tanggal (opsional)
   - Klik "Cetak Laporan"
   - Verifikasi: Halaman siap untuk print

2. **Test Laporan Pengembalian:**
   - Verifikasi: Semua data pengembalian ditampilkan
   - Verifikasi: Total denda terhitung
   - Test filter tanggal (opsional)
   - Klik "Cetak Laporan"
   - Verifikasi: Halaman siap untuk print

---

## 🔧 Akun Uji Coba (Default)

| Username     | Password | Role       | Halaman Awal                |
| :----------- | :------- | :--------- | :-------------------------- |
| `admin`      | `123456` | Admin      | `/admin/dashboard.php`      |
| `pustakawan` | `123456` | Pustakawan | `/pustakawan/dashboard.php` |

**Catatan:** Untuk mengakses katalog sebagai Siswa/Guru, buat user baru dengan role `siswa` atau `guru` melalui menu "Kelola User" (Admin). Semua role wajib login sesuai requirements.

---

## ⚠️ Troubleshooting

### 1. QR Code tidak tampil di halaman peminjaman

**Penyebab:**

- File QR Code tidak ter-generate
- Path file salah
- Permission folder tidak cukup

**Solusi:**

```bash
# Cek apakah folder ada dan writable
ls -la assets/img/qr_codes/
chmod 777 assets/img/qr_codes/

# Cek file QR Code yang ter-generate
ls -la assets/img/qr_codes/*.png

# Cek error log PHP
tail -f /var/log/apache2/error.log  # Linux
# atau cek error log di XAMPP
```

### 2. Error: "Call to undefined function QRcode::png()"

**Penyebab:**

- Library QR Code belum terinstall lengkap
- File qrlib.php atau file dependensi tidak ditemukan

**Solusi:**

```bash
# Cek file library QR Code
ls -la vendor/qr*.php

# Pastikan semua file ada (13 file)
# File yang harus ada:
# - qrlib.php
# - qrconst.php
# - qrconfig.php
# - qrtools.php
# - qrspec.php
# - qrimage.php
# - qrvect.php
# - qrinput.php
# - qrbitstream.php
# - qrsplit.php
# - qrrscode.php
# - qrmask.php
# - qrencode.php
```

### 3. Camera QR Scan tidak berfungsi

**Penyebab:**

- Browser tidak support getUserMedia API
- Situs tidak diakses via HTTPS (kecuali localhost)
- Permission kamera tidak diberikan

**Solusi:**

- Gunakan browser modern (Chrome, Firefox, Edge)
- Akses via `localhost` atau `127.0.0.1`
- Berikan permission kamera saat browser meminta
- Gunakan input manual sebagai alternatif

### 4. Error koneksi database

**Penyebab:**

- Konfigurasi database salah
- Database belum dibuat
- MySQL service tidak berjalan

**Solusi:**

```bash
# Cek MySQL service
# Windows (XAMPP): Start MySQL di XAMPP Control Panel
# Linux: sudo systemctl status mysql

# Cek konfigurasi di config/database.php
# Pastikan username, password, dan database name benar
```

### 5. Redirect loop di login

**Penyebab:**

- Session tidak berfungsi
- Path redirect salah

**Solusi:**

```bash
# Cek session save path
# Pastikan folder session writable
# Cek php.ini untuk session settings
```

### 6. Permission denied saat save QR Code

**Penyebab:**

- Folder `assets/img/qr_codes/` tidak writable
- User web server tidak memiliki permission

**Solusi:**

```bash
# Linux/Mac:
chmod 777 assets/img/qr_codes/
chown www-data:www-data assets/img/qr_codes/  # Sesuaikan user web server

# Windows:
# Klik kanan folder → Properties → Security → Edit permissions
```

---

## 🎨 UI/UX Design & Color Scheme

### Color Palette

Sistem menggunakan skema warna yang konsisten dengan nuansa pendidikan (biru/navy) sesuai requirements:

#### Primary Colors

- **Primary Color (Navy Blue):** `#003366`

  - Digunakan untuk: Sidebar background, header katalog, judul card, table header, teks utama
  - Nuansa pendidikan yang profesional

- **Secondary Color (Bright Blue):** `#007bff`
  - Digunakan untuk: Button primary, link, nilai statistik di card dashboard
  - Memberikan kontras yang baik dengan primary color

#### Status Colors

- **Success Color (Hijau):** `#28a745`

  - Digunakan untuk: Status badge "TERSEDIA", button success, indikator positif

- **Danger Color (Merah):** `#dc3545`
  - Digunakan untuk: Status badge "DIPINJAM", button danger/delete, error message

#### Background Colors

- **Background Color:** `#f4f7f6`

  - Warna background utama halaman (light gray)

- **Card Background:** `#ffffff`

  - Background untuk card, modal, table (white)

- **Text Color:** `#333333`

  - Warna teks utama (dark gray)

- **Border Color:** `#dddddd`
  - Warna border untuk input, table, separator (light gray)

#### Additional Colors

- **Gray Text:** `#6c757d`

  - Untuk teks sekunder, placeholder, subtitle

- **Button Hover:** `#0056b3`

  - Warna saat hover pada button primary

- **Login Gradient:** `linear-gradient(135deg, #003366, #0056b3)`

  - Gradient background untuk halaman login

- **Modal Overlay:** `rgba(0, 0, 0, 0.4)`

  - Background semi-transparan untuk modal overlay

- **Sidebar Hover:** `rgba(255, 255, 255, 0.1)`

  - Background saat hover pada menu sidebar

- **Error Background:** `#f8d7da`

  - Background untuk error message box

- **Error Border:** `#f5c6cb`
  - Border untuk error message box

### UI Components & Colors

#### Sidebar

- **Background:** Primary Color (`#003366`)
- **Text:** White (`#ffffff`)
- **Menu Hover/Active:** White dengan opacity 10% (`rgba(255, 255, 255, 0.1)`)
- **Border:** White dengan opacity 10% (`rgba(255, 255, 255, 0.1)`)

#### Buttons

- **Button Primary:** Secondary Color (`#007bff`) dengan text white
- **Button Success:** Success Color (`#28a745`) dengan text white
- **Button Danger:** Danger Color (`#dc3545`) dengan text white
- **Button Hover:** Opacity 80% atau warna lebih gelap

#### Status Badge

- **Status TERSEDIA:** Success Color (`#28a745`) dengan text white
- **Status DIPINJAM:** Danger Color (`#dc3545`) dengan text white

#### Table

- **Header Background:** Primary Color (`#003366`)
- **Header Text:** White (`#ffffff`)
- **Row Hover:** Light gray (`#f9f9f9`)
- **Border:** Border Color (`#dddddd`)

#### Card & Modal

- **Background:** Card Background (`#ffffff`)
- **Shadow:** Subtle shadow untuk depth (`rgba(0, 0, 0, 0.05)`)
- **Border Radius:** 8px untuk rounded corners

#### Form Input

- **Border:** Border Color (`#dddddd`)
- **Focus:** Secondary Color (`#007bff`)
- **Background:** White (`#ffffff`)

#### Login Page

- **Background:** Gradient (`linear-gradient(135deg, #003366, #0056b3)`)
- **Card Background:** White (`#ffffff`)
- **Title:** Primary Color (`#003366`)

### Typography

- **Font Family:** 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
- **Font Size Base:** 16px
- **Line Height:** 1.6
- **Heading Colors:** Primary Color untuk h1-h4

### Layout & Spacing

- **Sidebar Width:** 250px
- **Card Padding:** 20px
- **Button Padding:** 8px 15px (small), 12px (medium)
- **Border Radius:** 5px (button, input), 8px (card, modal)
- **Gap/Spacing:** 10px, 15px, 20px, 30px (bervariasi sesuai konteks)

### Icons

Sistem menggunakan **Font Awesome 5** untuk icons:

- Home, Users, Book, User Plus, Arrow Up/Down, File Text, Log Out, Search, Plus, Edit, Trash, Print, QR Code, Camera

---

## 📝 Catatan Penting

1. **Password Security:** Password saat ini disimpan dalam plain text untuk kemudahan testing. Untuk production, gunakan password hashing (password_hash/password_verify).

2. **QR Code Format:** Format QR Code adalah `ID_PINJAM:{id}|ID_ANGGOTA:{id}|KODE_BUKU:{kode}`. Format ini digunakan untuk scanning saat pengembalian.

3. **Denda:** Denda dihitung otomatis jika pengembalian terlambat. Besar denda: Rp 1.000 per hari. Durasi pinjam default: 7 hari.

4. **Status Buku:** Status buku hanya 2: `TERSEDIA` atau `DIPINJAM`. Status otomatis berubah saat peminjaman/pengembalian.

5. **Login Wajib:** Semua role wajib login. Tidak ada akses tanpa login (kecuali halaman login itu sendiri).

---

## 📚 Teknologi yang Digunakan

- **Backend:** PHP Native (tanpa framework)
- **Database:** MySQL/MariaDB
- **Frontend:** HTML, CSS, JavaScript (Vanilla)
- **Library QR Code:** PHP QR Code (LGPL License)
- **UI Framework:** Custom CSS dengan Font Awesome Icons

---

## 📄 Lisensi

Proyek ini dikembangkan untuk Sistem Informasi Perpustakaan SMAN 1 Mandau.

Library PHP QR Code menggunakan lisensi LGPL 3.0.

---

## 👨‍💻 Developer Notes

- Database menggunakan foreign key dengan ON DELETE CASCADE
- Semua input user di-sanitize untuk mencegah XSS
- Prepared statement digunakan untuk mencegah SQL Injection
- Session management untuk authentication
- QR Code disimpan sebagai file PNG untuk performa

---

**Versi Dokumen:** 1.0  
**Terakhir Diupdate:** Desember 2025
