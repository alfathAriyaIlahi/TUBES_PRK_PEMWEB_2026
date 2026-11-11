<?php

require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php');
}
if (!isCsrfValid($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Token keamanan tidak valid. Coba lagi.');
    redirect('../index.php');
}

$postId     = (int) ($_POST['post_id'] ?? 0);
$content    = trim((string) ($_POST['content'] ?? ''));
$categoryId = (int) ($_POST['category_id'] ?? 0);
$panjang    = mb_strlen($content);

$cek = $pdo->prepare('SELECT user_id FROM posts WHERE id = :id');
$cek->execute([':id' => $postId]);
$post = $cek->fetch();

if (!$post || (int) $post['user_id'] !== (int) $currentUser['id']) {
    setFlash('error', 'Kamu tidak berhak mengubah postingan ini.');
    redirect('../index.php');
}

if ($panjang < 10 || $panjang > 1000) {
    setFlash('error', 'Isi postingan harus 10 sampai 1000 karakter.');
    redirect('../edit-post.php?id=' . $postId);
}

$cekKategori = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = :id');
$cekKategori->execute([':id' => $categoryId]);
if ((int) $cekKategori->fetchColumn() === 0) {
    setFlash('error', 'Pilih kategori yang valid.');
    redirect('../edit-post.php?id=' . $postId);
}

$ubah = $pdo->prepare(
    'UPDATE posts SET content = :content, category_id = :category_id, updated_at = NOW()
    WHERE id = :id AND user_id = :user_id'
);
$ubah->execute([
    ':content'     => $content,
    ':category_id' => $categoryId,
    ':id'          => $postId,
    ':user_id'     => $currentUser['id'],
]);

setFlash('success', 'Postingan berhasil diperbarui.');
redirect('../detail-post.php?id=' . $postId);
