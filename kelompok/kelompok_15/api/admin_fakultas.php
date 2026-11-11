<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR MANAJEMEN FAKULTAS - ORANG 1
require_admin();$adminTitle='Manajemen Fakultas';$activeAdmin='admin/fakultas.html';
$rows = db()->query(
    'SELECT f.*, (SELECT COUNT(*) FROM articles a WHERE a.faculty_id=f.id) AS article_count '
        . 'FROM faculties f ORDER BY f.name'
)->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;
if ($editId) {
    $st = db()->prepare('SELECT * FROM faculties WHERE id=?');
    $st->execute([$editId]);
    $editing = $st->fetch();
}


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
$view['rows'] = [];
foreach($rows as $f) {
    $item1 = [];
    $item1['csrf_token'] = csrf_token();
    $item1['f_code'] = e($f['code']);
    $item1['f_name'] = e($f['name']);
    $item1['f_slug'] = e($f['slug']);
    $item1['int_f_article_count'] = (int)$f['article_count'];
    $item1['statusClass'] = $f['is_active']?'status-active':'status-inactive';
    $item1['f_is_active_Aktif_Nonaktif'] = $f['is_active']?'Aktif':'Nonaktif';
    $item1['int_f_id'] = (int)$f['id'];
    $view['rows'][] = $item1;
}
$view['csrf_token'] = csrf_token();
$view['int_editing_id_0'] = (int)($editing['id']??0);
$view['editing_Edit_Fakultas_Tambah_Fakultas'] = $editing ? 'Edit Informasi Fakultas' : 'Tambah Fakultas';
$view['editing'] = [];
if($editing) {
    $item1 = [];
    $item1['csrf_token'] = csrf_token();
    $item1['int_editing_id'] = (int)$editing['id'];
    $item1['editing_code'] = e($editing['code']);
    $item1['editing_name'] = e($editing['name']);
    $item1['editing_slug'] = e($editing['slug']);
    $item1['editing_description'] = e($editing['description']);
    $item1['editing_website_url'] = e($editing['website_url']);
    $item1['editing_is_active_checked'] = $editing['is_active']?'checked':'';
    $view['editing'][] = $item1;
}
$view['otherwise_editing'] = [];
if (!$view['editing']) {
    $item1 = [];
    $view['otherwise_editing'][] = $item1;
}

json_response([
    'content' => $view,
    'title' => $adminTitle . ' | Pena Kampus CMS',
    'layout' => admin_layout($adminTitle, $activeAdmin),
]);
