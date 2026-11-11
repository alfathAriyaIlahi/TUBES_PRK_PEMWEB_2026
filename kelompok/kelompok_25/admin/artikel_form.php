<?php
// MODUL ADMIN (Linda) - Form Tambah/Ubah Artikel
require_once __DIR__ . '/../config/app.php';
require_role('admin');

$id = $_GET['id'] ?? null;
$artikel = [
    'judul' => '',
    'konten' => '',
    'status' => 'draft'
];

// Jika ada ID, ambil data lama untuk diedit
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM artikel WHERE id = ?");
    $stmt->execute([$id]);
    $artikel = $stmt->fetch();
    if (!$artikel) {
        set_flash('Artikel tidak ditemukan.', 'danger');
        redirect('artikel.php');
    }
}

// Proses Simpan (Tambah / Ubah)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('Token CSRF tidak valid.', 'danger');
        redirect('artikel.php');
    }

    $judul_input = trim($_POST['judul'] ?? '');
    $konten = trim($_POST['konten'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $penulis_id = current_user()['id'];

    if (empty($judul_input) || empty($konten)) {
        set_flash('Judul dan konten artikel wajib diisi.', 'danger');
    } else {
        // Buat slug otomatis dari judul (huruf kecil, spasi jadi tanda -, hapus karakter khusus)
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $judul_input)));
        // Pastikan slug unik (tambahkan timestamp jika duplikat, kecuali untuk artikel milik sendiri yang sedang diedit)
        if ($id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM artikel WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $id]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM artikel WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        
        if ($stmt->fetchColumn() > 0) {
            $slug .= '-' . time();
        }

        if ($id) {
            // Update artikel
            $stmt = $pdo->prepare("UPDATE artikel SET judul = ?, slug = ?, konten = ?, status = ? WHERE id = ?");
            $stmt->execute([$judul_input, $slug, $konten, $status, $id]);
            set_flash('Artikel berhasil diperbarui.', 'success');
        } else {
            // Tambah artikel baru
            $stmt = $pdo->prepare("INSERT INTO artikel (judul, slug, konten, penulis_id, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$judul_input, $slug, $konten, $penulis_id, $status]);
            set_flash('Artikel baru berhasil ditambahkan.', 'success');
        }
        redirect('artikel.php');
    }
}

$judul_page = $id ? 'Ubah Artikel' : 'Tambah Artikel';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container page-head my-4">
    <h2><?= $id ? 'Ubah Artikel' : 'Tambah Artikel Baru' ?></h2>
    <p class="text-secondary">Silakan isi formulir di bawah ini dengan lengkap.</p>

    <?php if (has_flash()): ?>
        <div class="alert alert-<?= flash_type() ?> alert-dismissible fade show" role="alert">
            <?= flash() ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <form method="POST">
                <?= csrf_field() ?>
                
                <div class="mb-3">
                    <label for="judul" class="form-label">Judul Artikel</label>
                    <input type="text" class="form-control" id="judul" name="judul" value="<?= e($artikel['judul']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status Publikasi</label>
                    <select class="form-select" id="status" name="status">
                        <option value="draft" <?= ($artikel['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                        <option value="terbit" <?= ($artikel['status'] === 'terbit') ? 'selected' : '' ?>>Terbit</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="konten" class="form-label">Konten Artikel</label>
                    <textarea class="form-control" id="konten" name="konten" rows="8" required><?= e($artikel['konten']) ?></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="artikel.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan Artikel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>