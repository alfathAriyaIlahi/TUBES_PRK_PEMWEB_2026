<?php
/** @var array<string, mixed>|null $report */
?>
<main class="container page-content">
  <div class="page-heading"><span class="eyebrow">DETAIL LAPORAN</span><h1>Detail Pengaduan</h1><p class="text-secondary mb-0">Informasi status laporan dan tahapan penanganannya.</p></div>
  <?php if ($report === null): ?>
    <section class="empty-state" aria-labelledby="detailEmptyTitle">
      <span class="empty-state-icon"><i class="bi bi-file-earmark-lock" aria-hidden="true"></i></span>
      <h2 id="detailEmptyTitle">Laporan tidak ditemukan</h2>
      <p>Laporan tersebut tidak tersedia atau bukan milik akun Anda.</p>
      <a class="btn btn-outline-danger" href="<?= escape(url('history')) ?>"><i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Kembali ke Riwayat</a>
    </section>
  <?php else: ?>
    <section class="detail-card">
      <div class="detail-summary">
        <div><span class="eyebrow"><?= escape($report['case_number']) ?></span><h2><?= escape($report['title']) ?></h2><p>Dikirim <?= escape(format_date($report['created_at'])) ?></p></div>
        <span class="badge <?= escape(status_class($report['status'])) ?>"><?= escape(status_label($report['status'])) ?></span>
      </div>
      <section class="detail-section">
        <p>Kategori: <?= escape($report['category']) ?></p>
        <p>Tanggal kejadian: <?= escape(format_date($report['incident_date'])) ?></p>
        <h3>Kronologi</h3>
        <p class="detail-description"><?= escape($report['chronology']) ?></p>
      </section>
      <?php if ($report['decision_note'] !== null): ?>
        <section class="detail-section"><h3>Catatan keputusan</h3><p class="detail-description"><?= escape($report['decision_note']) ?></p></section>
      <?php endif; ?>
      <?php if ($report['attachments'] !== []): ?>
        <section class="detail-section"><h3>Lampiran</h3>
          <?php foreach ($report['attachments'] as $attachment): ?>
            <a class="d-block mb-2" href="<?= escape(url('attachment', ['case' => $report['case_number'], 'id' => $attachment['id']])) ?>"><?= escape($attachment['name']) ?></a>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>
    </section>
  <?php endif; ?>
  <div class="d-flex flex-wrap gap-2 mt-3 justify-content-center">
    <a class="btn btn-light" href="<?= escape(url('history')) ?>"><i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Kembali ke Riwayat</a>
    <a class="btn btn-danger" href="<?= escape(url('report')) ?>"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Buat Laporan Baru</a>
  </div>
</main>
