<?php
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Metode request tidak diizinkan. Hanya menerima GET.'
    ]);
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
    $jenis = isset($_GET['jenis']) ? trim($_GET['jenis']) : '';

    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) {
        $page = 1;
    }
    $offset = ($page - 1) * $limit;

    $where = " WHERE l.is_public = 1";
    $params = [];

    if ($search !== '') {
        $where .= " AND (l.judul LIKE :s1 OR l.isi LIKE :s2 OR l.lokasi LIKE :s3)";
        $searchTerm = '%' . $search . '%';
        $params[':s1'] = $searchTerm;
        $params[':s2'] = $searchTerm;
        $params[':s3'] = $searchTerm;
    }

    if ($kategori !== '' && is_numeric($kategori)) {
        $where .= " AND l.kategori_id = :kategori";
        $params[':kategori'] = (int)$kategori;
    }

    if ($jenis !== '' && in_array($jenis, ['pengaduan', 'aspirasi'])) {
        $where .= " AND l.jenis = :jenis";
        $params[':jenis'] = $jenis;
    }

    $countSql = "SELECT COUNT(*) FROM laporan l" . $where;
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total_data = (int)$countStmt->fetchColumn();
    $total_pages = $total_data > 0 ? (int)ceil($total_data / $limit) : 1;

    $sql = "
        SELECT l.id, l.judul, l.isi, l.lokasi, l.jenis, l.status, l.created_at,
               k.nama_kategori,
               u.nama AS nama_pelapor,
               (
                   SELECT isi_tanggapan 
                   FROM tanggapan 
                   WHERE laporan_id = l.id 
                   ORDER BY created_at DESC 
                   LIMIT 1
               ) AS tanggapan_terakhir
        FROM laporan l
        JOIN users u ON l.user_id = u.id
        LEFT JOIN kategori k ON l.kategori_id = k.id
        " . $where . "
        ORDER BY l.created_at DESC, l.id DESC
        LIMIT " . (int)$limit . " OFFSET " . (int)$offset . "
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        'status'     => 'success',
        'message'    => 'Data berhasil diambil',
        'data'       => $laporan,
        'pagination' => [
            'total_data'   => $total_data,
            'total_pages'  => $total_pages,
            'current_page' => $page,
            'limit'        => $limit
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Terjadi kesalahan pada server saat mengambil data laporan.'
    ]);
    exit;
}
