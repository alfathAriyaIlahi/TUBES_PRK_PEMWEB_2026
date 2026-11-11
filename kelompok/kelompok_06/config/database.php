<?php
// Pengaturan Koneksi Database
$host = "localhost";
$user = "root";       // Sesuaikan dengan setting XAMPP/Laragon
$pass = "";           // Kosongkan jika default Laragon/XAMPP
$db   = "db_flashpos";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}

// Fungsi Helper Global (Bisa dipakai semua anggota)
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Set Zona Waktu Indonesia Barat
date_default_timezone_set('Asia/Jakarta');
?>