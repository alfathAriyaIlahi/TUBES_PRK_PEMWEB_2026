<?php
// detail-post.php - Menampilkan satu postingan lengkap beserta jawabannya
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/post_card.php';
require_once __DIR__ . '/includes/reply_item.php';

$postId = (int) ($_GET['id'] ?? 0);
$posts  = $postId > 0 ? fetchPosts($pdo, (int) $currentUser['id'], ['post_id' => $postId, 'limit' => 1]) : [];

$pageTitle  = 'Detail Postingan';
$activeMenu = 'home';

// Kalau postingan nggak ketemu (id salah atau udah dihapus)
if (!$posts) {
    http_response_code(404);
    include __DIR__ . '/includes/header.php';
    echo '<div class="empty-state">Postingan tidak ditemukan atau sudah dihapus. <a href="index.php">Kembali ke beranda</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$post    = $posts[0];
$replies = fetchReplies($pdo, $postId);

include __DIR__ . '/includes/header.php';
?>

<a class="back-link" href="index.php">&larr; Kembali</a>

<?php renderPostCard($post, (int) $currentUser['id'], true, 'index.php'); ?>

<section class="card" id="jawaban">
    <h2 class="section-title">Jawaban (<span id="reply-count"><?= count($replies) ?></span>)</h2>

    <form id="reply-form" data-post-id="<?= $postId ?>" novalidate>
        <label class="sr-only" for="reply-content">Tulis jawaban</label>
        <textarea id="reply-content" name="content" rows="3" maxlength="1000"
                  placeholder="Tulis jawabanmu sebagai <?= e($currentUser['username']) ?>..."></textarea>
        <p class="form-error" id="reply-error" hidden></p>
        <div class="compose-bottom">
            <span class="char-counter" id="reply-counter">0 / 1000</span>
            <button type="submit" class="button">Kirim Jawaban</button>
        </div>
    </form>

    <div class="reply-list" id="reply-list">
        <?php foreach ($replies as $reply): ?>
            <?php renderReplyItem($reply, (int) $currentUser['id'], 'detail'); ?>
        <?php endforeach; ?>
    </div>
    <p class="muted reply-empty" id="reply-empty" <?= $replies ? 'hidden' : '' ?>>Belum ada jawaban. Bantu jawab pertanyaan ini.</p>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
