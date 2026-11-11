<?php
/** @var int $pendingCount */
/** @var int $announcementCount */
/** @var array<string, string> $formValues */
/** @var array<int, array<string, mixed>> $reports */
/** @var array<int, array<string, mixed>> $announcements */
?>
<main class="container page-content admin-page">
  <div class="page-heading"><span class="eyebrow">ADMINISTRASI</span><h1>Kelola layanan</h1><p class="text-secondary mb-0">Tinjau laporan yang masuk dan terbitkan informasi untuk pengguna.</p></div>

  <section class="admin-section" aria-labelledby="adminReportsHeading">
    <div class="section-heading"><div><span class="eyebrow">TINJAUAN</span><h2 id="adminReportsHeading">Laporan pengaduan</h2></div><span class="text-secondary small"><?= escape($pendingCount) ?> menunggu · <?= escape(count($reports)) ?> total</span></div>
    <?php if ($reports === []): ?>
      <p class="empty-inline">Belum ada laporan yang masuk.</p>
    <?php else: ?>
      <div class="admin-report-list">
        <?php foreach ($reports as $reportItem): ?>
          <article class="admin-report">
            <div class="admin-report-heading">
              <div><span class="report-number"><?= escape($reportItem['case_number']) ?> · <?= escape(format_date($reportItem['created_at'])) ?></span><h3><?= escape($reportItem['title']) ?></h3><span class="report-category"><?= escape($reportItem['category']) ?></span></div>
              <span class="badge <?= escape(status_class($reportItem['status'])) ?>"><?= escape(status_label($reportItem['status'])) ?></span>
            </div>
            <p class="admin-report-meta"><?= is_checked($reportItem['is_anonymous']) ? 'Anonim' : escape(($reportItem['submitter_name'] ?? 'Pengguna') . ' · NPM ' . ($reportItem['submitter_nim'] ?? '-')) ?> · Kejadian <?= escape(format_date($reportItem['incident_date'])) ?></p>
            <h4>Kronologi</h4><p class="admin-report-chronology"><?= escape($reportItem['chronology']) ?></p>
            <?php foreach ($reportItem['attachments'] as $attachment): ?>
              <a class="admin-attachment" href="<?= escape(url('attachment', ['case' => $reportItem['case_number'], 'id' => $attachment['id']])) ?>"><?= escape($attachment['name']) ?></a>
            <?php endforeach; ?>
            <?php if ($reportItem['decision_note'] !== null): ?><p class="report-decision-note">Catatan keputusan: <?= escape($reportItem['decision_note']) ?></p><?php endif; ?>
            <?php if ($reportItem['status'] === 'pending'): ?>
              <form class="admin-decision" method="post" action="<?= escape(url('admin')) ?>">
                <?php csrf_input(); ?>
                <input type="hidden" name="action" value="decision">
                <input type="hidden" name="reportId" value="<?= escape($reportItem['id']) ?>">
                <label class="visually-hidden" for="note-<?= escape($reportItem['id']) ?>">Catatan keputusan (opsional)</label>
                <textarea class="form-control" id="note-<?= escape($reportItem['id']) ?>" name="note" maxlength="2000" rows="2" placeholder="Catatan untuk pengguna (opsional)"></textarea>
                <div class="d-flex flex-wrap justify-content-end gap-2">
                  <button class="btn btn-outline-danger" type="submit" name="decision" value="rejected">Tolak</button>
                  <button class="btn btn-danger" type="submit" name="decision" value="approved">Setujui</button>
                </div>
              </form>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="admin-section" aria-labelledby="adminAnnouncementsHeading">
    <div class="section-heading"><div><span class="eyebrow">BERANDA</span><h2 id="adminAnnouncementsHeading">Pengumuman</h2></div><span class="text-secondary small"><?= escape($announcementCount) ?> total</span></div>
    <form class="admin-form" method="post" action="<?= escape(url('admin')) ?>">
      <?php csrf_input(); ?>
      <input type="hidden" name="action" value="announcement-create">
      <div class="mb-3"><label class="form-label" for="announcementTitle">Judul</label><input class="form-control" id="announcementTitle" name="title" maxlength="120" required value="<?= escape($formValues['title'] ?? '') ?>"></div>
      <div class="mb-3"><label class="form-label" for="announcementBody">Isi pengumuman</label><textarea class="form-control" id="announcementBody" name="body" rows="4" maxlength="3000" required><?= escape($formValues['body'] ?? '') ?></textarea></div>
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="form-check"><input class="form-check-input" id="announcementPublished" name="isPublished" type="checkbox" <?= !isset($formValues['action']) || isset($formValues['isPublished']) ? 'checked' : '' ?>><label class="form-check-label" for="announcementPublished">Terbitkan di Beranda</label></div>
        <button class="btn btn-danger" type="submit"><i class="bi bi-send me-2" aria-hidden="true"></i>Simpan Pengumuman</button>
      </div>
    </form>
    <?php if ($announcements === []): ?>
      <p class="empty-inline mt-3">Belum ada pengumuman.</p>
    <?php else: ?>
      <div class="admin-announcement-list mt-4">
        <?php foreach ($announcements as $announcement): ?>
          <article class="admin-announcement">
            <div><h3><?= escape($announcement['title']) ?></h3><span class="badge <?= $announcement['is_published'] ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $announcement['is_published'] ? 'Terbit' : 'Draf' ?></span></div>
            <p><?= escape($announcement['body']) ?></p>
            <div class="d-flex justify-content-end gap-2">
              <form method="post" action="<?= escape(url('admin')) ?>">
                <?php csrf_input(); ?>
                <input type="hidden" name="action" value="announcement-toggle">
                <input type="hidden" name="announcementId" value="<?= escape($announcement['id']) ?>">
                <input type="hidden" name="isPublished" value="<?= $announcement['is_published'] ? '0' : '1' ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit"><?= $announcement['is_published'] ? 'Sembunyikan dari Beranda' : 'Terbitkan' ?></button>
              </form>
              <form method="post" action="<?= escape(url('admin')) ?>">
                <?php csrf_input(); ?>
                <input type="hidden" name="action" value="announcement-delete">
                <input type="hidden" name="announcementId" value="<?= escape($announcement['id']) ?>">
                <button class="btn btn-sm btn-outline-secondary" type="submit">Hapus</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
