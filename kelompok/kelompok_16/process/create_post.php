<?php

require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php');
}
if (!isCsrfValid($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Token keamanan tidak valid. Coba lagi.');
    redirect('../index.php');
}

$content    = trim((string) ($_POST['content'] ?? ''));
$categoryId = (int) ($_POST['category_id'] ?? 0);
$panjang    = mb_strlen($content);

if ($panjang < 10 || $panjang > 1000) {
    setFlash('error', 'Isi postingan harus 10 sampai 1000 karakter.');
    redirect('../index.php');
}

$cekKategori = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = :id');
$cekKategori->execute([':id' => $categoryId]);
if ((int) $cekKategori->fetchColumn() === 0) {
    setFlash('error', 'Pilih kategori yang valid.');
    redirect('../index.php');
}

$simpan = $pdo->prepare('INSERT INTO posts (user_id, category_id, content) VALUES (:user_id, :category_id, :content)');
$simpan->execute([
    ':user_id'     => $currentUser['id'],
    ':category_id' => $categoryId,
    ':content'     => $content,
]);

setFlash('success', 'Postingan berhasil dikirim.');
redirect('../index.php');
