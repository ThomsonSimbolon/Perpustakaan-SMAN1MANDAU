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

<style>
.profile-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.profile-card {
    background: var(--bg-card);
    padding: 40px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    margin-bottom: 20px;
}

.profile-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 25px;
    border-bottom: 2px solid var(--border-color);
    position: relative;
}

.profile-header::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 2px;
    background: var(--color-primary);
}

.profile-header h2 {
    color: var(--color-primary);
    margin-bottom: 8px;
    font-size: 2em;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.profile-header h2::before {
    content: "\f007";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    font-size: 0.9em;
}

.profile-header .role-badge {
    display: inline-block;
    padding: 6px 16px;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    color: #fff;
    border-radius: 20px;
    font-size: 0.9em;
    font-weight: 600;
    margin-top: 8px;
}

.profile-section {
    margin-top: 35px;
    padding-top: 25px;
    border-top: 1px solid var(--border-color);
}

.profile-section:first-of-type {
    margin-top: 0;
    padding-top: 0;
    border-top: none;
}

.profile-section h3 {
    color: var(--color-secondary);
    margin-bottom: 20px;
    font-size: 1.3em;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.profile-section h3::before {
    content: "\f05a";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    font-size: 0.9em;
    color: var(--color-secondary);
}

.profile-section h3.section-kontak::before {
    content: "\f0e0";
}

.profile-section h3.section-status::before {
    content: "\f058";
}

.profile-info {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 20px;
    margin-bottom: 18px;
    padding: 15px;
    background: var(--bg-elevated);
    border-radius: var(--radius-sm);
    transition: var(--transition-fast);
    border-left: 3px solid transparent;
}

.profile-info:hover {
    border-left-color: var(--color-primary);
    background: var(--bg-muted);
    transform: translateX(5px);
}

.profile-info-label {
    font-weight: 600;
    color: var(--color-primary);
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95em;
}

.profile-info-label::before {
    content: "\f00c";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    font-size: 0.8em;
    opacity: 0.7;
}

.profile-info-value {
    color: var(--text-primary);
    font-size: 1em;
    display: flex;
    align-items: center;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 30px;
    padding: 12px 24px;
    background: var(--color-primary);
    color: #022c22;
    text-decoration: none;
    border-radius: var(--radius-sm);
    transition: var(--transition-fast);
    font-weight: 600;
    font-size: 0.95em;
}

.back-link::before {
    content: "\f060";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
}

.back-link:hover {
    background: var(--color-primary-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    text-decoration: none;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.9em;
    font-weight: 600;
}

.status-badge::before {
    content: "\f058";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    font-size: 0.9em;
}

.status-aktif {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
}

.status-nonaktif {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state-icon {
    font-size: 4em;
    color: var(--text-muted);
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state p {
    color: var(--text-secondary);
    font-size: 1.1em;
    line-height: 1.6;
    margin-bottom: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-container {
        padding: 15px;
    }

    .profile-card {
        padding: 25px 20px;
    }

    .profile-header h2 {
        font-size: 1.6em;
    }

    .profile-info {
        grid-template-columns: 1fr;
        gap: 8px;
        padding: 12px;
    }

    .profile-info-label {
        margin-bottom: 5px;
    }

    .profile-section h3 {
        font-size: 1.1em;
    }
}
</style>

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