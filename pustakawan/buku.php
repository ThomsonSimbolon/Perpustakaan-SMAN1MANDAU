<?php
// File: perpustakaan/pustakawan/buku.php
require_once '../config/database.php';
require_once '../config/functions.php';

check_login('pustakawan');

$db = new Database();
$conn = $db->getConnection();
$message = '';

// --- Logika CRUD Buku ---

// Tambah/Edit Buku
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = sanitize_input($_POST['action']);
    $kode_buku = sanitize_input($_POST['kode_buku']);
    $judul = sanitize_input($_POST['judul']);
    $pengarang = sanitize_input($_POST['pengarang']);
    $penerbit = sanitize_input($_POST['penerbit']);
    $tahun_terbit = sanitize_input($_POST['tahun_terbit']);
    $status = sanitize_input($_POST['status']);
    $kode_buku_old = isset($_POST['kode_buku_old']) ? sanitize_input($_POST['kode_buku_old']) : '';

    if ($action == 'add') {
        // Validasi kode_buku unik
        $stmt_check = $conn->prepare("SELECT kode_buku FROM buku WHERE kode_buku = ?");
        $stmt_check->bind_param("s", $kode_buku);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $message = show_alert('error', 'Kode buku sudah digunakan. Silakan gunakan kode lain.');
        } else {
            $stmt = $conn->prepare("INSERT INTO buku (kode_buku, judul, pengarang, penerbit, tahun_terbit, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssis", $kode_buku, $judul, $pengarang, $penerbit, $tahun_terbit, $status);
            if ($stmt->execute()) {
                $message = show_alert('success', 'Buku berhasil ditambahkan.');
            } else {
                $message = show_alert('error', 'Gagal menambahkan buku: ' . $stmt->error);
            }
        }
    } elseif ($action == 'edit' && !empty($kode_buku_old)) {
        // Validasi kode_buku unik (jika kode berubah)
        if ($kode_buku != $kode_buku_old) {
            $stmt_check = $conn->prepare("SELECT kode_buku FROM buku WHERE kode_buku = ?");
            $stmt_check->bind_param("s", $kode_buku);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $message = show_alert('error', 'Kode buku sudah digunakan. Silakan gunakan kode lain.');
            } else {
                // Update dengan kode baru
                $stmt = $conn->prepare("UPDATE buku SET kode_buku = ?, judul = ?, pengarang = ?, penerbit = ?, tahun_terbit = ?, status = ? WHERE kode_buku = ?");
                $stmt->bind_param("ssssiss", $kode_buku, $judul, $pengarang, $penerbit, $tahun_terbit, $status, $kode_buku_old);
                if ($stmt->execute()) {
                    $message = show_alert('success', 'Buku berhasil diperbarui.');
                } else {
                    $message = show_alert('error', 'Gagal memperbarui buku: ' . $stmt->error);
                }
            }
        } else {
            // Kode tidak berubah, update data lain saja
            $stmt = $conn->prepare("UPDATE buku SET judul = ?, pengarang = ?, penerbit = ?, tahun_terbit = ?, status = ? WHERE kode_buku = ?");
            $stmt->bind_param("sssiss", $judul, $pengarang, $penerbit, $tahun_terbit, $status, $kode_buku);
            if ($stmt->execute()) {
                $message = show_alert('success', 'Buku berhasil diperbarui.');
            } else {
                $message = show_alert('error', 'Gagal memperbarui buku: ' . $stmt->error);
            }
        }
    }
}

// Hapus Buku
if (isset($_GET['delete_id'])) {
    $kode_buku = sanitize_input($_GET['delete_id']);
    
    // Cek apakah buku sedang dipinjam
    $stmt_check = $conn->prepare("SELECT id_peminjaman FROM peminjaman WHERE kode_buku = ? AND status = 'AKTIF'");
    $stmt_check->bind_param("s", $kode_buku);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $message = show_alert('error', 'Buku tidak dapat dihapus karena sedang dipinjam.');
        header("Location: buku.php");
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM buku WHERE kode_buku = ?");
    $stmt->bind_param("s", $kode_buku);
    if ($stmt->execute()) {
        $message = show_alert('success', 'Buku berhasil dihapus.');
    } else {
        $message = show_alert('error', 'Gagal menghapus buku: ' . $stmt->error);
    }
    header("Location: buku.php");
    exit;
}

// Ambil semua data buku
$result = $conn->query("SELECT * FROM buku ORDER BY kode_buku ASC");
$buku = $result->fetch_all(MYSQLI_ASSOC);

