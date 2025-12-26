<?php
// File: perpustakaan/config/template_loader.php
// Fungsi untuk memuat template berdasarkan role

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
    
    // Render sidebar menu
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

    // Set base path untuk assets (relatif dari templates/)
    $base_path = '../assets';
    
    // Tentukan template path berdasarkan role
    $template_path = __DIR__ . '/../templates/' . $role . '/header.php';
    
    // Include template header
    if (file_exists($template_path)) {
        ob_start();
        include $template_path;
        return ob_get_clean();
    } else {
        return "<!-- Template header tidak ditemukan untuk role: {$role} -->";
    }
}

// Fungsi untuk membuat footer
function get_footer() {
    // Tentukan role dari session untuk menentukan footer yang tepat
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin';
    
    // Hanya admin dan pustakawan yang menggunakan get_footer()
    if ($role !== 'admin' && $role !== 'pustakawan') {
        $role = 'admin'; // Default fallback
    }
    
    // Set template path berdasarkan role
    $template_path = __DIR__ . '/../templates/' . $role . '/footer.php';
    
    // Include template footer
    if (file_exists($template_path)) {
        ob_start();
        include $template_path;
        return ob_get_clean();
    } else {
        return "<!-- Template footer tidak ditemukan untuk role: {$role} -->";
    }
}

// Fungsi untuk membuat header dan footer untuk katalog (Siswa/Guru)
function get_katalog_header($title) {
    // Ambil data user dari session
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
    $user_initials = strtoupper(substr($username, 0, 2));
    
    // Set base path untuk assets (relatif dari templates/)
    $base_path = '../assets';
    
    // Set template path untuk katalog
    $template_path = __DIR__ . '/../templates/katalog/header.php';
    
    // Include template header katalog
    if (file_exists($template_path)) {
        ob_start();
        include $template_path;
        return ob_get_clean();
    } else {
        return "<!-- Template katalog header tidak ditemukan -->";
    }
}

// Fungsi untuk membuat footer katalog
function get_katalog_footer() {
    // Set template path untuk katalog
    $template_path = __DIR__ . '/../templates/katalog/footer.php';
    
    // Include template footer katalog
    if (file_exists($template_path)) {
        ob_start();
        include $template_path;
        return ob_get_clean();
    } else {
        return "<!-- Template katalog footer tidak ditemukan -->";
    }
}
?>

