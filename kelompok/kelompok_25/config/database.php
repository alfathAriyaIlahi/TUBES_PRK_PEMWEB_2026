<?php
// Koneksi database (PDO). Dipakai semua modul lewat: $pdo = db();
// XAMPP dan Laragon sama-sama memakai user "root" tanpa password.

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $host = 'localhost';
        $name = 'ruang_hukum';
        $user = 'root';
        $pass = '';
        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$name;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die('Koneksi database gagal. Pastikan MySQL menyala dan database "ruang_hukum" sudah diimpor (database/schema.sql).');
        }
    }
    return $pdo;
}
