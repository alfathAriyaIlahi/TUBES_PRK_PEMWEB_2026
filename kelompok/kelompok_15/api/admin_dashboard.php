<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR DASHBOARD ADMIN - ORANG 4
require_editor_or_admin();
$user=current_user();$scope='';$params=[];
if(!is_admin()){$scope=' AND faculty_id = ?';$params[]=(int)$user['faculty_id'];}
$counts=[];
foreach (
    [
        'all' => '',
        'published' => " AND status='published'",
        'draft' => " AND status='draft'",
        'archived' => " AND status='archived'",
    ]
    as $key => $extra
) {
    $st = db()->prepare('SELECT COUNT(*) FROM articles WHERE 1=1' . $scope . $extra);
    $st->execute($params);
    $counts[$key] = (int)$st->fetchColumn();
}
$viewsSql = 'SELECT COALESCE(SUM(view_count),0) FROM articles WHERE 1=1' . $scope;
$st = db()->prepare($viewsSql);
$st->execute($params);
$views = (int)$st->fetchColumn();
$facultyCount=(int)db()->query('SELECT COUNT(*) FROM faculties WHERE is_active=1')->fetchColumn();
$sql = "SELECT a.*, f.name faculty_name, c.name category_name, u.name author_name "
    . "FROM articles a LEFT JOIN faculties f ON f.id=a.faculty_id "
    . "JOIN categories c ON c.id=a.category_id JOIN users u ON u.id=a.author_id WHERE 1=1"
    . ($scope ? str_replace('faculty_id', 'a.faculty_id', $scope) : '')
    . " ORDER BY a.updated_at DESC LIMIT 8";
$st = db()->prepare($sql);
$st->execute($params);
$recent = $st->fetchAll();
$adminTitle='Dashboard';$activeAdmin='admin/dashboard.html';

$view = [];
$view['msg_flash_success'] = [];
if($msg=flash('success')) {
    $item1 = [];
    $item1['msg'] = e($msg);
    $view['msg_flash_success'][] = $item1;
}
$view['user_name'] = e($user['name']);
$view['roleLabel'] = is_admin()?'Administrator pusat':'Editor · '.e($user['faculty_name']??'Fakultas');
$view['summaryCards'] = [];
foreach (
    [
        ['Total Berita', $counts['all'], 'article'],
        ['Sudah Terbit', $counts['published'], 'public'],
        ['Draft', $counts['draft'], 'edit_note'],
        ['Jumlah Pembaca', number_format($views, 0, ',', '.'), 'visibility'],
    ] as $card
) {
    $item1 = [];
    $item1['card_0'] = e($card[0]);
    $item1['card_2'] = e($card[2]);
    $item1['string_card_1'] = e((string)$card[1]);
    $view['summaryCards'][] = $item1;
}
$view['recent'] = [];
foreach($recent as $a) {
    $item1 = [];
    $item1['url_admin_berita_form_html_id_a_id'] = e(url('admin/berita-form.html?id='.$a['id']));
    $item1['a_title'] = e($a['title']);
    $item1['a_category_name'] = e($a['category_name']);
    $item1['a_faculty_name_Universitas'] = e($a['faculty_name']?:'Universitas');
    $item1['a_status'] = e($a['status']);
    $item1['ucfirst_a_status'] = e(ucfirst($a['status']));
    $item1['date_id_a_updated_at'] = e(date_id($a['updated_at']));
    $view['recent'][] = $item1;
}
$view['recent2'] = [];
if(!$recent) {
    $item1 = [];
    $view['recent2'][] = $item1;
}
$view['is_admin'] = [];
if(is_admin()) {
    $item1 = [];
    $view['is_admin'][] = $item1;
}
$view['facultyCount'] = $facultyCount;

json_response([
    'content' => $view,
    'title' => $adminTitle . ' | Pena Kampus CMS',
    'layout' => admin_layout($adminTitle, $activeAdmin),
]);
