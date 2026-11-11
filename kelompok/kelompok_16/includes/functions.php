<?php
function e($text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function readJsonInput(): array
{
    $data = json_decode(file_get_contents('php://input'), true);
    return is_array($data) ? $data : [];
}

function requirePostWithCsrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Metode request harus POST.'], 405);
    }
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!isCsrfValid($token)) {
        jsonResponse(['success' => false, 'message' => 'Token keamanan tidak valid. Muat ulang halaman.'], 403);
    }
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function pullFlash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function setOldInput(array $data): void
{
    $_SESSION['old_input'] = $data;
}

function pullOldInput(): array
{
    $old = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);
    return $old;
}

function timeAgo(string $datetime): string
{
    $selisih = time() - strtotime($datetime);
    if ($selisih < 60) {
        return 'baru saja';
    }
    if ($selisih < 3600) {
        return floor($selisih / 60) . ' menit lalu';
    }
    if ($selisih < 86400) {
        return floor($selisih / 3600) . ' jam lalu';
    }
    if ($selisih < 86400 * 7) {
        return floor($selisih / 86400) . ' hari lalu';
    }
    return date('d M Y', strtotime($datetime));
}

function excerpt(string $text, int $maxLength = 120): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if (mb_strlen($text) <= $maxLength) {
        return $text;
    }
    return mb_substr($text, 0, $maxLength) . '...';
}

function getCurrentUser(PDO $pdo): array
{
    if (!empty($_SESSION['user_id'])) {
        $stmt = $pdo->prepare(
            'SELECT id, username, email, is_guest, avatar_color, created_at FROM users WHERE id = :id'
        );
        $stmt->execute([':id' => (int) $_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) {
            return $user;
        }
    }
    return createGuestUser($pdo);
}

function createGuestUser(PDO $pdo): array
{
    $warna = ['#253B68', '#526B91', '#16A085', '#8E44AD', '#D35400', '#2C7A7B', '#B7791F'];

    for ($percobaan = 0; $percobaan < 20; $percobaan++) {
        $username = 'Pengguna-' . random_int(1000, 9999);
        $cek = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
        $cek->execute([':username' => $username]);
        if ((int) $cek->fetchColumn() === 0) {
            break;
        }
        $username = 'Pengguna-' . random_int(100000, 999999); // cadangan
    }

    $stmt = $pdo->prepare(
        'INSERT INTO users (username, is_guest, avatar_color) VALUES (:username, 1, :warna)'
    );
    $warnaDipilih = $warna[array_rand($warna)];
    $stmt->execute([
        ':username' => $username,
        ':warna'    => $warnaDipilih,
    ]);

    $_SESSION['user_id'] = (int) $pdo->lastInsertId();

    return [
        'id'           => $_SESSION['user_id'],
        'username'     => $username,
        'email'        => null,
        'is_guest'     => 1,
        'avatar_color' => $warnaDipilih,
        'created_at'   => date('Y-m-d H:i:s'),
    ];
}

function isGuest(array $user): bool
{
    return (int) $user['is_guest'] === 1;
}

function getCategories(PDO $pdo): array
{
    return $pdo->query('SELECT id, name, slug FROM categories ORDER BY id')->fetchAll();
}

function escapeLike(string $text): string
{
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $text);
}

function fetchPosts(PDO $pdo, int $viewerId, array $options = []): array
{
    $search       = trim($options['search'] ?? '');
    $categorySlug = $options['category'] ?? '';
    $userId       = (int) ($options['user_id'] ?? 0);
    $postId       = (int) ($options['post_id'] ?? 0);
    $sort         = $options['sort'] ?? 'terbaru';
    $limit        = max(1, min(100, (int) ($options['limit'] ?? 50)));

    $urutan = [
        'terbaru'    => 'p.created_at DESC, p.id DESC',
        'terpopuler' => 'like_count DESC, p.created_at DESC',
        'dijawab'    => 'reply_count DESC, p.created_at DESC',
        'trending'   => '(like_count + reply_count * 2) DESC, p.created_at DESC',
    ];
    $orderBy = $urutan[$sort] ?? $urutan['terbaru'];

    $kondisi = [];
    $params  = [':viewer' => $viewerId];

    if ($search !== '') {
        $kondisi[] = '(p.content LIKE :q1 OR u.username LIKE :q2 OR c.name LIKE :q3)';
        $like = '%' . escapeLike($search) . '%';
        $params[':q1'] = $like;
        $params[':q2'] = $like;
        $params[':q3'] = $like;
    }
    if ($categorySlug !== '') {
        $kondisi[] = 'c.slug = :slug';
        $params[':slug'] = $categorySlug;
    }
    if ($userId > 0) {
        $kondisi[] = 'p.user_id = :user_id';
        $params[':user_id'] = $userId;
    }
    if ($postId > 0) {
        $kondisi[] = 'p.id = :post_id';
        $params[':post_id'] = $postId;
    }

    $where = $kondisi ? 'WHERE ' . implode(' AND ', $kondisi) : '';

    $sql = "SELECT p.id, p.user_id, p.category_id, p.content, p.created_at, p.updated_at,
                   u.username, u.avatar_color, u.is_guest,
                   c.name AS category_name, c.slug AS category_slug,
                   (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
                   (SELECT COUNT(*) FROM replies r WHERE r.post_id = p.id) AS reply_count,
                   (SELECT COUNT(*) FROM likes lm WHERE lm.post_id = p.id AND lm.user_id = :viewer) AS liked_by_me
            FROM posts p
            JOIN users u ON u.id = p.user_id
            JOIN categories c ON c.id = p.category_id
            $where
            ORDER BY $orderBy
            LIMIT $limit";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetchReplies(PDO $pdo, int $postId): array
{
    $stmt = $pdo->prepare(
        'SELECT r.id, r.post_id, r.user_id, r.content, r.created_at,
                u.username, u.avatar_color, u.is_guest
         FROM replies r
         JOIN users u ON u.id = r.user_id
         WHERE r.post_id = :post_id
         ORDER BY r.created_at ASC, r.id ASC'
    );
    $stmt->execute([':post_id' => $postId]);
    return $stmt->fetchAll();
}

function postToJson(array $post): array
{
    return [
        'id'            => (int) $post['id'],
        'user_id'       => (int) $post['user_id'],
        'username'      => $post['username'],
        'avatar_color'  => $post['avatar_color'],
        'is_guest'      => (int) $post['is_guest'] === 1,
        'category_name' => $post['category_name'],
        'category_slug' => $post['category_slug'],
        'content'       => $post['content'],
        'time_text'     => timeAgo($post['created_at']),
        'is_edited'     => !empty($post['updated_at']),
        'like_count'    => (int) $post['like_count'],
        'reply_count'   => (int) $post['reply_count'],
        'liked_by_me'   => (int) $post['liked_by_me'] > 0,
    ];
}

function renderAvatar(string $username, string $color, string $size = 'md'): string
{
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
        $color = '#253B68';
    }
    $huruf = mb_strtoupper(mb_substr($username, 0, 1));
    return '<span class="avatar avatar-' . e($size) . '" style="background:' . $color . '">' . e($huruf) . '</span>';
}

function icon(string $name): string
{
    $paths = [
        'home'    => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'search'  => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'user'    => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'message' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'heart'   => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
        'logout'  => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'login'   => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>',
        'moon'    => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
        'edit'    => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>',
        'trash'   => '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>',
    ];
    $isi = $paths[$name] ?? '';
    return '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $isi . '</svg>';
}
