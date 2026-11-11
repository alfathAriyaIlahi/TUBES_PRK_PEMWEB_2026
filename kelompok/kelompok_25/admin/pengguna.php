<?php
// MODUL ADMIN (Linda) - Manajemen Pengguna
require_once __DIR__ . '/../config/app.php';
require_role('admin');

// Proses aksi ubah status (aktif / nonaktif)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('Token CSRF tidak valid.', 'danger');
        redirect('pengguna.php');
    }

    $user_id = $_POST['user_id'] ?? null;
    $aksi = $_POST['aksi'] ?? null;

    // Pastikan admin tidak menonaktifkan akun sendiri
    if ($user_id == current_user()['id']) {
        set_flash('Anda tidak dapat mengubah status akun sendiri.', 'danger');
        redirect('pengguna.php');
    }

    if ($user_id && in_array($aksi, ['aktif', 'nonaktif'])) {
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$aksi, $user_id]);
        
        $pesan = ($aksi === 'aktif') ? 'Pengguna berhasil diaktifkan.' : 'Pengguna dinonaktifkan.';
        set_flash($pesan, 'success');
    }
    redirect('pengguna.php');
}

// Ambil semua data pengguna
$stmt = $pdo->query("SELECT id, nama, email, role, status, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

$judul = 'Kelola Pengguna';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container page-head my-4">
    <h2>Manajemen Pengguna</h2>
    <p class="text-secondary">Daftar seluruh akun pengguna yang terdaftar di sistem Ruang Hukum.</p>

    <?php if (has_flash()): ?>
        <div class="alert alert-<?= flash_type() ?> alert-dismissible fade show" role="alert">
            <?= flash() ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Peran</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $u): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= e($u['nama']) ?></td>
                                <td><?= e($u['email']) ?></td>
                                <td>
                                    <span class="badge bg-secondary text-uppercase"><?= e($u['role']) ?></span>
                                </td>
                                <td>
                                    <?php if ($u['status'] === 'aktif'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php elseif ($u['status'] === 'menunggu'): ?>
                                        <span class="badge bg-warning text-dark">Menunggu</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($u['created_at']) ?></td>
                                <td>
                                    <?php if ($u['id'] != current_user()['id']): ?>
                                        <form method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="user_id" value="<?= e($u['id']) ?>">
                                            <?php if ($u['status'] === 'aktif'): ?>
                                                <button type="submit" name="aksi" value="nonaktif" class="btn btn-danger btn-sm" onclick="return confirm('Nonaktifkan pengguna ini?')">Nonaktifkan</button>
                                            <?php else: ?>
                                                <button type="submit" name="aksi" value="aktif" class="btn btn-success btn-sm" onclick="return confirm('Aktifkan pengguna ini?')">Aktifkan</button>
                                            <?php endif; ?>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">Akun Anda</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>