<?php

session_start();

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Silakan login terlebih dahulu.']);
    exit;
}

try {
    $stmt = $pdo->prepare('CALL sp_grafik_bulanan(?)');
    $stmt->execute([$_SESSION['user']['id_keluarga']]);

    $data = $stmt->fetchAll();
    $stmt->closeCursor();

    echo json_encode($data);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil data grafik.']);
}