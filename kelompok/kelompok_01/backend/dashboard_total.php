<?php

session_start();

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Silakan login terlebih dahulu.']);
    exit;
}

$idKeluarga = $_SESSION['user']['id_keluarga'];

try {
    $stmt = $pdo->prepare(
        'SELECT
            COALESCE(SUM(total_pemasukan), 0)   AS total_pemasukan,
            COALESCE(SUM(total_pengeluaran), 0) AS total_pengeluaran,
            COALESCE(SUM(total_setoran), 0)     AS total_setoran,
            COALESCE(SUM(total_pemasukan - total_pengeluaran - total_setoran), 0) AS saldo,
            (SELECT COALESCE(SUM(s.jumlah), 0)
               FROM setoran_tabungan s
               JOIN target_tabungan t ON t.id_target = s.id_target
              WHERE t.id_keluarga = ?) AS total_tabungan
         FROM v_ringkasan_bulanan
         WHERE id_keluarga = ?'
    );
    $stmt->execute([$idKeluarga, $idKeluarga]);

    echo json_encode($stmt->fetch());
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil ringkasan keuangan.']);
}