<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR FORM AKUN - ORANG 4
require_admin();

$id = max(0, (int) ($_GET['id'] ?? 0));
$editingUser = null;
$error = '';

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $editingUser = $stmt->fetch();
    if (!$editingUser) {
        http_response_code(404);
        exit('Pengguna tidak ditemukan.');
    }
}

$faculties = get_faculties(false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
    $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
    $role = is_string($_POST['role'] ?? null) ? $_POST['role'] : 'editor';
    $facultyId = $role === 'editor' ? (int) ($_POST['faculty_id'] ?? 0) : null;
    $status = is_string($_POST['status'] ?? null) ? $_POST['status'] : 'active';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $isSelf = $id > 0 && $id === (int) current_user()['id'];

    if ($name === '' || strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $error = 'Nama dan email wajib diisi dengan format yang benar.';
    } elseif (!in_array($role, ['admin', 'editor'], true) || !in_array($status, ['active', 'inactive'], true)) {
        $error = 'Role atau status pengguna tidak valid.';
    } elseif ($role === 'editor' && !$facultyId) {
        $error = 'Setiap editor wajib ditugaskan ke satu fakultas.';
    } elseif (!$id && strlen($password) < 8) {
        $error = 'Kata sandi awal minimal 8 karakter.';
    } elseif ($password !== '' && strlen($password) < 8) {
        $error = 'Kata sandi minimal 8 karakter.';
    } elseif ($isSelf && ($role !== 'admin' || $status !== 'active')) {
        $error = 'Kamu tidak dapat mengubah akun sendiri menjadi editor atau menonaktifkannya.';
    }

    if ($error === '' && $role === 'editor') {
        $facultyCheck = db()->prepare('SELECT id FROM faculties WHERE id = ? AND is_active = 1 LIMIT 1');
        $facultyCheck->execute([$facultyId]);
        if (!$facultyCheck->fetch()) {
            $error = 'Fakultas editor tidak ditemukan atau sedang nonaktif.';
        }
    }

    if ($error === '' && $id > 0 && $editingUser
        && $editingUser['role'] === 'admin' && $editingUser['status'] === 'active'
        && ($role !== 'admin' || $status !== 'active')) {
        $adminCount = (int) db()->query(
            "SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active'"
        )->fetchColumn();
        if ($adminCount <= 1) {
            $error = 'Tidak dapat menonaktifkan atau menurunkan role administrator aktif terakhir.';
        }
    }

    if ($error === '') {
        try {
            if ($id > 0) {
                if ($password !== '') {
                    $sql = 'UPDATE users SET name = ?, email = ?, role = ?, faculty_id = ?, status = ?, '
                        . 'password_hash = ? WHERE id = ?';
                    db()->prepare($sql)->execute([
                        $name, $email, $role, $facultyId, $status,
                        password_hash($password, PASSWORD_DEFAULT), $id,
                    ]);
                } else {
                    $sql = 'UPDATE users SET name = ?, email = ?, role = ?, faculty_id = ?, status = ? WHERE id = ?';
                    db()->prepare($sql)->execute([$name, $email, $role, $facultyId, $status, $id]);
                }
            } else {
                $sql = 'INSERT INTO users (name, email, password_hash, role, faculty_id, status) '
                    . 'VALUES (?, ?, ?, ?, ?, ?)';
                db()->prepare($sql)->execute([
                    $name, $email, password_hash($password, PASSWORD_DEFAULT),
                    $role, $facultyId, $status,
                ]);
            }

            flash('success', $id ? 'Pengguna berhasil diperbarui.' : 'Pengguna berhasil ditambahkan.');
            redirect('admin/akun.html');
        } catch (PDOException $ex) {
            error_log('Pena Kampus user save error: ' . $ex->getMessage());
            $error = 'Email mungkin sudah digunakan atau data tidak dapat disimpan.';
        }
    }
}

$value = static function (string $key, string $default = '') use ($editingUser): string {
    $posted = $_POST[$key] ?? ($editingUser[$key] ?? $default);
    return e(is_string($posted) ? $posted : $default);
};
$selectedFacultyId = (int) ($_POST['faculty_id'] ?? ($editingUser['faculty_id'] ?? 0));
$adminTitle = $id ? 'Edit Pengguna' : 'Tambah Pengguna';
$activeAdmin = 'admin/akun.html';


$view = [];
$view['error'] = [];
if ($error) {
    $item1 = [];
    $item1['error2'] = e($error);
    $view['error'][] = $item1;
}
$view['id_Edit_Akun_Tambah_Akun_Baru'] = $id ? 'Edit Akun' : 'Tambah Akun Baru';
$view['csrf_token'] = csrf_token();
$view['value_name'] = $value('name');
$view['value_email'] = $value('email');
$view['value_role_editor_editor_selected'] = $value('role', 'editor') === 'editor' ? 'selected' : '';
$view['value_role_admin_selected'] = $value('role') === 'admin' ? 'selected' : '';
$view['faculties'] = [];
foreach ($faculties as $faculty) {
    $item1 = [];
    $item1['int_faculty_id'] = (int) $faculty['id'];
    $item1['facultySelected'] = $selectedFacultyId === (int) $faculty['id'] ? 'selected' : '';
    $item1['facultyLabel'] = e(
        $faculty['code'] . ' · ' . $faculty['name'] . (empty($faculty['is_active']) ? ' (Nonaktif)' : '')
    );
    $view['faculties'][] = $item1;
}
$view['id_kosongkan_jika_tidak_diubah_awal'] = $id ? '(kosongkan jika tidak diubah)' : 'awal *';
$view['id_required'] = $id ? '' : 'required';
$view['value_status_active_active_selected'] = $value('status', 'active') === 'active' ? 'selected' : '';
$view['value_status_inactive_selected'] = $value('status') === 'inactive' ? 'selected' : '';

json_response([
    'content' => $view,
    'title' => $adminTitle . ' | Pena Kampus CMS',
    'layout' => admin_layout($adminTitle, $activeAdmin),
]);
