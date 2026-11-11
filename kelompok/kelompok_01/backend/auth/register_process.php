
<?php

session_start();

require_once __DIR__ . '/../../config/database.php';

// Memastikan data dikirim menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../register.php');
    exit;
}

// Mengambil data dari form
$namaKeluarga = trim($_POST['nama_keluarga'] ?? '');
$namaLengkap = trim($_POST['nama_lengkap'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

// Validasi input
if (
    $namaKeluarga === '' ||
    $namaLengkap === '' ||
    $email === '' ||
    $password === '' ||
    $konfirmasiPassword === ''
) {
    $_SESSION['error'] = 'Semua kolom wajib diisi.';
    header('Location: ../../register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Format email tidak valid.';
    header('Location: ../../register.php');
    exit;
}

if (strlen($password) < 8) {
    $_SESSION['error'] = 'Password minimal 8 karakter.';
    header('Location: ../../register.php');
    exit;
}

if ($password !== $konfirmasiPassword) {
    $_SESSION['error'] = 'Konfirmasi password tidak cocok.';
    header('Location: ../../register.php');
    exit;
}

try {
    // Mengamankan password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Memanggil stored procedure registrasi
    $stmt = $pdo->prepare('CALL sp_registrasi(?, ?, ?, ?)');

    $stmt->execute([
        $namaKeluarga,
        $namaLengkap,
        $email,
        $passwordHash
    ]);

    $stmt->closeCursor();

    $_SESSION['success'] = 'Registrasi berhasil. Silakan login.';
    header('Location: ../../login.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = 'Registrasi gagal. Periksa kembali data atau email Anda.';
    header('Location: ../../register.php');
    exit;
}
