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
    <div class="card">
        <h4>Total Judul Buku</h4>
        <div class="value"><?php echo $total_buku; ?></div>
    </div>
    <div class="card">
        <h4>Buku Tersedia</h4>
        <div class="value"><?php echo $buku_tersedia; ?></div>
    </div>
    <div class="card">
        <h4>Buku Dipinjam</h4>
        <div class="value"><?php echo $buku_dipinjam; ?></div>
    </div>
    <div class="card">
        <h4>Peminjaman Aktif</h4>
        <div class="value"><?php echo $peminjaman_aktif; ?></div>
    </div>
</div>

<div class="content-area">
    <h2>Selamat Datang, <?php echo $_SESSION['username']; ?>!</h2>
    <p>Ini adalah halaman dashboard untuk Pustakawan. Anda dapat mengelola data buku, anggota, dan transaksi peminjaman/pengembalian.</p>
</div>

<?php
echo get_footer();
?>
