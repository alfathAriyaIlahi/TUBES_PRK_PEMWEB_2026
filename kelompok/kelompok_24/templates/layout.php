<?php
/** @var string $title */
/** @var string $page */
/** @var array{role: string, fullName: string}|null $user */
/** @var string|null $error */
/** @var string|null $success */
?>
<!doctype html>
<html lang="id" class="h-100">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal pengaduan dan bantuan hukum mahasiswa ADVOKITA">
    <title><?= escape($title) ?> — ADVOKITA</title>
    <link rel="icon" href="/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/styles.css" rel="stylesheet">
  </head>
  <body class="<?= in_array($page, ['login', 'register'], true) ? 'auth-page' : 'd-flex flex-column h-100 bg-light' ?>" data-page="<?= escape($page) ?>">
    <?php if (!in_array($page, ['login', 'register'], true)): ?>
      <header class="site-header bg-white border-bottom shadow-sm sticky-top">
        <div class="container d-flex align-items-center justify-content-between py-2 py-md-0" style="min-height: 70px;">
          <a class="site-brand text-decoration-none d-flex align-items-center gap-2" href="<?= escape(url($user['role'] === 'admin' ? 'admin' : 'dashboard')) ?>">
            <span class="brand-icon bg-danger bg-opacity-10 text-danger p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
              <i class="bi bi-bank fs-5" aria-hidden="true"></i>
            </span>
            <span class="d-flex flex-column lh-sm">
              <strong class="text-dark tracking-tight">ADVOKITA</strong>
              <small class="text-muted" style="font-size: 0.75rem;"><?= $user['role'] === 'admin' ? 'Panel Administrasi' : 'Legal Aid System' ?></small>
            </span>
          </a>

          <?php if ($user['role'] === 'user'): ?>
            <nav class="main-nav d-none d-lg-flex align-items-center gap-1 ms-lg-4" aria-label="Navigasi utama">
              <a class="px-3 py-2 rounded-2 text-decoration-none fw-medium <?= $page === 'dashboard' ? 'active bg-danger text-white' : 'text-secondary hover-bg-light' ?>" href="<?= escape(url('dashboard')) ?>">
                <i class="bi bi-house me-1" aria-hidden="true"></i><span>Beranda</span>
              </a>
              <a class="px-3 py-2 rounded-2 text-decoration-none fw-medium <?= $page === 'report' ? 'active bg-danger text-white' : 'text-secondary hover-bg-light' ?>" href="<?= escape(url('report')) ?>">
                <i class="bi bi-plus-circle me-1" aria-hidden="true"></i><span>Pengaduan</span>
              </a>
              <a class="px-3 py-2 rounded-2 text-decoration-none fw-medium <?= in_array($page, ['history', 'detail'], true) ? 'active bg-danger text-white' : 'text-secondary hover-bg-light' ?>" href="<?= escape(url('history')) ?>">
                <i class="bi bi-clock-history me-1" aria-hidden="true"></i><span>Riwayat</span>
              </a>
            </nav>
          <?php endif; ?>

          <div class="d-flex align-items-center gap-3 ms-auto">
            <div class="user-info d-none d-md-flex align-items-center gap-2 border-start ps-3">
              <span class="avatar bg-danger text-white rounded-circle fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; font-size: 0.9rem;">
                <?= escape(strtoupper(substr($user['fullName'], 0, 1))) ?>
              </span>
              <span class="d-flex flex-column lh-sm">
                <strong class="text-dark small"><?= escape($user['fullName']) ?></strong>
                <small class="text-muted" style="font-size: 0.75rem;"><?= $user['role'] === 'admin' ? 'Admin' : 'Mahasiswa' ?></small>
              </span>
            </div>
            
            <form method="post" action="/index.php" class="mb-0">
              <?php csrf_input(); ?>
              <input type="hidden" name="action" value="logout">
              <button class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-medium d-flex align-items-center" type="submit">
                <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i> Keluar
              </button>
            </form>
          </div>
        </div>
      </header>
    <?php endif; ?>

    <?php if ($error !== null || $success !== null): ?>
      <div class="container notification-container <?= in_array($page, ['login', 'register'], true) ? 'auth-messages' : 'page-content pb-0 pt-3' ?>">
        <?php if ($error !== null): ?>
          <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= escape($error) ?>
          </div>
        <?php endif; ?>
        <?php if ($success !== null): ?>
          <div class="alert alert-success shadow-sm border-0 rounded-3 mb-2" role="status">
            <i class="bi bi-check-circle-fill me-2"></i><?= escape($success) ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="flex-shrink-0">
      <?php require APP_ROOT . '/templates/pages/' . $page . '.php'; ?>
    </div>

    <?php if (!in_array($page, ['login', 'register'], true)): ?>
      <footer class="site-footer bg-white border-top py-3 mt-auto text-muted small">
        <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
          <span class="fw-semibold text-dark">ADVOKITA</span>
          <span>Lembaga Bantuan Hukum Kampus &copy; <?= date('Y') ?></span>
        </div>
      </footer>

      <?php if ($user['role'] === 'user'): ?>
        <nav class="main-nav mobile-nav d-lg-none fixed-bottom bg-white border-top shadow-lg py-2 px-3 d-flex justify-content-around align-items-center" aria-label="Navigasi seluler">
          <a class="d-flex flex-column align-items-center text-decoration-none <?= $page === 'dashboard' ? 'text-danger fw-bold' : 'text-muted' ?>" href="<?= escape(url('dashboard')) ?>">
            <i class="bi bi-house fs-5" aria-hidden="true"></i><span style="font-size: 0.7rem;">Beranda</span>
          </a>
          <a class="d-flex flex-column align-items-center text-decoration-none <?= $page === 'report' ? 'text-danger fw-bold' : 'text-muted' ?>" href="<?= escape(url('report')) ?>">
            <i class="bi bi-plus-circle fs-5" aria-hidden="true"></i><span style="font-size: 0.7rem;">Pengaduan</span>
          </a>
          <a class="d-flex flex-column align-items-center text-decoration-none <?= in_array($page, ['history', 'detail'], true) ? 'text-danger fw-bold' : 'text-muted' ?>" href="<?= escape(url('history')) ?>">
            <i class="bi bi-clock-history fs-5" aria-hidden="true"></i><span style="font-size: 0.7rem;">Riwayat</span>
          </a>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </body>
</html>
