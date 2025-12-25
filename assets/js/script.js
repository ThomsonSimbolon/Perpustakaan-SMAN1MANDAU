/* File: perpustakaan/assets/js/script.js */

// Dark Mode Toggle Function
function toggleTheme() {
  const isDark = document.body.classList.contains("dark");
  const darkModeButtons = document.querySelectorAll(
    ".katalog-nav-icon-btn[onclick='toggleTheme()'], .icon-btn[onclick='toggleTheme()']"
  );

  if (isDark) {
    // Switch to light mode
    document.documentElement.classList.remove("dark");
    document.body.classList.remove("dark");
    localStorage.setItem("theme", "light");

    // Update icon
    darkModeButtons.forEach((btn) => {
      const icon = btn.querySelector("i");
      if (icon) {
        icon.className = "fas fa-moon";
      }
    });
  } else {
    // Switch to dark mode
    document.documentElement.classList.add("dark");
    document.body.classList.add("dark");
    localStorage.setItem("theme", "dark");

    // Update icon
    darkModeButtons.forEach((btn) => {
      const icon = btn.querySelector("i");
      if (icon) {
        icon.className = "fas fa-sun";
      }
    });
  }
}

// Loader Spinner Functions
function showLoader() {
  const loader = document.getElementById("pageLoader");
  if (loader) {
    loader.classList.add("active");
  }
}

function hideLoader() {
  const loader = document.getElementById("pageLoader");
  if (loader) {
    loader.classList.remove("active");
  }
}

// Custom Confirmation Modal Function
function customConfirm(message) {
  return new Promise((resolve) => {
    const modal = document.getElementById("confirmModal");
    const messageEl = document.getElementById("confirmMessage");
    const okBtn = document.getElementById("confirmOK");
    const cancelBtn = document.getElementById("confirmCancel");

    if (!modal || !messageEl || !okBtn || !cancelBtn) {
      // Fallback ke confirm bawaan jika modal tidak ada
      resolve(confirm(message));
      return;
    }

    // Set message
    messageEl.textContent = message;

    // Show modal
    modal.classList.add("active");

    // Handler untuk OK
    const handleOK = () => {
      modal.classList.remove("active");
      resolve(true);
      cleanup();
    };

    // Handler untuk Cancel
    const handleCancel = () => {
      modal.classList.remove("active");
      resolve(false);
      cleanup();
    };

    // Handler untuk ESC key
    const handleEscape = (e) => {
      if (e.key === "Escape") {
        handleCancel();
      }
    };

    // Handler untuk klik di luar modal (overlay)
    const handleOverlayClick = (e) => {
      if (e.target === modal) {
        handleCancel();
      }
    };

    // Cleanup function untuk remove event listeners
    const cleanup = () => {
      okBtn.removeEventListener("click", handleOK);
      cancelBtn.removeEventListener("click", handleCancel);
      window.removeEventListener("keydown", handleEscape);
      modal.removeEventListener("click", handleOverlayClick);
    };

    // Add event listeners
    okBtn.addEventListener("click", handleOK);
    cancelBtn.addEventListener("click", handleCancel);
    window.addEventListener("keydown", handleEscape);
    modal.addEventListener("click", handleOverlayClick);

    // Focus pada OK button
    okBtn.focus();
  });
}

// Sidebar Toggle Function (for desktop collapse/expand)
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  const mainContent = document.querySelector(".main-content");

  if (sidebar) {
    sidebar.classList.toggle("sidebar-collapsed");
    const isCollapsed = sidebar.classList.contains("sidebar-collapsed");
    localStorage.setItem("sidebarCollapsed", isCollapsed ? "true" : "false");
  }
}

// Sidebar Responsive Functions (for mobile/tablet)
function openSidebarResponsive() {
  const sidebar = document.getElementById("sidebar");
  const sidebarOverlay = document.getElementById("sidebarOverlay");

  if (sidebar && sidebarOverlay) {
    sidebar.classList.add("active");
    sidebarOverlay.classList.add("active");
    document.body.style.overflow = "hidden";
  }
}

function closeSidebarResponsive() {
  const sidebar = document.getElementById("sidebar");
  const sidebarOverlay = document.getElementById("sidebarOverlay");

  if (sidebar && sidebarOverlay) {
    sidebar.classList.remove("active");
    sidebarOverlay.classList.remove("active");
    document.body.style.overflow = "";
  }
}

function toggleSidebarResponsive() {
  const sidebar = document.getElementById("sidebar");
  if (sidebar && sidebar.classList.contains("active")) {
    closeSidebarResponsive();
  } else {
    openSidebarResponsive();
  }
}

