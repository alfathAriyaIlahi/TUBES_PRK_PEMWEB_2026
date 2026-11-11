<?php
require_once __DIR__ . '/includes/init.php';

if (!isGuest($currentUser)) {
    redirect('index.php');
}

$old = pullOldInput();
$pageTitle  = 'Masuk';
$activeMenu = 'login';
include __DIR__ . '/includes/header.php';
?>

<?php if (isset($_GET['logout'])): ?>
    <div class="alert alert-success">Kamu sudah keluar. Sampai jumpa lagi!</div>
<?php endif; ?>

<section class="card auth-card">
    <h1 class="page-title">Masuk</h1>
    <p class="muted">Masuk ke akun anggota Rumdis.</p>

    <form id="login-form" method="post" action="process/login_process.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">

        <label for="login-identity">Username atau email</label>
        <input type="text" id="login-identity" name="identity" autocomplete="username" value="<?= e($old['identity'] ?? '') ?>">

        <label for="login-password">Password</label>
        <input type="password" id="login-password" name="password" autocomplete="current-password">

        <p class="form-error" id="login-error" hidden></p>
        <button type="submit" class="button button-block">Masuk</button>
    </form>

    <p class="muted auth-switch">Belum punya akun? <a href="register.php">Daftar</a></p>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
