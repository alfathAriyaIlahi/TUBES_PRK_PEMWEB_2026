
<?php
/** @var array<int, array<string, mixed>> $announcements */
/** @var array<int, array<string, mixed>> $reports */
?>

<main class="container page-content">
  <div class="dashboard-container">

    <!-- Header Beranda -->
    <div class="page-heading d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3">
      <div>
        <span class="eyebrow">BERANDA</span>
        <h1>Beranda ADVOKITA</h1>
        <p class="text-secondary mb-0">
          Akses layanan pengaduan dan informasi tindak lanjut.
        </p>
      </div>

      <a class="btn btn-danger" href="<?= escape(url('report')) ?>">
        <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>
        Buat Laporan
      </a>
    </div>

    <div class="dashboard-divider"></div>

    <!-- Pengumuman -->
    <section class="announcement-section" aria-labelledby="announcementHeading">
      <div class="section-heading">
        <div>
          <span class="eyebrow">INFORMASI</span>
          <h2 id="announcementHeading">Pengumuman</h2>
        </div>
      </div>

      <div class="announcement-list">
        <?php if ($announcements === []): ?>
          <p class="announcement-empty">
            Belum ada pengumuman yang diterbitkan.
          </p>
        <?php else: ?>
          <?php foreach ($announcements as $announcement): ?>
            <article class="announcement-card">
              <span class="announcement-icon" aria-hidden="true">
                <i class="bi bi-megaphone-fill"></i>
              </span>

              <div class="announcement-content">
                <span class="announcement-label">Informasi terbaru</span>
                <h3><?= escape($announcement['title']) ?></h3>

                <time class="announcement-date" datetime="<?= escape($announcement['published_at']) ?>">
                  <?= escape(format_date($announcement['published_at'])) ?>
                </time>

                <p><?= escape($announcement['body']) ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <div class="dashboard-divider"></div>

    <!-- Laporan Terbaru -->
    <section>
      <?php if ($reports !== []): ?>

        <div class="section-heading">
          <div>
            <span class="eyebrow">LAPORAN ANDA</span>
            <h2>Laporan terbaru</h2>
          </div>

          <a href="<?= escape(url('history')) ?>">
            Lihat riwayat
            <i class="bi bi-arrow-right" aria-hidden="true"></i>
          </a>
        </div>

        <div class="report-list">
          <?php foreach (array_slice($reports, 0, 3) as $reportItem): ?>
            <article class="report-row">

              <div class="report-row-top">
                <div>
                  <span class="report-number">
                    <?= escape($reportItem['case_number']) ?>
                    ·
                    <?= escape(format_date($reportItem['created_at'])) ?>
                  </span>

                  <h2><?= escape($reportItem['title']) ?></h2>

                  <span class="report-category">
                    <?= escape($reportItem['category']) ?>
                  </span>
                </div>

                <span class="badge <?= escape(status_class($reportItem['status'])) ?>">
                  <?= escape(status_label($reportItem['status'])) ?>
                </span>
              </div>

              <a class="report-detail-link" href="<?= escape(url('detail', ['case' => $reportItem['case_number']])) ?>">
                Lihat detail
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
              </a>

              <?php if ($reportItem['decision_note'] !== null): ?>
                <p class="report-decision-note">
                  Catatan admin: <?= escape($reportItem['decision_note']) ?>
                </p>
              <?php endif; ?>

            </article>
          <?php endforeach; ?>
        </div>

      <?php else: ?>

        <section class="empty-state" aria-labelledby="dashboardEmptyTitle">
          <span class="empty-state-icon">
            <i class="bi bi-inbox" aria-hidden="true"></i>
          </span>

          <h2 id="dashboardEmptyTitle">Belum ada laporan</h2>

          <p>
            Setelah Anda mengirim pengaduan, statusnya akan muncul di sini.
          </p>

          <a class="btn btn-danger" href="<?= escape(url('report')) ?>">
            <i class="bi bi-plus-lg me-2" aria-hidden="true"></i>
            Buat Pengaduan
          </a>
        </section>

      <?php endif; ?>
    </section>

  </div>
</main>
