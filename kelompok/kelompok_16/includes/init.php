<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/functions.php';

try {
    $pdo = getDatabase();
    $currentUser = getCurrentUser($pdo);
} catch (PDOException $error) {
    if (defined('IS_API')) {
        jsonResponse(['success' => false, 'message' => 'Website belum bisa terhubung ke database. Pastikan MySQL sudah aktif dan pengaturan database sudah benar.'], 500);
    }
    http_response_code(500);
    echo '<h1>Database tidak dapat terhubung</h1>';
    echo '<p>Maaf, website belum bisa terhubung ke database. Silakan pastikan MySQL sudah aktif dan database <b>ruang_hukum</b> sudah tersedia. Jika masih mengalami masalah, periksa kembali pengaturan koneksi database pada file <code>config/database.php</code>.</p>';
    exit;
}
