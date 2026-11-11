<?php
// MODUL KLIEN (Ghani): detail konsultasi, balas, tandai selesai
require_once __DIR__ . '/../config/app.php';
require_role('klien');

$user = current_user();
$pdo  = db();
$id   = (int) ($_GET['id'] ?? 0);

// Klien hanya boleh membuka konsultasi miliknya sendiri
$stmt = $pdo->prepare(
    'SELECT k.*, b.nama AS bidang, u.nama AS pengacara
     FROM konsultasi k
     LEFT JOIN bidang_hukum b ON b.id = k.bidang_id
     LEFT JOIN users u ON u.id = k.pengacara_id
     WHERE k.id = ? AND k.klien_id = ?'
);
$stmt->execute([$id, $user['id']]);
$k = $stmt->fetch();

if (!$k) {
    http_response_code(404);
    $judul = 'Konsultasi tidak ditemukan';
    require_once __DIR__ . '/../includes/header.php';
    echo '<div class="container page-head"><div class="card-rh p-4">Konsultasi tidak ditemukan atau bukan milikmu. '
       . '<a href="' . e(url('klien/dashboard.php')) . '">Kembali ke dashboard</a></div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$errors = [];
$aktif  = in_array($k['status'], ['menunggu', 'dijawab'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    } elseif (!$aktif) {
        $errors[] = 'Konsultasi ini sudah ditutup, tidak bisa dibalas lagi.';
    } else {
        $aksi = $_POST['aksi'] ?? '';
        if ($aksi === 'balas') {
            $isi = trim($_POST['isi'] ?? '');
            if (mb_strlen($isi) < 2) {
                $errors[] = 'Isi balasan tidak boleh kosong.';
            } else {
                $ins = $pdo->prepare('INSERT INTO balasan (konsultasi_id, pengirim_id, isi) VALUES (?, ?, ?)');
                $ins->execute([$k['id'], $user['id'], $isi]);
                flash('success', 'Balasan terkirim.');
                redirect(url('klien/konsultasi_detail.php?id=' . $k['id']));
            }
        } elseif ($aksi === 'selesai') {
            $upd = $pdo->prepare("UPDATE konsultasi SET status = 'selesai' WHERE id = ? AND klien_id = ?");
            $upd->execute([$k['id'], $user['id']]);
            flash('success', 'Konsultasi ditandai selesai.');
            redirect(url('klien/konsultasi_detail.php?id=' . $k['id']));
        }
    }
}

$stmt = $pdo->prepare(
    'SELECT bl.id, bl.isi, bl.pengirim_id, u.nama, u.role
     FROM balasan bl JOIN users u ON u.id = bl.pengirim_id
     WHERE bl.konsultasi_id = ? ORDER BY bl.id ASC'
);
$stmt->execute([$k['id']]);
$balasan = $stmt->fetchAll();

$judul = 'Detail Konsultasi';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container page-head">
  <a href="<?= e(url('klien/dashboard.php')) ?>" class="text-decoration-none">&larr; Kembali ke dashboard</a>

  <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 mb-3 gap-2">
    <h2 class="mb-0"><?= e($k['judul']) ?></h2>
    <span class="badge-status badge-<?= e($k['status']) ?>"><?= e(ucfirst($k['status'])) ?></span>
  </div>
  <p class="text-secondary">
    Bidang: <?= e($k['bidang'] ?? '-') ?> &middot; Pengacara: <?= e($k['pengacara'] ?? 'Belum ada') ?>
  </p>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
  <?php endforeach; ?>

  <div class="card-rh p-4 mb-3">
    <strong>Pertanyaanmu</strong>
    <p class="mb-0 mt-2"><?= nl2br(e($k['isi'])) ?></p>
  </div>

  <?php foreach ($balasan as $b): ?>
    <?php $saya = ((int) $b['pengirim_id'] === (int) $user['id']); ?>
    <div class="d-flex <?= $saya ? 'justify-content-end' : 'justify-content-start' ?> mb-3">
      <div class="card-rh p-3" style="max-width: 80%;">
        <small class="text-secondary">
          <?= $saya ? 'Kamu' : e($b['nama']) . ' (' . e($b['role']) . ')' ?>
        </small>
        <div class="mt-1"><?= nl2br(e($b['isi'])) ?></div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (!$balasan): ?>
    <p class="text-secondary">Belum ada balasan.</p>
  <?php endif; ?>

  <?php if ($aktif): ?>
    <div class="card-rh p-4 mt-4">
      <form method="post" class="mb-3">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="balas">
        <label class="form-label" for="isi">Balas</label>
        <textarea class="form-control mb-3" id="isi" name="isi" rows="4" required></textarea>
        <button class="btn btn-gold" type="submit">Kirim Balasan</button>
      </form>
      <form method="post" onsubmit="return confirm('Tandai konsultasi ini selesai? Setelah itu tidak bisa dibalas lagi.');">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="selesai">
        <button class="btn btn-navy" type="submit">Tandai selesai</button>
      </form>
    </div>
  <?php else: ?>
    <div class="alert alert-secondary mt-4">Konsultasi ini sudah <?= e($k['status']) ?>.</div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>