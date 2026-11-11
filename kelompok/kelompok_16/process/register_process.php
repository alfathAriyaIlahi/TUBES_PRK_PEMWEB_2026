<?php
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../register.php');
}
if (!isCsrfValid($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Token keamanan tidak valid. Coba lagi.');
    redirect('../register.php');
}
if (!isGuest($currentUser)) {
    setFlash('error', 'Kamu sudah login. Keluar dulu jika ingin mendaftar akun lain.');
    redirect('../index.php');
}

$username        = trim((string) ($_POST['username'] ?? ''));
$email           = trim((string) ($_POST['email'] ?? ''));
$password        = (string) ($_POST['password'] ?? '');
$passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

setOldInput(['username' => $username, 'email' => $email]);

$error = '';
if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
    $error = 'Username 3-20 karakter, hanya huruf, angka, dan underscore.';
} elseif (stripos($username, 'pengguna-') === 0) {
    $error = 'Username tidak boleh diawali "Pengguna-".';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
    $error = 'Format email tidak valid.';
} elseif (mb_strlen($password) < 8) {
    $error = 'Password minimal 8 karakter.';
} elseif ($password !== $passwordConfirm) {
    $error = 'Konfirmasi password tidak sama.';
}

if ($error === '') {
    $cek = $pdo->prepare('SELECT username, email FROM users WHERE username = :username OR email = :email LIMIT 1');
    $cek->execute([':username' => $username, ':email' => $email]);
    $ada = $cek->fetch();
    if ($ada) {
        $error = (strcasecmp($ada['username'], $username) === 0)
            ? 'Username sudah dipakai.'
            : 'Email sudah terdaftar.';
    }
}

if ($error !== '') {
    setFlash('error', $error);
    redirect('../register.php');
}

try {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $ubah = $pdo->prepare(
        'UPDATE users SET username = :username, email = :email, password_hash = :hash, is_guest = 0
         WHERE id = :id AND is_guest = 1'
    );
    $ubah->execute([
        ':username' => $username,
        ':email'    => $email,
        ':hash'     => $hash,
        ':id'       => $currentUser['id'],
    ]);
} catch (PDOException $exception) {
    setFlash('error', 'Username atau email sudah dipakai.');
    redirect('../register.php');
}

session_regenerate_id(true);
unset($_SESSION['csrf_token']);
pullOldInput();

setFlash('success', 'Pendaftaran berhasil. Postingan sebelumnya tetap tersimpan di akunmu.');
redirect('../index.php');
