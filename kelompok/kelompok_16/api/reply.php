<?php
// Menambah jawaban pada sebuah postingan. Dipanggil lewat fetch().
define('IS_API', true);
require_once __DIR__ . '/../includes/init.php';

requirePostWithCsrf();

$input   = readJsonInput();
$postId  = (int) ($input['post_id'] ?? 0);
$content = trim((string) ($input['content'] ?? ''));
$panjang = mb_strlen($content);

// memvalidasi backend
if ($postId <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID postingan tidak valid.'], 422);
}
if ($panjang < 3) {
    jsonResponse(['success' => false, 'message' => 'Jawaban minimal 3 karakter.'], 422);
}
if ($panjang > 1000) {
    jsonResponse(['success' => false, 'message' => 'Jawaban maksimal 1000 karakter.'], 422);
}

try {
    $cekPost = $pdo->prepare('SELECT id FROM posts WHERE id = :id');
    $cekPost->execute([':id' => $postId]);
    if (!$cekPost->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Postingan tidak ditemukan.'], 404);
    }

    $simpan = $pdo->prepare('INSERT INTO replies (post_id, user_id, content) VALUES (:post_id, :user_id, :content)');
    $simpan->execute([
        ':post_id' => $postId,
        ':user_id' => $currentUser['id'],
        ':content' => $content,
    ]);
    $replyId = (int) $pdo->lastInsertId();

    $hitung = $pdo->prepare('SELECT COUNT(*) FROM replies WHERE post_id = :post_id');
    $hitung->execute([':post_id' => $postId]);

    jsonResponse([
        'success' => true,
        'reply'   => [
            'id'           => $replyId,
            'post_id'      => $postId,
            'user_id'      => (int) $currentUser['id'],
            'username'     => $currentUser['username'],
            'avatar_color' => $currentUser['avatar_color'],
            'is_guest'     => isGuest($currentUser),
            'content'      => $content,
            'time_text'    => 'baru saja',
        ],
        'reply_count' => (int) $hitung->fetchColumn(),
    ]);
} catch (PDOException $error) {
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan pada database.'], 500);
}
