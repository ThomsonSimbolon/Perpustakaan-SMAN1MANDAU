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
    $id_anggota = isset($_POST['id_anggota']) ? (int)$_POST['id_anggota'] : 0;
    
    // Ambil data form
    $nama_lengkap = sanitize_input($_POST['nama_lengkap'] ?? '');
    $jenis_anggota = sanitize_input($_POST['jenis_anggota']);
    $id_user = !empty($_POST['id_user']) ? (int)$_POST['id_user'] : null;
    $nis = !empty($_POST['nis']) ? sanitize_input($_POST['nis']) : null;
    $nip = !empty($_POST['nip']) ? sanitize_input($_POST['nip']) : null;
    $kelas = ($jenis_anggota == 'siswa' && !empty($_POST['kelas'])) ? sanitize_input($_POST['kelas']) : null;
    $jurusan = ($jenis_anggota == 'siswa' && !empty($_POST['jurusan'])) ? sanitize_input($_POST['jurusan']) : null;
    $mata_pelajaran = ($jenis_anggota == 'guru' && !empty($_POST['mata_pelajaran'])) ? sanitize_input($_POST['mata_pelajaran']) : null;
    $jenis_kelamin = !empty($_POST['jenis_kelamin']) ? sanitize_input($_POST['jenis_kelamin']) : null;
    $no_hp = !empty($_POST['no_hp']) ? sanitize_input($_POST['no_hp']) : null;
    $email = !empty($_POST['email']) ? sanitize_input($_POST['email']) : null;
    $status_aktif = !empty($_POST['status_aktif']) ? sanitize_input($_POST['status_aktif']) : 'AKTIF';
    
    // Validasi
    $error = '';
    if (empty($nama_lengkap)) {
        $error = 'Nama lengkap wajib diisi.';
    }
    
    // Validasi NIS untuk siswa
    if ($jenis_anggota == 'siswa' && empty($nis)) {
        $error = 'NIS wajib diisi untuk siswa.';
    }
    
    // Validasi NIP untuk guru
    if ($jenis_anggota == 'guru' && empty($nip)) {
        $error = 'NIP wajib diisi untuk guru.';
    }
    
    // Validasi id_user jika dipilih (untuk add baru wajib, untuk edit opsional)
    if ($action == 'add' && empty($id_user)) {
        $error = 'User wajib dipilih untuk anggota baru.';
    } elseif (!empty($id_user)) {
        // Validasi role user sesuai jenis_anggota
        $stmt_check = $conn->prepare("SELECT role FROM users WHERE id_user = ?");
        $stmt_check->bind_param("i", $id_user);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            $user_data = $result_check->fetch_assoc();
            if (($jenis_anggota == 'siswa' && $user_data['role'] != 'siswa') ||
                ($jenis_anggota == 'guru' && $user_data['role'] != 'guru')) {
                $error = 'Role user tidak sesuai dengan jenis anggota.';
            }
        } else {
            $error = 'User tidak ditemukan.';
        }
    }
    
    if (empty($error)) {
        if ($action == 'add') {
            // Insert anggota baru
            $stmt = $conn->prepare("INSERT INTO anggota (nama_lengkap, nama, jenis_anggota, id_user, nis, nip, kelas, jurusan, mata_pelajaran, jenis_kelamin, no_hp, email, status_aktif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiissssssss", $nama_lengkap, $nama_lengkap, $jenis_anggota, $id_user, $nis, $nip, $kelas, $jurusan, $mata_pelajaran, $jenis_kelamin, $no_hp, $email, $status_aktif);
            if ($stmt->execute()) {
                $message = show_alert('success', 'Anggota berhasil ditambahkan.');
            } else {
                $message = show_alert('error', 'Gagal menambahkan anggota: ' . $stmt->error);
            }
        } elseif ($action == 'edit' && $id_anggota > 0) {
            // Update anggota existing
            $stmt = $conn->prepare("UPDATE anggota SET nama_lengkap = ?, nama = ?, jenis_anggota = ?, id_user = ?, nis = ?, nip = ?, kelas = ?, jurusan = ?, mata_pelajaran = ?, jenis_kelamin = ?, no_hp = ?, email = ?, status_aktif = ? WHERE id_anggota = ?");
            $stmt->bind_param("sssiissssssssi", $nama_lengkap, $nama_lengkap, $jenis_anggota, $id_user, $nis, $nip, $kelas, $jurusan, $mata_pelajaran, $jenis_kelamin, $no_hp, $email, $status_aktif, $id_anggota);
            if ($stmt->execute()) {
                $message = show_alert('success', 'Anggota berhasil diperbarui.');
            } else {
                $message = show_alert('error', 'Gagal memperbarui anggota: ' . $stmt->error);
            }
        }
    } else {
        $message = show_alert('error', $error);
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

// Ambil semua data anggota dengan JOIN user
$result = $conn->query("
    SELECT a.*, u.username, u.role as user_role
    FROM anggota a
    LEFT JOIN users u ON a.id_user = u.id_user
    ORDER BY a.id_anggota DESC
");
$anggota = $result->fetch_all(MYSQLI_ASSOC);

// Ambil user yang belum punya anggota (untuk dropdown)
$users_available = $conn->query("
    SELECT u.id_user, u.username, u.role
    FROM users u
    WHERE u.role IN ('siswa', 'guru')
    AND (u.id_user NOT IN (SELECT id_user FROM anggota WHERE id_user IS NOT NULL) OR u.id_user IS NULL)
    ORDER BY u.role, u.username ASC
")->fetch_all(MYSQLI_ASSOC);

echo get_header("Kelola Anggota", $_SESSION['role']);
?>

<?php echo $message; ?>

<button class="btn btn-primary" data-modal-target="modalAddEdit"><i class="icon-plus"></i> Tambah Anggota</button>

<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Lengkap</th>
                <th>Jenis</th>
                <th>NIS/NIP</th>
                <th>Kelas</th>
                <th>Email</th>
                <th>User</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($anggota as $a): 
                $nama_display = $a['nama_lengkap'] ?? $a['nama'] ?? '-';
                $nis_nip = ($a['jenis_anggota'] == 'siswa') ? ($a['nis'] ?? '-') : ($a['nip'] ?? '-');
            ?>
            <tr>
                <td><?php echo $a['id_anggota']; ?></td>
                <td><?php echo htmlspecialchars($nama_display); ?></td>
                <td><?php echo ucfirst($a['jenis_anggota']); ?></td>
                <td><?php echo htmlspecialchars($nis_nip); ?></td>
                <td><?php echo htmlspecialchars($a['kelas'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($a['email'] ?? '-'); ?></td>
                <td><?php echo $a['username'] ? htmlspecialchars($a['username']) : '<em>Belum di-link</em>'; ?></td>
                <td><span class="status-badge status-<?php echo strtolower($a['status_aktif'] ?? 'AKTIF'); ?>"><?php echo $a['status_aktif'] ?? 'AKTIF'; ?></span></td>
                <td>
                    <button class="btn btn-primary btn-sm" 
                            data-modal-target="modalAddEdit" 
                            onclick="editAnggota(<?php echo htmlspecialchars(json_encode($a), ENT_QUOTES, 'UTF-8'); ?>)"><i class="icon-edit"></i> Edit</button>
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
    <div class="modal-content" style="max-width: 800px;">
        <span class="close-btn">&times;</span>
        <h3 id="modalTitle">Tambah Anggota Baru</h3>
        <form method="POST" action="anggota.php" class="modal-form" id="formAnggota">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id_anggota" id="anggotaId">
            
            <div class="form-group">
                <label for="id_user">User <span id="userRequired" style="color: red;">*</span></label>
                <select name="id_user" id="id_user" required>
                    <option value="">-- Pilih User --</option>
                    <?php foreach ($users_available as $user): ?>
                        <option value="<?php echo $user['id_user']; ?>" data-role="<?php echo $user['role']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?> (<?php echo ucfirst($user['role']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small id="userOptional" style="display: none; color: #666;">(Opsional untuk edit data existing)</small>
            </div>
            
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap <span style="color: red;">*</span></label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" required>
            </div>
            
            <div class="form-group">
                <label for="jenis_anggota">Jenis Anggota <span style="color: red;">*</span></label>
                <select name="jenis_anggota" id="jenis_anggota" required onchange="toggleFields(this.value)">
                    <option value="siswa">Siswa</option>
                    <option value="guru">Guru</option>
                </select>
            </div>
            
            <div class="form-group" id="nisGroup">
                <label for="nis">NIS <span style="color: red;">*</span></label>
                <input type="text" name="nis" id="nis">
            </div>
            
            <div class="form-group" id="nipGroup" style="display: none;">
                <label for="nip">NIP <span style="color: red;">*</span></label>
                <input type="text" name="nip" id="nip">
            </div>
            
            <div class="form-group" id="kelasGroup">
                <label for="kelas">Kelas</label>
                <input type="text" name="kelas" id="kelas">
            </div>
            
            <div class="form-group" id="jurusanGroup">
                <label for="jurusan">Jurusan</label>
                <input type="text" name="jurusan" id="jurusan">
            </div>
            
            <div class="form-group" id="mataPelajaranGroup" style="display: none;">
                <label for="mata_pelajaran">Mata Pelajaran</label>
                <input type="text" name="mata_pelajaran" id="mata_pelajaran">
            </div>
            
            <div class="form-group">
                <label for="jenis_kelamin">Jenis Kelamin</label>
                <select name="jenis_kelamin" id="jenis_kelamin">
                    <option value="">-- Pilih --</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="no_hp">No. HP</label>
                <input type="text" name="no_hp" id="no_hp">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email">
            </div>
            
            <div class="form-group">
                <label for="status_aktif">Status Aktif</label>
                <select name="status_aktif" id="status_aktif">
                    <option value="AKTIF">AKTIF</option>
                    <option value="NONAKTIF">NONAKTIF</option>
                </select>
            </div>
            
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleFields(jenis) {
        const nisGroup = document.getElementById('nisGroup');
        const nipGroup = document.getElementById('nipGroup');
        const kelasGroup = document.getElementById('kelasGroup');
        const jurusanGroup = document.getElementById('jurusanGroup');
        const mataPelajaranGroup = document.getElementById('mataPelajaranGroup');
        const nisField = document.getElementById('nis');
        const nipField = document.getElementById('nip');
        
        if (jenis === 'siswa') {
            // Tampilkan field siswa
            nisGroup.style.display = 'block';
            kelasGroup.style.display = 'block';
            jurusanGroup.style.display = 'block';
            // Sembunyikan field guru
            nipGroup.style.display = 'none';
            mataPelajaranGroup.style.display = 'none';
            // Set required
            nisField.setAttribute('required', 'required');
            nipField.removeAttribute('required');
            nipField.value = '';
        } else {
            // Tampilkan field guru
            nipGroup.style.display = 'block';
            mataPelajaranGroup.style.display = 'block';
            // Sembunyikan field siswa
            nisGroup.style.display = 'none';
            kelasGroup.style.display = 'none';
            jurusanGroup.style.display = 'none';
            // Set required
            nipField.setAttribute('required', 'required');
            nisField.removeAttribute('required');
            nisField.value = '';
        }
    }

    function editAnggota(data) {
        // Parse JSON jika data adalah string
        if (typeof data === 'string') {
            data = JSON.parse(data);
        }
        
        document.getElementById('modalTitle').innerText = 'Edit Anggota';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('anggotaId').value = data.id_anggota || '';
        document.getElementById('nama_lengkap').value = data.nama_lengkap || data.nama || '';
        document.getElementById('jenis_anggota').value = data.jenis_anggota || 'siswa';
        document.getElementById('id_user').value = data.id_user || '';
        document.getElementById('nis').value = data.nis || '';
        document.getElementById('nip').value = data.nip || '';
        document.getElementById('kelas').value = data.kelas || '';
        document.getElementById('jurusan').value = data.jurusan || '';
        document.getElementById('mata_pelajaran').value = data.mata_pelajaran || '';
        document.getElementById('jenis_kelamin').value = data.jenis_kelamin || '';
        document.getElementById('no_hp').value = data.no_hp || '';
        document.getElementById('email').value = data.email || '';
        document.getElementById('status_aktif').value = data.status_aktif || 'AKTIF';
        
        // User optional untuk edit
        document.getElementById('userRequired').style.display = 'none';
        document.getElementById('userOptional').style.display = 'inline';
        document.getElementById('id_user').removeAttribute('required');
        
        toggleFields(data.jenis_anggota || 'siswa');
        
        document.getElementById('modalAddEdit').style.display = 'block';
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi tampilan saat load
        const initialJenis = document.getElementById('jenis_anggota') ? document.getElementById('jenis_anggota').value : 'siswa';
        toggleFields(initialJenis);

        const addButton = document.querySelector('[data-modal-target="modalAddEdit"]');
        if (addButton) {
            addButton.addEventListener('click', function() {
                document.getElementById('modalTitle').innerText = 'Tambah Anggota Baru';
                document.getElementById('formAction').value = 'add';
                document.getElementById('anggotaId').value = '';
                document.getElementById('nama_lengkap').value = '';
                document.getElementById('jenis_anggota').value = 'siswa';
                document.getElementById('id_user').value = '';
                document.getElementById('nis').value = '';
                document.getElementById('nip').value = '';
                document.getElementById('kelas').value = '';
                document.getElementById('jurusan').value = '';
                document.getElementById('mata_pelajaran').value = '';
                document.getElementById('jenis_kelamin').value = '';
                document.getElementById('no_hp').value = '';
                document.getElementById('email').value = '';
                document.getElementById('status_aktif').value = 'AKTIF';
                
                // User required untuk add
                document.getElementById('userRequired').style.display = 'inline';
                document.getElementById('userOptional').style.display = 'none';
                document.getElementById('id_user').setAttribute('required', 'required');
                
                toggleFields('siswa');
            });
        }
        
        // Validasi id_user harus sesuai jenis_anggota saat form submit
        document.getElementById('formAnggota').addEventListener('submit', function(e) {
            const idUser = document.getElementById('id_user').value;
            const jenisAnggota = document.getElementById('jenis_anggota').value;
            const userSelect = document.getElementById('id_user');
            const selectedOption = userSelect.options[userSelect.selectedIndex];
            
            if (idUser && selectedOption) {
                const userRole = selectedOption.getAttribute('data-role');
                if ((jenisAnggota === 'siswa' && userRole !== 'siswa') || 
                    (jenisAnggota === 'guru' && userRole !== 'guru')) {
                    e.preventDefault();
                    alert('Role user harus sesuai dengan jenis anggota!');
                    return false;
                }
            }
        });
    });
</script>

<?php
echo get_footer();
?>

