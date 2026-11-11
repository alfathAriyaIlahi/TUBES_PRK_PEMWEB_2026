<?php
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../login.php');
}
if (!isCsrfValid($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Token keamanan tidak valid. Coba lagi.');
    redirect('../login.php');
}

$identitas = trim((string) ($_POST['identity'] ?? ''));
$password  = (string) ($_POST['password'] ?? '');

setOldInput(['identity' => $identitas]);

if ($identitas === '' || $password === '') {
    setFlash('error', 'Username/email dan password wajib diisi.');
    redirect('../login.php');
}

$stmt = $pdo->prepare(
    'SELECT id, password_hash FROM users
     WHERE (username = :username OR email = :email) AND is_guest = 0
     LIMIT 1'
);
$stmt->execute([':username' => $identitas, ':email' => $identitas]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    setFlash('error', 'Username/email atau password salah.');
    redirect('../login.php');
}

session_regenerate_id(true);
unset($_SESSION['csrf_token']);
$_SESSION['user_id'] = (int) $user['id'];
pullOldInput();

setFlash('success', 'Selamat datang kembali!');
redirect('../index.php');
