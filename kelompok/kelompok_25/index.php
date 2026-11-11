<?php
require_once __DIR__ . '/config/app.php';

$judul = 'Konsultasi Hukum Online';
$pdo = db();

// Bidang hukum + jumlah pengacara aktif
$bidang = $pdo->query(
    "SELECT b.id, b.nama,
            (SELECT COUNT(*) FROM profil_pengacara p
               JOIN users u ON u.id = p.user_id
              WHERE p.bidang_id = b.id AND u.role = 'pengacara' AND u.status = 'aktif') AS jml
       FROM bidang_hukum b
      ORDER BY b.nama"
)->fetchAll();

// Pengacara unggulan: 3 dengan pengalaman terbanyak
$unggulan = $pdo->query(
    "SELECT u.id, u.nama, p.pengalaman_tahun, p.kota, b.nama AS bidang
       FROM users u
       JOIN profil_pengacara p ON p.user_id = u.id
       JOIN bidang_hukum b ON b.id = p.bidang_id
      WHERE u.role = 'pengacara' AND u.status = 'aktif'
      ORDER BY p.pengalaman_tahun DESC, u.nama
      LIMIT 3"
)->fetchAll();

// Artikel terbaru (hanya yang terbit). Kosong kalau Linda belum mengisi.
$artikel = $pdo->query(
    "SELECT judul, slug, ringkasan FROM artikel WHERE status = 'terbit' ORDER BY id DESC LIMIT 3"
)->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<style>
  .keunggulan-icon {
    width: 56px; height: 56px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: #F3E9CF; color: #0F2747; font-size: 1.6rem;
  }
  .avatar-inisial {
    width: 52px; height: 52px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: #0F2747; color: #C9A24B; font-weight: 700; font-size: 1.1rem;
  }
  .cta-box { background: #0F2747; color: #fff; border-radius: 1rem; }
  a.card-rh { text-decoration: none; color: inherit; display: block; }
</style>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <h1 class="display-5 mb-3">Ruang Hukum: Konsultasi Hukum Online</h1>
    <p class="lead mb-4">Tanyakan masalah hukummu kepada pengacara terverifikasi, kapan saja.</p>
    <a class="btn btn-gold btn-lg me-2" href="<?= url('auth/register.php') ?>">Mulai Konsultasi</a>
    <a class="btn btn-outline-light btn-lg" href="<?= url('pengacara.php') ?>">Lihat Pengacara</a>
  </div>
</section>

<!-- 3 KEUNGGULAN -->
<section class="container my-5">
  <h2 class="section-title">Kenapa Ruang Hukum?</h2>
  <div class="row g-4 text-center">
    <div class="col-md-4">
      <div class="card-rh p-4 h-100">
        <span class="keunggulan-icon mb-3"><i class="bi bi-patch-check"></i></span>
        <h5>Pengacara Terverifikasi</h5>
        <p class="text-secondary mb-0">Setiap pengacara diperiksa dan disetujui admin sebelum bisa menjawab pertanyaanmu.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card-rh p-4 h-100">
        <span class="keunggulan-icon mb-3"><i class="bi bi-clock"></i></span>
        <h5>Konsultasi Kapan Saja</h5>
        <p class="text-secondary mb-0">Ajukan pertanyaan dari mana saja tanpa perlu datang ke kantor atau membuat janji.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card-rh p-4 h-100">
        <span class="keunggulan-icon mb-3"><i class="bi bi-lock"></i></span>
        <h5>Percakapan Terjaga</h5>
        <p class="text-secondary mb-0">Hanya kamu dan pengacara yang menangani yang bisa membaca konsultasimu.</p>
      </div>
    </div>
  </div>
</section>

<!-- BIDANG HUKUM -->
<section class="container my-5">
  <h2 class="section-title">Bidang Hukum</h2>
  <?php if (!$bidang): ?>
    <p class="text-secondary">Belum ada bidang hukum.</p>
  <?php endif; ?>
  <div class="row g-3">
    <?php foreach ($bidang as $b): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <a class="card-rh hoverable p-3 h-100" href="<?= url('pengacara.php') ?>?bidang=<?= (int)$b['id'] ?>">
          <h6 class="mb-1"><?= e($b['nama']) ?></h6>
          <span class="small text-secondary"><?= (int)$b['jml'] ?> pengacara</span>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- PENGACARA UNGGULAN -->
<?php if ($unggulan): ?>
<section class="container my-5">
  <h2 class="section-title">Pengacara Unggulan</h2>
  <div class="row g-4">
    <?php foreach ($unggulan as $p): ?>
      <div class="col-md-4">
        <div class="card-rh p-4 h-100">
          <div class="d-flex align-items-center gap-3 mb-2">
            <span class="avatar-inisial"><?= e(mb_strtoupper(mb_substr($p['nama'], 0, 1))) ?></span>
            <div>
              <h6 class="mb-0"><?= e($p['nama']) ?></h6>
              <span class="small text-secondary"><?= e($p['kota'] ?? '-') ?></span>
            </div>
          </div>
          <p class="mb-1"><?= e($p['bidang']) ?></p>
          <p class="small text-secondary mb-0"><?= (int)$p['pengalaman_tahun'] ?> tahun pengalaman</p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ARTIKEL TERBARU (tampil kalau sudah ada artikel terbit) -->
<?php if ($artikel): ?>
<section class="container my-5">
  <h2 class="section-title">Artikel Terbaru</h2>
  <div class="row g-4">
    <?php foreach ($artikel as $a): ?>
      <div class="col-md-4">
        <a class="card-rh hoverable p-4 h-100" href="<?= url('artikel_detail.php') ?>?slug=<?= e(urlencode($a['slug'])) ?>">
          <h5><?= e($a['judul']) ?></h5>
          <p class="small text-secondary mb-0"><?= e($a['ringkasan'] ?? '') ?></p>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- AJAKAN DAFTAR -->
<section class="container my-5">
  <div class="cta-box p-5 text-center">
    <h2 class="mb-2">Punya masalah hukum?</h2>
    <p class="mb-4">Daftar gratis dan ajukan pertanyaan pertamamu hari ini.</p>
    <a class="btn btn-gold btn-lg" href="<?= url('auth/register.php') ?>">Daftar Sekarang</a>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>