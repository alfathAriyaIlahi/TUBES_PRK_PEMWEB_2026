<?php
// Memulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login, arahkan ke halaman utama
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Menghubungkan database
require_once 'config/database.php';

$error = "";
$nama_lengkap = "";
$username = "";

// Memeriksa apakah form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

    // Memeriksa kelengkapan data
    if ($nama_lengkap === '' || $username === '' || $password === '' || $konfirmasi_password === '') {
        $error = "Semua kolom wajib diisi!";

    // Memeriksa kesesuaian password
    } elseif ($password !== $konfirmasi_password) {
        $error = "Konfirmasi password tidak sesuai!";

    // Memeriksa panjang password
    } elseif (strlen($password) < 8) {
        $error = "Password minimal 8 karakter!";

    } else {

        // Memeriksa apakah username sudah digunakan
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE username = ?"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $existing_user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if ($existing_user) {
            $error = "Username sudah digunakan!";

        } else {

            // Mengenkripsi password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Role otomatis sebagai kasir
            $role = "kasir";

            // Menyimpan akun baru
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (nama_lengkap, username, password, role)
                 VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $nama_lengkap,
                $username,
                $password_hash,
                $role
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                header("Location: login.php?registered=1");
                exit;

            } else {
                $error = "Registrasi gagal. Silakan coba kembali!";
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi FlashPOS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">

        <h1 class="text-2xl font-bold text-center">
            FlashPOS
        </h1>

        <p class="text-sm text-center text-slate-500 mt-2 mb-6">
            Buat akun kasir baru
        </p>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label class="block text-sm font-medium mb-2">
                Nama Lengkap
            </label>

            <input
                type="text"
                name="nama_lengkap"
                required
                value="<?= htmlspecialchars($nama_lengkap) ?>"
                class="w-full border rounded-lg p-3 mb-4"
                placeholder="Masukkan nama lengkap"
            >

            <label class="block text-sm font-medium mb-2">
                Username
            </label>

            <input
                type="text"
                name="username"
                required
                value="<?= htmlspecialchars($username) ?>"
                class="w-full border rounded-lg p-3 mb-4"
                placeholder="Masukkan username"
            >

            <label class="block text-sm font-medium mb-2">
                Password
            </label>

            <input
                type="password"
                name="password"
                required
                minlength="8"
                class="w-full border rounded-lg p-3 mb-4"
                placeholder="Minimal 8 karakter"
            >

            <label class="block text-sm font-medium mb-2">
                Konfirmasi Password
            </label>

            <input
                type="password"
                name="konfirmasi_password"
                required
                minlength="8"
                class="w-full border rounded-lg p-3 mb-6"
                placeholder="Ulangi password"
            >

            <button
                type="submit"
                class="w-full bg-slate-900 text-white p-3 rounded-lg hover:bg-slate-800"
            >
                Daftar sebagai Kasir
            </button>

        </form>

        <p class="text-sm text-center text-slate-500 mt-5">
            Sudah memiliki akun?
            <a href="login.php" class="text-blue-600 font-medium hover:underline">
                Login di sini
            </a>
        </p>

    </div>

</body>
</html>
