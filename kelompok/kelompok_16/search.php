<?php

require_once __DIR__ . '/includes/init.php';

$categories = getCategories($pdo);

$initialQuery    = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
$initialCategory = (string) ($_GET['category'] ?? '');
$initialSort     = (string) ($_GET['sort'] ?? 'terbaru');

$pageTitle  = 'Cari';
$activeMenu = 'search';
include __DIR__ . '/includes/header.php';
?>

<section class="card">
    <h1 class="page-title">Cari Diskusi</h1>
    <form id="search-form" action="search.php" method="get" role="search">
        <label class="sr-only" for="search-input">Kata kunci</label>
        <input type="search" id="search-input" name="q" maxlength="100" autocomplete="off"
               placeholder="Cari isi postingan, username, atau kategori..."
               value="<?= e($initialQuery) ?>">

        <div class="search-filters">
            <label class="sr-only" for="search-category">Kategori</label>
            <select id="search-category" name="category">
                <option value="">Semua kategori</option>
                <?php foreach ($categories as $kategori): ?>
                    <option value="<?= e($kategori['slug']) ?>" <?= $initialCategory === $kategori['slug'] ? 'selected' : '' ?>>
                        <?= e($kategori['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="sr-only" for="search-sort">Urutkan</label>
            <select id="search-sort" name="sort">
                <option value="terbaru" <?= $initialSort === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
                <option value="terpopuler" <?= $initialSort === 'terpopuler' ? 'selected' : '' ?>>Terpopuler</option>
                <option value="dijawab" <?= $initialSort === 'dijawab' ? 'selected' : '' ?>>Banyak dijawab</option>
            </select>
        </div>
    </form>
</section>

<p class="muted result-info" id="search-count" aria-live="polite">Memuat...</p>
<section class="feed" id="search-results" aria-label="Hasil pencarian"></section>

<?php include __DIR__ . '/includes/footer.php'; ?>
