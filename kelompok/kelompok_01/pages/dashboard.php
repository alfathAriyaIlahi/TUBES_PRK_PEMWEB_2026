<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

// Dashboard ini khusus Kepala Keluarga
if (($_SESSION['user']['role'] ?? '') !== 'kepala_keluarga') {
    if (($_SESSION['user']['role'] ?? '') === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: ../login.php');
    }
    exit;
}

$pageTitle = 'Dashboard';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SaveSmart - Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
</head>

<body>

    <div class="app-container">

        <!-- Sidebar -->
        <?php include __DIR__ . '/../components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">

            <!-- Navbar -->
            <?php include __DIR__ . '/../components/navbar.php'; ?>

            <div class="dashboard-content">

                <!-- Welcome Section -->
                <section class="welcome-section">
                    <div>
                        <h2>Ringkasan Keuangan Keluarga</h2>
                        <p>Pantau kondisi keuangan keluarga Anda.</p>
                    </div>
                </section>

                <!-- Summary Cards -->
                <section class="summary-grid">

                    <div class="summary-card">
                        <div class="summary-icon income">↙</div>
                        <p>Total Pemasukan</p>
                        <h3 id="cardPemasukan">Rp 0</h3>
                        <small>Semua waktu</small>
                    </div>

                    <div class="summary-card">
                        <div class="summary-icon expense">↗</div>
                        <p>Total Pengeluaran</p>
                        <h3 id="cardPengeluaran">Rp 0</h3>
                        <small>Semua waktu</small>
                    </div>

                    <div class="summary-card">
                        <div class="summary-icon balance">▣</div>
                        <p>Total Saldo</p>
                        <h3 id="cardSaldo">Rp 0</h3>
                        <small>Semua waktu</small>
                    </div>

                    <div class="summary-card">
                        <div class="summary-icon saving">◎</div>
                        <p>Total Tabungan</p>
                        <h3 id="cardTabungan">Rp 0</h3>
                        <small>Akumulasi tabungan</small>
                    </div>

                </section>

                <!-- Dashboard Panels -->
                <section class="dashboard-grid">

                    <!-- Finance Chart -->
                    <div class="dashboard-panel">

                        <div class="panel-heading">
                            <h3>Grafik Keuangan</h3>
                            <p>Pemasukan dan pengeluaran 6 bulan terakhir</p>
                        </div>

                        <div style="height: 280px; position: relative;">
                            <canvas id="financeChart"></canvas>
                        </div>

                    </div>

                    <!-- Finance Information -->
                    <div class="dashboard-panel">

                        <div class="panel-heading">
                            <h3>Informasi Keuangan</h3>
                            <p>Ringkasan kondisi keuangan keseluruhan</p>
                        </div>

                        <div class="info-item">
                            <span>Pemasukan</span>
                            <strong id="infoPemasukan">Rp 0</strong>
                        </div>

                        <div class="info-item">
                            <span>Pengeluaran</span>
                            <strong id="infoPengeluaran">Rp 0</strong>
                        </div>

                        <div class="info-item">
                            <span>Setoran Tabungan</span>
                            <strong id="infoSetoran">Rp 0</strong>
                        </div>

                        <div class="info-item">
                            <span>Saldo</span>
                            <strong id="infoSaldo">Rp 0</strong>
                        </div>

                    </div>

                </section>

            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/dashboard.js"></script>

</body>
</html>