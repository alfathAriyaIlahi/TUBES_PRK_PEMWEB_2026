<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';

// FITUR DETAIL BERITA - ORANG 3
$slug = isset($_GET['slug']) && is_string($_GET['slug']) ? trim($_GET['slug']) : '';

$stmt = db()->prepare(
    "SELECT a.*, f.name AS faculty_name, c.name AS category_name, u.name AS author_name
     FROM articles a
     LEFT JOIN faculties f ON f.id = a.faculty_id
     INNER JOIN categories c ON c.id = a.category_id
     INNER JOIN users u ON u.id = a.author_id
     WHERE a.slug = ? AND a.status = 'published'
     LIMIT 1"
);
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Berita tidak ditemukan';
    json_response([
        'missing' => true,
        'title' => $pageTitle . ' | Pena Kampus',
        'layout' => public_layout($activeNav ?? ''),
    ]);
}

$_SESSION['viewed_articles'] = $_SESSION['viewed_articles'] ?? [];
if (!in_array((int) $article['id'], $_SESSION['viewed_articles'], true)) {
    db()->prepare('UPDATE articles SET view_count = view_count + 1 WHERE id = ?')->execute([(int) $article['id']]);
    $_SESSION['viewed_articles'][] = (int) $article['id'];
    $article['view_count']++;
}

$commentError = '';
$commentInput = ['name' => '', 'email' => '', 'content' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'comment') {
    verify_csrf();
    foreach (array_keys($commentInput) as $key) {
        $value = $_POST[$key] ?? '';
        $commentInput[$key] = is_string($value) ? trim($value) : '';
    }

    [$name, $email, $content] = array_values($commentInput);
    if ($name === '' || $content === '') {
        $commentError = 'Nama dan isi komentar wajib diisi.';
    } elseif (strlen($name) > 120 || strlen($email) > 190 || strlen($content) > 3000) {
        $commentError = 'Panjang nama, email, atau komentar melebihi batas yang diperbolehkan.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $commentError = 'Alamat email tidak valid.';
    } else {
        $insert = db()->prepare(
            "INSERT INTO comments (article_id, commenter_name, commenter_email, content, status)
             VALUES (?, ?, ?, ?, 'pending')"
        );
        $insert->execute([(int) $article['id'], $name, $email !== '' ? $email : null, $content]);
        flash('success', 'Komentar berhasil dikirim dan menunggu moderasi.');
        redirect('detail.html?slug=' . rawurlencode($article['slug']) . '#komentar');
    }
}

$commentsStmt = db()->prepare(
    "SELECT * FROM comments WHERE article_id = ? AND status = 'approved' ORDER BY created_at DESC"
);
$commentsStmt->execute([(int) $article['id']]);
$comments = $commentsStmt->fetchAll();

$relatedStmt = db()->prepare(
    "SELECT a.*, f.name AS faculty_name, c.name AS category_name, u.name AS author_name
     FROM articles a
     LEFT JOIN faculties f ON f.id = a.faculty_id
     INNER JOIN categories c ON c.id = a.category_id
     INNER JOIN users u ON u.id = a.author_id
     WHERE a.status = 'published' AND a.category_id = ? AND a.id <> ?
     ORDER BY a.published_at DESC
     LIMIT 3"
);
$relatedStmt->execute([(int) $article['category_id'], (int) $article['id']]);
$related = $relatedStmt->fetchAll();
$pageTitle = $article['title'];


$view = [];
$view['article_category_name'] = e($article['category_name']);
$view['article_faculty_name_Universitas'] = e($article['faculty_name'] ?: 'Universitas');
$view['article_title'] = e($article['title']);
$view['article_excerpt'] = [];
if($article['excerpt']) {
    $item1 = [];
    $item1['article_excerpt2'] = e($article['excerpt']);
    $view['article_excerpt'][] = $item1;
}
$view['article_author_name'] = e($article['author_name']);
$view['date_id_article_published_at'] = e(date_id($article['published_at']));
$view['readerCount'] = number_format((int)$article['view_count'],0,',','.');
$view['article_image_article_featured_image'] = e(article_image($article['featured_image']));
$view['article_title2'] = e($article['title']);
$view['imageCaption'] = e($article['image_caption'] ?: 'Dokumentasi Pena Kampus');
$view['nl2br_e_article_content'] = nl2br(e($article['content']));
$view['count_comments'] = count($comments);
$view['success_flash_success'] = [];
if($success=flash('success')) {
    $item1 = [];
    $item1['success'] = e($success);
    $view['success_flash_success'][] = $item1;
}
$view['commentError'] = [];
if($commentError) {
    $item1 = [];
    $item1['commentError2'] = e($commentError);
    $view['commentError'][] = $item1;
}
$view['csrf_token'] = csrf_token();
$view['commentInput_name'] = e($commentInput['name']);
$view['commentInput_email'] = e($commentInput['email']);
$view['commentInput_content'] = e($commentInput['content']);
$view['comments'] = [];
foreach($comments as $comment) {
    $item1 = [];
    $item1['comment_commenter_name'] = e($comment['commenter_name']);
    $item1['date_id_comment_created_at'] = e(date_id($comment['created_at']));
    $item1['comment_content'] = e($comment['content']);
    $view['comments'][] = $item1;
}
$view['comments2'] = [];
if(!$comments) {
    $item1 = [];
    $view['comments2'][] = $item1;
}
$view['related'] = [];
foreach ($related as $item) {
    $item1 = [];
    $item1['card'] = article_card($item);
    $view['related'][] = $item1;
}
$view['related2'] = [];
if(!$related) {
    $item1 = [];
    $view['related2'][] = $item1;
}

json_response([
    'content' => $view,
    'title' => $pageTitle . ' | Pena Kampus',
    'layout' => public_layout($activeNav ?? ''),
]);
