<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR MANAJEMEN AKUN - ORANG 4
require_admin();$adminTitle='Manajemen Pengguna';$activeAdmin='admin/akun.html';
$q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$role = $_GET['role'] ?? '';
$where = ['1=1'];
$params = [];
if ($q !== '') {
    $where[] = '(u.name LIKE ? OR u.email LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if (in_array($role, ['admin', 'editor'], true)) {
    $where[] = 'u.role=?';
    $params[] = $role;
}
$st = db()->prepare(
    'SELECT u.*,f.name faculty_name FROM users u LEFT JOIN faculties f ON f.id=u.faculty_id WHERE '
        . implode(' AND ', $where)
        . ' ORDER BY u.created_at DESC'
);
$st->execute($params);
$users = $st->fetchAll();


$view = [];
$view['msg_flash_success'] = [];
if($msg=flash('success')) {
    $item1 = [];
    $item1['msg'] = e($msg);
    $view['msg_flash_success'][] = $item1;
}
$view['msg_flash_error'] = [];
if($msg=flash('error')) {
    $item1 = [];
    $item1['msg2'] = e($msg);
    $view['msg_flash_error'][] = $item1;
}
$view['q'] = e($q);
$view['role_admin_selected'] = $role==='admin'?'selected':'';
$view['role_editor_selected'] = $role==='editor'?'selected':'';
$view['users'] = [];
foreach($users as $u) {
    $item1 = [];
    $item1['u_name'] = e($u['name']);
    $item1['u_email'] = e($u['email']);
    $item1['roleClass'] = $u['role']==='admin'?'bg-blue-100 text-blue-800':'bg-slate-100 text-slate-700';
    $item1['ucfirst_u_role'] = e(ucfirst($u['role']));
    $item1['u_faculty_name_Semua_fakultas'] = e($u['faculty_name']?:'Semua fakultas');
    $item1['u_status'] = e($u['status']);
    $item1['ucfirst_u_status'] = e(ucfirst($u['status']));
    $item1['date_id_u_last_login_at'] = e(date_id($u['last_login_at']));
    $item1['url_admin_akun_form_html_id_u_id'] = e(url('admin/akun-form.html?id='.$u['id']));
    $item1['int_u_id_int_current_user_id'] = [];
    if((int)$u['id']!==(int)current_user()['id']) {
        $item2 = [];
        $item2['csrf_token'] = csrf_token();
        $item2['int_u_id'] = (int)$u['id'];
        $item2['u_status_active_inactive_active'] = $u['status']==='active'?'inactive':'active';
        $item2['u_status_active_Nonaktifkan_Aktifkan'] = $u['status']==='active'?'Nonaktifkan':'Aktifkan';
        $item1['int_u_id_int_current_user_id'][] = $item2;
    }
    $view['users'][] = $item1;
}
$view['users2'] = [];
if(!$users) {
    $item1 = [];
    $view['users2'][] = $item1;
}

json_response([
    'content' => $view,
    'title' => $adminTitle . ' | Pena Kampus CMS',
    'layout' => admin_layout($adminTitle, $activeAdmin),
]);
