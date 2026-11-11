<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR SIMPAN FAKULTAS (TAMBAH/EDIT) - ORANG 1
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

verify_csrf();
$id = (int) ($_POST['id'] ?? 0);
$code = trim(is_string($_POST['code'] ?? null) ? $_POST['code'] : '');
$name = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
$slugInput = trim(is_string($_POST['slug'] ?? null) ? $_POST['slug'] : '');
$slug = substr(slugify($slugInput !== '' ? $slugInput : $name), 0, 170);
$description = trim(is_string($_POST['description'] ?? null) ? $_POST['description'] : '');
$website = trim(is_string($_POST['website_url'] ?? null) ? $_POST['website_url'] : '');
$active = !empty($_POST['is_active']) ? 1 : 0;

if ($id < 0 || $name === '' || $slug === '' || strlen($name) > 150) {
    flash('error', 'Nama dan slug fakultas wajib diisi dengan batas panjang yang sesuai.');
    redirect('admin/fakultas.html');
}

if ($id === 0 && ($code === '' || strlen($code) > 20)) {
    flash('error', 'Kode fakultas wajib diisi (maksimal 20 karakter).');
    redirect('admin/fakultas.html');
}

if ($website !== '') {
    $validUrl = filter_var($website, FILTER_VALIDATE_URL);
    $scheme = strtolower((string) parse_url($website, PHP_URL_SCHEME));
    if (!$validUrl || !in_array($scheme, ['http', 'https'], true)) {
        $website = '';
    }
}

if ($id > 0) {
    $exists = db()->prepare('SELECT id FROM faculties WHERE id = ? LIMIT 1');
    $exists->execute([$id]);
    if (!$exists->fetch()) {
        flash('error', 'Data fakultas tidak ditemukan.');
        redirect('admin/fakultas.html');
    }
}

$duplicate = db()->prepare('SELECT id FROM faculties WHERE slug = ? AND id <> ? LIMIT 1');
$duplicate->execute([$slug, $id]);
if ($duplicate->fetch()) {
    flash('error', 'Slug tersebut sudah digunakan fakultas lain. Gunakan slug yang berbeda.');
    redirect('admin/fakultas.html?edit=' . $id);
}

if ($code !== '') {
    $dupCode = db()->prepare('SELECT id FROM faculties WHERE code = ? AND id <> ? LIMIT 1');
    $dupCode->execute([$code, $id]);
    if ($dupCode->fetch()) {
        flash('error', 'Kode tersebut sudah digunakan fakultas lain.');
        redirect('admin/fakultas.html?edit=' . $id);
    }
}

try {
    if ($id > 0) {
        db()->prepare(
            'UPDATE faculties
             SET name = ?, slug = ?, description = ?, website_url = ?, is_active = ?
             WHERE id = ?'
        )->execute([$name, $slug, $description, $website !== '' ? $website : null, $active, $id]);
        $message = 'Informasi fakultas berhasil diperbarui.';
    } else {
        db()->prepare(
            'INSERT INTO faculties (code, name, slug, description, website_url, is_active)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([$code, $name, $slug, $description, $website !== '' ? $website : null, $active]);
        $message = 'Fakultas berhasil ditambahkan.';
    }
    flash('success', $message);
} catch (PDOException $ex) {
    error_log('Pena Kampus faculty save error: ' . $ex->getMessage());
    flash('error', 'Data fakultas tidak dapat disimpan. Periksa kembali nama, kode, dan slug.');
}

redirect('admin/fakultas.html');
