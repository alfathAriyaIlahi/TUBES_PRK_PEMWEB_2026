<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR BERANDA - ORANG 1
$pageTitle = 'Beranda'; $activeNav = 'home';
$featured = db()->query(
    "SELECT a.*, f.name faculty_name, c.name category_name, u.name author_name "
    . "FROM articles a LEFT JOIN faculties f ON f.id=a.faculty_id "
    . "JOIN categories c ON c.id=a.category_id JOIN users u ON u.id=a.author_id "
    . "WHERE a.status='published' ORDER BY a.is_featured DESC, a.published_at DESC LIMIT 1"
)->fetch();
$latest = db()->query(
    "SELECT a.*, f.name faculty_name, c.name category_name, u.name author_name "
    . "FROM articles a LEFT JOIN faculties f ON f.id=a.faculty_id "
    . "JOIN categories c ON c.id=a.category_id JOIN users u ON u.id=a.author_id "
    . "WHERE a.status='published' ORDER BY a.published_at DESC LIMIT 6"
)->fetchAll();
$popular = db()->query(
    "SELECT a.*, f.name faculty_name, c.name category_name, u.name author_name "
    . "FROM articles a LEFT JOIN faculties f ON f.id=a.faculty_id "
    . "JOIN categories c ON c.id=a.category_id JOIN users u ON u.id=a.author_id "
    . "WHERE a.status='published' ORDER BY a.view_count DESC, a.published_at DESC LIMIT 4"
)->fetchAll();
$faculties = get_faculties();
$categories = get_categories();


$view = [];
$view['featured'] = [];
if ($featured) {
    $item1 = [];
    $item1['article_image_featured_featured_image'] = e(article_image($featured['featured_image']));
    $item1['featured_faculty_name_Universitas'] = e($featured['faculty_name'] ?: 'Universitas');
    $item1['featured_category_name'] = e($featured['category_name']);
    $item1['featuredUrl'] = e(url('detail.html?slug=' . rawurlencode($featured['slug'])));
    $item1['featured_title'] = e($featured['title']);
    $item1['featuredSummary'] = e(excerpt($featured['excerpt'] ?: $featured['content'], 190));
    $item1['featured_author_name'] = e($featured['author_name']);
    $item1['date_id_featured_published_at'] = e(date_id($featured['published_at']));
    $item1['featuredUrl2'] = e(url('detail.html?slug=' . rawurlencode($featured['slug'])));
    $item1['popular'] = [];
    foreach ($popular as $item) {
        $item2 = [];
        $item2['card'] = article_card($item);
        $item1['popular'][] = $item2;
    }
    $item1['popular2'] = [];
    if (!$popular) {
        $item2 = [];
        $item1['popular2'][] = $item2;
    }
    $view['featured'][] = $item1;
}
$view['otherwise_featured'] = [];
if (!$view['featured']) {
    $item1 = [];
    $view['otherwise_featured'][] = $item1;
}
$view['faculties'] = [];
foreach ($faculties as $f) {
    $item1 = [];
    $item1['url_fakultas_html_slug_f_slug'] = e(url('fakultas.html?slug=' . $f['slug']));
    $item1['f_code'] = e($f['code']);
    $item1['f_name'] = e($f['name']);
    $view['faculties'][] = $item1;
}
$view['latest'] = [];
if ($latest) {
    $item1 = [];
    $item1['latest2'] = [];
    foreach ($latest as $article) {
        $item2 = [];
        $item2['card'] = article_card($article);
        $item1['latest2'][] = $item2;
    }
    $view['latest'][] = $item1;
}
$view['otherwise_latest'] = [];
if (!$view['latest']) {
    $item1 = [];
    $view['otherwise_latest'][] = $item1;
}
$view['categories'] = [];
foreach ($categories as $cat) {
    $item1 = [];
    $item1['url_berita_html_kategori_cat_id'] = e(url('berita.html?kategori=' . $cat['id']));
    $item1['cat_name'] = e($cat['name']);
    $view['categories'][] = $item1;
}

json_response([
    'content' => $view,
    'title' => $pageTitle . ' | Pena Kampus',
    'layout' => public_layout($activeNav ?? ''),
]);
