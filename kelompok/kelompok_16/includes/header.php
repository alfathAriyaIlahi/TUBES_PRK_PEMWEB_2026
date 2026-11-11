<?php
// Bagian atas halaman: <head>, sidebar kiri, dan pembuka konten utama.

$activeMenu = $activeMenu ?? '';
$pageTitle  = $pageTitle ?? 'Rumdis';
$flash      = pullFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(getCsrfToken()) ?>">
    <title><?= e($pageTitle) ?> - Rumdis</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/logo.svg">
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        
        (function () {
            try {
                var tema = localStorage.getItem('rh-theme');
                if (tema) { document.documentElement.setAttribute('data-theme', tema); }
            } catch (error) {}
        })();
    </script>
</head>
<body>

<header class="mobile-topbar">
    <a class="brand" href="index.php"><img src="assets/images/logo.svg" alt="" width="28" height="28"> Rumdis</a>
    <button type="button" class="icon-button js-theme-toggle" aria-label="Ganti tema"><?= icon('moon') ?></button>
</header>

<div class="app-layout">

    <aside class="sidebar-left">
        <a class="brand" href="index.php"><img src="assets/images/logo.svg" alt="" width="32" height="32"> Rumdis</a>

        <nav class="nav-list" aria-label="Menu utama">
            <a class="nav-link <?= $activeMenu === 'home' ? 'active' : '' ?>" href="index.php"><?= icon('home') ?> <span>Beranda</span></a>
            <a class="nav-link <?= $activeMenu === 'search' ? 'active' : '' ?>" href="search.php"><?= icon('search') ?> <span>Cari</span></a>
            <a class="nav-link <?= $activeMenu === 'answers' ? 'active' : '' ?>" href="my-answers.php"><?= icon('message') ?> <span>Jawaban Saya</span></a>
            <a class="nav-link <?= $activeMenu === 'profile' ? 'active' : '' ?>" href="profile.php"><?= icon('user') ?> <span>Profil</span></a>

            <?php if (isGuest($currentUser)): ?>
                <a class="nav-link <?= $activeMenu === 'login' ? 'active' : '' ?>" href="login.php"><?= icon('login') ?> <span>Masuk</span></a>
                <a class="nav-link <?= $activeMenu === 'register' ? 'active' : '' ?>" href="register.php"><?= icon('user') ?> <span>Daftar</span></a>
            <?php else: ?>
                <form method="post" action="logout.php">
                    <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">
                    <button type="submit" class="nav-link nav-button"><?= icon('logout') ?> <span>Keluar</span></button>
                </form>
            <?php endif; ?>
        </nav>

        <button type="button" class="nav-link nav-button js-theme-toggle"><?= icon('moon') ?> <span>Mode Gelap</span></button>

        <a class="user-card" href="profile.php">
            <?= renderAvatar($currentUser['username'], $currentUser['avatar_color']) ?>
            <span>
                <strong><?= e($currentUser['username']) ?></strong>
                <small><?= isGuest($currentUser) ? 'Tamu' : 'Anggota' ?></small>
            </span>
        </a>
    </aside>

    <main class="main-content">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
        <?php endif; ?>
