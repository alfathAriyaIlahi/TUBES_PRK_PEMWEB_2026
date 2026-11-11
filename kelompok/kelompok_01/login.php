<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SaveSmart</title>
</head>
<body>

    <h2>Login SaveSmart</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <p><?= htmlspecialchars($_SESSION['error']) ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <p><?= htmlspecialchars($_SESSION['success']) ?></p>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="backend/auth/login_process.php" method="POST">

        <label>Email</label><br>
        <input type="email" name="email" required>
        <br><br>

        <label>Password</label><br>
        <input type="password" name="password" required>
        <br><br>

        <button type="submit">Login</button>

    </form>

    <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>

</body>
</html>