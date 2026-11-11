<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR TENTANG - ORANG 1
$pageTitle='Tentang Kami';$activeNav='about';$faculties=get_faculties();

$view = [];
$view['count_faculties'] = count($faculties);
$view['faculties'] = [];
foreach($faculties as $f) {
    $item1 = [];
    $item1['url_fakultas_html_slug_f_slug'] = e(url('fakultas.html?slug='.$f['slug']));
    $item1['f_code'] = e($f['code']);
    $item1['f_name'] = e($f['name']);
    $view['faculties'][] = $item1;
}

json_response([
    'content' => $view,
    'title' => $pageTitle . ' | Pena Kampus',
    'layout' => public_layout($activeNav ?? ''),
]);
