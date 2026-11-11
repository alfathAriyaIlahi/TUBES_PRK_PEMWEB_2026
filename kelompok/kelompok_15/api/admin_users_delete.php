<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR HAPUS AKUN - ORANG 4
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

verify_csrf();
$id = (int) ($_POST['id'] ?? 0);

if ($id === (int) current_user()['id']) {
    flash('error', 'Kamu tidak dapat menghapus akun sendiri.');
    redirect('admin/akun.html');
}

$stmt = db()->prepare('SELECT id, role, status FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$target = $stmt->fetch();
if (!$target) {
    flash('error', 'Pengguna tidak ditemukan.');
    redirect('admin/akun.html');
}

if ($target['role'] === 'admin' && $target['status'] === 'active') {
    $activeAdmins = (int) db()->query(
        "SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active'"
    )->fetchColumn();
    if ($activeAdmins <= 1) {
        flash('error', 'Administrator aktif terakhir tidak dapat dihapus.');
        redirect('admin/akun.html');
    }
}

$articleCount = (int) db()->query('SELECT COUNT(*) FROM articles WHERE author_id = ' . (int)$id)->fetchColumn();
if ($articleCount > 0) {
    flash('error', 'Akun memiliki ' . $articleCount . ' artikel. Pindahkan atau hapus artikel terlebih dahulu.');
    redirect('admin/akun.html');
}

db()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
flash('success', 'Pengguna berhasil dihapus.');
redirect('admin/akun.html');
