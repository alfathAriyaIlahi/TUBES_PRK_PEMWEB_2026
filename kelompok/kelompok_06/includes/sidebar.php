
<!-- SIDEBAR KIRI (Disembunyikan saat mode print) -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between no-print shrink-0">
        <div>
            <!-- Brand / Logo -->
            <div class="h-16 flex items-center px-6 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center text-white font-bold text-sm tracking-tight">
                        FP
                    </div>
                    <div>
                        <span class="font-bold text-slate-900 tracking-tight text-base block leading-none">FlashPOS</span>
                        <span class="text-[11px] text-slate-400 font-medium">Point of Sale System</span>
                    </div>
                </div>
            </div>

            <!-- Menu Navigasi (Slot Integrasi Anggota) -->
            <nav class="p-4 space-y-1">
                <p class="px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Menu Utama</p>
                
                <!-- Slot Anggota 5: Dashboard -->
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="index.php?page=dashboard" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= ($active_page == 'dashboard') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    Dashboard Analitik
                </a>
                <?php endif; ?>

                <!-- Slot Anggota 4: Kasir & Split Bill -->
                <a href="index.php?page=kasir" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= ($active_page == 'kasir') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                    <i data-lucide="calculator" class="w-4 h-4"></i>
                    Terminal Kasir (POS)
                </a>

                <p class="px-3 pt-4 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Manajemen Data</p>

                <!-- Slot Anggota 3: Data Master Produk -->
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="index.php?page=produk" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= ($active_page == 'produk') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    Data Master Produk
                </a>
                <?php endif; ?>

                <!-- Slot Anggota 5: Laporan & Riwayat -->
                <a href="index.php?page=laporan" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= ($active_page == 'laporan') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                    <i data-lucide="file-Spreadsheet" class="w-4 h-4"></i>
                    Laporan & Bukti Cetak
                </a>
            </nav>
        </div>

        <!-- Footer Sidebar / Logout (Slot Anggota 2) -->
        <div class="p-4 border-t border-slate-100">
            <a href="logout.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                Keluar Sesi
            </a>
        </div>
    </aside>

    <!-- AREA KONTEN KANAN -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- TOPBAR ATAS -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8 no-print shrink-0">
            <div>
                <h1 class="text-base font-semibold text-slate-900"><?= $page_heading ?? 'Ringkasan Sistem' ?></h1>
            </div>
            
            <div class="flex items-center gap-4">
                <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                    ● Sistem Online
                </span>
                <div class="h-4 w-[1px] bg-slate-200"></div>
                <div class="text-right">
                    <p class="text-xs font-semibold text-slate-800 leading-none"><?= htmlspecialchars($nama_user) ?></p>
                    <p class="text-[11px] text-slate-400 mt-0.5"><?= htmlspecialchars($role_user) ?></p>
                </div>
            </div>
        </header>

        <!-- KONTEN DINAMIS DIMULAI DI SINI -->
        <main class="flex-1 overflow-y-auto p-8">
