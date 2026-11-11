<?php
// MODUL ADMIN (Linda) - Verifikasi Pengacara
require_once __DIR__ . '/../config/app.php';
require_role('admin');

// Proses aksi Setujui atau Tolak jika tombol diklik
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('Token CSRF tidak valid.', 'danger');
        redirect('verifikasi.php');
    }

    $user_id = $_POST['user_id'] ?? null;
    $aksi = $_POST['aksi'] ?? null;

    if ($user_id && in_array($aksi, ['aktif', 'ditolak'])) {
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'pengacara'");
        $stmt->execute([$aksi, $user_id]);
        
        $pesan = ($aksi === 'aktif') ? 'Pengacara berhasil disetujui.' : 'Pengacara ditolak.';
        set_flash($pesan, 'success');
    }
    redirect('verifikasi.php');
}

// Ambil daftar pengacara yang statusnya 'menunggu' beserta data profilnya
$stmt = $pdo->query("
    SELECT u.id, u.nama, u.email, p.no_izin, p.pengalaman_tahun, p.kota, b.nama_ bidang 
    FROM users u
    JOIN profil_pengacara p ON u.id = p.user_id
    LEFT JOIN bidang_hukum b ON p.bidang_id = b.id
    WHERE u.role = 'pengacara' AND u.status = 'menunggu'
");
$pending_pengacara = $stmt->fetchAll();

$judul = 'Verifikasi Pengacara';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container page-head my-4">
    <h2>Verifikasi Pendaftaran Pengacara</h2>
    <p class="text-secondary">Daftar pengacara yang mendaftar dan menunggu persetujuan admin.</p>

    <?php if (has_flash()): ?>
        <div class="alert alert-<?= flash_type() ?> alert-dismissible fade show" role="alert">
            <?= flash() ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <?php if (empty($pending_pengacara)): ?>
                <div class="alert alert-info mb-0">Tidak ada pengacara yang menunggu verifikasi saat ini.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. Izin</th>
                                <th>Pengalaman</th>
                                <th>Kota</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_pengacara as $index => $p): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= e($p['nama']) ?></td>
                                    <td><?= e($p['email']) ?></td>
                                    <td><?= e($p['no_izin']) ?></td>
                                    <td><?= e($p['pengalaman_tahun']) ?> Tahun</td>
                                    <td><?= e($p['kota']) ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="user_id" value="<?= e($p['id']) ?>">
                                            <button type="submit" name="aksi" value="aktif" class="btn btn-success btn-sm" onclick="return confirm('Setujui pengacara ini?')">Setujui</button>
                                            <button type="submit" name="aksi" value="ditolak" class="btn btn-danger btn-sm" onclick="return confirm('Tolak pengacara ini?')">Tolak</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>