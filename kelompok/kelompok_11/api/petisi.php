<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

function kirimJson(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function ambilPetisi(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT id, judul, deskripsi, target_tanda_tangan, current_tanda_tangan, created_at
         FROM petisi WHERE id = :id'
    );
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return null;
    }
    $row['id'] = (int) $row['id'];
    $row['target_tanda_tangan'] = (int) $row['target_tanda_tangan'];
    $row['current_tanda_tangan'] = (int) $row['current_tanda_tangan'];
    return $row;
}

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if ($id === false || $id < 1) {
                kirimJson(400, ['success' => false, 'message' => 'ID petisi tidak valid.']);
            }
            $petisi = ambilPetisi($pdo, $id);
            if ($petisi === null) {
                kirimJson(404, ['success' => false, 'message' => 'Petisi tidak ditemukan.']);
            }
            kirimJson(200, ['success' => true, 'data' => $petisi]);
        }

        $stmt = $pdo->query(
            'SELECT id, judul, deskripsi, target_tanda_tangan, current_tanda_tangan, created_at
             FROM petisi ORDER BY created_at DESC, id DESC'
        );
        $daftar = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($daftar as &$p) {
            $p['id'] = (int) $p['id'];
            $p['target_tanda_tangan'] = (int) $p['target_tanda_tangan'];
            $p['current_tanda_tangan'] = (int) $p['current_tanda_tangan'];
        }
        unset($p);

        kirimJson(200, ['success' => true, 'data' => $daftar]);
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input') ?: '', true);
        if (!is_array($input) || !isset($input['id'])) {
            kirimJson(400, ['success' => false, 'message' => 'Body JSON harus berisi "id".']);
        }

        $id = filter_var($input['id'], FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            kirimJson(400, ['success' => false, 'message' => 'ID petisi tidak valid.']);
        }

        $stmt = $pdo->prepare(
            'UPDATE petisi
             SET current_tanda_tangan = current_tanda_tangan + 1
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        $petisi = ambilPetisi($pdo, $id);
        if ($petisi === null) {
            kirimJson(404, ['success' => false, 'message' => 'Petisi tidak ditemukan.']);
        }

        kirimJson(200, [
            'success' => true,
            'message' => 'Terima kasih, dukungan Anda tercatat.',
            'data'    => $petisi,
        ]);
    }

    header('Allow: GET, POST');
    kirimJson(405, ['success' => false, 'message' => 'Metode tidak diizinkan.']);

} catch (PDOException $e) {
    error_log('[petisi.php] ' . $e->getMessage());
    kirimJson(500, ['success' => false, 'message' => 'Terjadi kesalahan pada server.']);
}
