<?php
require_once __DIR__ . '/../config/app.php';

if (is_logged_in()) {
    redirect(dashboard_path(current_user()['role']));
}

$pdo = db();
$bidang = $pdo->query('SELECT id, nama FROM bidang_hukum ORDER BY nama')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = trim($_POST['nama'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $pass   = $_POST['password'] ?? '';
    $pass2  = $_POST['password2'] ?? '';
    $role   = ($_POST['role'] ?? 'klien') === 'pengacara' ? 'pengacara' : 'klien';
    $bidang_id = (int) ($_POST['bidang_id'] ?? 0);
    $no_izin   = trim($_POST['no_izin'] ?? '');

    if (!csrf_valid())                              $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman.';
    if (mb_strlen($nama) < 3)                       $errors[] = 'Nama minimal 3 huruf.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (strlen($pass) < 8)                          $errors[] = 'Kata sandi minimal 8 karakter.';
    if ($pass !== $pass2)                           $errors[] = 'Konfirmasi kata sandi tidak sama.';
    if ($role === 'pengacara' && $bidang_id < 1)    $errors[] = 'Pilih bidang keahlian.';
    if ($role === 'pengacara' && $no_izin === '')   $errors[] = 'Nomor izin advokat wajib diisi.';

    if (!$errors) {
        $cek = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $cek->execute([$email]);
        if ($cek->fetch()) $errors[] = 'Email sudah terdaftar.';
    }

    if (!$errors) {
        $status = $role === 'pengacara' ? 'menunggu' : 'aktif';
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO users (nama, email, password, role, status) VALUES (?,?,?,?,?)')
            ->execute([$nama, $email, password_hash($pass, PASSWORD_DEFAULT), $role, $status]);
        if ($role === 'pengacara') {
            $pdo->prepare('INSERT INTO profil_pengacara (user_id, bidang_id, no_izin) VALUES (?,?,?)')
                ->execute([$pdo->lastInsertId(), $bidang_id, $no_izin]);
        }
        $pdo->commit();

        flash('success', $role === 'pengacara'
            ? 'Pendaftaran berhasil. Akunmu akan aktif setelah diverifikasi admin.'
            : 'Pendaftaran berhasil. Silakan masuk.');
        redirect('auth/login.php');
    }
}

$judul = 'Daftar';
$roleDipilih = $_POST['role'] ?? 'klien';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="card-rh p-4 p-md-5">
        <h2 class="mb-1">Buat akun</h2>
        <p class="text-secondary mb-4">Daftar sebagai klien atau pengacara.</p>

        <?php if ($errors): ?>
          <div class="alert alert-danger"><ul class="mb-0 ps-3">
            <?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
          </ul></div>
        <?php endif; ?>

        <form method="post" novalidate>
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label d-block">Daftar sebagai</label>
            <div class="btn-group w-100" role="group">
              <input type="radio" class="btn-check" name="role" id="r-klien" value="klien" <?= $roleDipilih !== 'pengacara' ? 'checked' : '' ?>>
              <label class="btn btn-outline-secondary" for="r-klien"><i class="bi bi-person"></i> Klien</label>
              <input type="radio" class="btn-check" name="role" id="r-pengacara" value="pengacara" <?= $roleDipilih === 'pengacara' ? 'checked' : '' ?>>
              <label class="btn btn-outline-secondary" for="r-pengacara"><i class="bi bi-briefcase"></i> Pengacara</label>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="nama">Nama lengkap</label>
            <input class="form-control" id="nama" name="nama" value="<?= e($_POST['nama'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="password">Kata sandi</label>
              <input type="password" class="form-control" id="password" name="password" minlength="8" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label" for="password2">Ulangi kata sandi</label>
              <input type="password" class="form-control" id="password2" name="password2" required>
            </div>
          </div>

          <div id="kolom-pengacara" class="border rounded p-3 mb-3 bg-light" style="display:none">
            <div class="mb-3">
              <label class="form-label" for="bidang_id">Bidang keahlian</label>
              <select class="form-select" id="bidang_id" name="bidang_id">
                <option value="">Pilih bidang</option>
                <?php foreach ($bidang as $b): ?>
                  <option value="<?= $b['id'] ?>" <?= (int) ($_POST['bidang_id'] ?? 0) === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['nama']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="form-label" for="no_izin">Nomor izin advokat</label>
              <input class="form-control" id="no_izin" name="no_izin" value="<?= e($_POST['no_izin'] ?? '') ?>">
              <div class="form-text">Akun pengacara aktif setelah diverifikasi admin.</div>
            </div>
          </div>

          <button class="btn btn-navy w-100 py-2" type="submit">Daftar</button>
        </form>
        <p class="text-center mt-4 mb-0 small">Sudah punya akun? <a href="<?= url('auth/login.php') ?>">Masuk</a></p>
      </div>
    </div>
  </div>
</div>
<script>
  const kolom = document.getElementById('kolom-pengacara');
  function atur() { kolom.style.display = document.getElementById('r-pengacara').checked ? 'block' : 'none'; }
  document.querySelectorAll('input[name=role]').forEach(r => r.addEventListener('change', atur));
  atur();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
