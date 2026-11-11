<?php
/**
 * File: backend/koneksi.php
 * Konfigurasi koneksi database MySQL untuk Sistem Informasi KlikObat (Kelompok 22)
 */

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'klikobat';
$db_port = 3306;

mysqli_report(MYSQLI_REPORT_OFF);

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

// Alias variabel $koneksi bila ada anggota yang memakai nama variabel $koneksi
$koneksi = $conn;

if (!$conn) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Gagal terhubung ke database: ' . mysqli_connect_error()
    ]);
    exit;
}

mysqli_set_charset($conn, 'utf8mb4');