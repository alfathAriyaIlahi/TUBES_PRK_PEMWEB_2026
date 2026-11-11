<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR MODERASI KOMENTAR - ORANG 4
require_editor_or_admin();$adminTitle='Moderasi Komentar';$activeAdmin='admin/komentar.html';
$status = $_GET['status'] ?? 'pending';
$q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$where = ['1=1'];
$params = [];
if(in_array($status,['pending','approved','rejected'],true)){$where[]='cm.status=?';$params[]=$status;}
if ($q !== '') {
    $where[] = '(cm.commenter_name LIKE ? OR cm.content LIKE ? OR a.title LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
if(!is_admin()){$where[]='a.faculty_id=?';$params[]=(int)current_user()['faculty_id'];}
$sql = 'SELECT cm.*,a.title article_title,a.slug article_slug,f.name faculty_name '
    . 'FROM comments cm JOIN articles a ON a.id=cm.article_id '
    . 'LEFT JOIN faculties f ON f.id=a.faculty_id WHERE '
    . implode(' AND ', $where)
    . ' ORDER BY cm.created_at DESC';
$st = db()->prepare($sql);
$st->execute($params);
$comments = $st->fetchAll();
$counts = [];
foreach (['pending', 'approved', 'rejected'] as $s) {
    $w = 'WHERE status=?';
    $p = [$s];
    if (!is_admin()) {
        $w .= ' AND article_id IN (SELECT id FROM articles WHERE faculty_id=?)';
        $p[] = (int) current_user()['faculty_id'];
    }
    $c = db()->prepare("SELECT COUNT(*) FROM comments $w");
    $c->execute($p);
    $counts[$s] = (int)$c->fetchColumn();
}


$view = [];
$view['msg_flash_success'] = [];
if($msg=flash('success')) {
    $item1 = [];
    $item1['msg'] = e($msg);
    $view['msg_flash_success'][] = $item1;
}
$view['moderationStatuses'] = [];
foreach(['pending'=>'Menunggu','approved'=>'Disetujui','rejected'=>'Ditolak'] as $k=>$label) {
    $item1 = [];
    $item1['label'] = e($label);
    $item1['counts_k'] = $counts[$k];
    $view['moderationStatuses'][] = $item1;
}
$view['q'] = e($q);
$view['status_pending_selected'] = $status==='pending'?'selected':'';
$view['status_approved_selected'] = $status==='approved'?'selected':'';
$view['status_rejected_selected'] = $status==='rejected'?'selected':'';
$view['status_all_selected'] = $status==='all'?'selected':'';
$view['comments'] = [];
foreach($comments as $c) {
    $item1 = [];
    $item1['c_commenter_name'] = e($c['commenter_name']);
    $item1['c_commenter_email_Tanpa_email'] = e($c['commenter_email']?:'Tanpa email');
    $item1['url_detail_html_slug_c_article_slug'] = e(url('detail.html?slug='.$c['article_slug']));
    $item1['c_article_title'] = e($c['article_title']);
    $item1['c_faculty_name_Universitas'] = e($c['faculty_name']?:'Universitas');
    $item1['date_id_c_created_at_true'] = e(date_id($c['created_at'],true));
    $item1['c_status'] = e($c['status']);
    $item1['ucfirst_c_status'] = e(ucfirst($c['status']));
    $item1['c_content'] = e($c['content']);
    $item1['c_status_approved'] = [];
    if($c['status']!=='approved') {
        $item2 = [];
        $item2['csrf_token'] = csrf_token();
        $item2['int_c_id'] = (int)$c['id'];
        $item1['c_status_approved'][] = $item2;
    }
    $item1['c_status_rejected'] = [];
    if($c['status']!=='rejected') {
        $item2 = [];
        $item2['csrf_token2'] = csrf_token();
        $item2['int_c_id2'] = (int)$c['id'];
        $item1['c_status_rejected'][] = $item2;
    }
    $item1['csrf_token3'] = csrf_token();
    $item1['int_c_id3'] = (int)$c['id'];
    $view['comments'][] = $item1;
}
$view['comments2'] = [];
if(!$comments) {
    $item1 = [];
    $view['comments2'][] = $item1;
}

json_response([
    'content' => $view,
    'title' => $adminTitle . ' | Pena Kampus CMS',
    'layout' => admin_layout($adminTitle, $activeAdmin),
]);
