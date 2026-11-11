<?php
require_once __DIR__ . '/config/app.php';

$judul = 'Pengacara';
$pdo = db();

$bidangList = $pdo->query('SELECT id, nama FROM bidang_hukum ORDER BY nama')->fetchAll();
$bidangId = (int)($_GET['bidang'] ?? 0);

$sql = "SELECT u.id, u.nama, p.bio, p.pengalaman_tahun, p.kota, b.nama AS bidang
        FROM users u
        JOIN profil_pengacara p ON p.user_id = u.id
        JOIN bidang_hukum b ON b.id = p.bidang_id
        WHERE u.role = 'pengacara' AND u.status = 'aktif'";
$params = [];
if ($bidangId > 0) {
    $sql .= ' AND p.bidang_id = ?';
    $params[] = $bidangId;
}
$sql .= ' ORDER BY u.nama';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="container page-head">
  <h2 class="section-title">Pengacara Kami</h2>

    <div class="mb-4 d-flex flex-wrap gap-2">
    <a href="<?= url('pengacara.php') ?>" class="btn btn-sm <?= $bidangId ? 'btn-outline-secondary' : 'btn-warning' ?>">Semua</a>
    <?php foreach ($bidangList as $b): ?>
      <a href="<?= url('pengacara.php') ?>?bidang=<?= (int)$b['id'] ?>"
         class="btn btn-sm <?= $bidangId === (int)$b['id'] ? 'btn-warning' : 'btn-outline-secondary' ?>">
        <?= e($b['nama']) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (!$daftar): ?>
    <p class="text-secondary">Belum ada pengacara pada bidang ini.</p>
  <?php endif; ?>

  <div class="row g-4">
    <?php foreach ($daftar as $p): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title"><?= e($p['nama']) ?></h5>
            <span class="badge text-bg-primary mb-2"><?= e($p['bidang']) ?></span>
            <p class="card-text small text-secondary">
              <?= e($p['kota'] ?? '-') ?> &middot; <?= (int)$p['pengalaman_tahun'] ?> tahun pengalaman
            </p>
            <p class="card-text"><?= e($p['bio'] ?? '') ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>