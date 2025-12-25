<?php
// File: perpustakaan/pustakawan/pengembalian.php
require_once '../config/database.php';
require_once '../config/functions.php';

check_login('pustakawan');

$db = new Database();
$conn = $db->getConnection();
$message = '';
$peminjaman_data = null;

// --- Logika Proses Pengembalian ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'proses_kembali') {
    $id_peminjaman = (int)sanitize_input($_POST['id_peminjaman']);
    $kode_buku = sanitize_input($_POST['kode_buku']);
    $tanggal_kembali = date('Y-m-d');
    $tanggal_kembali_harus = sanitize_input($_POST['tanggal_kembali_harus']);

    // Hitung denda
    $denda = calculate_fine($tanggal_kembali, $tanggal_kembali_harus);

    // Mulai transaksi
    $conn->begin_transaction();
    try {
        // 1. Update status peminjaman menjadi SELESAI
        $stmt_update_pinjam = $conn->prepare("UPDATE peminjaman SET status = 'SELESAI' WHERE id_peminjaman = ?");
        $stmt_update_pinjam->bind_param("i", $id_peminjaman);
        $stmt_update_pinjam->execute();

        // 2. Catat di tabel pengembalian
        $stmt_insert_kembali = $conn->prepare("INSERT INTO pengembalian (id_peminjaman, tanggal_kembali, denda) VALUES (?, ?, ?)");
        $stmt_insert_kembali->bind_param("isd", $id_peminjaman, $tanggal_kembali, $denda);
        $stmt_insert_kembali->execute();

        // 3. Update status buku menjadi TERSEDIA
        $stmt_update_buku = $conn->prepare("UPDATE buku SET status = 'TERSEDIA' WHERE kode_buku = ?");
        $stmt_update_buku->bind_param("s", $kode_buku);
        $stmt_update_buku->execute();

        $conn->commit();
        $message = show_alert('success', "Pengembalian berhasil dicatat. Denda: Rp " . number_format($denda, 0, ',', '.'));
    } catch (Exception $e) {
        $conn->rollback();
        $message = show_alert('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
    }
}

// --- Logika Pencarian Transaksi (Scan QR Code / Input Manual) ---
if (isset($_GET['qr_data']) || isset($_GET['id_pinjam'])) {
    $search_param = isset($_GET['qr_data']) ? sanitize_input($_GET['qr_data']) : null;
    $id_pinjam_param = isset($_GET['id_pinjam']) ? (int)sanitize_input($_GET['id_pinjam']) : null;

    $sql = "SELECT p.*, a.nama as nama_anggota, b.judul as judul_buku, b.kode_buku
            FROM peminjaman p
            JOIN anggota a ON p.id_anggota = a.id_anggota
            JOIN buku b ON p.kode_buku = b.kode_buku
            WHERE p.status = 'AKTIF'";
    
    if ($search_param) {
        // Asumsi QR Code berisi data yang sama dengan kolom qr_code
        $sql .= " AND p.qr_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $search_param);
    } elseif ($id_pinjam_param) {
        $sql .= " AND p.id_peminjaman = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_pinjam_param);
    }

    if (isset($stmt)) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $peminjaman_data = $result->fetch_assoc();
        } else {
            $message = show_alert('warning', 'Transaksi peminjaman aktif tidak ditemukan atau sudah selesai.');
        }
    }
}

// Ambil data pengembalian yang sudah selesai
$sql_selesai = "SELECT p.id_peminjaman, a.nama as nama_anggota, b.judul as judul_buku, k.tanggal_kembali, k.denda
                FROM pengembalian k
                JOIN peminjaman p ON k.id_peminjaman = p.id_peminjaman
                JOIN anggota a ON p.id_anggota = a.id_anggota
                JOIN buku b ON p.kode_buku = b.kode_buku
                ORDER BY k.tanggal_kembali DESC LIMIT 10";
$pengembalian_selesai = $conn->query($sql_selesai)->fetch_all(MYSQLI_ASSOC);

echo get_header("Transaksi Pengembalian", $_SESSION['role']);
?>

<?php echo $message; ?>

<style>
.qr-tab-button {
    padding: 10px 20px;
    margin-right: 5px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9em;
    transition: all 0.3s;
}

.qr-tab-button.active {
    background-color: var(--color-primary);
    color: #022C22;
}

.qr-tab-button:not(.active) {
    background-color: #6c757d;
    color: white;
}

.qr-tab-button:hover {
    opacity: 0.8;
}

#qr-reader {
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 10px;
    background: #f8f9fa;
}
</style>

<div class="card" style="margin-bottom: 20px;">
    <h3><i class="icon-camera"></i> Scan / Input QR Code</h3>

    <!-- Tab untuk Scan Kamera dan Input Manual -->
    <div style="margin-bottom: 15px;">
        <button id="btnTabManual" class="qr-tab-button active" onclick="showManualInput(); return false;">Input
            Manual</button>
        <button id="btnTabCamera" class="qr-tab-button" onclick="showCameraScan(); return false;">Scan dengan
            Kamera</button>
    </div>

    <!-- Input Manual -->
    <div id="manualInputSection">
        <form method="GET" action="pengembalian.php" class="modal-form" style="display: flex; gap: 10px;">
            <input type="text" name="qr_data" id="qr_data_input" placeholder="Input data QR Code manual"
                style="flex-grow: 1;" required>
            <button type="submit" class="btn btn-primary"><i class="icon-search"></i> Cari Transaksi</button>
        </form>
    </div>

    <!-- Camera Scan Section -->
    <div id="cameraScanSection" style="display: none;">
        <div id="qr-reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
        <div id="qr-reader-results" style="margin-top: 15px; text-align: center;"></div>
        <button onclick="stopCameraScan()" class="btn btn-danger" style="margin-top: 15px;"><i class="icon-x"></i> Tutup
            Kamera</button>
    </div>

    <p style="margin-top: 10px; text-align: center;">Atau gunakan tombol "Proses Kembali" dari halaman Peminjaman Aktif.
    </p>
