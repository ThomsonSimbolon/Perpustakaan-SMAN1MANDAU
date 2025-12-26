<?php
// Path relatif dari templates/pustakawan/ ke shared/
$shared_path = __DIR__ . '/../shared';
require_once $shared_path . '/head.php';
require_once $shared_path . '/loader.php';
require_once $shared_path . '/confirm-modal.php';
?>
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
            <?php echo $sidebar_menu; ?>
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
            <button class='icon-btn' onclick='toggleTheme()' title='Dark Mode' aria-label='Toggle Dark Mode'>
                <i class='fas fa-moon'></i>
            </button>
            <div class='user-profile-dropdown'>
                <button class='user-profile-btn' id='userProfileBtn' aria-label='User Profile'>
                    <div class='user-avatar'><?php echo htmlspecialchars($initials); ?></div>
                    <div class='user-info'>
                        <span class='user-name'><?php echo htmlspecialchars($username); ?></span>
                        <span class='user-role'><?php echo htmlspecialchars($role_display); ?></span>
                    </div>
                    <i class='fas fa-chevron-down'></i>
                </button>
                <div class='dropdown-menu' id='userDropdown'>
                    <?php if ($role == 'siswa' || $role == 'guru'): ?>
                        <a href='../profile/index.php' class='dropdown-item'><i class='fas fa-user'></i> Profile</a>
                    <?php endif; ?>
                    <a href='../auth/logout.php' class='dropdown-item'><i class='fas fa-sign-out-alt'></i> Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class='main-content'>
        <div class='content-area'>

