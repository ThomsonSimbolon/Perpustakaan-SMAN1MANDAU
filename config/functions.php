<?php
// File: perpustakaan/config/functions.php

// Fungsi untuk mendapatkan jumlah baris dari sebuah tabel
function get_count($conn, $table, $condition = "") {
    $sql = "SELECT COUNT(*) as total FROM " . $table;
    if (!empty($condition)) {
        $sql .= " WHERE " . $condition;
    }
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

// Fungsi untuk membersihkan input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk menampilkan pesan alert
function show_alert($type, $message) {
    // type bisa 'success', 'error', 'warning'
    return "<div class='alert alert-{$type}'>{$message}</div>";
}

// Fungsi untuk generate QR Code (membutuhkan library qrlib.php)
function generate_qr_code($data, $filename) {
    // Path ke library qrlib.php
    include_once '../vendor/qrlib.php';
    
    // Path untuk menyimpan gambar QR Code
    $filepath = '../assets/img/qr_codes/' . $filename;
    
    // Pastikan folder penyimpanan ada
    if (!file_exists('../assets/img/qr_codes')) {
        mkdir('../assets/img/qr_codes', 0777, true);
    }

    // Parameter QR Code: data, path, level error correction, ukuran, margin
    QRcode::png($data, $filepath, QR_ECLEVEL_L, 4, 2);
    
    return $filepath;
}

// Fungsi untuk mendapatkan tanggal kembali yang seharusnya (misal 7 hari)
function get_due_date($start_date) {
    return date('Y-m-d', strtotime($start_date . ' + 7 days'));
}

// Fungsi untuk menghitung denda
function calculate_fine($return_date, $due_date) {
    $denda_per_hari = 1000; // Contoh denda Rp 1000 per hari
    
    $date1 = new DateTime($due_date);
    $date2 = new DateTime($return_date);
    
    if ($date2 > $date1) {
        $interval = $date1->diff($date2);
        $days_late = $interval->days;
        return $days_late * $denda_per_hari;
    }
    
    return 0;
}
?>
