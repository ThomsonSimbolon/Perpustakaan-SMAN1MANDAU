<?php
// File: perpustakaan/pustakawan/peminjaman.php
require_once '../config/database.php';
require_once '../config/functions.php';

check_login('pustakawan');

$db = new Database();
$conn = $db->getConnection();
$message = '';
$qr_code_path = '';
$id_peminjaman = 0;
$qr_content = '';

// --- Logika Peminjaman Buku ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'pinjam') {
    $id_anggota = (int)sanitize_input($_POST['id_anggota']);
    $kode_buku = sanitize_input($_POST['kode_buku']);
    $tanggal_pinjam = date('Y-m-d');
    $tanggal_kembali_harus = get_due_date($tanggal_pinjam);

    // 1. Validasi Anggota
    $stmt_anggota = $conn->prepare("SELECT id_anggota FROM anggota WHERE id_anggota = ?");
    $stmt_anggota->bind_param("i", $id_anggota);
    $stmt_anggota->execute();
    if ($stmt_anggota->get_result()->num_rows == 0) {
        $message = show_alert('error', 'Anggota tidak ditemukan.');
    } else {
        // 2. Validasi Buku (Status TERSEDIA)
        $stmt_buku = $conn->prepare("SELECT status FROM buku WHERE kode_buku = ? AND status = 'TERSEDIA'");
        $stmt_buku->bind_param("s", $kode_buku);
        $stmt_buku->execute();
        $result_buku = $stmt_buku->get_result();
        
        if ($result_buku->num_rows == 0) {
            $message = show_alert('error', 'Buku tidak tersedia untuk dipinjam.');
        } else {
            // 3. Simpan data peminjaman
            // Generate QR Code data sementara
            $qr_data_temp = uniqid('PINJAM_', true); 
            
            $stmt_pinjam = $conn->prepare("INSERT INTO peminjaman (id_anggota, kode_buku, tanggal_pinjam, tanggal_kembali_harus, qr_code, status) VALUES (?, ?, ?, ?, ?, 'AKTIF')");
            $stmt_pinjam->bind_param("issss", $id_anggota, $kode_buku, $tanggal_pinjam, $tanggal_kembali_harus, $qr_data_temp);
            
            if ($stmt_pinjam->execute()) {
                $id_peminjaman = $conn->insert_id;
                
                // Update QR Code data dengan ID Peminjaman yang sebenarnya
                $qr_content = "ID_PINJAM:{$id_peminjaman}|ID_ANGGOTA:{$id_anggota}|KODE_BUKU:{$kode_buku}";
                $qr_filename = "peminjaman_{$id_peminjaman}.png";
                
                // Generate QR Code dan simpan path
                $qr_code_path = generate_qr_code($qr_content, $qr_filename);
                
                // Update tabel peminjaman dengan QR Code yang sudah di-generate
                $stmt_update_qr = $conn->prepare("UPDATE peminjaman SET qr_code = ? WHERE id_peminjaman = ?");
                $stmt_update_qr->bind_param("si", $qr_content, $id_peminjaman);
                $stmt_update_qr->execute();

                // 4. Update status buku menjadi DIPINJAM
                $stmt_update_buku = $conn->prepare("UPDATE buku SET status = 'DIPINJAM' WHERE kode_buku = ?");
                $stmt_update_buku->bind_param("s", $kode_buku);
                $stmt_update_buku->execute();
                
                $message = show_alert('success', 'Peminjaman berhasil dicatat. QR Code siap dicetak.');
            } else {
                $message = show_alert('error', 'Gagal mencatat peminjaman: ' . $stmt_pinjam->error);
            }
        }
    }
}

// Ambil data untuk form
$anggota_list = $conn->query("SELECT id_anggota, nama, jenis_anggota, kelas FROM anggota ORDER BY nama ASC")->fetch_all(MYSQLI_ASSOC);
$buku_list = $conn->query("SELECT kode_buku, judul FROM buku WHERE status = 'TERSEDIA' ORDER BY judul ASC")->fetch_all(MYSQLI_ASSOC);

// Ambil data peminjaman aktif
$sql_aktif = "SELECT p.*, a.nama as nama_anggota, a.jenis_anggota, b.judul as judul_buku 
              FROM peminjaman p
              JOIN anggota a ON p.id_anggota = a.id_anggota
              JOIN buku b ON p.kode_buku = b.kode_buku
              WHERE p.status = 'AKTIF'
              ORDER BY p.tanggal_pinjam DESC";
