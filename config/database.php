<?php
// File: perpustakaan/config/database.php
class Database {
    private $host = "localhost";
    private $db_name = "perpustakaan_sman1mandau";
    private $username = "root"; // Ganti dengan user database yang sesuai
    private $password = ""; // Ganti dengan password database yang sesuai
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            if ($this->conn->connect_error) {
                die("Koneksi database gagal: " . $this->conn->connect_error);
            }
        } catch (Exception $exception) {
            echo "Koneksi database gagal: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

// Fungsi untuk memastikan user sudah login dan memiliki role yang sesuai
function check_login($required_role = null) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit;
    }

    if ($required_role && $_SESSION['role'] !== $required_role) {
        // Jika role tidak sesuai, arahkan ke halaman yang sesuai atau tampilkan error
        switch ($_SESSION['role']) {
            case 'admin':
                header("Location: ../admin/dashboard.php");
                break;
            case 'pustakawan':
                header("Location: ../pustakawan/dashboard.php");
                break;
            case 'siswa':
            case 'guru':
                header("Location: ../katalog/index.php");
                break;
            default:
                // Jika role tidak dikenal, log out
                header("Location: ../auth/logout.php");
                break;
        }
        exit;
    }
}

// Fungsi untuk memastikan user sudah login dengan role siswa atau guru (untuk katalog)
function check_login_katalog() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit;
    }
    
    // Hanya siswa dan guru yang boleh mengakses katalog
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
}

// Fungsi untuk membuat header dan sidebar (UI/UX Ketentuan)
function get_header($title, $role) {
    $menu = [
        'admin' => [
            ['link' => '../admin/dashboard.php', 'icon' => 'home', 'text' => 'Dashboard'],
            ['link' => '../admin/users.php', 'icon' => 'users', 'text' => 'Kelola User'],
        ],
        'pustakawan' => [
            ['link' => '../pustakawan/dashboard.php', 'icon' => 'home', 'text' => 'Dashboard'],
            ['link' => '../pustakawan/buku.php', 'icon' => 'book', 'text' => 'Data Buku'],
            ['link' => '../pustakawan/anggota.php', 'icon' => 'user-plus', 'text' => 'Data Anggota'],
            ['link' => '../pustakawan/peminjaman.php', 'icon' => 'arrow-up', 'text' => 'Peminjaman'],
            ['link' => '../pustakawan/pengembalian.php', 'icon' => 'arrow-down', 'text' => 'Pengembalian'],
            ['link' => '../pustakawan/laporan.php', 'icon' => 'file-text', 'text' => 'Laporan'],
        ],
    ];

    // Ambil data user dari session
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
    $user_role = isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : ucfirst($role);
    
    // Generate inisial untuk avatar (ambil 2 karakter pertama dari username)
    $initials = strtoupper(substr($username, 0, 2));
    
    // Generate role display text
    $role_display = $user_role . ' Dashboard';

    $sidebar_menu = '';
    if (isset($menu[$role])) {
        foreach ($menu[$role] as $item) {
            $active = (strpos($_SERVER['REQUEST_URI'], $item['link']) !== false) ? 'active' : '';
            $sidebar_menu .= "<li class='nav-item {$active}'><a href='{$item['link']}' data-tooltip='{$item['text']}'><i class='icon-{$item['icon']}'></i><span class='nav-text'>{$item['text']}</span></a></li>";
        }
    }

    return "
<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$title} | Perpustakaan SMAN 1 Mandau</title>
    <link rel='stylesheet' href='../assets/css/theme.css'>
    <link rel='stylesheet' href='../assets/css/style.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>
</head>
<body>
    <div class='wrapper'>
        <!-- Sidebar dengan Header Kiri di dalamnya -->
        <aside class='sidebar sidebar-expanded' id='sidebar'>
            <div class='sidebar-header'>
                <div class='sidebar-logo'>
                    <i class='fas fa-book'></i>
                    <span class='sidebar-logo-text'>Perpus SMAN 1 MD</span>
                </div>
            </div>
            <ul class='nav-links'>
                {$sidebar_menu}
                <li class='nav-item'><a href='../auth/logout.php' data-tooltip='Logout'><i class='icon-log-out'></i><span class='nav-text'>Logout</span></a></li>
            </ul>
        </aside>

        <!-- Header bagian kanan (di luar sidebar) -->
        <header class='top-header-right'>
            <button class='sidebar-toggle' id='sidebarToggle' aria-label='Toggle Sidebar'>
                <i class='fas fa-bars'></i>
            </button>
            <div class='header-right-items'>
                <button class='icon-btn' id='notificationBtn' title='Notifikasi' aria-label='Notifikasi'>
                    <i class='fas fa-bell'></i>
                </button>
                <button class='icon-btn' onclick='toggleTheme()' title='Dark Mode' aria-label='Toggle Dark Mode'>
                    <i class='fas fa-moon'></i>
                </button>
                <div class='user-profile-dropdown'>
                    <button class='user-profile-btn' id='userProfileBtn' aria-label='User Profile'>
                        <div class='user-avatar'>{$initials}</div>
                        <div class='user-info'>
                            <span class='user-name'>{$username}</span>
                            <span class='user-role'>{$role_display}</span>
                        </div>
                        <i class='fas fa-chevron-down'></i>
                    </button>
                    <div class='dropdown-menu' id='userDropdown'>
                        <a href='#' class='dropdown-item'><i class='fas fa-user'></i> Profile</a>
                        <a href='../auth/logout.php' class='dropdown-item'><i class='fas fa-sign-out-alt'></i> Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class='main-content'>
            <div class='content-area'>
    ";
}

// Fungsi untuk membuat footer
function get_footer() {
    return "
            </div>
        </main>
    </div>
    <script src='../assets/js/script.js'></script>
</body>
</html>
    ";
}

// Fungsi untuk membuat header dan footer untuk katalog (Siswa/Guru)
function get_katalog_header($title) {
    return "
<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$title} | Katalog Perpustakaan SMAN 1 Mandau</title>
    <link rel='stylesheet' href='../assets/css/theme.css'>
    <link rel='stylesheet' href='../assets/css/style.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>
</head>
<body>
    <div class='katalog-wrapper'>
        <header class='katalog-header'>
            <h1>Katalog Buku SMAN 1 Mandau</h1>
            <nav>
                <button onclick='toggleTheme()' style='padding: 8px 12px; background: rgba(2, 44, 34, 0.2); border: 1px solid rgba(2, 44, 34, 0.3); border-radius: var(--radius-sm); color: #022C22; cursor: pointer; margin-right: 10px; transition: var(--transition-fast);'>
                    <i class='fas fa-moon'></i> Dark Mode
                </button>
                <a href='index.php'>Katalog</a>
                <a href='../auth/login.php'>Login Staf</a>
            </nav>
        </header>
        <div class='katalog-content'>
    ";
}

function get_katalog_footer() {
    return "
        </div>
        <footer class='katalog-footer'>
            <p>&copy; " . date('Y') . " Perpustakaan SMAN 1 Mandau</p>
        </footer>
    </div>
    <script src='../assets/js/script.js'></script>
</body>
</html>
    ";
}
?>