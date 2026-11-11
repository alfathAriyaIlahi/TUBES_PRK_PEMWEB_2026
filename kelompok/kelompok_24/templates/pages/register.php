<?php
/** @var array<string, string> $formValues */
?>
<main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
  <section class="auth-card card border-0 overflow-hidden">
    <div class="brand-panel text-center text-white">
      <h1 class="brand-title mb-1">ADVOKITA</h1>
      <p class="brand-subtitle mb-0">Whistleblowing &amp; Legal Aid System</p>
    </div>
    <form class="auth-form" method="post" action="<?= escape(url('register')) ?>" autocomplete="on">
      <?php csrf_input(); ?>
      <input type="hidden" name="action" value="register">
      <div class="mb-4"><h2>Buat Akun Baru</h2><p class="text-secondary mb-0">Daftar untuk mengajukan laporan.</p></div>
      <div class="mb-3">
        <label class="form-label" for="registerName">Nama lengkap <span class="text-danger">*</span></label>
        <input class="form-control" id="registerName" name="fullName" maxlength="120" autocomplete="name" required placeholder="Nama lengkap" value="<?= escape($formValues['fullName'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="registerNim">NPM <span class="text-danger">*</span></label>
        <input class="form-control" id="registerNim" name="nim" required inputmode="numeric" pattern="[0-9]{4,20}" autocomplete="username" placeholder="Masukkan NPM" value="<?= escape($formValues['nim'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="registerPassword">Kata sandi <span class="text-danger">*</span></label>
        <input class="form-control" type="password" id="registerPassword" name="password" minlength="8" maxlength="72" autocomplete="new-password" required placeholder="Minimal 8 karakter">
      </div>
      <div class="mb-4">
        <label class="form-label" for="registerPasswordConfirm">Konfirmasi kata sandi <span class="text-danger">*</span></label>
        <input class="form-control" type="password" id="registerPasswordConfirm" name="passwordConfirm" minlength="8" maxlength="72" autocomplete="new-password" required placeholder="Ulangi kata sandi">
      </div>
      <button class="btn btn-danger w-100" type="submit">Registrasi</button>
      <p class="auth-switch mb-0">Sudah punya akun? <a href="<?= escape(url('login')) ?>">Masuk di sini</a></p>
    </form>
  </section>
</main>
