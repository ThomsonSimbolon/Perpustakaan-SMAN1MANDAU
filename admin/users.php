<?php
// File: perpustakaan/admin/users.php
require_once '../config/database.php';
require_once '../config/functions.php';

check_login('admin');

$db = new Database();
$conn = $db->getConnection();
$message = '';

// --- Logika CRUD User ---

// Tambah/Edit User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = sanitize_input($_POST['action']);
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);
    $role = sanitize_input($_POST['role']);
    $id_user = isset($_POST['id_user']) ? (int)$_POST['id_user'] : 0;

    if ($action == 'add') {
        // Validasi password wajib diisi saat tambah user
        if (empty($password)) {
            $message = show_alert('error', 'Password wajib diisi.');
        } else {
            // Cek apakah username sudah ada
            $stmt_check = $conn->prepare("SELECT id_user FROM users WHERE username = ?");
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $message = show_alert('error', 'Username sudah digunakan.');
            } else {
                // Hash password menggunakan password_hash() dengan PASSWORD_DEFAULT
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $password_hash, $role);
                if ($stmt->execute()) {
                    $message = show_alert('success', 'Pengguna berhasil ditambahkan.');
                } else {
                    $message = show_alert('error', 'Gagal menambahkan pengguna: ' . $stmt->error);
                }
            }
        }
    } elseif ($action == 'edit' && $id_user > 0) {
        $sql = "UPDATE users SET username = ?, role = ?";
        $params = [$username, $role];
        $types = "ss";

        if (!empty($password)) {
            // Hash password baru menggunakan password_hash() dengan PASSWORD_DEFAULT
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password = ?";
            $params[] = $password_hash;
            $types .= "s";
        }

        $sql .= " WHERE id_user = ?";
        $params[] = $id_user;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $message = show_alert('success', 'Pengguna berhasil diperbarui.');
        } else {
            $message = show_alert('error', 'Gagal memperbarui pengguna: ' . $stmt->error);
        }
    }
}

// Hapus User
if (isset($_GET['delete_id'])) {
    $id_user = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    if ($stmt->execute()) {
        $message = show_alert('success', 'Pengguna berhasil dihapus.');
    } else {
        $message = show_alert('error', 'Gagal menghapus pengguna: ' . $stmt->error);
    }
    // Redirect untuk menghilangkan parameter GET
    header("Location: users.php");
    exit;
}

// Ambil semua data user
$result = $conn->query("SELECT * FROM users ORDER BY id_user DESC");
$users = $result->fetch_all(MYSQLI_ASSOC);

echo get_header("Kelola Pengguna", $_SESSION['role']);
?>

<?php echo $message; ?>

<button class="btn btn-primary" data-modal-target="modalAddEdit"><i class="icon-plus"></i> Tambah Pengguna</button>

<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['id_user']; ?></td>
                <td><?php echo $user['username']; ?></td>
                <td><?php echo ucfirst($user['role']); ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" data-modal-target="modalAddEdit"
                        data-id="<?php echo $user['id_user']; ?>" data-username="<?php echo $user['username']; ?>"
                        data-role="<?php echo $user['role']; ?>" onclick="editUser(this)"><i class="icon-edit"></i>
                        Edit</button>
                    <a href="users.php?delete_id=<?php echo $user['id_user']; ?>" class="btn btn-danger btn-sm"><i class="icon-trash"></i>
                        Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah/Edit User -->
<div id="modalAddEdit" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3 id="modalTitle">Tambah Pengguna Baru</h3>
        <form method="POST" action="users.php" class="modal-form">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id_user" id="userId">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password <span id="passwordRequired" style="color: red;">*</span><span
                        id="passwordOptional">(Kosongkan jika tidak diubah)</span></label>
                <input type="password" name="password" id="password">
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" id="role" required>
                    <option value="admin">Admin</option>
                    <option value="pustakawan">Pustakawan</option>
                    <option value="siswa">Siswa</option>
                    <option value="guru">Guru</option>
                </select>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
// Fungsi untuk mengisi form modal saat edit
function editUser(button) {
    document.getElementById('modalTitle').innerText = 'Edit Pengguna';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('userId').value = button.getAttribute('data-id');
    document.getElementById('username').value = button.getAttribute('data-username');
    document.getElementById('role').value = button.getAttribute('data-role');
    document.getElementById('password').value = ''; // Kosongkan password saat edit
    document.getElementById('password').required = false; // Password tidak wajib saat edit
    document.getElementById('passwordRequired').style.display = 'none';
    document.getElementById('passwordOptional').style.display = 'inline';

    // Tampilkan modal
    document.getElementById('modalAddEdit').style.display = 'block';
}

// Reset form saat tombol tambah diklik
document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.querySelector('[data-modal-target="modalAddEdit"]');
    if (addButton) {
        addButton.addEventListener('click', function() {
            document.getElementById('modalTitle').innerText = 'Tambah Pengguna Baru';
            document.getElementById('formAction').value = 'add';
            document.getElementById('userId').value = '';
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
            document.getElementById('password').required = true; // Password wajib saat tambah
            document.getElementById('passwordRequired').style.display = 'inline';
            document.getElementById('passwordOptional').style.display = 'none';
            document.getElementById('role').value = 'siswa'; // Default role
        });
    }
});
</script>

<?php
echo get_footer();
?>