<?php

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/post_card.php';

$categories = getCategories($pdo);

$selectedCategory = (string) ($_GET['kategori'] ?? '');
$slugValid = array_column($categories, 'slug');
if (!in_array($selectedCategory, $slugValid, true)) {
    $selectedCategory = '';
}

$sort = (string) ($_GET['urut'] ?? 'terbaru');
if (!in_array($sort, ['terbaru', 'terpopuler', 'dijawab'], true)) {
    $sort = 'terbaru';
}

$posts = fetchPosts($pdo, (int) $currentUser['id'], [
    'category' => $selectedCategory,
    'sort'     => $sort,
]);

$selectedCategoryId = 0;
foreach ($categories as $kategori) {
    if ($kategori['slug'] === $selectedCategory) {
        $selectedCategoryId = (int) $kategori['id'];
    }
}

$pageTitle  = 'Beranda';
$activeMenu = 'home';
include __DIR__ . '/includes/header.php';

function sortLink(string $urut, string $kategori): string
{
    $query = ['urut' => $urut];
    if ($kategori !== '') {
        $query['kategori'] = $kategori;
    }
    return 'index.php?' . http_build_query($query);
}
?>

<section class="card compose-card">
    <form id="post-form" method="post" action="process/create_post.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">
        <div class="compose-top">
            <?= renderAvatar($currentUser['username'], $currentUser['avatar_color']) ?>
            <div class="compose-fields">
                <label class="sr-only" for="post-content">Tulis masalah hukummu</label>
                <textarea id="post-content" name="content" rows="3" maxlength="1000"
                        placeholder="Ada masalah hukum? Ceritakan di sini sebagai <?= e($currentUser['username']) ?>..."></textarea>
                <p class="form-error" id="post-error" hidden></p>
            </div>
        </div>
        <div class="compose-bottom">
            <label class="sr-only" for="post-category">Kategori</label>
            <select id="post-category" name="category_id">
                <option value="">Pilih kategori</option>
                <?php foreach ($categories as $kategori): ?>
                    <option value="<?= (int) $kategori['id'] ?>" <?= (int) $kategori['id'] === $selectedCategoryId ? 'selected' : '' ?>>
                        <?= e($kategori['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="char-counter" id="post-counter">0 / 1000</span>
            <button type="submit" class="button">Kirim</button>
        </div>
    </form>
</section>

<div class="chip-scroll">
    <a class="chip <?= $selectedCategory === '' ? 'active' : '' ?>" href="<?= e(sortLink($sort, '')) ?>">Semua</a>
    <?php foreach ($categories as $kategori): ?>
        <a class="chip <?= $selectedCategory === $kategori['slug'] ? 'active' : '' ?>"
        href="<?= e(sortLink($sort, $kategori['slug'])) ?>"><?= e($kategori['name']) ?></a>
    <?php endforeach; ?>
</div>

<div class="tabs" role="tablist">
    <a class="tab <?= $sort === 'terbaru' ? 'active' : '' ?>" href="<?= e(sortLink('terbaru', $selectedCategory)) ?>">Terbaru</a>
    <a class="tab <?= $sort === 'terpopuler' ? 'active' : '' ?>" href="<?= e(sortLink('terpopuler', $selectedCategory)) ?>">Terpopuler</a>
    <a class="tab <?= $sort === 'dijawab' ? 'active' : '' ?>" href="<?= e(sortLink('dijawab', $selectedCategory)) ?>">Banyak Dijawab</a>
</div>

<section class="feed" aria-label="Daftar postingan">
    <?php if (!$posts): ?>
        <div class="empty-state">Belum ada postingan di kategori ini. Jadilah yang pertama bertanya!</div>
    <?php endif; ?>

    <?php foreach ($posts as $post): ?>
        <?php renderPostCard($post, (int) $currentUser['id'], false, 'index.php'); ?>
    <?php endforeach; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
