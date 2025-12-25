/* File: perpustakaan/assets/js/script.js */

// Dark Mode Toggle Function
function toggleTheme() {
    document.body.classList.toggle('dark');
    const isDark = document.body.classList.contains('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}

// Sidebar Toggle Function
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (sidebar) {
        sidebar.classList.toggle('sidebar-collapsed');
        const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed ? 'true' : 'false');
    }
}

// Load saved sidebar state
function loadSidebarState() {
    const sidebar = document.getElementById('sidebar');
    const savedState = localStorage.getItem('sidebarCollapsed');
    
    if (sidebar && savedState === 'true') {
        sidebar.classList.add('sidebar-collapsed');
    }
}

// User Profile Dropdown Toggle
function toggleUserDropdown() {
    const dropdown = document.querySelector('.user-profile-dropdown');
    if (dropdown) {
        dropdown.classList.toggle('active');
    }
}

// Close dropdown when clicking outside
function closeDropdownOnOutsideClick(event) {
    const dropdown = document.querySelector('.user-profile-dropdown');
    const profileBtn = document.getElementById('userProfileBtn');
    
    if (dropdown && profileBtn && !dropdown.contains(event.target)) {
        dropdown.classList.remove('active');
    }
}

// Load saved theme preference
document.addEventListener('DOMContentLoaded', function() {
    // Load theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark');
    }

    // Load sidebar state
    loadSidebarState();

    // Sidebar toggle button
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    // User profile dropdown toggle
    const userProfileBtn = document.getElementById('userProfileBtn');
    if (userProfileBtn) {
        userProfileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleUserDropdown();
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', closeDropdownOnOutsideClick);

    // Fungsi untuk menangani modal
    const modals = document.querySelectorAll('.modal');
    const openModalBtns = document.querySelectorAll('[data-modal-target]');
    const closeModalBtns = document.querySelectorAll('.close-btn');

    openModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal-target');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'block';
            }
        });
    });

    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });

    window.addEventListener('click', function(event) {
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Tambahkan logika lain seperti validasi form, AJAX, atau interaksi QR Code di sini
});
