<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT judul_isu, link_sumber, status, created_at FROM lapor_hoaks WHERE status != 'pending' ORDER BY created_at DESC");
        $data = $stmt->fetchAll();
        echo json_encode(["status" => "success", "data" => $data]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak diizinkan. Gunakan POST.'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$judul = trim($data['judul_isu'] ?? '');
$link = trim($data['link_sumber'] ?? '');
$keterangan = trim($data['keterangan'] ?? '');

if ($judul === '' || $link === '' || $keterangan === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Semua kolom wajib diisi.'
    ]);
    exit;
}

if (!filter_var($link, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Link sumber tidak valid.'
    ]);
    exit;
}

try {
    $sql = "INSERT INTO lapor_hoaks
            (judul_isu, link_sumber, keterangan)
            VALUES (:judul, :link, :keterangan)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':judul' => $judul,
        ':link' => $link,
        ':keterangan' => $keterangan
    ]);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Laporan hoaks berhasil dikirim.',
        'data' => [
            'id' => $pdo->lastInsertId(),
            'judul_isu' => $judul,
            'status' => 'pending'
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menyimpan laporan.'
    ]);
}
?>
