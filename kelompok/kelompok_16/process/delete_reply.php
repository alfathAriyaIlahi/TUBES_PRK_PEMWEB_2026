<?php
// Menghapus jawaban milik sendiri.
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php');
}

$replyId = (int) ($_POST['reply_id'] ?? 0);
$from    = ($_POST['from'] ?? '') === 'answers' ? 'answers' : 'detail';

// mengambil data jawaban untuk cek pemilik sekaligus mengetahui post asalnya
$cek = $pdo->prepare('SELECT user_id, post_id FROM replies WHERE id = :id');
$cek->execute([':id' => $replyId]);
$reply = $cek->fetch();

$tujuan = ($from === 'answers' || !$reply)
    ? '../my-answers.php'
    : '../detail-post.php?id=' . (int) $reply['post_id'] . '#jawaban';

if (!isCsrfValid($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Token keamanan tidak valid. Coba lagi.');
    redirect($tujuan);
}

// memeriksa kepemilikan
if (!$reply || (int) $reply['user_id'] !== (int) $currentUser['id']) {
    setFlash('error', 'Kamu tidak berhak menghapus jawaban ini.');
    redirect($tujuan);
}

$hapus = $pdo->prepare('DELETE FROM replies WHERE id = :id AND user_id = :user_id');
$hapus->execute([':id' => $replyId, ':user_id' => $currentUser['id']]);

setFlash('success', 'Jawaban berhasil dihapus.');
redirect($tujuan);
