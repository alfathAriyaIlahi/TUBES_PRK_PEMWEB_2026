<?php
// Pengaturan umum. Wajib di-require paling atas di setiap halaman.
// require_once __DIR__ . '/../config/app.php';   (sesuaikan jumlah ../ dengan lokasi file)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'Ruang Hukum');
define('ROOT_PATH', str_replace('\\', '/', realpath(__DIR__ . '/..')));

// BASE_URL otomatis: jalan di XAMPP maupun Laragon tanpa diubah.
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
define('BASE_URL', rtrim(substr(ROOT_PATH, strlen($docRoot)), '/'));

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
