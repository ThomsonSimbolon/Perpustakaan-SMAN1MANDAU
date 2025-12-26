<?php
// File: perpustakaan/katalog/index.php
require_once '../config/database.php';
require_once '../config/functions.php';

// Semua role wajib login (sesuai requirements)
// Katalog hanya bisa diakses oleh siswa dan guru
check_login_katalog();

$user_logged_in = true; // Setelah check_login_katalog, pasti sudah login

$db = new Database();
$conn = $db->getConnection();

// Pencarian buku
$search_query = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$search_type = isset($_GET['search_type']) ? sanitize_input($_GET['search_type']) : 'judul';

// Query buku
if (!empty($search_query)) {
    if ($search_type == 'judul') {
        $sql = "SELECT * FROM buku WHERE judul LIKE ? ORDER BY judul ASC";
        $search_param = "%{$search_query}%";
    } else {
        $sql = "SELECT * FROM buku WHERE pengarang LIKE ? ORDER BY judul ASC";
        $search_param = "%{$search_query}%";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM buku ORDER BY judul ASC";
    $result = $conn->query($sql);
}
$buku_list = $result->fetch_all(MYSQLI_ASSOC);

echo get_katalog_header("Katalog Buku");
?>
<link rel="stylesheet" href="../assets/css/katalog.css">

<div class="welcome-card">
    <div class="welcome-card-left">
        <div class="welcome-icon">
            <i class="fas fa-user"></i>
        </div>
        <div class="welcome-text">
            <strong>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</strong>
            <span><?php echo ucfirst($_SESSION['role']); ?></span>
        </div>
    </div>
    <div class="welcome-card-right">
        <a href="../auth/logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<div class="search-section">
    <h2>Cari Buku</h2>
    <form method="GET" action="index.php" class="search-form">
        <div class="form-group">
            <label for="search">
                <i class="fas fa-search" style="margin-right: 8px;"></i>
                Kata Kunci
            </label>
            <input type="text" name="search" id="search" placeholder="Masukkan judul atau pengarang buku..."
                value="<?php echo htmlspecialchars($search_query); ?>">
        </div>
        <div class="form-group">
            <label for="search_type">
                <i class="fas fa-filter" style="margin-right: 8px;"></i>
                Cari Berdasarkan
            </label>
            <select name="search_type" id="search_type">
                <option value="judul" <?php echo $search_type == 'judul' ? 'selected' : ''; ?>>Judul</option>
                <option value="pengarang" <?php echo $search_type == 'pengarang' ? 'selected' : ''; ?>>Pengarang
                </option>
            </select>
        </div>
        <div class="search-buttons">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Cari
            </button>
            <?php if (!empty($search_query)): ?>
            <a href="index.php" class="btn btn-secondary" style="text-decoration: none;">
                <i class="fas fa-redo"></i> Reset
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php
// Hitung statistik
$total_buku = count($buku_list);
$buku_tersedia = 0;
$buku_dipinjam = 0;
foreach ($buku_list as $b) {
    if ($b['status'] == 'TERSEDIA') {
        $buku_tersedia++;
    } else {
        $buku_dipinjam++;
    }
}
?>

<div class="catalog-stats">
    <div class="stat-card total">
        <div class="stat-card-icon">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-value"><?php echo $total_buku; ?></div>
        <div class="stat-label">Total Buku</div>
    </div>
    <div class="stat-card available">
        <div class="stat-card-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-value"><?php echo $buku_tersedia; ?></div>
        <div class="stat-label">Tersedia</div>
    </div>
    <div class="stat-card borrowed">
        <div class="stat-card-icon">
            <i class="fas fa-book-reader"></i>
        </div>
        <div class="stat-value"><?php echo $buku_dipinjam; ?></div>
        <div class="stat-label">Dipinjam</div>
    </div>
</div>

<?php if (empty($buku_list)): ?>
<div class="book-card" style="text-align: center; padding: 60px 40px; grid-column: 1 / -1;">
    <div style="font-size: 4em; color: var(--text-muted); margin-bottom: 20px; opacity: 0.5;">
        <i class="fas fa-book-open"></i>
    </div>
    <p style="font-size: 1.2em; color: var(--text-primary); font-weight: 600; margin-bottom: 10px;">
        <?php echo !empty($search_query) ? 'Buku tidak ditemukan' : 'Belum ada buku dalam katalog'; ?>
    </p>
    <p style="font-size: 1em; color: var(--text-muted);">
        <?php echo !empty($search_query) ? 'Tidak ditemukan buku dengan kata kunci "' . htmlspecialchars($search_query) . '"' : 'Silakan hubungi pustakawan untuk informasi lebih lanjut'; ?>
    </p>
</div>
<?php else: ?>
<div class="catalog-grid">
    <?php foreach ($buku_list as $buku): ?>
    <div class="book-card">
        <div class="book-icon">
            <i class="fas fa-book"></i>
        </div>
        <div class="book-title"><?php echo htmlspecialchars($buku['judul']); ?></div>
        <div class="book-info">
            <i class="fas fa-barcode"></i>
            <strong>Kode:</strong> <span><?php echo htmlspecialchars($buku['kode_buku']); ?></span>
        </div>
        <div class="book-info">
            <i class="fas fa-user-edit"></i>
            <strong>Pengarang:</strong> <span><?php echo htmlspecialchars($buku['pengarang']); ?></span>
        </div>
        <div class="book-info">
            <i class="fas fa-building"></i>
            <strong>Penerbit:</strong> <span><?php echo htmlspecialchars($buku['penerbit']); ?></span>
        </div>
        <div class="book-info">
            <i class="fas fa-calendar-alt"></i>
            <strong>Tahun:</strong> <span><?php echo $buku['tahun_terbit']; ?></span>
        </div>
        <div class="book-status">
            <span style="font-size: 0.85em; color: var(--text-muted);">Status:</span>
            <?php if ($buku['status'] == 'TERSEDIA'): ?>
            <span class="status-badge status-tersedia">TERSEDIA</span>
            <?php else: ?>
            <span class="status-badge status-dipinjam">DIPINJAM</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php
echo get_katalog_footer();
?>