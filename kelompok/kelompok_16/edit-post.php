<?php

require_once __DIR__ . '/includes/init.php';

$postId = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT id, user_id, category_id, content FROM posts WHERE id = :id');
$stmt->execute([':id' => $postId]);
$post = $stmt->fetch();

if (!$post || (int) $post['user_id'] !== (int) $currentUser['id']) {
    setFlash('error', 'Kamu tidak berhak mengubah postingan ini.');
    redirect('index.php');
}

$categories = getCategories($pdo);
$pageTitle  = 'Edit Postingan';
$activeMenu = 'home';
include __DIR__ . '/includes/header.php';
?>

<a class="back-link" href="detail-post.php?id=<?= (int) $post['id'] ?>">&larr; Batal</a>

<section class="card">
    <h1 class="page-title">Edit Postingan</h1>
    <form id="post-form" method="post" action="process/update_post.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">
        <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">

        <label for="post-content">Isi postingan</label>
        <textarea id="post-content" name="content" rows="6" maxlength="1000"><?= e($post['content']) ?></textarea>
        <p class="form-error" id="post-error" hidden></p>

        <label for="post-category">Kategori</label>
        <select id="post-category" name="category_id">
            <option value="">Pilih kategori</option>
            <?php foreach ($categories as $kategori): ?>
                <option value="<?= (int) $kategori['id'] ?>" <?= (int) $kategori['id'] === (int) $post['category_id'] ? 'selected' : '' ?>>
                    <?= e($kategori['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="compose-bottom">
            <span class="char-counter" id="post-counter">0 / 1000</span>
            <button type="submit" class="button">Simpan Perubahan</button>
        </div>
    </form>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
