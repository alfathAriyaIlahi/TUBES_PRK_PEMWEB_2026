<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR KONTAK - ORANG 3
$pageTitle = 'Kontak';
$activeNav = 'contact';
$error = '';
$oldInput = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach (array_keys($oldInput) as $key) {
        $value = $_POST[$key] ?? '';
        $oldInput[$key] = is_string($value) ? trim($value) : '';
    }

    [$name, $email, $subject, $message] = array_values($oldInput);

    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        $error = 'Mohon lengkapi semua kolom dengan benar.';
    } elseif (strlen($name) > 120 || strlen($email) > 190 || strlen($subject) > 180 || strlen($message) > 10000) {
        $error = 'Panjang salah satu kolom melebihi batas yang diperbolehkan.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Alamat email tidak valid.';
    } else {
        $stmt = db()->prepare(
            "INSERT INTO contact_messages (name, email, subject, message, status)
             VALUES (?, ?, ?, ?, 'new')"
        );
        $stmt->execute([$name, $email, $subject, $message]);
        flash('success', 'Pesan berhasil dikirim. Terima kasih telah menghubungi Pena Kampus.');
        redirect('kontak.html');
    }
}



$view = [];
$view['success_flash_success'] = [];
if ($success = flash('success')) {
    $item1 = [];
    $item1['success'] = e($success);
    $view['success_flash_success'][] = $item1;
}
$view['error'] = [];
if ($error) {
    $item1 = [];
    $item1['error2'] = e($error);
    $view['error'][] = $item1;
}
$view['csrf_token'] = csrf_token();
$view['oldInput_name'] = e($oldInput['name']);
$view['oldInput_email'] = e($oldInput['email']);
$view['oldInput_subject'] = e($oldInput['subject']);
$view['oldInput_message'] = e($oldInput['message']);

json_response([
    'content' => $view,
    'title' => $pageTitle . ' | Pena Kampus',
    'layout' => public_layout($activeNav ?? ''),
]);
