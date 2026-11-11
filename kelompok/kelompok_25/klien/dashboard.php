<?php
// MODUL KLIEN (Ghani): dashboard klien
require_once __DIR__ . '/../config/app.php';
require_role('klien');

$user = current_user();
$pdo  = db();

// Hitung konsultasi per status
$stmt = $pdo->prepare('SELECT status, COUNT(*) AS jumlah FROM konsultasi WHERE klien_id = ? GROUP BY status');
$stmt->execute([$user['id']]);
$hitung = ['menunggu' => 0, 'dijawab' => 0, 'selesai' => 0, 'ditutup' => 0];
foreach ($stmt->fetchAll() as $r) {
    $hitung[$r['status']] = (int) $r['jumlah'];
}
$total = array_sum($hitung);

// Riwayat konsultasi milik klien yang login
$stmt = $pdo->prepare(
    'SELECT k.id, k.judul, k.status, b.nama AS bidang, u.nama AS pengacara
     FROM konsultasi k
     LEFT JOIN bidang_hukum b ON b.id = k.bidang_id
     LEFT JOIN users u ON u.id = k.pengacara_id
     WHERE k.klien_id = ?
     ORDER BY k.id DESC'
);
$stmt->execute([$user['id']]);
$riwayat = $stmt->fetchAll();

$judul = 'Dashboard Klien';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container page-head">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
      <h2 class="mb-0">Dashboard Klien</h2>
      <p class="text-secondary mb-0">Halo, <?= e($user['nama']) ?>.</p>
    </div>
    <a href="<?= e(url('klien/konsultasi_baru.php')) ?>" class="btn btn-gold">
      <i class="bi bi-plus-circle"></i> Ajukan Konsultasi
    </a>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card-rh stat-card p-3"><div class="angka"><?= $hitung['menunggu'] ?></div>Menunggu</div></div>
    <div class="col-6 col-md-3"><div class="card-rh stat-card p-3"><div class="angka"><?= $hitung['dijawab'] ?></div>Dijawab</div></div>
    <div class="col-6 col-md-3"><div class="card-rh stat-card p-3"><div class="angka"><?= $hitung['selesai'] ?></div>Selesai</div></div>
    <div class="col-6 col-md-3"><div class="card-rh stat-card p-3"><div class="angka"><?= $total ?></div>Total</div></div>
  </div>

  <h3 class="section-title">Riwayat Konsultasi</h3>
  <div class="card-rh p-3">
    <?php if (!$riwayat): ?>
      <p class="text-secondary mb-0">Belum ada konsultasi. Klik "Ajukan Konsultasi" untuk memulai.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr><th>Judul</th><th>Bidang</th><th>Pengacara</th><th>Status</th><th></th></tr>
          </thead>
          <tbody>
          <?php foreach ($riwayat as $r): ?>
            <tr>
              <td><?= e($r['judul']) ?></td>
              <td><?= e($r['bidang'] ?? '-') ?></td>
              <td><?= e($r['pengacara'] ?? 'Belum ada') ?></td>
              <td><span class="badge-status badge-<?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
              <td class="text-end">
                <a class="btn btn-sm btn-navy" href="<?= e(url('klien/konsultasi_detail.php?id=' . $r['id'])) ?>">Buka</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>