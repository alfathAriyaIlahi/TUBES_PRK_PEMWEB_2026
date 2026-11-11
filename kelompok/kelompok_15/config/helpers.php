<?php
require_once APP_ROOT . '/config/database.php';

// HELPERS UMUM (EXCERPT/GAMBAR/TANGGAL) - ORANG 3
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    json_response(['redirect' => url($path)]);
}

// FITUR AUTENTIKASI & HAK AKSES - INFRASTRUKTUR (DIPAKAI ADMIN)
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || $sent === '' || !hash_equals((string)($_SESSION['csrf_token'] ?? ''), $sent)) {
        http_response_code(419);
        exit('Permintaan tidak valid atau sesi formulir telah berakhir. Silakan kembali dan coba lagi.');
    }
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Silakan masuk terlebih dahulu.');
        redirect('admin/login.html');
    }

    $sessionUser = current_user();
    $userId = (int) ($sessionUser['id'] ?? 0);
    $stmt = db()->prepare(
        'SELECT u.id, u.name, u.email, u.role, u.status, u.faculty_id,
                f.name AS faculty_name, f.is_active AS faculty_active
         FROM users u
         LEFT JOIN faculties f ON f.id = u.faculty_id
         WHERE u.id = ? LIMIT 1'
    );
    $stmt->execute([$userId]);
    $freshUser = $stmt->fetch();

    $invalidEditorAssignment = $freshUser
        && $freshUser['role'] === 'editor'
        && (empty($freshUser['faculty_id']) || (int) ($freshUser['faculty_active'] ?? 0) !== 1);

    if (!$freshUser || $freshUser['status'] !== 'active' || $invalidEditorAssignment) {
        unset($_SESSION['user']);
        flash('error', 'Akun tidak aktif atau penugasan fakultas editor tidak valid. Hubungi administrator.');
        redirect('admin/login.html');
    }

    $_SESSION['user'] = [
        'id' => (int) $freshUser['id'],
        'name' => $freshUser['name'],
        'email' => $freshUser['email'],
        'role' => $freshUser['role'],
        'faculty_id' => $freshUser['faculty_id'] !== null ? (int) $freshUser['faculty_id'] : null,
        'faculty_name' => $freshUser['faculty_name'],
    ];
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit('Akses ditolak. Halaman ini hanya dapat diakses administrator.');
    }
}

function require_editor_or_admin(): void
{
    require_login();
    $role = current_user()['role'] ?? '';
    if (!in_array($role, ['admin', 'editor'], true)) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
    if ($role === 'editor' && empty(current_user()['faculty_id'])) {
        http_response_code(403);
        exit('Akun editor belum ditugaskan ke fakultas. Hubungi administrator.');
    }
}

// INFRASTRUKTUR (BAWAAN) - DIPAKAI SEMUA FITUR
function month_id(int $month): string
{
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
        'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];
    return $months[$month] ?? '';
}

function date_id(?string $date, bool $withTime = false): string
{
    if (!$date) return '-';
    $ts = strtotime($date);
    if (!$ts) return '-';
    $result = date('j', $ts) . ' ' . month_id((int)date('n', $ts)) . ' ' . date('Y', $ts);
    return $withTime ? $result . ' · ' . date('H:i', $ts) : $result;
}

function excerpt(string $text, int $length = 150): string
{
    $plain = trim(strip_tags($text));
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($plain, 'UTF-8') <= $length) return $plain;
        return mb_substr($plain, 0, $length, 'UTF-8') . '…';
    }
    $chars = preg_split('//u', $plain, -1, PREG_SPLIT_NO_EMPTY);
    if ($chars === false) return strlen($plain) <= $length ? $plain : substr($plain, 0, $length) . '…';
    return count($chars) <= $length ? $plain : implode('', array_slice($chars, 0, $length)) . '…';
}

function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'artikel';
}

function article_image(?string $path): string
{
    if ($path && (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))) return $path;
    if ($path) return url('uploads/' . rawurlencode(basename($path)));
    return url('assets/news-placeholder.svg');
}

function faculty_badge(?string $facultyName): string
{
    return $facultyName ?: 'Universitas';
}

function get_faculties(bool $activeOnly = true): array
{
    $sql = 'SELECT * FROM faculties' . ($activeOnly ? ' WHERE is_active = 1' : '') . ' ORDER BY name';
    return db()->query($sql)->fetchAll();
}

function get_categories(bool $activeOnly = true): array
{
    $sql = 'SELECT * FROM categories' . ($activeOnly ? ' WHERE is_active = 1' : '') . ' ORDER BY name';
    return db()->query($sql)->fetchAll();
}

function article_scope_sql(string $alias = 'a'): array
{
    if (is_admin()) return ['', []];
    return [" AND {$alias}.faculty_id = ?", [(int)(current_user()['faculty_id'] ?? 0)]];
}

function can_manage_article(array $article): bool
{
    if (is_admin()) return true;
    return is_logged_in()
        && (current_user()['role'] ?? '') === 'editor'
        && (int)($article['faculty_id'] ?? 0) === (int)(current_user()['faculty_id'] ?? -1);
}

function image_upload(string $field, ?string $oldFile = null): ?string
{
    if (!isset($_FILES[$field]) || (int) ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $oldFile;
    }

    $upload = $_FILES[$field];
    if ((int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload gambar gagal.');
    }
    if ((int) ($upload['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('Ukuran gambar maksimal 2 MB.');
    }
    if (empty($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])) {
        throw new RuntimeException('Berkas gambar tidak valid. Silakan pilih ulang gambar.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($upload['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $imageInfo = @getimagesize($upload['tmp_name']);
    if (!isset($allowed[$mime]) || !$imageInfo || ($imageInfo['mime'] ?? '') !== $mime) {
        throw new RuntimeException('Format gambar harus berupa JPG, PNG, atau WebP yang valid.');
    }

    $uploadDirectory = APP_ROOT . '/uploads';
    if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
        throw new RuntimeException('Folder upload tidak tersedia atau tidak dapat ditulis.');
    }

    $filename = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    $destination = $uploadDirectory . '/' . $filename;
    if (!move_uploaded_file($upload['tmp_name'], $destination)) {
        throw new RuntimeException('Gambar tidak dapat disimpan.');
    }

    return $filename;
}

function delete_uploaded_image(?string $path): void
{
    if (!$path || preg_match('~^https?://~i', $path)) {
        return;
    }

    $filename = basename($path);
    if ($filename === '' || $filename === '.' || $filename === '..') {
        return;
    }

    $directory = realpath(APP_ROOT . '/uploads');
    $file = APP_ROOT . '/uploads/' . $filename;
    if ($directory && is_file($file) && realpath(dirname($file)) === $directory) {
        @unlink($file);
    }
}
