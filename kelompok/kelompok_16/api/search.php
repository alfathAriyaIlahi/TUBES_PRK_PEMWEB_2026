<?php
define('IS_API', true);
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Metode request harus GET.'], 405);
}

$keyword  = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
$category = (string) ($_GET['category'] ?? '');
$sort     = (string) ($_GET['sort'] ?? 'terbaru');

if ($category !== '') {
    $cekKategori = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE slug = :slug');
    $cekKategori->execute([':slug' => $category]);
    if ((int) $cekKategori->fetchColumn() === 0) {
        $category = '';
    }
}

try {
    $posts = fetchPosts($pdo, (int) $currentUser['id'], [
        'search'   => $keyword,
        'category' => $category,
        'sort'     => $sort,
        'limit'    => 50,
    ]);

    jsonResponse([
        'success' => true,
        'total'   => count($posts),
        'posts'   => array_map('postToJson', $posts),
    ]);
} catch (PDOException $error) {
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan pada database.'], 500);
}
