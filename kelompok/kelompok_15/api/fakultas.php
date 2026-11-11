<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR KATALOG FAKULTAS - ORANG 2
$slug = isset($_GET['slug']) && is_string($_GET['slug']) ? trim($_GET['slug']) : '';
$facultyStmt = db()->prepare(
    'SELECT id, code, name, slug, description, is_active
     FROM faculties
     WHERE slug = ? AND is_active = 1
     LIMIT 1'
);
$facultyStmt->execute([$slug]);
$currentFaculty = $facultyStmt->fetch();

if (!$currentFaculty) {
    http_response_code(404);
    $pageTitle = 'Fakultas tidak ditemukan';
    $activeNav = 'news';
    json_response([
        'missing' => true,
        'title' => $pageTitle . ' | Pena Kampus',
        'layout' => public_layout($activeNav ?? ''),
    ]);
}

$pageTitle = 'Berita ' . $currentFaculty['code'];
$activeNav = 'news';
$q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$categoryId = max(0, (int) ($_GET['kategori'] ?? 0));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 9;

$where = ["a.status = 'published'", 'a.faculty_id = ?'];
$params = [(int) $currentFaculty['id']];

if ($q !== '') {
    $where[] = '(a.title LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}

if ($categoryId > 0) {
    $where[] = 'a.category_id = ?';
    $params[] = $categoryId;
}

$whereSql = implode(' AND ', $where);
$countStmt = db()->prepare("SELECT COUNT(*) FROM articles a WHERE {$whereSql}");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$articleSql = "
    SELECT a.*, f.name AS faculty_name, c.name AS category_name, u.name AS author_name
    FROM articles a
    LEFT JOIN faculties f ON f.id = a.faculty_id
    INNER JOIN categories c ON c.id = a.category_id
    INNER JOIN users u ON u.id = a.author_id
    WHERE {$whereSql}
    ORDER BY a.published_at DESC, a.created_at DESC
    LIMIT {$perPage} OFFSET {$offset}
";
$articleStmt = db()->prepare($articleSql);
$articleStmt->execute($params);
$articles = $articleStmt->fetchAll();

$facultiesForTabs = db()->query(
    'SELECT id, code, name, slug FROM faculties WHERE is_active = 1 ORDER BY id ASC'
)->fetchAll();
$categories = get_categories();



$view = [];
$view['currentFaculty_code'] = e($currentFaculty['code']);
$view['currentFaculty_code2'] = e($currentFaculty['code']);
$view['currentFaculty_name'] = e($currentFaculty['name']);
$view['facultyDescription'] = e(
    $currentFaculty['description'] ?: ('Berita dan informasi ' . $currentFaculty['name'] . '.')
);
$view['total'] = $total;
$view['facultiesForTabs'] = [];
foreach ($facultiesForTabs as $tabFaculty) {
    $item1 = [];
    $isActiveFaculty = (int) $tabFaculty['id'] === (int) $currentFaculty['id'];
    $item1['tabUrl'] = e(url('fakultas.html?slug=' . rawurlencode($tabFaculty['slug'])));
    $item1['isActiveFaculty_aria_current_page'] = $isActiveFaculty ? 'aria-current="page"' : '';
    $item1['tabClass'] = $isActiveFaculty
            ? 'border-ink bg-ink text-white'
            : 'border-slate-300 bg-white text-slate-700 hover:border-ink';
    $item1['tabFaculty_code'] = e($tabFaculty['code']);
    $view['facultiesForTabs'][] = $item1;
}
$view['currentFaculty_slug'] = e($currentFaculty['slug']);
$view['q'] = e($q);
$view['categories'] = [];
foreach ($categories as $category) {
    $item1 = [];
    $item1['int_category_id'] = (int) $category['id'];
    $item1['categoryId_int_category_id_selected'] = $categoryId === (int) $category['id'] ? 'selected' : '';
    $item1['category_name'] = e($category['name']);
    $view['categories'][] = $item1;
}
$view['currentFaculty_code3'] = e($currentFaculty['code']);
$view['total2'] = $total;
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
    $item1['currentFaculty_code4'] = e($currentFaculty['code']);
    $view['otherwise_articles'][] = $item1;
}
$view['hasPagination'] = [];
if ($totalPages > 1) {
    $item1 = [];
    $item1['pageLinks'] = [];
    for ($i = 1; $i <= $totalPages; $i++) {
        $item2 = [];
        $queryParams = $_GET; $queryParams['page'] = $i;
        $item2['pageClass'] = $i === $page ? 'border-ink bg-ink text-white' : 'border-slate-300 bg-white';
        $item2['http_build_query_queryParams'] = e(http_build_query($queryParams));
        $item2['i_page_aria_current_page'] = $i === $page ? 'aria-current="page"' : '';
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