</div>

<?php if ($peminjaman_data): ?>
<div class="card" style="border: 2px solid var(--color-primary);">
    <h3>Detail Peminjaman Aktif</h3>
    <div class="table-responsive">
        <table class="data-table" style="margin-top: 10px;">
            <tr>
                <th>ID Peminjaman</th>
                <td><?php echo $peminjaman_data['id_peminjaman']; ?></td>
            </tr>
            <tr>
                <th>Anggota</th>
                <td><?php echo $peminjaman_data['nama_anggota']; ?></td>
            </tr>
            <tr>
                <th>Buku</th>
                <td><?php echo $peminjaman_data['judul_buku']; ?> (<?php echo $peminjaman_data['kode_buku']; ?>)</td>
            </tr>
            <tr>
                <th>Tanggal Pinjam</th>
                <td><?php echo $peminjaman_data['tanggal_pinjam']; ?></td>
            </tr>
            <tr>
                <th>Harus Kembali</th>
                <td><?php echo $peminjaman_data['tanggal_kembali_harus']; ?></td>
            </tr>
            <tr>
                <th>Tanggal Kembali Hari Ini</th>
                <td><?php echo date('Y-m-d'); ?></td>
            </tr>
            <tr>
                <th>Potensi Denda</th>
                <td>Rp
                    <?php echo number_format(calculate_fine(date('Y-m-d'), $peminjaman_data['tanggal_kembali_harus']), 0, ',', '.'); ?>
                </td>
            </tr>
        </table>
    </div>

    <form method="POST" action="pengembalian.php" style="margin-top: 20px;">
        <input type="hidden" name="action" value="proses_kembali">
        <input type="hidden" name="id_peminjaman" value="<?php echo $peminjaman_data['id_peminjaman']; ?>">
        <input type="hidden" name="kode_buku" value="<?php echo $peminjaman_data['kode_buku']; ?>">
        <input type="hidden" name="tanggal_kembali_harus"
            value="<?php echo $peminjaman_data['tanggal_kembali_harus']; ?>">
        <button type="submit" class="btn btn-success"
            onclick="return confirm('Konfirmasi proses pengembalian buku ini?');"><i class="icon-arrow-down"></i>
            Selesaikan Pengembalian</button>
    </form>
</div>
<?php endif; ?>

<h3 style="margin-top: 30px;">10 Transaksi Pengembalian Terakhir</h3>
<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID Pinjam</th>
                <th>Anggota</th>
                <th>Buku</th>
                <th>Tgl Kembali</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pengembalian_selesai as $k): ?>
            <tr>
                <td><?php echo $k['id_peminjaman']; ?></td>
                <td><?php echo $k['nama_anggota']; ?></td>
                <td><?php echo $k['judul_buku']; ?></td>
                <td><?php echo $k['tanggal_kembali']; ?></td>
                <td>Rp <?php echo number_format($k['denda'], 0, ',', '.'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Include html5-qrcode library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
let html5QrcodeScanner = null;

function showManualInput() {
    document.getElementById('manualInputSection').style.display = 'block';
    document.getElementById('cameraScanSection').style.display = 'none';
    document.getElementById('btnTabManual').classList.add('active');
    document.getElementById('btnTabCamera').classList.remove('active');
    stopCameraScan();
}

function showCameraScan() {
    document.getElementById('manualInputSection').style.display = 'none';
    document.getElementById('cameraScanSection').style.display = 'block';
    document.getElementById('btnTabManual').classList.remove('active');
    document.getElementById('btnTabCamera').classList.add('active');

    startCameraScan();
}

function startCameraScan() {
    const resultContainer = document.getElementById('qr-reader-results');
    resultContainer.innerHTML = '';

    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear();
    }

    html5QrcodeScanner = new Html5Qrcode("qr-reader");

    html5QrcodeScanner.start({
            facingMode: "environment"
        }, // Gunakan kamera belakang
        {
            fps: 10,
            qrbox: {
                width: 250,
                height: 250
            }
        },
        (decodedText, decodedResult) => {
            // QR Code berhasil di-scan
            resultContainer.innerHTML =
                '<div style="color: green; padding: 10px; background: #d4edda; border-radius: 5px; margin-bottom: 10px;"><strong>QR Code berhasil di-scan!</strong></div>';

            // Redirect ke halaman dengan QR data
            window.location.href = 'pengembalian.php?qr_data=' + encodeURIComponent(decodedText);

            // Stop scanning setelah berhasil
            html5QrcodeScanner.stop();
        },
        (errorMessage) => {
            // Error handling (diam saja untuk menghindari spam di console)
        }
    ).catch((err) => {
        // Jika kamera tidak tersedia atau diizinkan
        resultContainer.innerHTML =
            '<div style="color: red; padding: 10px; background: #f8d7da; border-radius: 5px;">Error: Tidak dapat mengakses kamera. Pastikan browser mendukung dan izin kamera sudah diberikan.</div>';
    });
}

function stopCameraScan() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
            html5QrcodeScanner.clear();
            html5QrcodeScanner = null;
        }).catch((err) => {
            // Ignore errors
        });
    }
}

// Inisialisasi saat page load
document.addEventListener('DOMContentLoaded', function() {
    // Default show manual input
    showManualInput();

    // Cleanup saat page unload
    window.addEventListener('beforeunload', function() {
        stopCameraScan();
    });
});
</script>

<?php
echo get_footer();
?>