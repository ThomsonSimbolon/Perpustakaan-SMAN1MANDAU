<?php
// File: perpustakaan/pustakawan/anggota.php
require_once '../config/database.php';
require_once '../config/functions.php';

check_login('pustakawan');

$db = new Database();
$conn = $db->getConnection();
$message = '';

// --- Logika CRUD Anggota ---

// Tambah/Edit Anggota
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = sanitize_input($_POST['action']);
    $nama = sanitize_input($_POST['nama']);
    $jenis_anggota = sanitize_input($_POST['jenis_anggota']);
    $kelas = ($jenis_anggota == 'siswa') ? sanitize_input($_POST['kelas']) : NULL;
    $id_anggota = isset($_POST['id_anggota']) ? (int)$_POST['id_anggota'] : 0;

    if ($action == 'add') {
        $stmt = $conn->prepare("INSERT INTO anggota (nama, jenis_anggota, kelas) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama, $jenis_anggota, $kelas);
        if ($stmt->execute()) {
            $message = show_alert('success', 'Anggota berhasil ditambahkan.');
        } else {
            $message = show_alert('error', 'Gagal menambahkan anggota: ' . $stmt->error);
        }
    } elseif ($action == 'edit' && $id_anggota > 0) {
        $stmt = $conn->prepare("UPDATE anggota SET nama = ?, jenis_anggota = ?, kelas = ? WHERE id_anggota = ?");
        $stmt->bind_param("sssi", $nama, $jenis_anggota, $kelas, $id_anggota);
        if ($stmt->execute()) {
            $message = show_alert('success', 'Anggota berhasil diperbarui.');
        } else {
            $message = show_alert('error', 'Gagal memperbarui anggota: ' . $stmt->error);
        }
    }
}

// Hapus Anggota
if (isset($_GET['delete_id'])) {
    $id_anggota = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM anggota WHERE id_anggota = ?");
    $stmt->bind_param("i", $id_anggota);
    if ($stmt->execute()) {
        $message = show_alert('success', 'Anggota berhasil dihapus.');
    } else {
        $message = show_alert('error', 'Gagal menghapus anggota: ' . $stmt->error);
    }
    header("Location: anggota.php");
    exit;
}

// Ambil semua data anggota
$result = $conn->query("SELECT * FROM anggota ORDER BY id_anggota DESC");
$anggota = $result->fetch_all(MYSQLI_ASSOC);

echo get_header("Kelola Anggota", $_SESSION['role']);
?>

<?php echo $message; ?>

<button class="btn btn-primary" data-modal-target="modalAddEdit"><i class="icon-plus"></i> Tambah Anggota</button>

<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Jenis</th>
                <th>Kelas</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($anggota as $a): ?>
            <tr>
                <td><?php echo $a['id_anggota']; ?></td>
                <td><?php echo $a['nama']; ?></td>
                <td><?php echo ucfirst($a['jenis_anggota']); ?></td>
                <td><?php echo $a['kelas'] ?? '-'; ?></td>
                <td>
                    <button class="btn btn-primary btn-sm" 
                            data-modal-target="modalAddEdit" 
                            data-id="<?php echo $a['id_anggota']; ?>"
                            data-nama="<?php echo $a['nama']; ?>"
                            data-jenis="<?php echo $a['jenis_anggota']; ?>"
                            data-kelas="<?php echo $a['kelas']; ?>"
                            onclick="editAnggota(this)"><i class="icon-edit"></i> Edit</button>
                    <a href="anggota.php?delete_id=<?php echo $a['id_anggota']; ?>" 
                       class="btn btn-danger btn-sm" 
                       onclick="return confirm('Yakin ingin menghapus anggota ini? Semua data peminjaman terkait akan terhapus.');"><i class="icon-trash"></i> Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah/Edit Anggota -->
<div id="modalAddEdit" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3 id="modalTitle">Tambah Anggota Baru</h3>
        <form method="POST" action="anggota.php" class="modal-form">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id_anggota" id="anggotaId">
            
            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" name="nama" id="nama" required>
            </div>
            
            <div class="form-group">
                <label for="jenis_anggota">Jenis Anggota</label>
                <select name="jenis_anggota" id="jenis_anggota" required onchange="toggleKelas(this.value)">
                    <option value="siswa">Siswa</option>
                    <option value="guru">Guru</option>
                </select>
            </div>
            
            <div class="form-group" id="kelasGroup">
                <label for="kelas">Kelas</label>
                <input type="text" name="kelas" id="kelas">
            </div>
            
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleKelas(jenis) {
        const kelasGroup = document.getElementById('kelasGroup');
        if (jenis === 'siswa') {
            kelasGroup.style.display = 'block';
            document.getElementById('kelas').setAttribute('required', 'required');
        } else {
            kelasGroup.style.display = 'none';
            document.getElementById('kelas').removeAttribute('required');
        }
    }

    function editAnggota(button) {
        document.getElementById('modalTitle').innerText = 'Edit Anggota';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('anggotaId').value = button.getAttribute('data-id');
        document.getElementById('nama').value = button.getAttribute('data-nama');
        document.getElementById('jenis_anggota').value = button.getAttribute('data-jenis');
        document.getElementById('kelas').value = button.getAttribute('data-kelas');
        
        toggleKelas(button.getAttribute('data-jenis'));
        
        document.getElementById('modalAddEdit').style.display = 'block';
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi tampilan kelas saat load
        const initialJenis = document.getElementById('jenis_anggota') ? document.getElementById('jenis_anggota').value : 'siswa';
        toggleKelas(initialJenis);

        const addButton = document.querySelector('[data-modal-target="modalAddEdit"]');
        if (addButton) {
            addButton.addEventListener('click', function() {
                document.getElementById('modalTitle').innerText = 'Tambah Anggota Baru';
                document.getElementById('formAction').value = 'add';
                document.getElementById('anggotaId').value = '';
                document.getElementById('nama').value = '';
                document.getElementById('jenis_anggota').value = 'siswa';
                document.getElementById('kelas').value = '';
                toggleKelas('siswa');
            });
        }
    });
</script>

<?php
echo get_footer();
?>
