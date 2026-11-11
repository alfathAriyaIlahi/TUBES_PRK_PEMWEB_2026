<?php
/** @var array<string, string> $formValues */
?>
<main class="container page-content form-page">
  <div class="page-heading"><span class="eyebrow">PENGADUAN</span><h1>Buat Laporan Baru</h1><p class="text-secondary mb-0">Isi informasi utama mengenai kasus yang ingin Anda laporkan. Kolom bertanda <span class="text-danger fw-bold">*</span> wajib diisi.</p></div>
  <form class="report-form" method="post" action="/index.php?page=report" enctype="multipart/form-data">
    <?php csrf_input(); ?>
    <input type="hidden" name="action" value="report">
    <input type="hidden" name="MAX_FILE_SIZE" value="10485760">
    <div class="form-intro"><i class="bi bi-shield-check" aria-hidden="true"></i><div><h2>Formulir Pengaduan</h2><p class="mb-0 text-secondary">Lengkapi informasi yang diperlukan untuk menyampaikan pengaduan.</p></div></div>
    <div class="row g-3 form-body">
      <div class="col-md-6">
        <label class="form-label" for="reportTitle">Judul laporan <span class="text-danger">*</span></label>
        <input class="form-control" id="reportTitle" name="title" maxlength="160" required placeholder="Ringkasan pokok permasalahan" value="<?= escape($formValues['title'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label" for="reportCategory">Kategori kasus <span class="text-danger">*</span></label>
        <select class="form-select" id="reportCategory" name="category" required>
          <option value="">Pilih kategori</option>
          <?php foreach (REPORT_CATEGORIES as $category): ?>
            <option value="<?= escape($category) ?>" <?= ($formValues['category'] ?? '') === $category ? 'selected' : '' ?>><?= escape($category) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="incidentDate">Tanggal kejadian <span class="text-danger">*</span></label>
        <input class="form-control" type="date" id="incidentDate" name="incidentDate" max="<?= escape(gmdate('Y-m-d')) ?>" required value="<?= escape($formValues['incidentDate'] ?? '') ?>">
      </div>
      <div class="col-12">
        <label class="form-label" for="chronology">Kronologi singkat <span class="text-danger">*</span></label>
        <textarea class="form-control" id="chronology" name="chronology" rows="5" required minlength="20" maxlength="2000" placeholder="Ceritakan kejadian secara singkat dan jelas."><?= escape($formValues['chronology'] ?? '') ?></textarea>
        <div class="form-text">Minimal 20 karakter dan maksimal 2000 karakter.</div>
      </div>
      <div class="col-12"><div class="form-check option-box"><input class="form-check-input" type="checkbox" name="anonymous" value="true" id="anonymous" <?= isset($formValues['anonymous']) ? 'checked' : '' ?>><label class="form-check-label" for="anonymous"><b>Kirim sebagai anonim</b><small>Nama dan NPM tidak ditampilkan pada daftar laporan admin.</small></label></div></div>
      <div class="col-12">
        <div class="upload-box">
          <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
          <span><label for="attachment"><b>Lampirkan bukti (opsional)</b></label><small>PDF, JPG, atau PNG · Maksimal 10 MB</small></span>
          <input class="form-control" type="file" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">
        </div>
      </div>
    </div>
    <div class="form-actions"><a class="btn btn-light" href="<?= escape(url('dashboard')) ?>">Batal</a><button class="btn btn-danger" type="submit">Kirim Laporan <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></button></div>
  </form>
</main>
