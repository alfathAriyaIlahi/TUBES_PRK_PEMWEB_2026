<?php
// Halaman Publik - Daftar Artikel Ruang Hukum
require_once __DIR__ . '/config/app.php';

// Ambil semua artikel yang berstatus 'terbit'
$stmt = $pdo->query("SELECT a.*, u.nama as penulis FROM artikel a LEFT JOIN users u ON a.penulis_id = u.id WHERE a.status = 'terbit' ORDER BY a.created_at DESC");
$artikels = $stmt->fetchAll();

$judul = 'Artikel Hukum';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container page-head my-4">
    <h2>Artikel & Publikasi Hukum</h2>
    <p class="text-secondary">Temukan berbagai informasi dan wawasan hukum terkini.</p>

    <div class="row g-4 mt-3">
        <?php if (empty($artikels)): ?>
            <div class="col-12">
                <div class="alert alert-info">Belum ada artikel yang diterbitkan.</div>
            </div>
        <?php else: ?>
            <?php foreach ($artikels as $a): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= e($a['judul']) ?></h5>
                            <p class="text-muted small mb-2">
                                Oleh: <?= e($a['penulis'] ?? 'Admin') ?> | <?= e(date('d M Y', strtotime($a['created_at']))) ?>
                            </p>
                            <p class="card-text flex-grow-1">
                                <?= e(substr(strip_tags($a['konten']), 0, 100)) ?>...
                            </p>
                            <a href="artikel_detail.php?slug=<?= e($a['slug']) ?>" class="btn btn-outline-primary btn-sm mt-auto">Baca Selengkapnya</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>