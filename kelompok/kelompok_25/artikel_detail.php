<?php
// Halaman Publik - Detail Artikel Ruang Hukum
require_once __DIR__ . '/config/app.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    redirect('artikel.php');
}

// Ambil data artikel berdasarkan slug dan pastikan statusnya 'terbit'
$stmt = $pdo->prepare("SELECT a.*, u.nama as penulis FROM artikel a LEFT JOIN users u ON a.penulis_id = u.id WHERE a.slug = ? AND a.status = 'terbit'");
$stmt->execute([$slug]);
$artikel = $stmt->fetch();

if (!$artikel) {
    set_flash('Artikel tidak ditemukan atau belum diterbitkan.', 'danger');
    redirect('artikel.php');
}

$judul = $artikel['judul'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container page-head my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="artikel.php">Artikel</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e(substr($artikel['judul'], 0, 30)) ?>...</li>
        </ol>
    </nav>

    <div class="card shadow-sm mt-3">
        <div class="card-body p-4">
            <h1 class="mb-3"><?= e($artikel['judul']) ?></h1>
            <p class="text-muted border-bottom pb-3">
                Ditulis oleh: <strong><?= e($artikel['penulis'] ?? 'Admin') ?></strong> | Dipublikasikan pada: <?= e(date('d M Y H:i', strtotime($artikel['created_at']))) ?>
            </p>
            
            <div class="artikel-konten mt-4" style="line-height: 1.8; white-space: pre-line;">
                <?= e($artikel['konten']) ?>
            </div>

            <div class="mt-4 pt-3 border-top">
                <a href="artikel.php" class="btn btn-secondary btn-sm">&larr; Kembali ke Daftar Artikel</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>