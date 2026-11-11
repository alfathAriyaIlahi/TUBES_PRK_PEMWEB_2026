<?php
// MODUL KLIEN (Ghani): form ajukan konsultasi
require_once __DIR__ . '/../config/app.php';
require_role('klien');

$user = current_user();
$pdo  = db();

$bidang = $pdo->query('SELECT id, nama FROM bidang_hukum ORDER BY nama')->fetchAll();

$errors = [];
$bidang_id = '';
$judul_k = '';
$isi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    } else {
        $bidang_id = (int) ($_POST['bidang_id'] ?? 0);
        $judul_k   = trim($_POST['judul'] ?? '');
        $isi       = trim($_POST['isi'] ?? '');

        $cek = $pdo->prepare('SELECT COUNT(*) FROM bidang_hukum WHERE id = ?');
        $cek->execute([$bidang_id]);
        if (!$cek->fetchColumn()) {
            $errors[] = 'Pilih bidang hukum yang tersedia.';
        }
        if (mb_strlen($judul_k) < 5 || mb_strlen($judul_k) > 150) {
            $errors[] = 'Judul harus 5 sampai 150 karakter.';
        }
        if (mb_strlen($isi) < 20) {
            $errors[] = 'Isi pertanyaan minimal 20 karakter agar pengacara paham masalahmu.';
        }

        if (!$errors) {
            $ins = $pdo->prepare(
                "INSERT INTO konsultasi (klien_id, bidang_id, judul, isi, status) VALUES (?, ?, ?, ?, 'menunggu')"
            );
            $ins->execute([$user['id'], $bidang_id, $judul_k, $isi]);
            $id = $pdo->lastInsertId();
            flash('success', 'Konsultasi berhasil dikirim. Tunggu jawaban pengacara.');
            redirect(url('klien/konsultasi_detail.php?id=' . $id));
        }
    }
}

$judul = 'Ajukan Konsultasi';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container page-head">
  <h2>Ajukan Konsultasi</h2>
  <p class="text-secondary">Ceritakan masalah hukummu. Pengacara di bidang terkait akan menjawab.</p>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
  <?php endforeach; ?>

  <div class="card-rh p-4">
    <form method="post">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label" for="bidang_id">Bidang hukum</label>
        <select class="form-select" id="bidang_id" name="bidang_id" required>
          <option value="">-- Pilih bidang --</option>
          <?php foreach ($bidang as $b): ?>
            <option value="<?= (int) $b['id'] ?>" <?= (int) $bidang_id === (int) $b['id'] ? 'selected' : '' ?>>
              <?= e($b['nama']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label" for="judul">Judul</label>
        <input class="form-control" type="text" id="judul" name="judul" maxlength="150"
               value="<?= e($judul_k) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label" for="isi">Isi pertanyaan</label>
        <textarea class="form-control" id="isi" name="isi" rows="7" required><?= e($isi) ?></textarea>
      </div>
      <button class="btn btn-gold" type="submit">Kirim Konsultasi</button>
      <a class="btn btn-outline-secondary" href="<?= e(url('klien/dashboard.php')) ?>">Batal</a>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>