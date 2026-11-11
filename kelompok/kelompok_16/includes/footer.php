<?php
// Bagian bawah halaman: penutup konten, sidebar kanan, bottom nav, dan script.

$activeMenu = $activeMenu ?? '';
$sidebarCategories = getCategories($pdo);
$trendingPosts     = fetchPosts($pdo, (int) $currentUser['id'], ['sort' => 'trending', 'limit' => 5]);
?>
    </main>

    <aside class="sidebar-right">
        <section class="side-card">
            <h2>Diskusi Trending</h2>
            <?php if (!$trendingPosts): ?>
                <p class="muted">Belum ada diskusi.</p>
            <?php endif; ?>
            <ol class="trending-list">
                <?php foreach ($trendingPosts as $trending): ?>
                    <li>
                        <a href="detail-post.php?id=<?= (int) $trending['id'] ?>">
                            <?= e(excerpt($trending['content'], 70)) ?>
                        </a>
                        <small><?= (int) $trending['like_count'] ?> suka &middot; <?= (int) $trending['reply_count'] ?> jawaban</small>
                    </li>
                <?php endforeach; ?>
            </ol>
        </section>

        <section class="side-card">
            <h2>Kategori</h2>
            <div class="chip-list">
                <?php foreach ($sidebarCategories as $kategori): ?>
                    <a class="chip" href="index.php?kategori=<?= e($kategori['slug']) ?>"><?= e($kategori['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </section>

        <p class="side-note">Diskusi di sini bersifat informasi umum dan bukan nasihat hukum resmi. Untuk kasus nyata, konsultasikan dengan advokat atau lembaga bantuan hukum.</p>
    </aside>
</div>

<nav class="bottom-nav" aria-label="Menu mobile">
    <a class="<?= $activeMenu === 'home' ? 'active' : '' ?>" href="index.php"><?= icon('home') ?><span>Beranda</span></a>
    <a class="<?= $activeMenu === 'search' ? 'active' : '' ?>" href="search.php"><?= icon('search') ?><span>Cari</span></a>
    <a class="<?= $activeMenu === 'answers' ? 'active' : '' ?>" href="my-answers.php"><?= icon('message') ?><span>Jawaban</span></a>
    <a class="<?= $activeMenu === 'profile' ? 'active' : '' ?>" href="profile.php"><?= icon('user') ?><span>Profil</span></a>
    <?php if (isGuest($currentUser)): ?>
        <a class="<?= in_array($activeMenu, ['login', 'register'], true) ? 'active' : '' ?>" href="login.php"><?= icon('login') ?><span>Masuk</span></a>
    <?php else: ?>
        <form method="post" action="logout.php">
            <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">
            <button type="submit"><?= icon('logout') ?><span>Keluar</span></button>
        </form>
    <?php endif; ?>
</nav>

<div id="toast" class="toast" role="status" aria-live="polite"></div>
<script src="assets/js/app.js"></script>
</body>
</html>
