<?php

session_start();

require_once __DIR__ . '/../config/database.php';

// Memastikan pengguna sudah login
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Silakan login terlebih dahulu.'
    ]);
    exit;
}

$idKeluarga = $_SESSION['user']['id_keluarga'];

try {

    // Mengambil ringkasan keuangan keluarga
    $stmt = $pdo->prepare('CALL sp_dashboard(?)');
    $stmt->execute([$idKeluarga]);

    $data = $stmt->fetch();
    $stmt->closeCursor();

    header('Content-Type: application/json');
    echo json_encode($data ?: [
        'total_pemasukan' => 0,
        'total_pengeluaran' => 0,
        'total_setoran' => 0,
        'saldo' => 0,
        'total_tabungan' => 0
    ]);

    exit;

} catch (PDOException $e) {

    http_response_code(500);
    header('Content-Type: application/json');

    echo json_encode([
        'error' => 'Gagal mengambil ringkasan keuangan.'
    ]);

    exit;
}