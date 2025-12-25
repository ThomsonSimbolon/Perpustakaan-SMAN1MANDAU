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

    $sidebar_menu = '';
    if (isset($menu[$role])) {
        foreach ($menu[$role] as $item) {
            $active = (strpos($_SERVER['REQUEST_URI'], $item['link']) !== false) ? 'active' : '';
            $sidebar_menu .= "<li class='nav-item {$active}'><a href='{$item['link']}'><i class='icon-{$item['icon']}'></i> {$item['text']}</a></li>";
        }
    }

    return "
<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$title} | Perpustakaan SMAN 1 Mandau</title>
    <link rel='stylesheet' href='../assets/css/style.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>
</head>
<body>
    <div class='wrapper'>
        <aside class='sidebar'>
            <div class='sidebar-header'>
                <h3>Perpus SMAN 1 MD</h3>
                <p>Role: " . ucfirst($role) . "</p>
            </div>
            <ul class='nav-links'>
                {$sidebar_menu}
                <li class='nav-item'><a href='../auth/logout.php'><i class='icon-log-out'></i> Logout</a></li>
            </ul>
        </aside>
        <main class='main-content'>
            <header class='main-header'>
                <h1>{$title}</h1>
            </header>
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
    <link rel='stylesheet' href='../assets/css/style.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>
</head>
<body>
    <div class='katalog-wrapper'>
        <header class='katalog-header'>
            <h1>Katalog Buku SMAN 1 Mandau</h1>
            <nav>
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