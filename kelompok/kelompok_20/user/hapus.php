<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Memastikan halaman hanya bisa diakses melalui metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /user/laporan_saya.php');
    exit;
}

wajibLogin();

verifikasiCsrf();

$userId = $_SESSION['user_id'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    setFlash('danger', 'ID laporan tidak valid.');
    session_write_close();
    header('Location: /user/laporan_saya.php');
    exit;
}

try {
    // Mengecek data laporan dan memastikan laporan tersebut milik pengguna
    $stmtCek = $pdo->prepare("
        SELECT id, user_id, foto, status 
        FROM laporan 
        WHERE id = :id AND user_id = :user_id
    ");
    $stmtCek->execute([
        ':id'      => $id,
        ':user_id' => $userId
    ]);
    $laporan = $stmtCek->fetch();

    if (!$laporan) {
        // Jika laporan tidak ditemukan atau bukan milik pengguna.
        setFlash('danger', 'Laporan tidak ditemukan atau Anda tidak memiliki akses untuk menghapusnya.');
        session_write_close();
        header('Location: /user/laporan_saya.php');
        exit;
    }

    // Hanya laporan berstatus 'menunggu' yang boleh dihapus
    if ($laporan['status'] !== 'menunggu') {
        setFlash('danger', 'Laporan yang sedang diproses atau sudah selesai tidak dapat dihapus.');
        session_write_close();
        header('Location: /user/laporan_saya.php');
        exit;
    }

    // Hapus data berdasarkan ID, kepemilikan pengguna, dan status laporan.
    $stmtHapus = $pdo->prepare("
        DELETE FROM laporan 
        WHERE id = :id AND user_id = :user_id AND status = 'menunggu'
    ");
    $stmtHapus->execute([
        ':id'      => $id,
        ':user_id' => $userId
    ]);

    // Menghapus file foto laporan jika tersedia
    if (!empty($laporan['foto'])) {
        $pathFoto = __DIR__ . '/../assets/uploads/' . $laporan['foto'];
        if (file_exists($pathFoto) && is_file($pathFoto)) {
            unlink($pathFoto);
        }
    }

    // Notifikasi berhasil dihapus
    setFlash('success', 'Laporan berhasil dihapus.');
    session_write_close();
    header('Location: /user/laporan_saya.php');
    exit;

} catch (PDOException $e) {
    error_log("Error saat menghapus laporan: " . $e->getMessage());
    setFlash('danger', 'Terjadi kesalahan sistem saat menghapus laporan.');
    session_write_close();
    header('Location: /user/laporan_saya.php');
    exit;
}

