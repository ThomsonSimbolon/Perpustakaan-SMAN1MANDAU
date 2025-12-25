<?php
// File: perpustakaan/pustakawan/laporan.php
require_once '../config/database.php';
require_once '../config/functions.php';

check_login('pustakawan');

$db = new Database();
$conn = $db->getConnection();

// Filter tanggal (opsional)
$filter_tanggal_pinjam = isset($_GET['tanggal_pinjam']) ? sanitize_input($_GET['tanggal_pinjam']) : '';
$filter_tanggal_kembali = isset($_GET['tanggal_kembali']) ? sanitize_input($_GET['tanggal_kembali']) : '';

// Query Laporan Peminjaman
$sql_peminjaman = "SELECT p.*, a.nama as nama_anggota, a.jenis_anggota, a.kelas, b.judul as judul_buku, b.kode_buku
                    FROM peminjaman p
                    JOIN anggota a ON p.id_anggota = a.id_anggota
                    JOIN buku b ON p.kode_buku = b.kode_buku";
if (!empty($filter_tanggal_pinjam)) {
    $sql_peminjaman .= " WHERE DATE(p.tanggal_pinjam) = ?";
    $sql_peminjaman .= " ORDER BY p.tanggal_pinjam DESC";
    $stmt_pinjam = $conn->prepare($sql_peminjaman);
    $stmt_pinjam->bind_param("s", $filter_tanggal_pinjam);
    $stmt_pinjam->execute();
    $result_pinjam = $stmt_pinjam->get_result();
} else {
    $sql_peminjaman .= " ORDER BY p.tanggal_pinjam DESC";
    $result_pinjam = $conn->query($sql_peminjaman);
}
$laporan_peminjaman = $result_pinjam->fetch_all(MYSQLI_ASSOC);

// Query Laporan Pengembalian
$sql_pengembalian = "SELECT k.*, p.id_peminjaman, p.tanggal_pinjam, p.kode_buku, a.nama as nama_anggota, a.jenis_anggota, b.judul as judul_buku
                      FROM pengembalian k
                      JOIN peminjaman p ON k.id_peminjaman = p.id_peminjaman
                      JOIN anggota a ON p.id_anggota = a.id_anggota
                      JOIN buku b ON p.kode_buku = b.kode_buku";
if (!empty($filter_tanggal_kembali)) {
    $sql_pengembalian .= " WHERE DATE(k.tanggal_kembali) = ?";
    $sql_pengembalian .= " ORDER BY k.tanggal_kembali DESC";
    $stmt_kembali = $conn->prepare($sql_pengembalian);
    $stmt_kembali->bind_param("s", $filter_tanggal_kembali);
    $stmt_kembali->execute();
    $result_kembali = $stmt_kembali->get_result();
} else {
    $sql_pengembalian .= " ORDER BY k.tanggal_kembali DESC";
    $result_kembali = $conn->query($sql_pengembalian);
}
$laporan_pengembalian = $result_kembali->fetch_all(MYSQLI_ASSOC);

echo get_header("Laporan Perpustakaan", $_SESSION['role']);
?>

<style>
    @media print {
        .no-print {
            display: none !important;
        }
        .content-area {
            padding: 0;
        }
        body {
            background: white;
        }
    }
    .filter-section {
        background: var(--bg-card);
        padding: 20px;
        border-radius: var(--radius-md);
        margin-bottom: 20px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
    }
    .filter-section h3 {
        margin-bottom: 15px;
        color: var(--color-primary);
    }
    .filter-form {
        display: flex;
        gap: 15px;
        align-items: flex-end;
    }
    .filter-form .form-group {
        flex: 1;
        margin-bottom: 0;
    }
    .report-section {
        margin-bottom: 40px;
        page-break-inside: avoid;
    }
    .report-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
</style>

