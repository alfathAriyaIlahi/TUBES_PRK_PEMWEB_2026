<?php

// Fungsi untuk menampilkan satu jawaban.
// $from = 'detail' atau 'answers' (menentukan tujuan setelah hapus)

function renderReplyItem(array $reply, int $viewerId, string $from = 'detail'): void
{
    $isOwner = ((int) $reply['user_id'] === $viewerId);
    $replyId = (int) $reply['id'];
    ?>
    <div class="reply-item" id="reply-<?= $replyId ?>">
        <?= renderAvatar($reply['username'], $reply['avatar_color'], 'sm') ?>
        <div class="reply-body">
            <div class="reply-meta">
                <a class="post-user" href="profile.php?id=<?= (int) $reply['user_id'] ?>"><?= e($reply['username']) ?></a>
                <?php if ((int) $reply['is_guest'] === 1): ?><span class="badge">Tamu</span><?php endif; ?>
                <span class="post-time">&middot; <?= e(timeAgo($reply['created_at'])) ?></span>
            </div>
            <p class="reply-text"><?= nl2br(e($reply['content'])) ?></p>
            <?php if ($isOwner): ?>
                <form method="post" action="process/delete_reply.php" class="js-confirm" data-confirm="Hapus jawaban ini?">
                    <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">
                    <input type="hidden" name="reply_id" value="<?= $replyId ?>">
                    <input type="hidden" name="from" value="<?= e($from) ?>">
                    <button type="submit" class="action-button danger small"><?= icon('trash') ?> <span>Hapus</span></button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
