<?php
require_once __DIR__ . '/config/app.php';

$judul = 'FAQ';
$faq = db()->query('SELECT pertanyaan, jawaban FROM faq ORDER BY urutan, id')->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<div class="container page-head">
  <h2 class="section-title">Pertanyaan yang Sering Diajukan</h2>

  <?php if (!$faq): ?>
    <p class="text-secondary">Belum ada FAQ.</p>
  <?php endif; ?>

  <div class="accordion" id="faqList">
    <?php foreach ($faq as $i => $f): ?>
      <div class="accordion-item">
        <h3 class="accordion-header">
          <button class="accordion-button <?= $i ? 'collapsed' : '' ?>" type="button"
                  data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
            <?= e($f['pertanyaan']) ?>
          </button>
        </h3>
        <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i ? '' : 'show' ?>" data-bs-parent="#faqList">
          <div class="accordion-body"><?= nl2br(e($f['jawaban'])) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>