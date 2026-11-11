<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR HAPUS PESAN - ORANG 3
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

verify_csrf();
$id = (int) ($_POST['id'] ?? 0);

$stmt = db()->prepare('DELETE FROM contact_messages WHERE id = ?');
$stmt->execute([$id]);
if ($stmt->rowCount() < 1) {
    flash('error', 'Pesan tidak ditemukan.');
    redirect('admin/pesan.html');
}

flash('success', 'Pesan berhasil dihapus.');
redirect('admin/pesan.html');
