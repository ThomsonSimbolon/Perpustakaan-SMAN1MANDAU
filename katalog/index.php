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

<style>
    .search-section {
        background: var(--card-background);
        padding: 25px;
        border-radius: 8px;
        margin-bottom: 30px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }
    .search-form {
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }
    .search-form .form-group {
        flex: 1;
        margin-bottom: 0;
    }
    .search-form .form-group:first-child {
        flex: 2;
    }
    .catalog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    .book-card {
        background: var(--card-background);
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .book-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .book-title {
        font-size: 1.2em;
        font-weight: bold;
        color: var(--primary-color);
        margin-bottom: 10px;
    }
    .book-info {
        margin-bottom: 8px;
        color: var(--text-color);
    }
    .book-info strong {
        color: var(--primary-color);
    }
    .book-status {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid var(--border-color);
    }
    .info-banner {
        background: #e7f3ff;
        border-left: 4px solid var(--secondary-color);
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    .catalog-stats {
        background: var(--card-background);
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-around;
        text-align: center;
    }
    .catalog-stats .stat-item {
        flex: 1;
    }
    .catalog-stats .stat-value {
        font-size: 2em;
        font-weight: bold;
        color: var(--secondary-color);
    }
    .catalog-stats .stat-label {
        color: var(--text-color);
        font-size: 0.9em;
    }
</style>

<div class="info-banner">
    <strong>Selamat Datang, <?php echo $_SESSION['username']; ?>!</strong> (<?php echo ucfirst($_SESSION['role']); ?>) | 
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="search-section">
    <h2 style="margin-bottom: 20px; color: var(--primary-color);">Cari Buku</h2>
    <form method="GET" action="index.php" class="search-form">
        <div class="form-group">
            <label for="search">Kata Kunci</label>
            <input type="text" name="search" id="search" placeholder="Masukkan judul atau pengarang buku..." value="<?php echo htmlspecialchars($search_query); ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px;">
        </div>
        <div class="form-group">
            <label for="search_type">Cari Berdasarkan</label>
            <select name="search_type" id="search_type" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px;">
                <option value="judul" <?php echo $search_type == 'judul' ? 'selected' : ''; ?>>Judul</option>
                <option value="pengarang" <?php echo $search_type == 'pengarang' ? 'selected' : ''; ?>>Pengarang</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px; height: fit-content;"><i class="icon-search"></i> Cari</button>
            <?php if (!empty($search_query)): ?>
                <a href="index.php" class="btn btn-secondary" style="padding: 10px 20px; height: fit-content; margin-left: 10px;"><i class="icon-refresh"></i> Reset</a>
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
    <div class="stat-item">
        <div class="stat-value"><?php echo $total_buku; ?></div>
        <div class="stat-label">Total Buku</div>
    </div>
    <div class="stat-item">
        <div class="stat-value" style="color: var(--success-color);"><?php echo $buku_tersedia; ?></div>
        <div class="stat-label">Tersedia</div>
    </div>
    <div class="stat-item">
        <div class="stat-value" style="color: var(--danger-color);"><?php echo $buku_dipinjam; ?></div>
        <div class="stat-label">Dipinjam</div>
    </div>
</div>

<?php if (empty($buku_list)): ?>
    <div class="book-card" style="text-align: center; padding: 40px;">
        <p style="font-size: 1.1em; color: #666;"><?php echo !empty($search_query) ? 'Tidak ditemukan buku dengan kata kunci "' . htmlspecialchars($search_query) . '"' : 'Belum ada buku dalam katalog'; ?></p>
    </div>
<?php else: ?>
    <div class="catalog-grid">
        <?php foreach ($buku_list as $buku): ?>
        <div class="book-card">
            <div class="book-title"><?php echo htmlspecialchars($buku['judul']); ?></div>
            <div class="book-info">
                <strong>Kode:</strong> <?php echo htmlspecialchars($buku['kode_buku']); ?>
            </div>
            <div class="book-info">
                <strong>Pengarang:</strong> <?php echo htmlspecialchars($buku['pengarang']); ?>
            </div>
            <div class="book-info">
                <strong>Penerbit:</strong> <?php echo htmlspecialchars($buku['penerbit']); ?>
            </div>
            <div class="book-info">
                <strong>Tahun:</strong> <?php echo $buku['tahun_terbit']; ?>
            </div>
            <div class="book-status">
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
