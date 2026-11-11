<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Laporan Publik';

$stmtKategori = $pdo->query("SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
$kategoriList = $stmtKategori->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<section style="margin-bottom: 2rem;">
    <div style="margin-bottom: 1.25rem;">
        <h1 style="font-size: 1.85rem; color: var(--color-primary); margin-bottom: 0.35rem; letter-spacing: -0.5px;">
            Daftar Laporan Publik
        </h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem; margin: 0;">
            Semua aspirasi dan pengaduan terbuka yang disampaikan warga kampus FISIP dapat dipantau dan ditelusuri di sini secara transparan.
        </p>
    </div>

    <div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; align-items: flex-end;">
            <div style="grid-column: 1 / -1;">
                <label for="search-input" class="form-label">Cari Laporan</label>
                <input 
                    type="text" 
                    id="search-input" 
                    class="form-control" 
                    placeholder="Ketik kata kunci judul, rincian isi, atau lokasi kejadian..." 
                    autocomplete="off"
                >
            </div>
            <div>
                <label for="filter-kategori" class="form-label">Kategori</label>
                <select id="filter-kategori" class="form-control">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategoriList as $kat): ?>
                        <option value="<?= sanitize($kat['id']) ?>"><?= sanitize($kat['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="filter-jenis" class="form-label">Jenis</label>
                <select id="filter-jenis" class="form-control">
                    <option value="">Semua Jenis</option>
                    <option value="pengaduan">Pengaduan</option>
                    <option value="aspirasi">Aspirasi</option>
                </select>
            </div>
            <div>
                <button type="button" id="btn-reset" class="btn btn-secondary" style="width: 100%;">
                    Reset Filter
                </button>
            </div>
        </div>
        <div id="search-info" style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 0.75rem;">
            Memuat data...
        </div>
    </div>
</section>

<section id="laporan-container">
    <div style="text-align: center; padding: 2.5rem 1rem; color: var(--color-text-muted);">
        <em>Memuat data dari server...</em>
    </div>
</section>

<nav id="pagination-container" style="margin-top: 2rem; display: flex; justify-content: center; align-items: center; gap: 0.5rem; flex-wrap: wrap;" aria-label="Navigasi Halaman"></nav>

<script src="/assets/js/app.js" defer></script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
