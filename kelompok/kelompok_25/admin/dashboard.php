<?php
// MODUL ADMIN (Linda) - Dashboard Admin
require_once __DIR__ . '/../config/app.php';
require_role('admin');

// 1. Hitung jumlah pengguna berdasarkan peran
$stmt = $pdo->query("SELECT role, COUNT(*) as total FROM users GROUP BY role");
$users_count = [];
while ($row = $stmt->fetch()) {
    $users_count[$row['role']] = $row['total'];
}

// 2. Hitung jumlah konsultasi berdasarkan status
$stmt = $pdo->query("SELECT status, COUNT(*) as total FROM konsultasi GROUP BY status");
$konsultasi_count = [];
while ($row = $stmt->fetch()) {
    $konsultasi_count[$row['status']] = $row['total'];
}

// 3. Hitung jumlah pengacara yang menunggu verifikasi
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pengacara' AND status = 'menunggu'");
$pengacara_menunggu = $stmt->fetchColumn();

$judul = 'Dashboard Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container page-head my-4">
    <h2>Dashboard Admin</h2>
    <p class="text-secondary">Halo, <?= e(current_user()['nama']) ?>! Berikut ringkasan sistem Ruang Hukum.</p>

    <div class="row g-4 mt-3">
        <!-- Kartu Pengacara Menunggu Verifikasi -->
        <div class="col-md-4">
            <div class="card text-white bg-warning shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Pengacara Menunggu</h5>
                    <h3><?= e($pengacara_menunggu) ?></h3>
                    <p class="card-text">Perlu verifikasi segera.</p>
                    <a href="verifikasi.php" class="btn btn-light btn-sm">Kelola Verifikasi</a>
                </div>
            </div>
        </div>

        <!-- Kartu Total Klien -->
        <div class="col-md-4">
            <div class="card text-white bg-primary shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Klien</h5>
                    <h3><?= e($users_count['klien'] ?? 0) ?></h3>
                    <p class="card-text">Pengguna terdaftar sebagai klien.</p>
                    <a href="pengguna.php" class="btn btn-light btn-sm">Lihat Pengguna</a>
                </div>
            </div>
        </div>

        <!-- Kartu Total Pengacara Aktif -->
        <div class="col-md-4">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Pengacara</h5>
                    <h3><?= e($users_count['pengacara'] ?? 0) ?></h3>
                    <p class="card-text">Pengacara terdaftar.</p>
                    <a href="pengguna.php" class="btn btn-light btn-sm">Lihat Pengguna</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Konsultasi -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    Statistik Status Konsultasi
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Menunggu Dijawab
                            <span class="badge bg-secondary rounded-pill"><?= e($konsultasi_count['menunggu'] ?? 0) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Sedang Dijawab / Aktif
                            <span class="badge bg-primary rounded-pill"><?= e($konsultasi_count['dijawab'] ?? 0) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Selesai
                            <span class="badge bg-success rounded-pill"><?= e($konsultasi_count['selesai'] ?? 0) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Ditutup
                            <span class="badge bg-danger rounded-pill"><?= e($konsultasi_count['ditutup'] ?? 0) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>