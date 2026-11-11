<?php
require_once __DIR__ . '/../config/app.php';

if (is_logged_in()) {
    redirect(dashboard_path(current_user()['role']));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $error = 'Sesi formulir tidak valid. Muat ulang halaman.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        if (!$u || !password_verify($pass, $u['password'])) {
            $error = 'Email atau kata sandi salah.';
        } elseif ($u['status'] === 'menunggu') {
            $error = 'Akun pengacara kamu masih menunggu verifikasi admin.';
        } elseif ($u['status'] !== 'aktif') {
            $error = 'Akun ini tidak aktif. Hubungi admin.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'    => (int) $u['id'],
                'nama'  => $u['nama'],
                'email' => $u['email'],
                'role'  => $u['role'],
            ];
            flash('success', 'Selamat datang, ' . $u['nama'] . '!');
            redirect(dashboard_path($u['role']));
        }
    }
}

$judul = 'Masuk';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card-rh p-4 p-md-5">
        <h2 class="mb-1">Masuk</h2>
        <p class="text-secondary mb-4">Lanjutkan konsultasi hukummu.</p>

        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

        <form method="post" novalidate>
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
          </div>
          <div class="mb-4">
            <label class="form-label" for="password">Kata sandi</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <button class="btn btn-navy w-100 py-2" type="submit">Masuk</button>
        </form>
        <p class="text-center mt-4 mb-0 small">Belum punya akun? <a href="<?= url('auth/register.php') ?>">Daftar di sini</a></p>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
