<?php
/**
 * File: admin/auth.php
 * Pengecekan session keamanan admin
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_admin'])) {
    header("Location: ../login.php");
    exit;
}