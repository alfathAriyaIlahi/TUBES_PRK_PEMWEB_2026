<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR TOGGLE AKUN - ORANG 5
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

verify_csrf();
$id = (int) ($_POST['id'] ?? 0);
$status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';

if ($id === (int) current_user()['id']) {
    flash('error', 'Kamu tidak dapat menonaktifkan atau mengubah status akun sendiri dari menu ini.');
    redirect('admin/akun.html');
}
if (!in_array($status, ['active', 'inactive'], true)) {
    flash('error', 'Status akun tidak valid.');
    redirect('admin/akun.html');
}

$stmt = db()->prepare('SELECT id, role, status FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$target = $stmt->fetch();
if (!$target) {
    flash('error', 'Pengguna tidak ditemukan.');
    redirect('admin/akun.html');
}

if ($target['role'] === 'admin' && $target['status'] === 'active' && $status === 'inactive') {
    $activeAdmins = (int) db()->query(
        "SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active'"
    )->fetchColumn();
    if ($activeAdmins <= 1) {
        flash('error', 'Administrator aktif terakhir tidak dapat dinonaktifkan.');
        redirect('admin/akun.html');
    }
}

db()->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([$status, $id]);
flash('success', 'Status akun diperbarui.');
redirect('admin/akun.html');
