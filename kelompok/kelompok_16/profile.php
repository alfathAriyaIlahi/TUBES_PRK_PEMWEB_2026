<?php
// profile.php - Profil pengguna: avatar, status, statistik, riwayat postingan
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/post_card.php';

// Tanpa ?id= berarti profil sendiri
$profileId = isset($_GET['id']) ? (int) $_GET['id'] : (int) $currentUser['id'];

$stmt = $pdo->prepare('SELECT id, username, is_guest, avatar_color, created_at FROM users WHERE id = :id');
$stmt->execute([':id' => $profileId]);
$profileUser = $stmt->fetch();

$pageTitle  = 'Profil';
$activeMenu = ($profileId === (int) $currentUser['id']) ? 'profile' : '';

// jika user nggak ketemu (id salah atau akun udah nggak ada)
if (!$profileUser) {
    http_response_code(404);
    include __DIR__ . '/includes/header.php';
    echo '<div class="empty-state">Pengguna tidak ditemukan. <a href="index.php">Kembali ke beranda</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// menghitung jumlah postingan dan jawaban
$hitungPost = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE user_id = :id');
$hitungPost->execute([':id' => $profileId]);
$totalPosts = (int) $hitungPost->fetchColumn();

$hitungReply = $pdo->prepare('SELECT COUNT(*) FROM replies WHERE user_id = :id');
$hitungReply->execute([':id' => $profileId]);
$totalReplies = (int) $hitungReply->fetchColumn();

$posts = fetchPosts($pdo, (int) $currentUser['id'], ['user_id' => $profileId]);
$isOwnProfile = ($profileId === (int) $currentUser['id']);

include __DIR__ . '/includes/header.php';
?>

<section class="card profile-card">
    <?= renderAvatar($profileUser['username'], $profileUser['avatar_color'], 'lg') ?>
    <div class="profile-info">
        <h1><?= e($profileUser['username']) ?></h1>
        <p class="muted">
            <span class="badge <?= (int) $profileUser['is_guest'] === 1 ? '' : 'badge-member' ?>">
                <?= (int) $profileUser['is_guest'] === 1 ? 'Tamu' : 'Anggota' ?>
            </span>
            Bergabung <?= e(date('d M Y', strtotime($profileUser['created_at']))) ?>
        </p>
    </div>
    <div class="profile-stats">
        <div><strong><?= $totalPosts ?></strong><span>Postingan</span></div>
        <div><strong><?= $totalReplies ?></strong><span>Jawaban</span></div>
    </div>
</section>

<?php if ($isOwnProfile && isGuest($currentUser)): ?>
    <div class="alert alert-info">
        Kamu masuk sebagai tamu. Identitas ini tersimpan di browser ini saja.
        <a href="register.php">Daftar</a> untuk memiliki akun permanen (postingan lamamu ikut terbawa) atau
        <a href="login.php">masuk</a> jika sudah punya akun.
    </div>
<?php endif; ?>

<h2 class="section-title">Riwayat Postingan</h2>
<section class="feed">
    <?php if (!$posts): ?>
        <div class="empty-state">Belum ada postingan.</div>
    <?php endif; ?>
    <?php foreach ($posts as $post): ?>
        <?php renderPostCard($post, (int) $currentUser['id'], false, 'profile.php'); ?>
    <?php endforeach; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
