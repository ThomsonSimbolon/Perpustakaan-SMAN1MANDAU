<?php
// Path relatif dari templates/katalog/ ke shared/
$shared_path = __DIR__ . '/../shared';
require_once $shared_path . '/head.php';
require_once $shared_path . '/loader.php';
require_once $shared_path . '/confirm-modal.php';
?>
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
                    <div class='katalog-user-avatar'><?php echo htmlspecialchars($user_initials); ?></div>
                    <span class='katalog-nav-text katalog-user-name'><?php echo htmlspecialchars($username); ?></span>
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

