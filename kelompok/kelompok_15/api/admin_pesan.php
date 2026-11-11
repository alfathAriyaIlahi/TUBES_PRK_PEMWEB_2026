<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR INBOX PESAN - ORANG 3
require_admin();$adminTitle='Pesan Kontak';$activeAdmin='admin/pesan.html';
$q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$status = $_GET['status'] ?? '';
$where = ['1=1'];
$params = [];
if ($q !== '') {
    $where[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
if (in_array($status, ['new', 'read', 'replied', 'archived'], true)) {
    $where[] = 'status=?';
    $params[] = $status;
}
$st = db()->prepare(
    'SELECT * FROM contact_messages WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC'
);
$st->execute($params);
$messages = $st->fetchAll();
$selected = null;
$selectedId = (int) ($_GET['id'] ?? 0);
if ($selectedId) {
    $st = db()->prepare('SELECT * FROM contact_messages WHERE id=?');
    $st->execute([$selectedId]);
    $selected = $st->fetch();
}


$view = [];
$view['msg_flash_success'] = [];
if($msg=flash('success')) {
    $item1 = [];
    $item1['msg'] = e($msg);
    $view['msg_flash_success'][] = $item1;
}
$view['q'] = e($q);
$view['status_new_selected'] = $status==='new'?'selected':'';
$view['status_read_selected'] = $status==='read'?'selected':'';
$view['status_replied_selected'] = $status==='replied'?'selected':'';
$view['status_archived_selected'] = $status==='archived'?'selected':'';
$view['messages'] = [];
foreach($messages as $m) {
    $item1 = [];
    $item1['selectedId_int_m_id_bg_blue_50'] = $selectedId===(int)$m['id']?'bg-blue-50':'';
    $item1['int_m_id'] = (int)$m['id'];
    $item1['m_name'] = e($m['name']);
    $item1['m_email'] = e($m['email']);
    $item1['int_m_id2'] = (int)$m['id'];
    $item1['m_subject'] = e($m['subject']);
    $item1['date_id_m_created_at'] = e(date_id($m['created_at']));
    $item1['m_status'] = e($m['status']);
    $item1['ucfirst_m_status'] = e(ucfirst($m['status']));
    $view['messages'][] = $item1;
}
$view['messages2'] = [];
if(!$messages) {
    $item1 = [];
    $view['messages2'][] = $item1;
}
$view['selected'] = [];
if($selected) {
    $item1 = [];
    $item1['selected_subject'] = e($selected['subject']);
    $item1['selected_status'] = e($selected['status']);
    $item1['ucfirst_selected_status'] = e(ucfirst($selected['status']));
    $item1['selected_name'] = e($selected['name']);
    $item1['selected_email'] = e($selected['email']);
    $item1['selected_email2'] = e($selected['email']);
    $item1['date_id_selected_created_at_true'] = e(date_id($selected['created_at'],true));
    $item1['selected_message'] = e($selected['message']);
    $item1['selected_email3'] = e($selected['email']);
    $item1['rawurlencode_selected_subject'] = rawurlencode($selected['subject']);
    $item1['csrf_delete'] = csrf_token();
    $item1['messageActions'] = [];
    foreach(['read'=>'Tandai Dibaca','replied'=>'Tandai Dibalas','archived'=>'Arsipkan'] as $s=>$label) {
        $item2 = [];
        $item2['csrf_token'] = csrf_token();
        $item2['int_selected_id'] = (int)$selected['id'];
        $item2['s'] = $s;
        $item2['label'] = e($label);
        $item1['messageActions'][] = $item2;
    }
    $view['selected'][] = $item1;
}
$view['otherwise_selected'] = [];
if (!$view['selected']) {
    $item1 = [];
    $view['otherwise_selected'][] = $item1;
}

json_response([
    'content' => $view,
    'title' => $adminTitle . ' | Pena Kampus CMS',
    'layout' => admin_layout($adminTitle, $activeAdmin),
]);