// Check if screen is mobile/tablet
function isMobileOrTablet() {
  return window.innerWidth <= 1024;
}

// Load saved sidebar state
function loadSidebarState() {
  const sidebar = document.getElementById("sidebar");
  const savedState = localStorage.getItem("sidebarCollapsed");

  if (sidebar && savedState === "true" && !isMobileOrTablet()) {
    sidebar.classList.add("sidebar-collapsed");
  }
}

// User Profile Dropdown Toggle
function toggleUserDropdown() {
  const dropdown = document.querySelector(".user-profile-dropdown");
  if (dropdown) {
    dropdown.classList.toggle("active");
  }
}

// Close dropdown when clicking outside
function closeDropdownOnOutsideClick(event) {
  const dropdown = document.querySelector(".user-profile-dropdown");
  const profileBtn = document.getElementById("userProfileBtn");

  if (dropdown && profileBtn && !dropdown.contains(event.target)) {
    dropdown.classList.remove("active");
  }
}

// Load saved theme preference
document.addEventListener("DOMContentLoaded", function () {
  // Load theme preference (backup - sudah di-apply di head, ini untuk konsistensi)
  const savedTheme = localStorage.getItem("theme");
  const darkModeButtons = document.querySelectorAll(
    ".katalog-nav-icon-btn[onclick='toggleTheme()'], .icon-btn[onclick='toggleTheme()']"
  );

  if (savedTheme === "dark") {
    document.documentElement.classList.add("dark");
    document.body.classList.add("dark");

    // Update icon untuk dark mode
    darkModeButtons.forEach((btn) => {
      const icon = btn.querySelector("i");
      if (icon) {
        icon.className = "fas fa-sun";
      }
    });
  } else {
    document.documentElement.classList.remove("dark");
    document.body.classList.remove("dark");

    // Update icon untuk light mode
    darkModeButtons.forEach((btn) => {
      const icon = btn.querySelector("i");
      if (icon) {
        icon.className = "fas fa-moon";
      }
    });
  }

  // Load sidebar state (only for desktop)
  loadSidebarState();

  // Initialize sidebar responsive state on mobile/tablet
  const sidebar = document.getElementById("sidebar");
  if (sidebar && isMobileOrTablet()) {
    // Pastikan sidebar tersembunyi di mobile/tablet saat pertama kali load
    sidebar.classList.remove("active");
    const sidebarOverlay = document.getElementById("sidebarOverlay");
    if (sidebarOverlay) {
      sidebarOverlay.classList.remove("active");
    }
    document.body.style.overflow = "";
  }

  // Sidebar toggle button - handle both desktop collapse and mobile responsive
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebarClose = document.getElementById("sidebarClose");
  const sidebarOverlay = document.getElementById("sidebarOverlay");

  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", function() {
      if (isMobileOrTablet()) {
        toggleSidebarResponsive();
      } else {
        toggleSidebar();
      }
    });
  }

  // Sidebar close button (mobile/tablet)
  if (sidebarClose) {
    sidebarClose.addEventListener("click", function() {
      closeSidebarResponsive();
    });
  }

  // Sidebar overlay click to close
  if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", function() {
      closeSidebarResponsive();
    });
  }

  // Handle window resize - close responsive sidebar if screen becomes desktop
  window.addEventListener("resize", function() {
    if (!isMobileOrTablet() && sidebar && sidebar.classList.contains("active")) {
      closeSidebarResponsive();
    }
  });

  // User profile dropdown toggle
  const userProfileBtn = document.getElementById("userProfileBtn");
  if (userProfileBtn) {
    userProfileBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      toggleUserDropdown();
    });
  }

  // Close dropdown when clicking outside
  document.addEventListener("click", closeDropdownOnOutsideClick);

  // Fungsi untuk menangani modal
  const modals = document.querySelectorAll(".modal");
  const openModalBtns = document.querySelectorAll("[data-modal-target]");
  const closeModalBtns = document.querySelectorAll(".close-btn");

  openModalBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const modalId = this.getAttribute("data-modal-target");
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.style.display = "block";
      }
    });
  });

  closeModalBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const modal = this.closest(".modal");
      if (modal) {
        modal.style.display = "none";
      }
    });
  });

  window.addEventListener("click", function (event) {
    modals.forEach((modal) => {
      if (event.target === modal) {
        modal.style.display = "none";
      }
    });
  });

  // Pastikan loader tersembunyi saat DOM ready
  hideLoader();

  // Tambahkan event listener untuk form submit (Create, Update)
  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      // Pastikan loader muncul sebelum form submit
      showLoader();
    });
  });

  // Handle link Delete dengan onclick confirm
  // Wrap semua link delete yang ada di halaman setelah DOM ready
  setTimeout(function () {
    const allLinks = document.querySelectorAll("a[href]");
    allLinks.forEach(function (link) {
      // Cek jika ini link delete
      if (link.href.includes("delete_id") || link.href.includes("delete")) {
        const onclickAttr = link.getAttribute("onclick");

        // Jika punya onclick dengan confirm, replace dengan handler baru
        if (onclickAttr && onclickAttr.includes("confirm")) {
          // Ekstrak pesan confirm dari onclick attribute
          const confirmMatch = onclickAttr.match(/confirm\(['"](.*?)['"]\)/);
          const confirmMsg = confirmMatch
            ? confirmMatch[1]
            : "Yakin ingin menghapus?";

          // Hapus onclick attribute lama
          link.removeAttribute("onclick");

          // Tambahkan event listener baru yang handle custom confirm dan loader
          link.addEventListener("click", async function (event) {
            event.preventDefault();

            // Tampilkan custom confirm dialog
            const confirmed = await customConfirm(confirmMsg);

            if (confirmed) {
              // User konfirmasi, tampilkan loader dan navigate
              showLoader();
              window.location.href = link.href;
            }
            // Jika cancel, tidak perlu melakukan apa-apa (sudah preventDefault)
          });
        } else {
          // Jika tidak ada onclick, tambahkan handler dengan custom confirm
          link.addEventListener("click", async function (event) {
            event.preventDefault();

            const confirmed = await customConfirm("Yakin ingin menghapus?");

            if (confirmed) {
              showLoader();
              window.location.href = link.href;
            }
          });
        }
      }
    });
  }, 50); // Delay kecil untuk memastikan semua elemen sudah ter-render

  // Katalog Navigation - User Dropdown
  const katalogUserBtn = document.getElementById("katalogUserBtn");
  const katalogDropdown = document.getElementById("katalogDropdown");
  const katalogUserDropdown = katalogUserBtn?.closest(".katalog-user-dropdown");

  if (katalogUserBtn && katalogUserDropdown) {
    katalogUserBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      katalogUserDropdown.classList.toggle("active");
    });

    // Close dropdown saat klik di luar
    document.addEventListener("click", function (e) {
      if (!katalogUserDropdown.contains(e.target)) {
        katalogUserDropdown.classList.remove("active");
      }
    });
  }

  // Katalog Sidebar Toggle
  const katalogSidebarToggle = document.getElementById("katalogSidebarToggle");
  const katalogSidebar = document.getElementById("katalogSidebar");
  const katalogSidebarClose = document.getElementById("katalogSidebarClose");
  const katalogSidebarOverlay = document.getElementById(
    "katalogSidebarOverlay"
  );

  function openKatalogSidebar() {
    if (katalogSidebar) {
      katalogSidebar.classList.add("active");
      if (katalogSidebarOverlay) {
        katalogSidebarOverlay.classList.add("active");
      }
      document.body.style.overflow = "hidden";
    }
  }

  function closeKatalogSidebar() {
    if (katalogSidebar) {
      katalogSidebar.classList.remove("active");
      if (katalogSidebarOverlay) {
        katalogSidebarOverlay.classList.remove("active");
      }
      document.body.style.overflow = "";
    }
  }

  if (katalogSidebarToggle) {
    katalogSidebarToggle.addEventListener("click", function () {
      openKatalogSidebar();
    });
  }

  if (katalogSidebarClose) {
    katalogSidebarClose.addEventListener("click", function () {
      closeKatalogSidebar();
    });
  }

  if (katalogSidebarOverlay) {
    katalogSidebarOverlay.addEventListener("click", function () {
      closeKatalogSidebar();
    });
  }

  // Set active state untuk katalog navigation links
  const currentPage = window.location.pathname;
  const katalogNavItems = document.querySelectorAll(".katalog-nav-item a");

  katalogNavItems.forEach((link) => {
    if (link.getAttribute("href")) {
      const href = link.getAttribute("href");
      // Check if current page matches
      if (
        currentPage.includes(href) ||
        (href === "index.php" && currentPage.includes("katalog/index.php"))
      ) {
        link.closest(".katalog-nav-item")?.classList.add("active");
      }
    }
  });

  // Tambahkan logika lain seperti validasi form, AJAX, atau interaksi QR Code di sini
});
