<?php

// Like dan unlike satu postingan (toggle). Dipanggil lewat fetch() dari JavaScript.
define('IS_API', true);
require_once __DIR__ . '/../includes/init.php';

requirePostWithCsrf();

$input  = readJsonInput();
$postId = (int) ($input['post_id'] ?? 0);

if ($postId <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID postingan tidak valid.'], 422);
}

try {
    // memastikan postingan ada
    $cekPost = $pdo->prepare('SELECT id FROM posts WHERE id = :id');
    $cekPost->execute([':id' => $postId]);
    if (!$cekPost->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Postingan tidak ditemukan.'], 404);
    }

    // mengecek apakah pengguna sudah pernah like
    $cekLike = $pdo->prepare('SELECT id FROM likes WHERE post_id = :post_id AND user_id = :user_id');
    $cekLike->execute([':post_id' => $postId, ':user_id' => $currentUser['id']]);
    $sudahLike = $cekLike->fetch();

    // jika Sudah like pengguna bisa unlike dan jika belum like pengguna bisa tambah like.
    if ($sudahLike) {
        $aksi = $pdo->prepare('DELETE FROM likes WHERE id = :id');
        $aksi->execute([':id' => $sudahLike['id']]);
        $isLiked = false;
    } else {
        $aksi = $pdo->prepare('INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)');
        $aksi->execute([':post_id' => $postId, ':user_id' => $currentUser['id']]);
        $isLiked = true;
    }

    // menghitung ulang jumlah like
    $hitung = $pdo->prepare('SELECT COUNT(*) FROM likes WHERE post_id = :post_id');
    $hitung->execute([':post_id' => $postId]);

    jsonResponse([
        'success'    => true,
        'liked'      => $isLiked,
        'like_count' => (int) $hitung->fetchColumn(),
    ]);
} catch (PDOException $error) {
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan pada database.'], 500);
}
