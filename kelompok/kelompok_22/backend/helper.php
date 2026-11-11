<?php
/**
 * File: backend/helper.php
 * Fungsi pembantu respons JSON API, penentu status stok, dan sanitasi input
 */

function jsonResponse(string $status, string $message, $data = null, int $code = 200): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    http_response_code($code);

    $response = [
        'status'  => $status,
        'message' => $message,
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function getStatusStok(int $stok): string
{
    if ($stok <= 0) {
        return 'Habis';
    }
    if ($stok <= 10) {
        return 'Menipis';
    }
    return 'Tersedia';
}

function sanitizeInput(?string $input): string
{
    if ($input === null) {
        return '';
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}