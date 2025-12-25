<?php
// File: perpustakaan/pustakawan/dashboard.php
require_once '../config/database.php';
require_once '../config/functions.php';

check_login('pustakawan');

$db = new Database();
$conn = $db->getConnection();

// Ambil data statistik untuk dashboard
$total_buku = get_count($conn, 'buku');
$buku_tersedia = get_count($conn, 'buku', "status = 'TERSEDIA'");
$buku_dipinjam = get_count($conn, 'buku', "status = 'DIPINJAM'");
$peminjaman_aktif = get_count($conn, 'peminjaman', "status = 'AKTIF'");

echo get_header("Dashboard Pustakawan", $_SESSION['role']);
?>

<div class="card-container">
    <div class="dashboard-card card-primary">
        <div class="card-icon">
            <i class="fas fa-book"></i>
        </div>
        <div class="card-content">
            <h4>Total Judul Buku</h4>
            <div class="value"><?php echo $total_buku; ?></div>
            <p class="card-description">Total koleksi buku</p>
        </div>
    </div>
    <div class="dashboard-card card-success">
        <div class="card-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="card-content">
            <h4>Buku Tersedia</h4>
            <div class="value"><?php echo $buku_tersedia; ?></div>
            <p class="card-description">Buku yang dapat dipinjam</p>
        </div>
    </div>
    <div class="dashboard-card card-warning">
        <div class="card-icon">
            <i class="fas fa-book-reader"></i>
        </div>
        <div class="card-content">
            <h4>Buku Dipinjam</h4>
            <div class="value"><?php echo $buku_dipinjam; ?></div>
            <p class="card-description">Buku sedang dipinjam</p>
        </div>
    </div>
    <div class="dashboard-card card-info">
        <div class="card-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="card-content">
            <h4>Peminjaman Aktif</h4>
            <div class="value"><?php echo $peminjaman_aktif; ?></div>
            <p class="card-description">Transaksi aktif</p>
        </div>
    </div>
</div>

<div class="welcome-section">
    <div class="welcome-content">
        <h2 class="welcome-title">Selamat Datang, <span
                class="welcome-name"><?php echo $_SESSION['username']; ?>!</span> 👋</h2>
        <p class="welcome-description">Ini adalah halaman dashboard untuk Pustakawan. Anda dapat mengelola data buku,
            anggota, dan transaksi peminjaman/pengembalian.</p>
    </div>
</div>

<?php
echo get_footer();
?>