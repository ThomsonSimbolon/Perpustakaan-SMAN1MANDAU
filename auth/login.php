<?php
// File: perpustakaan/auth/login.php
// Halaman Login
require_once '../config/database.php';

// Cek apakah sudah login
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi.";
    } else {
        $db = new Database();
        $conn = $db->getConnection();

        // Query untuk mencari user
        $stmt = $conn->prepare("SELECT id_user, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verifikasi password menggunakan password_verify()
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect ke dashboard sesuai role
                switch ($user['role']) {
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
                        header("Location: ../index.php");
                        break;
                }
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Username tidak ditemukan.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMAN 1 Mandau Library</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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

<body class="login-body">
    <div class="login-container">
        <h2>Sistem Informasi Perpustakaan</h2>
        <h3>SMAN 1 Mandau</h3>
        <form method="POST" action="">
            <?php if ($error): ?>
            <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>
    <script>
    // Load saved theme preference (backup - sudah di-apply di head, ini untuk konsistensi)
    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
            document.body.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
            document.body.classList.remove('dark');
        }
    });
    </script>
</body>

</html>