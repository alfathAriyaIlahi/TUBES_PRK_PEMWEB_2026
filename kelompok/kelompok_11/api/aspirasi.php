<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query('SELECT nama_pengirim, pesan_aspirasi, created_at FROM aspirasi ORDER BY id DESC');
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memuat daftar aspirasi']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$nama  = trim($data['nama_pengirim'] ?? '');
$email = trim($data['email'] ?? '');
$pesan = trim($data['pesan_aspirasi'] ?? '');

if ($nama === '' || $pesan === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO aspirasi (nama_pengirim, email, pesan_aspirasi) VALUES (?, ?, ?)'
    );
    $stmt->execute([$nama, $email, $pesan]);
    echo json_encode(['status' => 'success', 'message' => 'Aspirasi terkirim']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data']);
}