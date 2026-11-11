<?php

require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php');
}

$tujuanBoleh = ['index.php', 'profile.php'];
$returnTo    = in_array($_POST['return_to'] ?? '', $tujuanBoleh, true) ? $_POST['return_to'] : 'index.php';

if (!isCsrfValid($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Token keamanan tidak valid. Coba lagi.');
    redirect('../' . $returnTo);
}

$postId = (int) ($_POST['post_id'] ?? 0);

$cek = $pdo->prepare('SELECT user_id FROM posts WHERE id = :id');
$cek->execute([':id' => $postId]);
$post = $cek->fetch();

if (!$post || (int) $post['user_id'] !== (int) $currentUser['id']) {
    setFlash('error', 'Kamu tidak berhak menghapus postingan ini.');
    redirect('../' . $returnTo);
}

$hapus = $pdo->prepare('DELETE FROM posts WHERE id = :id AND user_id = :user_id');
$hapus->execute([':id' => $postId, ':user_id' => $currentUser['id']]);

setFlash('success', 'Postingan berhasil dihapus.');
redirect('../' . $returnTo);
