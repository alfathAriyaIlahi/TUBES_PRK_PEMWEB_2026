<?php
// Fungsi bantu yang dipakai semua modul. JANGAN diubah tanpa izin Rafi.

/** Amankan teks sebelum ditampilkan di HTML. Pakai: <?= e($teks) ?> */
function e($teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

/** Buat alamat lengkap. Pakai: <?= url('auth/login.php') ?> */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/** Pesan sekali tampil. flash('success', 'Berhasil!') lalu tampil di header. */
function flash(string $tipe, string $pesan): void
{
    $_SESSION['flash'][] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function ambil_flash(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/** Perlindungan formulir (CSRF). Taruh <?= csrf_field() ?> di dalam setiap <form method="post"> */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_valid(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

/** Data pengguna yang sedang login (atau null). */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function dashboard_path(string $role): string
{
    return match ($role) {
        'admin'     => 'admin/dashboard.php',
        'pengacara' => 'pengacara/dashboard.php',
        default     => 'klien/dashboard.php',
    };
}

/** Pagar halaman: harus login. */
function require_login(): void
{
    if (!is_logged_in()) {
        flash('warning', 'Silakan masuk terlebih dahulu.');
        redirect('auth/login.php');
    }
}

/** Pagar halaman: harus login DAN berperan tertentu. Contoh: require_role('klien'); */
function require_role(string ...$roles): void
{
    require_login();
    if (!in_array(current_user()['role'], $roles, true)) {
        http_response_code(403);
        flash('danger', 'Kamu tidak punya akses ke halaman itu.');
        redirect(dashboard_path(current_user()['role']));
    }
}

/** Format tanggal Indonesia sederhana: 13 Nov 2026 */
function tgl(string $waktu): string
{
    $bln = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($waktu);
    return date('j', $t) . ' ' . $bln[(int) date('n', $t)] . ' ' . date('Y', $t);
}
