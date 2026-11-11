<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR HAPUS FAKULTAS - ORANG 1
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

verify_csrf();
$id = (int) ($_POST['id'] ?? 0);

$stmt = db()->prepare('SELECT id FROM faculties WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    flash('error', 'Data fakultas tidak ditemukan.');
    redirect('admin/fakultas.html');
}

db()->prepare('DELETE FROM faculties WHERE id = ?')->execute([$id]);
flash('success', 'Fakultas berhasil dihapus.');
redirect('admin/fakultas.html');
