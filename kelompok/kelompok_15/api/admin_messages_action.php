<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR AKSI PESAN - ORANG 3
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

verify_csrf();
$id = (int) ($_POST['id'] ?? 0);
$status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';
if (!in_array($status, ['new', 'read', 'replied', 'archived'], true)) {
    flash('error', 'Status pesan tidak valid.');
    redirect('admin/pesan.html');
}

$stmt = db()->prepare(
    "UPDATE contact_messages
     SET status = ?, replied_at = CASE WHEN ? = 'replied' THEN COALESCE(replied_at, NOW()) ELSE replied_at END
     WHERE id = ?"
);
$stmt->execute([$status, $status, $id]);
if ($stmt->rowCount() < 1) {
    $check = db()->prepare('SELECT id FROM contact_messages WHERE id = ? LIMIT 1');
    $check->execute([$id]);
    if (!$check->fetch()) {
        flash('error', 'Pesan tidak ditemukan.');
        redirect('admin/pesan.html');
    }
}

flash('success', 'Status pesan diperbarui.');
redirect('admin/pesan.html?id=' . $id);
