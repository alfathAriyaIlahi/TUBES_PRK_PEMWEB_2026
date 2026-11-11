<?php
// Query Database
$q_omset = mysqli_query($conn, "SELECT SUM(nominal_bayar) AS total FROM payments");
$d_omset = mysqli_fetch_assoc($q_omset);
$total_omset = $d_omset['total'] ?? 0;

$q_transaksi = mysqli_query($conn, "SELECT COUNT(id) AS total FROM orders");
$d_transaksi = mysqli_fetch_assoc($q_transaksi);
$total_transaksi = $d_transaksi['total'] ?? 0;

$q_produk = mysqli_query($conn, "SELECT COUNT(id) AS total FROM products");
$d_produk = mysqli_fetch_assoc($q_produk);
$total_produk = $d_produk['total'] ?? 0;

$rata_rata = ($total_transaksi > 0) ? ($total_omset / $total_transaksi) : 0;

$q_chart = mysqli_query($conn, "SELECT DATE(waktu_bayar) AS tanggal, SUM(nominal_bayar) AS total FROM payments GROUP BY DATE(waktu_bayar) ORDER BY tanggal ASC LIMIT 7");
$labels = [];
$data_grafik = [];
while ($row = mysqli_fetch_assoc($q_chart)) {
    $labels[] = date('d M', strtotime($row['tanggal']));
    $data_grafik[] = (int)$row['total'];
}
?>


<style>
    :root {
        --color-primary: #10b981;
        --color-bg: #f8fafc;
        --color-surface: #ffffff;
        --color-text: #0f172a;
        --color-text-muted: #64748b;
        --color-border: #e2e8f0;
        --radius: 12px;
    }
    * {
        box-sizing: border-box;
    }

    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .card {
        background-color: var(--color-surface);
        padding: 20px;
        border-radius: var(--radius);
        border: 1px solid var(--color-border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    .card-title {
        font-size: 12px;
        font-weight: 500;
        color: var(--color-text-muted);
    }
    .card-value {
        font-size: 22px;
        font-weight: 700;
        color: var(--color-text);
        margin: 0;
    }
    .card-subtext {
        font-size: 11px;
        color: var(--color-primary);
        margin-top: 4px;
    }

    .chart-box {
        background-color: var(--color-surface);
        padding: 24px;
        border-radius: var(--radius);
        border: 1px solid var(--color-border);
        margin-bottom: 24px;
    }
    .chart-container {
        position: relative;
        height: 260px;
        width: 100%;
    }
</style>

<div class="cards-grid">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Total Omset</span>
            <i data-lucide="wallet" style="width: 18px; height: 18px; color: var(--color-primary);"></i>
        </div>
        <p class="card-value">Rp <?= number_format($total_omset, 0, ',', '.') ?></p>
        <p class="card-subtext">Terhubung MySQL</p>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Total Transaksi</span>
            <i data-lucide="receipt" style="width: 18px; height: 18px; color: #2563eb;"></i>
        </div>
        <p class="card-value"><?= $total_transaksi ?> Pesanan</p>
        <p class="card-subtext" style="color: var(--color-text-muted);">Tercatat di database</p>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Produk Aktif</span>
            <i data-lucide="box" style="width: 18px; height: 18px; color: #6366f1;"></i>
        </div>
        <p class="card-value"><?= $total_produk ?> Item</p>
        <p class="card-subtext" style="color: var(--color-text-muted);">Terhubung MySQL</p>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Rata-rata Transaksi</span>
            <i data-lucide="trending-up" style="width: 18px; height: 18px; color: #d97706;"></i>
        </div>
        <p class="card-value">Rp <?= number_format($rata_rata, 0, ',', '.') ?></p>
        <p class="card-subtext" style="color: var(--color-text-muted);">Per pesanan</p>
    </div>
</div>

<!-- GRAFIK ANALITIK -->
<div class="chart-box">
    <h3 style="font-size: 16px; font-weight: 700; color: var(--color-text); margin-top: 0; margin-bottom: 16px;">
        Grafik Penjualan Harian
    </h3>
    <div class="chart-container">
        <canvas id="omsetChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('omsetChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels); ?>,
                datasets: [{
                    label: 'Omset (Rp)',
                    data: <?= json_encode($data_grafik); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
    });
</script>