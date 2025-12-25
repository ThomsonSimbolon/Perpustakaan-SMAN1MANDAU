<?php
// File: perpustakaan/assets/database/migrate_anggota.php
// Script migrasi untuk link data anggota existing dengan user
// 
// INSTRUKSI:
// 1. Pastikan script alter_anggota.sql sudah dijalankan terlebih dahulu
// 2. Akses file ini melalui browser atau jalankan via CLI
// 3. File ini akan menampilkan form untuk mapping manual anggota dengan user

require_once '../../config/database.php';

// Cek apakah sudah login sebagai admin (untuk keamanan)
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die('Akses ditolak. Hanya admin yang bisa mengakses script migrasi ini.');
}

$db = new Database();
$conn = $db->getConnection();
$message = '';
$migration_done = false;

// Proses mapping anggota dengan user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'migrate') {
    $mapping_data = $_POST['mapping'] ?? [];
    $success_count = 0;
    $error_count = 0;
    
    foreach ($mapping_data as $id_anggota => $id_user) {
        $id_anggota = (int)$id_anggota;
        $id_user = !empty($id_user) ? (int)$id_user : null;
        
        if ($id_user > 0) {
            // Validasi: user harus ada dan role harus sesuai jenis_anggota
            $stmt_check = $conn->prepare("
                SELECT u.id_user, u.role, a.jenis_anggota 
                FROM users u, anggota a 
                WHERE u.id_user = ? AND a.id_anggota = ?
            ");
            $stmt_check->bind_param("ii", $id_user, $id_anggota);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                // Validasi role sesuai jenis_anggota
                if (($row['role'] == 'siswa' && $row['jenis_anggota'] == 'siswa') ||
                    ($row['role'] == 'guru' && $row['jenis_anggota'] == 'guru')) {
                    
                    // Update id_user di tabel anggota
                    $stmt_update = $conn->prepare("UPDATE anggota SET id_user = ? WHERE id_anggota = ?");
                    $stmt_update->bind_param("ii", $id_user, $id_anggota);
                    if ($stmt_update->execute()) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                } else {
                    $error_count++;
                }
            } else {
                $error_count++;
            }
        }
    }
    
    $message = "Migrasi selesai. Berhasil: {$success_count}, Gagal: {$error_count}";
    $migration_done = true;
}

// Ambil semua anggota yang belum di-link dengan user
$anggota_list = $conn->query("
    SELECT a.*, u.id_user as linked_user_id, u.username as linked_username 
    FROM anggota a 
    LEFT JOIN users u ON a.id_user = u.id_user 
    ORDER BY a.id_anggota ASC
")->fetch_all(MYSQLI_ASSOC);

// Ambil semua user dengan role siswa/guru yang belum punya anggota
$users_list = $conn->query("
    SELECT u.id_user, u.username, u.role 
    FROM users u 
    WHERE u.role IN ('siswa', 'guru') 
    AND u.id_user NOT IN (SELECT id_user FROM anggota WHERE id_user IS NOT NULL)
    ORDER BY u.role, u.username ASC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migrasi Data Anggota - Link dengan User</title>
    <link rel="stylesheet" href="../../assets/css/theme.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .migration-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .migration-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .migration-table th,
        .migration-table td {
            padding: 10px;
            border: 1px solid var(--border-color);
            text-align: left;
        }
        .migration-table th {
            background: var(--color-primary);
            color: #fff;
        }
        .migration-table select {
            width: 100%;
            padding: 5px;
        }
        .info-box {
            background: var(--bg-muted);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid var(--color-primary);
        }
    </style>
</head>
<body>
    <div class="migration-container">
        <h1>Migrasi Data Anggota - Link dengan User</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $migration_done ? 'success' : 'info'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <strong>Petunjuk:</strong>
            <ul>
                <li>Script ini untuk mem-link anggota existing dengan user yang sudah ada</li>
                <li>Pilih user yang sesuai untuk setiap anggota (role harus sesuai: siswa → siswa, guru → guru)</li>
                <li>Jika anggota belum punya user, biarkan kosong (bisa di-link nanti via form anggota)</li>
                <li>Setelah semua mapping selesai, klik tombol "Simpan Migrasi"</li>
            </ul>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="migrate">
            
            <table class="migration-table">
                <thead>
                    <tr>
                        <th>ID Anggota</th>
                        <th>Nama</th>
                        <th>Jenis Anggota</th>
                        <th>Kelas</th>
                        <th>User Terkait (Saat Ini)</th>
                        <th>Pilih User Baru</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($anggota_list as $anggota): ?>
                    <tr>
                        <td><?php echo $anggota['id_anggota']; ?></td>
                        <td><?php echo htmlspecialchars($anggota['nama'] ?? $anggota['nama_lengkap'] ?? '-'); ?></td>
                        <td><?php echo ucfirst($anggota['jenis_anggota']); ?></td>
                        <td><?php echo $anggota['kelas'] ?? '-'; ?></td>
                        <td>
                            <?php if ($anggota['linked_username']): ?>
                                <strong><?php echo htmlspecialchars($anggota['linked_username']); ?></strong> (ID: <?php echo $anggota['linked_user_id']; ?>)
                            <?php else: ?>
                                <em>Belum di-link</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <select name="mapping[<?php echo $anggota['id_anggota']; ?>]">
                                <option value="">-- Kosongkan / Tidak di-link --</option>
                                <?php foreach ($users_list as $user): ?>
                                    <?php if ($user['role'] == $anggota['jenis_anggota']): ?>
                                        <option value="<?php echo $user['id_user']; ?>" 
                                                <?php echo ($anggota['linked_user_id'] == $user['id_user']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['username']); ?> (<?php echo ucfirst($user['role']); ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-success">Simpan Migrasi</button>
                <a href="../../admin/dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
            </div>
        </form>
        
        <?php if (count($users_list) == 0 && count($anggota_list) > 0): ?>
            <div class="info-box" style="margin-top: 20px;">
                <strong>Catatan:</strong> Tidak ada user siswa/guru yang tersedia untuk di-link. 
                Buat user terlebih dahulu di halaman Kelola User.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
