<?php
// File: perpustakaan/profile/index.php
// Halaman Profil untuk Siswa dan Guru
require_once '../config/database.php';
require_once '../config/functions.php';

// Cek login - hanya siswa dan guru yang bisa akses profil
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Hanya siswa dan guru yang bisa akses halaman profil ini
if ($_SESSION['role'] !== 'siswa' && $_SESSION['role'] !== 'guru') {
    // Redirect sesuai role
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: ../admin/dashboard.php");
            break;
        case 'pustakawan':
            header("Location: ../pustakawan/dashboard.php");
            break;
        default:
            header("Location: ../auth/logout.php");
            break;
    }
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Ambil data user dan anggota
$id_user = $_SESSION['user_id'];
$sql = "SELECT u.id_user, u.username, u.role,
               a.id_anggota, a.nama_lengkap, a.nama, a.jenis_anggota,
               a.nis, a.nip, a.kelas, a.jurusan, a.mata_pelajaran,
               a.jenis_kelamin, a.no_hp, a.email, a.status_aktif
        FROM users u
        LEFT JOIN anggota a ON a.id_user = u.id_user
        WHERE u.id_user = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // User tidak ditemukan
    header("Location: ../auth/logout.php");
    exit;
}

$data = $result->fetch_assoc();

// Validasi: hanya bisa akses profil sendiri
if ($data['id_user'] != $_SESSION['user_id']) {
    header("Location: ../auth/logout.php");
    exit;
}

// Tentukan nama yang akan ditampilkan (fallback ke nama jika nama_lengkap belum ada)
$nama_display = $data['nama_lengkap'] ?? $data['nama'] ?? $data['username'];

// Tentukan header berdasarkan role
if ($_SESSION['role'] == 'siswa') {
    echo get_katalog_header("Profil Saya");
} else {
    echo get_katalog_header("Profil Saya");
}
?>
<link rel="stylesheet" href="../assets/css/profile.css">

<div class="profile-container">
    <div class="profile-card">
        <div class="profile-header">
            <h2>Profil Saya</h2>
            <span class="role-badge"><?php echo ucfirst($_SESSION['role']); ?></span>
        </div>

        <?php if ($data['id_anggota']): ?>
        <!-- Data Diri Lengkap -->
        <div class="profile-section">
            <h3>Informasi Dasar</h3>
            <div class="profile-info">
                <div class="profile-info-label">Nama Lengkap:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($nama_display); ?></div>
            </div>

            <div class="profile-info">
                <div class="profile-info-label">Username:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($data['username']); ?></div>
            </div>

            <?php if ($data['jenis_anggota'] == 'siswa'): ?>
            <div class="profile-info">
                <div class="profile-info-label">NIS:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($data['nis'] ?? '-'); ?></div>
            </div>
            <div class="profile-info">
                <div class="profile-info-label">Kelas:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($data['kelas'] ?? '-'); ?></div>
            </div>
            <?php if ($data['jurusan']): ?>
            <div class="profile-info">
                <div class="profile-info-label">Jurusan:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($data['jurusan']); ?></div>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="profile-info">
                <div class="profile-info-label">NIP:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($data['nip'] ?? '-'); ?></div>
            </div>
            <?php if ($data['mata_pelajaran']): ?>
            <div class="profile-info">
                <div class="profile-info-label">Mata Pelajaran:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($data['mata_pelajaran']); ?></div>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <div class="profile-info">
                <div class="profile-info-label">Jenis Kelamin:</div>
                <div class="profile-info-value">
                    <?php 
                        if ($data['jenis_kelamin']) {
                            echo $data['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan';
                        } else {
                            echo '-';
                        }
                        ?>
                </div>
            </div>
        </div>

        <div class="profile-section">
            <h3 class="section-kontak">Kontak</h3>
            <div class="profile-info">
                <div class="profile-info-label">No. HP:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($data['no_hp'] ?? '-'); ?></div>
            </div>
            <div class="profile-info">
                <div class="profile-info-label">Email:</div>
                <div class="profile-info-value"><?php echo htmlspecialchars($data['email'] ?? '-'); ?></div>
            </div>
        </div>

        <div class="profile-section">
            <h3 class="section-status">Status Akun</h3>
            <div class="profile-info">
                <div class="profile-info-label">Status:</div>
                <div class="profile-info-value">
                    <span class="status-badge status-<?php echo strtolower($data['status_aktif'] ?? 'AKTIF'); ?>">
                        <?php echo $data['status_aktif'] ?? 'AKTIF'; ?>
                    </span>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Belum ada data anggota -->
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-user-slash"></i>
            </div>
            <p>
                Data diri Anda belum lengkap. Silakan hubungi pustakawan untuk mengisi data diri.
            </p>
            <p style="margin-top: 15px;">
                Username: <strong
                    style="color: var(--color-primary);"><?php echo htmlspecialchars($data['username']); ?></strong>
            </p>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="../katalog/index.php" class="back-link">Kembali ke Katalog</a>
        </div>
    </div>
</div>

<?php
echo get_katalog_footer();
?>