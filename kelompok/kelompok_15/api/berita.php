<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR DAFTAR BERITA - ORANG 2
$pageTitle = 'Semua Berita'; $activeNav = 'news';
$q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$facultyId = max(0, (int)($_GET['fakultas'] ?? 0));
$categoryId = max(0, (int)($_GET['kategori'] ?? 0));
$sort = isset($_GET['sort']) && is_string($_GET['sort']) ? $_GET['sort'] : 'terbaru';
$orderBy = [
    'terbaru' => 'a.published_at DESC',
    'terlama' => 'a.published_at ASC',
    'terpopuler' => 'a.view_count DESC, a.published_at DESC',
][$sort] ?? 'a.published_at DESC';
$page = max(1, (int)($_GET['page'] ?? 1)); $perPage = 9;
$where = ["a.status='published'"]; $params = [];
if ($q !== '') {
    $where[] = '(a.title LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
if ($facultyId > 0) { $where[] = 'a.faculty_id = ?'; $params[] = $facultyId; }
if ($categoryId > 0) { $where[] = 'a.category_id = ?'; $params[] = $categoryId; }
$whereSql = implode(' AND ', $where);
$count = db()->prepare("SELECT COUNT(*) FROM articles a WHERE $whereSql");
$count->execute($params);
$total = (int)$count->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage)); $page = min($page, $totalPages); $offset = ($page - 1) * $perPage;
$sql = "SELECT a.*, f.name faculty_name, c.name category_name, u.name author_name "
    . "FROM articles a LEFT JOIN faculties f ON f.id=a.faculty_id "
    . "JOIN categories c ON c.id=a.category_id JOIN users u ON u.id=a.author_id "
    . "WHERE $whereSql ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
$stmt = db()->prepare($sql); $stmt->execute($params); $articles = $stmt->fetchAll();
$faculties = get_faculties(); $categories = get_categories();


$view = [];
$view['q'] = e($q);
$view['faculties'] = [];
foreach ($faculties as $f) {
    $item1 = [];
    $item1['int_f_id'] = (int)$f['id'];
    $item1['facultyId_int_f_id_selected'] = $facultyId===(int)$f['id']?'selected':'';
    $item1['f_code_f_name'] = e($f['code'] . ' · ' . $f['name']);
    $view['faculties'][] = $item1;
}
$view['categories'] = [];
foreach ($categories as $c) {
    $item1 = [];
    $item1['int_c_id'] = (int)$c['id'];
    $item1['categoryId_int_c_id_selected'] = $categoryId===(int)$c['id']?'selected':'';
    $item1['c_name'] = e($c['name']);
    $view['categories'][] = $item1;
}
$view['sort_terbaru_selected'] = $sort==='terbaru'?'selected':'';
$view['sort_terlama_selected'] = $sort==='terlama'?'selected':'';
$view['sort_terpopuler_selected'] = $sort==='terpopuler'?'selected':'';
$view['count_articles'] = count($articles);
$view['total'] = $total;
$view['q2'] = [];
if ($q) {
    $item1 = [];
    $item1['q3'] = e($q);
    $view['q2'][] = $item1;
}
$view['articles'] = [];
if ($articles) {
    $item1 = [];
    $item1['articles2'] = [];
    foreach ($articles as $article) {
        $item2 = [];
        $item2['card'] = article_card($article);
        $item1['articles2'][] = $item2;
    }
    $view['articles'][] = $item1;
}
$view['otherwise_articles'] = [];
if (!$view['articles']) {
    $item1 = [];
    $view['otherwise_articles'][] = $item1;
}
$view['hasPagination'] = [];
if ($totalPages > 1) {
    $item1 = [];
    $item1['pageLinks'] = [];
    for ($i=1;$i<=$totalPages;$i++) {
        $item2 = [];
        $query=$_GET; $query['page']=$i;
        $item2['pageClass'] = $i === $page
            ? 'border-ink bg-ink font-bold text-white'
            : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50';
        $item2['http_build_query_query'] = e(http_build_query($query));
        $item2['i'] = $i;
        $item1['pageLinks'][] = $item2;
    }
    $view['hasPagination'][] = $item1;
}

json_response([
    'content' => $view,
    'title' => $pageTitle . ' | Pena Kampus',
    'layout' => public_layout($activeNav ?? ''),
]);
