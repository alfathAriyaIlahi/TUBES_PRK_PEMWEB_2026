<?php
// MODUL ADMIN (Linda) - Daftar Artikel
require_once __DIR__ . '/../config/app.php';
require_role('admin');

// Proses Hapus Artikel jika tombol hapus diklik
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'hapus') {
    if (!verify_csrf()) {
        set_flash('Token CSRF tidak valid.', 'danger');
        redirect('artikel.php');
    }

    $id = $_POST['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM artikel WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('Artikel berhasil dihapus.', 'success');
    }
    redirect('artikel.php');
}

// Ambil semua data artikel
$stmt = $pdo->query("SELECT a.*, u.nama as penulis FROM artikel a LEFT JOIN users u ON a.penulis_id = u.id ORDER BY a.created_at DESC");
$artikels = $stmt->fetchAll();

$judul = 'Kelola Artikel';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container page-head my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2>Kelola Artikel</h2>
            <p class="text-secondary mb-0">Daftar artikel dan publikasi hukum.</p>
        </div>
        <a href="artikel_form.php" class="btn btn-primary">+ Tambah Artikel</a>
    </div>

    <?php if (has_flash()): ?>
        <div class="alert alert-<?= flash_type() ?> alert-dismissible fade show" role="alert">
            <?= flash() ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Status</th>
                            <th>Tanggal Buat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($artikels)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada artikel.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($artikels as $index => $a): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= e($a['judul']) ?></td>
                                    <td><?= e($a['penulis'] ?? 'Admin') ?></td>
                                    <td>
                                        <?php if ($a['status'] === 'terbit'): ?>
                                            <span class="badge bg-success">Terbit</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= e($a['created_at']) ?></td>
                                    <td>
                                        <a href="artikel_form.php?id=<?= e($a['id']) ?>" class="btn btn-warning btn-sm">Ubah</a>
                                        <form method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= e($a['id']) ?>">
                                            <button type="submit" name="aksi" value="hapus" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus artikel ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>