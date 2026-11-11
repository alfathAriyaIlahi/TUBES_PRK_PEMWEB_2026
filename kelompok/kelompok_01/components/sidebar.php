<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-logo">S</div>
        <div class="brand-info">
            <h2>SaveSmart</h2>
            <small><?= ($_SESSION['user']['role'] ?? '') === 'admin' ? 'Administrator' : 'Family Finance' ?></small>
        </div>
    </div>

    <nav class="sidebar-menu">

        <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>

            <span class="menu-heading">ADMINISTRATOR</span>

            <a href="admin_dashboard.php" class="menu-item <?= ($pageTitle ?? '') === 'Admin Dashboard' ? 'active' : '' ?>">
                <span>▦</span>
                <span>Dashboard</span>
            </a>

            <a href="admin_pengguna.php" class="menu-item <?= ($pageTitle ?? '') === 'Manajemen Pengguna' ? 'active' : '' ?>">
                <span>♙</span>
                <span>Manajemen Pengguna</span>
            </a>

            <a href="admin_kategori.php" class="menu-item <?= ($pageTitle ?? '') === 'Manajemen Kategori' ? 'active' : '' ?>">
                <span>▤</span>
                <span>Manajemen Kategori</span>
            </a>

        <?php else: ?>

            <span class="menu-heading">MENU UTAMA</span>

            <a href="dashboard.php" class="menu-item <?= ($pageTitle ?? '') === 'Dashboard' ? 'active' : '' ?>">
                <span>▦</span> Dashboard
            </a>

            <a href="pemasukan.php" class="menu-item">
                <span>↙</span> Pemasukan
            </a>

            <a href="pengeluaran.php" class="menu-item">
                <span>↗</span> Pengeluaran
            </a>

            <span class="menu-heading">KEUANGAN</span>

            <a href="budget.php" class="menu-item">
                <span>▤</span> Anggaran
            </a>

            <a href="tabungan.php" class="menu-item">
                <span>◎</span> Tabungan
            </a>

            <a href="laporan.php" class="menu-item">
                <span>▥</span> Laporan
            </a>

        <?php endif; ?>

    </nav>

    <a href="../logout.php" class="sidebar-logout" id="logoutBtn">
        <span>↪</span>
        <span>Logout</span>
    </a>
</aside>

<div class="modal-overlay" id="logoutModal" hidden>
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="logoutTitle">
        <div class="modal-icon">↪</div>
        <h3 id="logoutTitle">Keluar dari SaveSmart?</h3>
        <p>Anda perlu login kembali untuk mengakses data keuangan keluarga.</p>

        <div class="modal-actions">
            <button type="button" class="modal-btn modal-btn-cancel" id="logoutCancel">Batal</button>
            <a href="../logout.php" class="modal-btn modal-btn-danger">Ya, Keluar</a>
        </div>
    </div>
</div>