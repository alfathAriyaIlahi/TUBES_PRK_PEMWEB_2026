<?php

function renderPostCard(array $post, int $viewerId, bool $detail = false, string $returnTo = 'index.php'): void
{
    $isOwner  = ((int) $post['user_id'] === $viewerId);
    $isLiked  = ((int) $post['liked_by_me'] > 0);
    $postId   = (int) $post['id'];
    $detailUrl = 'detail-post.php?id=' . $postId;
    ?>
    <article class="post-card" id="post-<?= $postId ?>">
        <div class="post-head">
            <?= renderAvatar($post['username'], $post['avatar_color']) ?>
            <div class="post-meta">
                <a class="post-user" href="profile.php?id=<?= (int) $post['user_id'] ?>"><?= e($post['username']) ?></a>
                <?php if ((int) $post['is_guest'] === 1): ?><span class="badge">Tamu</span><?php endif; ?>
                <span class="post-time">&middot; <?= e(timeAgo($post['created_at'])) ?><?= !empty($post['updated_at']) ? ' (diedit)' : '' ?></span>
            </div>
            <a class="category-tag" href="index.php?kategori=<?= e($post['category_slug']) ?>"><?= e($post['category_name']) ?></a>
        </div>

        <?php if ($detail): ?>
            <p class="post-content"><?= nl2br(e($post['content'])) ?></p>
        <?php else: ?>
            <a class="post-link" href="<?= $detailUrl ?>">
                <p class="post-content clamp"><?= nl2br(e($post['content'])) ?></p>
            </a>
        <?php endif; ?>

        <div class="post-actions">
            <button type="button" class="action-button like-btn <?= $isLiked ? 'liked' : '' ?>"
                    data-post-id="<?= $postId ?>" aria-pressed="<?= $isLiked ? 'true' : 'false' ?>" aria-label="Suka">
                <?= icon('heart') ?> <span class="like-count"><?= (int) $post['like_count'] ?></span>
            </button>
            <a class="action-button" href="<?= $detailUrl ?>#jawaban" aria-label="Jawaban">
                <?= icon('message') ?> <span><?= (int) $post['reply_count'] ?></span>
            </a>

            <?php if ($isOwner): ?>
                <span class="action-spacer"></span>
                <a class="action-button" href="edit-post.php?id=<?= $postId ?>" aria-label="Edit postingan"><?= icon('edit') ?> <span>Edit</span></a>
                <form method="post" action="process/delete_post.php" class="js-confirm" data-confirm="Hapus postingan ini beserta semua jawabannya?">
                    <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">
                    <input type="hidden" name="post_id" value="<?= $postId ?>">
                    <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                    <button type="submit" class="action-button danger" aria-label="Hapus postingan"><?= icon('trash') ?> <span>Hapus</span></button>
                </form>
            <?php endif; ?>
        </div>
    </article>
    <?php
}
