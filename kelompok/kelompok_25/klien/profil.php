<?php
// MODUL KLIEN (Ghani): profil klien (nama, telepon, kata sandi)
require_once __DIR__ . '/../config/app.php';
require_role('klien');

$user = current_user();
$pdo  = db();

$stmt = $pdo->prepare('SELECT id, nama, email, telepon, password FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$profil = $stmt->fetch();

$errors_profil = [];
$errors_sandi  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors_profil[] = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    } else {
        $aksi = $_POST['aksi'] ?? '';

        if ($aksi === 'profil') {
            $nama    = trim($_POST['nama'] ?? '');
            $telepon = trim($_POST['telepon'] ?? '');
            if (mb_strlen($nama) < 3 || mb_strlen($nama) > 100) {
                $errors_profil[] = 'Nama harus 3 sampai 100 karakter.';
            }
            if ($telepon !== '' && !preg_match('/^[0-9+\- ]{8,20}$/', $telepon)) {
                $errors_profil[] = 'Nomor telepon tidak valid (8 sampai 20 angka).';
            }
            if (!$errors_profil) {
                $upd = $pdo->prepare('UPDATE users SET nama = ?, telepon = ? WHERE id = ?');
                $upd->execute([$nama, $telepon, $user['id']]);
                flash('success', 'Profil diperbarui.');
                redirect(url('klien/profil.php'));
            }
            $profil['nama'] = $nama;
            $profil['telepon'] = $telepon;
        }

        if ($aksi === 'sandi') {
            $lama  = $_POST['sandi_lama'] ?? '';
            $baru  = $_POST['sandi_baru'] ?? '';
            $ulang = $_POST['sandi_ulang'] ?? '';
            if (!password_verify($lama, $profil['password'])) {
                $errors_sandi[] = 'Kata sandi lama salah.';
            }
            if (strlen($baru) < 8) {
                $errors_sandi[] = 'Kata sandi baru minimal 8 karakter.';
            }
            if ($baru !== $ulang) {
                $errors_sandi[] = 'Konfirmasi kata sandi tidak sama.';
            }
            if (!$errors_sandi) {
                $upd = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                $upd->execute([password_hash($baru, PASSWORD_DEFAULT), $user['id']]);
                flash('success', 'Kata sandi diperbarui.');
                redirect(url('klien/profil.php'));
            }
        }
    }
}

$judul = 'Profil Saya';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container page-head">
  <h2>Profil Saya</h2>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="card-rh p-4">
        <h3 class="h5">Data diri</h3>
        <?php foreach ($errors_profil as $err): ?>
          <div class="alert alert-danger"><?= e($err) ?></div>
        <?php endforeach; ?>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="aksi" value="profil">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="text" value="<?= e($profil['email']) ?>" disabled>
          </div>
          <div class="mb-3">
            <label class="form-label" for="nama">Nama</label>
            <input class="form-control" id="nama" name="nama" value="<?= e($profil['nama']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="telepon">Telepon</label>
            <input class="form-control" id="telepon" name="telepon" value="<?= e($profil['telepon'] ?? '') ?>">
          </div>
          <button class="btn btn-gold" type="submit">Simpan</button>
        </form>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card-rh p-4">
        <h3 class="h5">Ubah kata sandi</h3>
        <?php foreach ($errors_sandi as $err): ?>
          <div class="alert alert-danger"><?= e($err) ?></div>
        <?php endforeach; ?>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="aksi" value="sandi">
          <div class="mb-3">
            <label class="form-label" for="sandi_lama">Kata sandi lama</label>
            <input class="form-control" type="password" id="sandi_lama" name="sandi_lama" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="sandi_baru">Kata sandi baru</label>
            <input class="form-control" type="password" id="sandi_baru" name="sandi_baru" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="sandi_ulang">Ulangi kata sandi baru</label>
            <input class="form-control" type="password" id="sandi_ulang" name="sandi_ulang" required>
          </div>
          <button class="btn btn-navy" type="submit">Ubah Kata Sandi</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>