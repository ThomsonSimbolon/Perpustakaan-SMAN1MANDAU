<?php
// File: perpustakaan/admin/dashboard.php
require_once '../config/database.php';
require_once '../config/functions.php';

check_login('admin');

$db = new Database();
$conn = $db->getConnection();

// Ambil data statistik untuk dashboard
$total_users = get_count($conn, 'users');
$total_anggota = get_count($conn, 'anggota');
$total_buku = get_count($conn, 'buku');

echo get_header("Dashboard Admin", $_SESSION['role']);
?>

<div class="card-container">
    <div class="card">
        <h4>Total Pengguna Sistem</h4>
        <div class="value"><?php echo $total_users; ?></div>
    </div>
    <div class="card">
        <h4>Total Anggota Perpustakaan</h4>
        <div class="value"><?php echo $total_anggota; ?></div>
    </div>
    <div class="card">
        <h4>Total Judul Buku</h4>
        <div class="value"><?php echo $total_buku; ?></div>
    </div>
</div>

<div class="content-area">
    <h2>Selamat Datang, <?php echo $_SESSION['username']; ?>!</h2>
    <p>Ini adalah halaman dashboard untuk Administrator. Anda memiliki hak akses penuh untuk mengelola pengguna sistem.</p>
</div>

<?php
echo get_footer();
?>
