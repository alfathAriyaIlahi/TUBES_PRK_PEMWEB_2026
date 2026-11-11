<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR HAPUS KATEGORI - ORANG 2
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

verify_csrf();
$id = (int) ($_POST['id'] ?? 0);

$stmt = db()->prepare('SELECT id FROM categories WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    flash('error', 'Kategori tidak ditemukan.');
    redirect('admin/kategori.html');
}

$used = (int) db()->query('SELECT COUNT(*) FROM articles WHERE category_id = ' . (int)$id)->fetchColumn();
if ($used > 0) {
    flash('error', 'Kategori masih digunakan oleh ' . $used . ' artikel. Pindahkan artikel terlebih dahulu sebelum menghapus.');
    redirect('admin/kategori.html');
}

db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
flash('success', 'Kategori berhasil dihapus.');
redirect('admin/kategori.html');
