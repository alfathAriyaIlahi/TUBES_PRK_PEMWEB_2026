<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - SaveSmart</title>
</head>
<body>

    <h2>Registrasi SaveSmart</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <p><?= htmlspecialchars($_SESSION['error']) ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="backend/auth/register_process.php" method="POST">

        <label>Nama Keluarga</label><br>
        <input type="text" name="nama_keluarga" required>
        <br><br>

        <label>Nama Lengkap</label><br>
        <input type="text" name="nama_lengkap" required>
        <br><br>

        <label>Email</label><br>
        <input type="email" name="email" required>
        <br><br>

        <label>Password</label><br>
        <input type="password" name="password" required>
        <br><br>

        <label>Konfirmasi Password</label><br>
        <input type="password" name="konfirmasi_password" required>
        <br><br>

        <button type="submit">Daftar</button>

    </form>

    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>

</body>
</html>