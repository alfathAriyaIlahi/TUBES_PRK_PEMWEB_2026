<?php
require_once __DIR__ . '/config/app.php';

$judul = 'Tentang Kami';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container page-head">
  <h2 class="section-title">Tentang Ruang Hukum</h2>

  <div class="row g-4 align-items-center mb-5">
    <div class="col-lg-7">
      <!-- GANTI paragraf ini dengan teks "Tentang Kami" dari Linda -->
      <p class="lead">
        Ruang Hukum adalah platform konsultasi hukum online yang menghubungkan masyarakat
        dengan pengacara terverifikasi. Ajukan pertanyaan, dapatkan jawaban yang jelas,
        dan pahami hakmu tanpa harus datang ke kantor.
      </p>
    </div>
    <div class="col-lg-5">
      <div class="card-rh p-4">
        <h4 class="mb-3">Kenapa Ruang Hukum?</h4>
        <ul class="list-unstyled mb-0">
          <li class="mb-2"><i class="bi bi-patch-check text-warning"></i> Pengacara terverifikasi admin</li>
          <li class="mb-2"><i class="bi bi-clock text-warning"></i> Konsultasi kapan saja</li>
          <li><i class="bi bi-lock text-warning"></i> Data percakapan terjaga</li>
        </ul>
      </div>
    </div>
  </div>

  <h3 class="mb-3">Cara Kerja</h3>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card-rh p-4 h-100">
        <div class="stat-card"><div class="angka">1</div></div>
        <h5>Daftar dan ajukan pertanyaan</h5>
        <p class="text-secondary mb-0">Buat akun klien, pilih bidang hukum, lalu tulis masalahmu.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card-rh p-4 h-100">
        <div class="stat-card"><div class="angka">2</div></div>
        <h5>Pengacara menjawab</h5>
        <p class="text-secondary mb-0">Pengacara di bidang yang sesuai membalas pertanyaanmu.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card-rh p-4 h-100">
        <div class="stat-card"><div class="angka">3</div></div>
        <h5>Diskusi sampai tuntas</h5>
        <p class="text-secondary mb-0">Lanjutkan percakapan, lalu tandai selesai dan beri ulasan.</p>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>