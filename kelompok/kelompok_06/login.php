
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

// [TAMBAHAN] Pesan setelah registrasi berhasil
$success = isset($_GET['registered'])
    ? "Registrasi berhasil! Silakan login menggunakan akun baru."
    : "";

// Memeriksa apakah form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Mencari akun berdasarkan username
    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM users WHERE username = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    // Memeriksa password dengan hash
    if ($user && password_verify($password, $user['password'])) {

        // Membuat session baru
        session_regenerate_id(true);

        // Menyimpan identitas pengguna
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Mengarahkan pengguna
        header("Location: index.php");
        exit;

    } else {
        $error = "Username atau password salah!";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login FlashPOS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">

        <h1 class="text-2xl font-bold text-center">
            FlashPOS
        </h1>

        <p class="text-sm text-center text-slate-500 mt-2 mb-6">
            Silakan login untuk masuk ke sistem
        </p>

        <!-- [TAMBAHAN] Pesan registrasi berhasil -->
        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label class="block text-sm font-medium mb-2">
                Username
            </label>

            <input
                type="text"
                name="username"
                required
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
                class="w-full border rounded-lg p-3 mb-6"
                placeholder="Masukkan password"
            >

            <button
                type="submit"
                class="w-full bg-slate-900 text-white p-3 rounded-lg"
            >
                Login
            </button>

        </form>

        <!-- [TAMBAHAN] Link menuju halaman registrasi -->
        <p class="text-sm text-center text-slate-500 mt-5">
            Belum memiliki akun?
            <a href="register.php" class="text-blue-600 font-medium hover:underline">
                Daftar sebagai Kasir
            </a>
        </p>

    </div>

</body>
</html>
