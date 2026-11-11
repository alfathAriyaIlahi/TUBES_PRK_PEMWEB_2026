<?php

session_start();

// Menghapus seluruh data session pengguna
$_SESSION = [];

// Menghapus cookie session jika digunakan
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Menghancurkan session
session_destroy();

// Mengarahkan pengguna ke halaman login
header('Location: ../../login.php');
exit;