$peminjaman_aktif = $conn->query($sql_aktif)->fetch_all(MYSQLI_ASSOC);

echo get_header("Transaksi Peminjaman", $_SESSION['role']);
?>

<?php echo $message; ?>

<button class="btn btn-primary" data-modal-target="modalPeminjaman"><i class="icon-plus"></i> Peminjaman Baru</button>

<?php if ($qr_code_path): ?>
<div class="card" style="margin-top: 20px; text-align: center;">
    <h3>QR Code Transaksi Peminjaman #<?php echo $id_peminjaman; ?></h3>
    <?php 
    // Path relatif dari peminjaman.php ke assets/img/qr_codes/
    // File berada di: perpustakaan/pustakawan/peminjaman.php
    // QR Code berada di: perpustakaan/assets/img/qr_codes/
    $qr_filename = basename($qr_code_path);
    $qr_image_path = '../assets/img/qr_codes/' . $qr_filename;
    $qr_full_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'qr_codes' . DIRECTORY_SEPARATOR . $qr_filename;
    ?>
    <?php if (file_exists($qr_full_path)): ?>
        <img src="<?php echo htmlspecialchars($qr_image_path); ?>" alt="QR Code Peminjaman" style="width: 200px; height: 200px; margin: 10px auto; display: block; border: 1px solid #ddd;">
    <?php else: ?>
        <div style="color:red; padding:10px; border:1px solid red; background:#ffe6e6;">
            <strong>⚠️ Error:</strong> File QR Code tidak ditemukan!<br>
            <small>File yang dicari: <?php echo htmlspecialchars($qr_full_path); ?></small><br>
            <small>Path web: <?php echo htmlspecialchars($qr_image_path); ?></small>
        </div>
    <?php endif; ?>
    <p>Data QR: <code><?php echo $qr_content; ?></code></p>
    <button class="btn btn-success" onclick="window.print()"><i class="icon-print"></i> Cetak QR Code</button>
</div>
<?php endif; ?>

<h3 style="margin-top: 30px;">Daftar Peminjaman Aktif</h3>
<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>ID Pinjam</th>
                <th>Anggota</th>
                <th>Buku</th>
                <th>Tgl Pinjam</th>
                <th>Harus Kembali</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($peminjaman_aktif as $p): ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo $p['id_peminjaman']; ?></td>
                <td><?php echo $p['nama_anggota']; ?> (<?php echo ucfirst($p['jenis_anggota']); ?>)</td>
                <td><?php echo $p['judul_buku']; ?> (<?php echo $p['kode_buku']; ?>)</td>
                <td><?php echo $p['tanggal_pinjam']; ?></td>
                <td><?php echo $p['tanggal_kembali_harus']; ?></td>
                <td><span class="status-badge status-aktif"><?php echo $p['status']; ?></span></td>
                <td>
                    <a href="pengembalian.php?id_pinjam=<?php echo $p['id_peminjaman']; ?>" class="btn btn-primary btn-sm"><i class="icon-arrow-down"></i> Proses Kembali</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Peminjaman Baru -->
<div id="modalPeminjaman" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3>Form Peminjaman Buku</h3>
        <form method="POST" action="peminjaman.php" class="modal-form">
            <input type="hidden" name="action" value="pinjam">
            
            <div class="form-group">
                <label for="id_anggota">Anggota (Siswa/Guru)</label>
                <select name="id_anggota" id="id_anggota" required>
                    <option value="">-- Pilih Anggota --</option>
                    <?php foreach ($anggota_list as $a): ?>
                        <option value="<?php echo $a['id_anggota']; ?>">
                            <?php echo $a['nama']; ?> (<?php echo ucfirst($a['jenis_anggota']); ?> <?php echo $a['kelas'] ? ' - ' . $a['kelas'] : ''; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="kode_buku">Buku (Status TERSEDIA)</label>
                <select name="kode_buku" id="kode_buku" required>
                    <option value="">-- Pilih Buku --</option>
                    <?php foreach ($buku_list as $b): ?>
                        <option value="<?php echo $b['kode_buku']; ?>">
                            <?php echo $b['judul']; ?> (<?php echo $b['kode_buku']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><i class="icon-arrow-up"></i> Proses Peminjaman</button>
            </div>
        </form>
    </div>
</div>

<?php
echo get_footer();
?>
