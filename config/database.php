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

    // Ambil nama file saat ini dari REQUEST_URI atau SCRIPT_NAME
    $current_uri = $_SERVER['REQUEST_URI'];
    $current_script = $_SERVER['SCRIPT_NAME'];
    
    // Ekstrak nama file dari script name (lebih reliable)
    $current_file = basename($current_script);
    
    // Jika tidak ada, coba dari URI
    if (empty($current_file) || $current_file == 'index.php') {
        $uri_path = parse_url($current_uri, PHP_URL_PATH);
        $current_file = basename($uri_path);
    }
    
    $sidebar_menu = '';
    if (isset($menu[$role])) {
        foreach ($menu[$role] as $item) {
            // Ekstrak nama file dari link (hilangkan ../ jika ada)
            $link_path = str_replace('../', '', $item['link']);
            $link_file = basename($link_path);
            
            // Bandingkan nama file untuk menentukan active
            $is_active = ($current_file === $link_file);
            
            // Fallback: cek apakah current script path mengandung link path
            if (!$is_active) {
                // Normalisasi path untuk perbandingan
                $normalized_script = str_replace('\\', '/', $current_script);
                $normalized_link = str_replace('\\', '/', $link_path);
                $is_active = (strpos($normalized_script, $normalized_link) !== false);
            }
            
            $active = $is_active ? 'active' : '';
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
    <!-- Inline script untuk apply theme SEBELUM body render (mencegah flash) -->
    <script>
        (function() {
            try {
                var savedTheme = localStorage.getItem('theme');
                if (savedTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                    document.body.classList.add('dark');
                }
            } catch (e) {
                // Jika localStorage tidak tersedia, skip
            }
        })();
    </script>
</head>
<body>
    <!-- Page Loader -->
    <div id='pageLoader' class='page-loader'>
        <div class='loader-overlay'></div>
        <div class='loader-spinner'></div>
    </div>
    
    <!-- Custom Confirmation Modal -->
    <div id='confirmModal' class='confirm-modal'>
        <div class='confirm-modal-content'>
            <div class='confirm-modal-header'>
                <i class='fas fa-exclamation-triangle'></i>
                <h3>Konfirmasi</h3>
            </div>
            <div class='confirm-modal-body'>
                <p id='confirmMessage'></p>
            </div>
            <div class='confirm-modal-footer'>
                <button class='confirm-btn confirm-btn-secondary' id='confirmCancel'>Batal</button>
                <button class='confirm-btn confirm-btn-primary' id='confirmOK'>OK</button>
            </div>
        </div>
    </div>
    <div class='wrapper'>
        <!-- Sidebar dengan Header Kiri di dalamnya -->
        <aside class='sidebar sidebar-expanded sidebar-responsive' id='sidebar'>
            <div class='sidebar-header'>
                <div class='sidebar-logo'>
                    <i class='fas fa-book'></i>
                    <span class='sidebar-logo-text'>Perpus SMAN 1 MD</span>
                </div>
                <button class='sidebar-close' id='sidebarClose' aria-label='Close Sidebar'>
                    <i class='fas fa-times'></i>
                </button>
            </div>
            <ul class='nav-links'>
                {$sidebar_menu}
                <li class='nav-item'><a href='../auth/logout.php' data-tooltip='Logout'><i class='fas fa-sign-out-alt'></i><span class='nav-text'>Logout</span></a></li>
            </ul>
        </aside>
        <div class='sidebar-overlay' id='sidebarOverlay'></div>

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
                        " . (($role == 'siswa' || $role == 'guru') ? "<a href='../profile/index.php' class='dropdown-item'><i class='fas fa-user'></i> Profile</a>" : "") . "
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
    // Ambil data user dari session
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
    $user_initials = strtoupper(substr($username, 0, 2));
    
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
    <!-- Inline script untuk apply theme SEBELUM body render (mencegah flash) -->
    <script>
        (function() {
            try {
                var savedTheme = localStorage.getItem('theme');
                if (savedTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                    document.body.classList.add('dark');
                }
            } catch (e) {
                // Jika localStorage tidak tersedia, skip
            }
        })();
    </script>
</head>
<body>
    <!-- Page Loader -->
    <div id='pageLoader' class='page-loader'>
        <div class='loader-overlay'></div>
        <div class='loader-spinner'></div>
    </div>
    
    <!-- Custom Confirmation Modal -->
    <div id='confirmModal' class='confirm-modal'>
        <div class='confirm-modal-content'>
            <div class='confirm-modal-header'>
                <i class='fas fa-exclamation-triangle'></i>
                <h3>Konfirmasi</h3>
            </div>
            <div class='confirm-modal-body'>
                <p id='confirmMessage'></p>
            </div>
            <div class='confirm-modal-footer'>
                <button class='confirm-btn confirm-btn-secondary' id='confirmCancel'>Batal</button>
                <button class='confirm-btn confirm-btn-primary' id='confirmOK'>OK</button>
            </div>
        </div>
    </div>
    <div class='katalog-wrapper'>
        <!-- Sidebar -->
        <aside class='katalog-sidebar' id='katalogSidebar'>
            <div class='katalog-sidebar-header'>
                <div class='katalog-sidebar-logo'>
                    <i class='fas fa-book'></i>
                    <span class='katalog-sidebar-logo-text'>Perpustakaan</span>
                </div>
                <button class='katalog-sidebar-close' id='katalogSidebarClose' aria-label='Close Sidebar'>
                    <i class='fas fa-times'></i>
                </button>
            </div>
            <ul class='katalog-nav-links'>
                <li class='katalog-nav-item active'>
                    <a href='index.php' data-tooltip='Katalog'>
                        <i class='fas fa-book'></i>
                        <span class='katalog-nav-text'>Katalog</span>
                    </a>
                </li>
                <li class='katalog-nav-item'>
                    <a href='../profile/index.php' data-tooltip='Profile'>
                        <i class='fas fa-user'></i>
                        <span class='katalog-nav-text'>Profile</span>
                    </a>
                </li>
                <li class='katalog-nav-item'>
                    <a href='../auth/logout.php' data-tooltip='Logout'>
                        <i class='fas fa-sign-out-alt'></i>
                        <span class='katalog-nav-text'>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>
        <div class='katalog-sidebar-overlay' id='katalogSidebarOverlay'></div>
        
        <header class='katalog-header'>
            <div class='katalog-header-left'>
                <h1>Perpustakaan</h1>
            </div>
            <nav class='katalog-nav'>
                <button class='katalog-nav-icon-btn' onclick='toggleTheme()' title='Toggle Dark Mode' aria-label='Toggle Dark Mode'>
                    <i class='fas fa-moon'></i>
                </button>
                <div class='katalog-user-dropdown katalog-user-dropdown-desktop'>
                    <button class='katalog-user-btn' id='katalogUserBtn' aria-label='User Menu'>
                        <div class='katalog-user-avatar'>{$user_initials}</div>
                        <span class='katalog-nav-text katalog-user-name'>" . htmlspecialchars($username) . "</span>
                        <i class='fas fa-chevron-down'></i>
                    </button>
                    <div class='katalog-dropdown-menu' id='katalogDropdown'>
                        <a href='../profile/index.php' class='katalog-dropdown-item'>
                            <i class='fas fa-user'></i> Profile
                        </a>
                        <a href='../auth/logout.php' class='katalog-dropdown-item'>
                            <i class='fas fa-sign-out-alt'></i> Logout
                        </a>
                    </div>
                </div>
                <button class='katalog-sidebar-toggle' id='katalogSidebarToggle' aria-label='Toggle Sidebar'>
                    <i class='fas fa-bars'></i>
                </button>
            </nav>
        </header>
        <div class='katalog-main-content'>
            <div class='katalog-content'>
    ";
}

function get_katalog_footer() {
    return "
            </div>
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