echo get_header("Kelola Data Buku", $_SESSION['role']);
?>

<?php echo $message; ?>

<button class="btn btn-primary" data-modal-target="modalAddEdit"><i class="icon-plus"></i> Tambah Buku</button>

<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Kode Buku</th>
                <th>Judul</th>
                <th>Pengarang</th>
                <th>Penerbit</th>
                <th>Tahun Terbit</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($buku as $b): ?>
            <tr>
                <td><?php echo $b['kode_buku']; ?></td>
                <td><?php echo $b['judul']; ?></td>
                <td><?php echo $b['pengarang']; ?></td>
                <td><?php echo $b['penerbit']; ?></td>
                <td><?php echo $b['tahun_terbit']; ?></td>
                <td>
                    <?php if ($b['status'] == 'TERSEDIA'): ?>
                        <span class="status-badge status-tersedia"><?php echo $b['status']; ?></span>
                    <?php else: ?>
                        <span class="status-badge status-dipinjam"><?php echo $b['status']; ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-primary btn-sm" 
                            data-modal-target="modalAddEdit" 
                            data-kode="<?php echo htmlspecialchars($b['kode_buku']); ?>"
                            data-judul="<?php echo htmlspecialchars($b['judul']); ?>"
                            data-pengarang="<?php echo htmlspecialchars($b['pengarang']); ?>"
                            data-penerbit="<?php echo htmlspecialchars($b['penerbit']); ?>"
                            data-tahun="<?php echo $b['tahun_terbit']; ?>"
                            data-status="<?php echo $b['status']; ?>"
                            onclick="editBuku(this)"><i class="icon-edit"></i> Edit</button>
                    <a href="buku.php?delete_id=<?php echo urlencode($b['kode_buku']); ?>" 
                       class="btn btn-danger btn-sm" 
                       onclick="return confirm('Yakin ingin menghapus buku ini? Pastikan buku tidak sedang dipinjam.');"><i class="icon-trash"></i> Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah/Edit Buku -->
<div id="modalAddEdit" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3 id="modalTitle">Tambah Buku Baru</h3>
        <form method="POST" action="buku.php" class="modal-form">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="kode_buku_old" id="kodeBukuOld">
            
            <div class="form-group">
                <label for="kode_buku">Kode Buku</label>
                <input type="text" name="kode_buku" id="kode_buku" required maxlength="20">
            </div>
            
            <div class="form-group">
                <label for="judul">Judul</label>
                <input type="text" name="judul" id="judul" required maxlength="255">
            </div>
            
            <div class="form-group">
                <label for="pengarang">Pengarang</label>
                <input type="text" name="pengarang" id="pengarang" required maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="penerbit">Penerbit</label>
                <input type="text" name="penerbit" id="penerbit" required maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="tahun_terbit">Tahun Terbit</label>
                <input type="number" name="tahun_terbit" id="tahun_terbit" required min="1900" max="<?php echo date('Y'); ?>">
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" required>
                    <option value="TERSEDIA">TERSEDIA</option>
                    <option value="DIPINJAM">DIPINJAM</option>
                </select>
            </div>
            
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editBuku(button) {
        document.getElementById('modalTitle').innerText = 'Edit Buku';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('kodeBukuOld').value = button.getAttribute('data-kode');
        document.getElementById('kode_buku').value = button.getAttribute('data-kode');
        document.getElementById('judul').value = button.getAttribute('data-judul');
        document.getElementById('pengarang').value = button.getAttribute('data-pengarang');
        document.getElementById('penerbit').value = button.getAttribute('data-penerbit');
        document.getElementById('tahun_terbit').value = button.getAttribute('data-tahun');
        document.getElementById('status').value = button.getAttribute('data-status');
        
        document.getElementById('modalAddEdit').style.display = 'block';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const addButton = document.querySelector('[data-modal-target="modalAddEdit"]');
        if (addButton) {
            addButton.addEventListener('click', function() {
                document.getElementById('modalTitle').innerText = 'Tambah Buku Baru';
                document.getElementById('formAction').value = 'add';
                document.getElementById('kodeBukuOld').value = '';
                document.getElementById('kode_buku').value = '';
                document.getElementById('kode_buku').removeAttribute('readonly');
                document.getElementById('judul').value = '';
                document.getElementById('pengarang').value = '';
                document.getElementById('penerbit').value = '';
                document.getElementById('tahun_terbit').value = '';
                document.getElementById('status').value = 'TERSEDIA';
            });
        }
    });
</script>

<?php
echo get_footer();
?>
