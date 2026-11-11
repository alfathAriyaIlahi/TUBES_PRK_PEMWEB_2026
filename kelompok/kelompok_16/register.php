<?php
require_once __DIR__ . '/includes/init.php';

if (!isGuest($currentUser)) {
    redirect('index.php');
}

$old = pullOldInput();
$pageTitle  = 'Daftar';
$activeMenu = 'register';
include __DIR__ . '/includes/header.php';
?>

<section class="card auth-card">
    <h1 class="page-title">Daftar</h1>
    <p class="muted">Postingan dan jawabanmu sebagai tamu (<?= e($currentUser['username']) ?>) akan ikut pindah ke akun baru.</p>

    <form id="register-form" method="post" action="process/register_process.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">

        <label for="register-username">Username</label>
        <input type="text" id="register-username" name="username" maxlength="20" autocomplete="username" value="<?= e($old['username'] ?? '') ?>">
        <small class="muted">3-20 karakter: huruf, angka, atau underscore.</small>

        <label for="register-email">Email</label>
        <input type="email" id="register-email" name="email" maxlength="100" autocomplete="email" value="<?= e($old['email'] ?? '') ?>">

        <label for="register-password">Password</label>
        <input type="password" id="register-password" name="password" autocomplete="new-password">
        <small class="muted">Minimal 8 karakter.</small>

        <label for="register-confirm">Konfirmasi password</label>
        <input type="password" id="register-confirm" name="password_confirm" autocomplete="new-password">

        <p class="form-error" id="register-error" hidden></p>
        <button type="submit" class="button button-block">Daftar</button>
    </form>

    <p class="muted auth-switch">Sudah punya akun? <a href="login.php">Masuk</a></p>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
