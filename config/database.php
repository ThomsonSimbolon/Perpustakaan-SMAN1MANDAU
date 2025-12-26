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

// Template functions dipindahkan ke config/template_loader.php
require_once __DIR__ . '/template_loader.php';
?>