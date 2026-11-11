<?php
// Header bersama. Cara pakai di halaman mana pun:
//   $judul = 'Judul Halaman';
//   require_once __DIR__ . '/../config/app.php';
//   require_once __DIR__ . '/../includes/header.php';
$judul = $judul ?? APP_NAME;
$user  = current_user();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($judul) ?> | <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-rh sticky-top">
  <div class="container">
    <a class="navbar-brand" href="<?= url('index.php') ?>"><i class="bi bi-bank2"></i> Ruang Hukum</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-label="Menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= url('index.php') ?>">Beranda</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('pengacara.php') ?>">Pengacara</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('artikel.php') ?>">Artikel</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('faq.php') ?>">FAQ</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('tentang.php') ?>">Tentang Kami</a></li>
      </ul>
      <div class="d-flex gap-2 align-items-center">
        <?php if ($user): ?>
          <a class="btn btn-gold btn-sm" href="<?= url(dashboard_path($user['role'])) ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
          <span class="text-white-50 small d-none d-lg-inline"><?= e($user['nama']) ?></span>
          <a class="btn btn-outline-light btn-sm" href="<?= url('auth/logout.php') ?>">Keluar</a>
        <?php else: ?>
          <a class="btn btn-outline-light btn-sm" href="<?= url('auth/login.php') ?>">Masuk</a>
          <a class="btn btn-gold btn-sm" href="<?= url('auth/register.php') ?>">Daftar</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<?php $pesan = ambil_flash(); if ($pesan): ?>
<div class="container mt-3">
  <?php foreach ($pesan as $f): ?>
    <div class="alert alert-<?= e($f['tipe']) ?> alert-dismissible fade show" role="alert">
      <?= e($f['pesan']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<main>
