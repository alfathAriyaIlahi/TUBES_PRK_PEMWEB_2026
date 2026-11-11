<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

wajibLogin();

$userId = (int)$_SESSION['user_id'];
$errorsProfil = [];
$errorsPassword = [];

$stmt = $pdo->prepare('SELECT id, nama, email, password, role, created_at FROM users WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: /logout.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasiCsrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'update_profil') {
        $nama  = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nama)) {
            $errorsProfil[] = 'Nama lengkap wajib diisi.';
        } elseif (strlen($nama) > 100) {
            $errorsProfil[] = 'Nama lengkap maksimal 100 karakter.';
        }

        if (empty($email)) {
            $errorsProfil[] = 'Alamat email wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorsProfil[] = 'Format alamat email tidak valid.';
        } else {
            if ($email !== $user['email']) {
                $stmtCek = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
                $stmtCek->execute([
                    ':email' => $email,
                    ':id'    => $userId
                ]);
                if ($stmtCek->fetch()) {
                    $errorsProfil[] = 'Alamat email ini sudah digunakan oleh akun lain.';
                }
            }
        }

        if (empty($errorsProfil)) {
            $stmtUpdate = $pdo->prepare('UPDATE users SET nama = :nama, email = :email WHERE id = :id');
            $sukses = $stmtUpdate->execute([
                ':nama'  => $nama,
                ':email' => $email,
                ':id'    => $userId
            ]);

            if ($sukses) {
                $_SESSION['nama']  = $nama;
                $_SESSION['email'] = $email;
                setFlash('success', 'Informasi profil berhasil diperbarui.');
                header('Location: /profil.php');
                exit;
            } else {
                $errorsProfil[] = 'Gagal menyimpan perubahan profil ke sistem.';
            }
        }
    }

    elseif ($action === 'update_password') {
        $passwordLama       = $_POST['password_lama'] ?? '';
        $passwordBaru       = $_POST['password_baru'] ?? '';
        $konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

        if (empty($passwordLama)) {
            $errorsPassword[] = 'Kata sandi saat ini wajib diisi.';
        } elseif (!password_verify($passwordLama, $user['password'])) {
            $errorsPassword[] = 'Kata sandi saat ini tidak cocok.';
        }

        if (empty($passwordBaru)) {
            $errorsPassword[] = 'Kata sandi baru wajib diisi.';
        } elseif (strlen($passwordBaru) < 6) {
            $errorsPassword[] = 'Kata sandi baru minimal 6 karakter.';
        }

        if ($passwordBaru !== $konfirmasiPassword) {
            $errorsPassword[] = 'Konfirmasi kata sandi baru tidak cocok.';
        }

        if (empty($errorsPassword)) {
            $hashBaru = password_hash($passwordBaru, PASSWORD_DEFAULT);

            $stmtPass = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
            $sukses = $stmtPass->execute([
                ':password' => $hashBaru,
                ':id'       => $userId
            ]);

            if ($sukses) {
                setFlash('success', 'Kata sandi berhasil diperbarui.');
                header('Location: /profil.php');
                exit;
            } else {
                $errorsPassword[] = 'Gagal memperbarui kata sandi sistem.';
            }
        }
    }
}

$pageTitle = 'Profil Pengguna';
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 680px; margin: 0 auto;">
    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.5rem;">
            <div>
                <h2 class="card-title" style="margin-bottom: 0.25rem;">
                    <?= sanitize($user['nama']) ?>
                </h2>
                <p style="margin: 0; color: var(--color-text-muted); font-size: 0.95rem;">
                    <?= sanitize($user['email']) ?>
                </p>
            </div>
            <div>
                <span class="badge <?= $user['role'] === 'admin' ? 'badge-selesai' : 'badge-diproses' ?>">
                    <?= sanitize(ucfirst($user['role'])) ?>
                </span>
            </div>
        </div>
        <hr style="border: none; border-top: 1px solid var(--color-border); margin: 1rem 0;">
        <small style="color: var(--color-text-muted);">
            Terdaftar sejak: <?= date('d M Y, H:i', strtotime($user['created_at'])) ?> WIB
        </small>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <h3 class="card-title" style="font-size: 1.15rem; margin-bottom: 0.5rem;">
            Perbarui Data Diri
        </h3>
        <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.25rem;">
            Ubah nama lengkap atau alamat email akun Anda.
        </p>

        <?php if (!empty($errorsProfil)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    <?php foreach ($errorsProfil as $err): ?>
                        <li><?= sanitize($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/profil.php" method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_profil">

            <div class="form-group">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <input 
                    type="text" 
                    id="nama" 
                    name="nama" 
                    class="form-control" 
                    value="<?= sanitize($_POST['nama'] ?? $user['nama']) ?>" 
                    required 
                    maxlength="100"
                >
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Alamat Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    value="<?= sanitize($_POST['email'] ?? $user['email']) ?>" 
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary">
                Simpan Perubahan Data Diri
            </button>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title" style="font-size: 1.15rem; margin-bottom: 0.5rem;">
            Ganti Kata Sandi
        </h3>
        <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.25rem;">
            Untuk keamanan, masukkan kata sandi saat ini sebelum menentukan kata sandi baru.
        </p>

        <?php if (!empty($errorsPassword)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    <?php foreach ($errorsPassword as $err): ?>
                        <li><?= sanitize($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/profil.php" method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_password">

            <div class="form-group">
                <label for="password_lama" class="form-label">Kata Sandi Saat Ini</label>
                <input 
                    type="password" 
                    id="password_lama" 
                    name="password_lama" 
                    class="form-control" 
                    placeholder="Masukkan kata sandi lama Anda" 
                    required
                >
            </div>

            <div class="form-group">
                <label for="password_baru" class="form-label">Kata Sandi Baru</label>
                <input 
                    type="password" 
                    id="password_baru" 
                    name="password_baru" 
                    class="form-control" 
                    placeholder="Minimal 6 karakter" 
                    minlength="6" 
                    required
                >
            </div>

            <div class="form-group">
                <label for="konfirmasi_password" class="form-label">Konfirmasi Kata Sandi Baru</label>
                <input 
                    type="password" 
                    id="konfirmasi_password" 
                    name="konfirmasi_password" 
                    class="form-control" 
                    placeholder="Ulangi kata sandi baru" 
                    minlength="6" 
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary">
                Perbarui Kata Sandi
            </button>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
