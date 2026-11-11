<?php

date_default_timezone_set('Asia/Jakarta'); //time zone yang dengan daerah jakarta

if (session_status() === PHP_SESSION_NONE) {
    $masaSession = 60 * 60 * 24; //Sesi ini untuk seberapa lama akun aktif di web (default 24 jam)
    ini_set('session.gc_maxlifetime', (string) $masaSession);
    ini_set('session.use_strict_mode', '1');

    session_set_cookie_params([
        'lifetime' => $masaSession,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function getCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function isCsrfValid(?string $token): bool
{
    return !empty($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
