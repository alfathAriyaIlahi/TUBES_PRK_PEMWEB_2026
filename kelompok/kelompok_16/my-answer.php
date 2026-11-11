<?php
// my-answers.php - Riwayat jawaban yang pernah ditulis pengguna aktif
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/reply_item.php';

$stmt = $pdo->prepare(
    'SELECT r.id, r.post_id, r.user_id, r.content, r.created_at,
            u.username, u.avatar_color, u.is_guest,
            p.content AS post_content, pu.username AS post_username
     FROM replies r
     JOIN users u  ON u.id  = r.user_id
     JOIN posts p  ON p.id  = r.post_id
     JOIN users pu ON pu.id = p.user_id
     WHERE r.user_id = :user_id
     ORDER BY r.created_at DESC, r.id DESC'
);
$stmt->execute([':user_id' => $currentUser['id']]);
$answers = $stmt->fetchAll();

$pageTitle  = 'Jawaban Saya';
$activeMenu = 'answers';
include __DIR__ . '/includes/header.php';
?>

<h1 class="page-title">Jawaban Saya</h1>

<section class="feed">
    <?php if (!$answers): ?>
        <div class="empty-state">Kamu belum pernah menjawab. <a href="index.php">Lihat diskusi terbaru</a> dan bantu jawab.</div>
    <?php endif; ?>

    <?php foreach ($answers as $answer): ?>
        <article class="card answer-card">
            <a class="answer-context" href="detail-post.php?id=<?= (int) $answer['post_id'] ?>#reply-<?= (int) $answer['id'] ?>">
                Menjawab <strong><?= e($answer['post_username']) ?></strong>: <?= e(excerpt($answer['post_content'], 110)) ?>
            </a>
            <?php renderReplyItem($answer, (int) $currentUser['id'], 'answers'); ?>
        </article>
    <?php endforeach; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
