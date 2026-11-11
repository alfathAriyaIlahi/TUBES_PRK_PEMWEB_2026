<?php
// Memulai session jika belum aktif (Persiapan untuk Anggota 2)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// Mengambil data pengguna dari session login
$nama_user = $_SESSION['nama_lengkap'] ?? 'Pengguna';
$role_user = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'FlashPOS — Sistem Kasir & Split Bill' ?></title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- CSS Khusus Cetak Bukti Transaksi -->
    <link rel="stylesheet" href="assets/css/print.css">
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-slate-900 selection:text-white">

<!-- Wrapper Utama -->
<div class="flex h-screen overflow-hidden">
