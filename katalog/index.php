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
.welcome-card {
    background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-elevated) 100%);
    padding: 20px 25px;
    border-radius: var(--radius-md);
    margin-bottom: 30px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.welcome-card-left {
    display: flex;
    align-items: center;
    gap: 15px;
    flex: 1;
    min-width: 0;
}

.welcome-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    color: #022c22;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5em;
    flex-shrink: 0;
}

.welcome-text {
    display: flex;
    flex-direction: column;
    min-width: 0;
    flex: 1;
}

.welcome-text strong {
    color: var(--text-primary);
    font-size: 1.1em;
    font-weight: 600;
    line-height: 1.3;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.welcome-text span {
    color: var(--text-muted);
    font-size: 0.9em;
    line-height: 1.4;
    margin-top: 2px;
}

.welcome-card-right {
    flex-shrink: 0;
}

.welcome-card-right a {
    padding: 8px 16px;
    background-color: var(--color-primary);
    color: #022c22;
    border-radius: var(--radius-sm);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9em;
    transition: var(--transition-fast);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.welcome-card-right a:hover {
    background-color: var(--color-primary-hover);
    transform: translateY(-1px);
    text-decoration: none;
}

.search-section {
    background: var(--bg-card);
    padding: 30px;
    border-radius: var(--radius-md);
    margin-bottom: 30px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
}

.search-section h2 {
    margin-bottom: 20px;
    color: var(--color-primary);
    font-size: 1.4em;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-section h2::before {
    content: "\f002";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    font-size: 1.1em;
}

.search-form {
    display: grid;
    grid-template-columns: 2fr 1fr auto;
    gap: 15px;
    align-items: end;
}

.search-form .form-group {
    margin-bottom: 0;
}

.search-form .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.95em;
}

.search-form .form-group input[type="text"],
.search-form .form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    font-size: 1em;
    background-color: var(--bg-card);
    color: var(--text-primary);
    transition: var(--transition-fast);
    box-sizing: border-box;
    height: 48px;
}

.search-form .form-group input[type="text"]:focus,
.search-form .form-group select:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(0, 220, 130, 0.1);
}

.search-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: flex-end;
}

.search-buttons .btn {
    flex: 1;
    min-width: 120px;
    padding: 12px 20px;
    font-size: 0.95em;
    white-space: nowrap;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 48px;
    height: 48px;
    box-sizing: border-box;
    margin: 0;
}

.catalog-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--bg-card);
    padding: 25px;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
    text-align: center;
    transition: transform var(--transition-fast), box-shadow var(--transition-fast);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

.stat-card-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    margin: 0 auto 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8em;
}

.stat-card.total .stat-card-icon {
    background: linear-gradient(135deg, var(--color-secondary), var(--color-secondary-hover));
    color: white;
}

.stat-card.available .stat-card-icon {
    background: linear-gradient(135deg, var(--color-success), #059669);
    color: white;
}

.stat-card.borrowed .stat-card-icon {
    background: linear-gradient(135deg, var(--color-danger), #dc2626);
    color: white;
}

.stat-value {
    font-size: 2.5em;
    font-weight: 700;
    margin-bottom: 5px;
    line-height: 1;
}

.stat-card.total .stat-value {
    color: var(--color-secondary);
}

.stat-card.available .stat-value {
    color: var(--color-success);
}

.stat-card.borrowed .stat-value {
    color: var(--color-danger);
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.95em;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.catalog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}

.book-card {
    background: var(--bg-card);
    padding: 25px;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
    transition: all var(--transition-fast);
    position: relative;
    overflow: hidden;
}

.book-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--color-primary);
    transform: scaleX(0);
    transition: transform var(--transition-fast);
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.book-card:hover::before {
    transform: scaleX(1);
}

.book-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    color: #022c22;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5em;
    margin-bottom: 15px;
}

.book-title {
    font-size: 1.25em;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 15px;
    line-height: 1.3;
    min-height: 3.2em;
}

.book-info {
    margin-bottom: 10px;
    color: var(--text-secondary);
    font-size: 0.95em;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.book-info i {
    color: var(--color-primary);
    margin-top: 2px;
    width: 16px;
    flex-shrink: 0;
}

.book-info strong {
    color: var(--text-primary);
    font-weight: 600;
    min-width: 80px;
}

.book-status {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Additional responsive styles for katalog page elements */

/* Tablet Styles */
@media (max-width: 1024px) {
    .welcome-card {
        padding: 18px 20px;
    }

    .welcome-icon {
        width: 45px;
        height: 45px;
        font-size: 1.3em;
    }

    .welcome-text strong {
        font-size: 1em;
    }

    .welcome-text span {
        font-size: 0.85em;
    }

    .welcome-card-right a {
        padding: 8px 14px;
        font-size: 0.85em;
    }
}

/* Mobile Styles */
@media (max-width: 768px) {
    .welcome-card {
        flex-direction: column;
        text-align: center;
        gap: 15px;
        padding: 18px 20px;
    }

    .welcome-card-left {
        flex-direction: column;
        gap: 12px;
        width: 100%;
    }

    .welcome-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5em;
    }

    .welcome-text {
        align-items: center;
        width: 100%;
    }

    .welcome-text strong {
        font-size: 1em;
        text-align: center;
    }

    .welcome-text span {
        font-size: 0.85em;
        text-align: center;
    }

    .welcome-card-right {
        width: 100%;
    }

    .welcome-card-right a {
        width: 100%;
        justify-content: center;
        padding: 10px 16px;
    }
}

/* Small Mobile Styles */
@media (max-width: 480px) {
    .welcome-card {
        padding: 15px;
        gap: 12px;
    }

    .welcome-icon {
        width: 45px;
        height: 45px;
        font-size: 1.3em;
    }

    .welcome-text strong {
        font-size: 0.95em;
    }

    .welcome-text span {
        font-size: 0.8em;
    }

    .welcome-card-right a {
        padding: 10px 14px;
        font-size: 0.85em;
    }
}

/* Mobile Styles - Search Form & Cards */
@media (max-width: 768px) {
    .search-form {
        grid-template-columns: 1fr;
    }

    .search-buttons {
        flex-direction: row;
        width: 100%;
    }

    .catalog-stats {
        grid-template-columns: 1fr;
    }

    .catalog-grid {
        grid-template-columns: 1fr;
    }
}
</style>

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