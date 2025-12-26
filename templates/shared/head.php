<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title><?php echo htmlspecialchars($title); ?> | Perpustakaan SMAN 1 Mandau</title>
    <link rel='stylesheet' href='<?php echo $base_path; ?>/css/theme.css'>
    <link rel='stylesheet' href='<?php echo $base_path; ?>/css/style.css'>
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

