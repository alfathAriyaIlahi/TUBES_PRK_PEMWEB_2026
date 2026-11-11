<?php
// Memulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menghapus seluruh data session
session_unset();

// Menghancurkan session
session_destroy();

// Mengarahkan pengguna kembali ke halaman login
header("Location: login.php");
exit;
?>