<div class="no-print">
    <div class="filter-section">
        <h3>Filter Laporan</h3>
        <form method="GET" action="laporan.php" class="filter-form">
            <div class="form-group">
                <label for="tanggal_pinjam">Filter Tanggal Peminjaman</label>
                <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" value="<?php echo $filter_tanggal_pinjam; ?>">
            </div>
            <div class="form-group">
                <label for="tanggal_kembali">Filter Tanggal Pengembalian</label>
                <input type="date" name="tanggal_kembali" id="tanggal_kembali" value="<?php echo $filter_tanggal_kembali; ?>">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="icon-search"></i> Filter</button>
                <a href="laporan.php" class="btn btn-secondary"><i class="icon-refresh"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Laporan Peminjaman -->
<div class="report-section">
    <div class="report-header">
        <h2>Laporan Peminjaman Buku</h2>
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-success"><i class="icon-print"></i> Cetak Laporan</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID Peminjaman</th>
                    <th>Tanggal Pinjam</th>
                    <th>Harus Kembali</th>
                    <th>Anggota</th>
                    <th>Jenis</th>
                    <th>Kelas</th>
                    <th>Kode Buku</th>
                    <th>Judul Buku</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laporan_peminjaman)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px;">Tidak ada data peminjaman</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($laporan_peminjaman as $lp): ?>
                    <tr>
                        <td><?php echo $lp['id_peminjaman']; ?></td>
                        <td><?php echo date('d-m-Y', strtotime($lp['tanggal_pinjam'])); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($lp['tanggal_kembali_harus'])); ?></td>
                        <td><?php echo $lp['nama_anggota']; ?></td>
                        <td><?php echo ucfirst($lp['jenis_anggota']); ?></td>
                        <td><?php echo $lp['kelas'] ?? '-'; ?></td>
                        <td><?php echo $lp['kode_buku']; ?></td>
                        <td><?php echo $lp['judul_buku']; ?></td>
                        <td>
                            <?php if ($lp['status'] == 'AKTIF'): ?>
                                <span class="status-badge status-dipinjam"><?php echo $lp['status']; ?></span>
                            <?php else: ?>
                                <span class="status-badge status-tersedia"><?php echo $lp['status']; ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p style="margin-top: 10px;"><strong>Total Peminjaman:</strong> <?php echo count($laporan_peminjaman); ?> transaksi</p>
</div>

<!-- Laporan Pengembalian -->
<div class="report-section">
    <div class="report-header">
        <h2>Laporan Pengembalian Buku</h2>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID Pengembalian</th>
                    <th>ID Peminjaman</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Anggota</th>
                    <th>Jenis</th>
                    <th>Kode Buku</th>
                    <th>Judul Buku</th>
                    <th>Denda</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laporan_pengembalian)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px;">Tidak ada data pengembalian</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $total_denda = 0;
                    foreach ($laporan_pengembalian as $lk): 
                        $total_denda += $lk['denda'];
                    ?>
                    <tr>
                        <td><?php echo $lk['id_pengembalian']; ?></td>
                        <td><?php echo $lk['id_peminjaman']; ?></td>
                        <td><?php echo date('d-m-Y', strtotime($lk['tanggal_pinjam'])); ?></td>
                        <td><?php echo date('d-m-Y', strtotime($lk['tanggal_kembali'])); ?></td>
                        <td><?php echo $lk['nama_anggota']; ?></td>
                        <td><?php echo ucfirst($lk['jenis_anggota']); ?></td>
                        <td><?php echo $lk['kode_buku']; ?></td>
                        <td><?php echo $lk['judul_buku']; ?></td>
                        <td>Rp <?php echo number_format($lk['denda'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p style="margin-top: 10px;">
        <strong>Total Pengembalian:</strong> <?php echo count($laporan_pengembalian); ?> transaksi<br>
        <strong>Total Denda:</strong> Rp <?php echo number_format($total_denda, 0, ',', '.'); ?>
    </p>
</div>

<div class="no-print" style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
    <p><strong>Catatan:</strong> Gunakan tombol "Cetak Laporan" untuk mencetak halaman ini. Pastikan filter tanggal sudah diatur sesuai kebutuhan sebelum mencetak.</p>
</div>

<?php
echo get_footer();
?>
