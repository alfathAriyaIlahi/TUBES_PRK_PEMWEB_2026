<?php

session_start();

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Silakan login terlebih dahulu.']);
    exit;
}

$jenis = $_GET['jenis'] ?? '';

if (!in_array($jenis, ['pemasukan', 'pengeluaran'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Jenis kategori tidak valid.']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT id_kategori, nama_kategori, jenis
         FROM kategori
         WHERE jenis = ?
         ORDER BY nama_kategori'
    );
    $stmt->execute([$jenis]);

    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    http_response_code(500);
    // Sementara untuk debugging; hapus $e->getMessage() kalau sudah selesai
    echo json_encode(['error' => 'Gagal mengambil kategori: ' . $e->getMessage()]);
}