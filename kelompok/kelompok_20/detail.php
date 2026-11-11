<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Ambil ID laporan dari parameter URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlash('danger', 'Laporan tidak ditemukan.');
    header('Location: /index.php');
    exit;
}

// Ambil data laporan dari database
$stmt = $pdo->prepare("
    SELECT l.*, k.nama_kategori, u.nama AS nama_pelapor
    FROM laporan l
    JOIN users u ON l.user_id = u.id
    LEFT JOIN kategori k ON l.kategori_id = k.id
    WHERE l.id = :id
");
$stmt->execute([':id' => $id]);
$laporan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$laporan) {
    setFlash('danger', 'Laporan tidak ditemukan.');
    header('Location: /index.php');
    exit;
}

// Cek hak akses jika laporan bersifat privat (is_public = 0)
// Hanya pemilik laporan dan admin yang boleh melihat
if ((int)$laporan['is_public'] === 0) {
    $currentUserId = $_SESSION['user_id'] ?? 0;
    $isOwner = isLogin() && ((int)$currentUserId === (int)$laporan['user_id']);
    $admin = isAdmin();

    if (!$isOwner && !$admin) {
        setFlash('danger', 'Anda tidak memiliki izin untuk melihat laporan privat ini.');
        header('Location: /index.php');
        exit;
    }
}

// Ambil semua tanggapan admin untuk laporan ini
$stmtTanggapan = $pdo->prepare("
    SELECT t.*, u.nama AS nama_admin
    FROM tanggapan t
    JOIN users u ON t.admin_id = u.id
    WHERE t.laporan_id = :laporan_id
    ORDER BY t.created_at ASC
");
$stmtTanggapan->execute([':laporan_id' => $id]);
$daftarTanggapan = $stmtTanggapan->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Detail Laporan: ' . $laporan['judul'];
require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 1.5rem;">
    <a href="/laporan_publik.php" style="color: var(--color-primary); text-decoration: none; font-size: 0.95rem; font-weight: 600;">
        &larr; Kembali ke Daftar Laporan
    </a>
</div>

<!-- Informasi Utama Laporan -->
<article class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;">
        <div>
            <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
                <?= sanitize($laporan['jenis']) ?> &bull; <?= sanitize($laporan['nama_kategori'] ?? 'Umum') ?>
            </span>
            <h1 style="font-size: 1.6rem; color: var(--color-primary); margin: 0.35rem 0; line-height: 1.3;">
                <?= sanitize($laporan['judul']) ?>
            </h1>
        </div>
        <div>
            <?= badgeStatus($laporan['status']) ?>
        </div>
    </div>

    <!-- Metadata Laporan -->
    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem 1.25rem; font-size: 0.9rem; color: var(--color-text-muted); padding: 0.75rem 0; border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border); margin-bottom: 1.25rem;">
        <div>Pelapor: <strong style="color: var(--color-text);"><?= sanitize($laporan['nama_pelapor']) ?></strong></div>
        <div>Lokasi: <strong style="color: var(--color-text);"><?= sanitize($laporan['lokasi'] ?? '-') ?></strong></div>
        <div>Waktu: <?= date('d M Y, H:i', strtotime($laporan['created_at'])) ?> WIB</div>
        <?php if ((int)$laporan['is_public'] === 0): ?>
            <div><span style="color: #6B5420; font-weight: 600;">(Laporan Privat)</span></div>
        <?php endif; ?>
    </div>

    <!-- Isi Laporan -->
    <div style="font-size: 1rem; line-height: 1.7; color: var(--color-text); margin-bottom: 1.5rem;">
        <?= nl2br(sanitize($laporan['isi'])) ?>
    </div>

    <!-- Foto Lampiran (jika ada) -->
    <?php if (!empty($laporan['foto'])): ?>
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--color-border);">
            <div style="font-size: 0.9rem; font-weight: 600; color: var(--color-primary); margin-bottom: 0.5rem;">
                Foto Bukti Lampiran:
            </div>
            <img 
                src="/assets/uploads/<?= sanitize($laporan['foto']) ?>" 
                alt="Foto Bukti Laporan" 
                style="max-width: 100%; max-height: 450px; border-radius: var(--radius); border: 1px solid var(--color-border); object-fit: contain; background-color: var(--color-bg);"
            >
        </div>
    <?php endif; ?>
</article>

<!-- Riwayat Tanggapan Admin -->
<section class="card">
    <h2 class="card-title" style="font-size: 1.25rem; margin-bottom: 1rem;">
        Tanggapan Resmi Pihak Kampus
    </h2>

    <?php if (empty($daftarTanggapan)): ?>
        <p style="color: var(--color-text-muted); font-size: 0.95rem; margin: 0;">
            Belum ada tanggapan resmi dari pihak kampus untuk laporan ini.
        </p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($daftarTanggapan as $index => $t): ?>
                <div style="background-color: var(--color-bg); border-left: 3px solid var(--color-secondary); border-radius: 4px; padding: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.4rem;">
                        <strong style="color: var(--color-primary); font-size: 0.95rem;">
                            <?= sanitize($t['nama_admin']) ?> (Admin)
                        </strong>
                        <span style="font-size: 0.85rem; color: var(--color-text-muted);">
                            <?= date('d M Y, H:i', strtotime($t['created_at'])) ?> WIB
                        </span>
                    </div>
                    <div style="font-size: 0.95rem; line-height: 1.6; color: var(--color-text);">
                        <?= nl2br(sanitize($t['isi_tanggapan'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

