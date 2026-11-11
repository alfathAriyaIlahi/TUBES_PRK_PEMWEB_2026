<?php
// MODUL PENGACARA (Nday). Ganti isi halaman ini dengan dashboard pengacara.
require_once __DIR__ . '/../config/app.php';
require_role('pengacara');

$judul = 'Dashboard Pengacara';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container page-head">
  <h2>Dashboard Pengacara</h2>
  <p class="text-secondary">Halo, <?= e(current_user()['nama']) ?>.</p>
  <div class="card-rh p-4">
    Halaman ini sementara. <strong>Nday</strong> akan mengisi: pertanyaan masuk, jawab konsultasi, profil keahlian.
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
