<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'ruang_hukum');
define('DB_USER', 'RlazDB');   // user default (Bisa saja di ubah sesuaikan)
define('DB_PASS', 'r476');       // password default (Bisa di ubah sesusai dengan akunya)

function getDatabase(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      
            PDO::ATTR_EMULATE_PREPARES   => false,                  
        ];
        $pdo = new PDO($dsn,DB_USER,DB_PASS,$options);

        $pdo->exec("SET time_zone = '+07:00'"); //Seperti zona waktu jakarta
    }

    return $pdo;
}
