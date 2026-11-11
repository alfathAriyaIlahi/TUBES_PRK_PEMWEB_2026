<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR AKSI KOMENTAR - ORANG 4
require_editor_or_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

verify_csrf();
$id = (int) ($_POST['id'] ?? 0);
$status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';

$stmt = db()->prepare(
    'SELECT cm.id, a.faculty_id
     FROM comments cm
     INNER JOIN articles a ON a.id = cm.article_id
     WHERE cm.id = ? LIMIT 1'
);
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row || (!is_admin() && (int) $row['faculty_id'] !== (int) current_user()['faculty_id'])) {
    http_response_code(404);
    exit('Komentar tidak ditemukan.');
}

if ($status === 'delete') {
    db()->prepare('DELETE FROM comments WHERE id = ?')->execute([$id]);
} elseif (in_array($status, ['approved', 'rejected', 'pending'], true)) {
    db()->prepare('UPDATE comments SET status = ? WHERE id = ?')->execute([$status, $id]);
} else {
    flash('error', 'Aksi moderasi tidak valid.');
    redirect('admin/komentar.html');
}

flash('success', 'Komentar berhasil diperbarui.');
redirect('admin/komentar.html');
