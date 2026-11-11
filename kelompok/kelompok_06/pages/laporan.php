<?php
if (isset($_GET['tgl_mulai']) && isset($_GET['tgl_selesai'])) {
    $tgl_mulai = $_GET['tgl_mulai'];
    $tgl_selesai = $_GET['tgl_selesai'];
} else {
    $tgl_mulai = date('Y-m-01');
    $tgl_selesai = date('Y-m-d');
}

$query = "SELECT orders.no_invoice,
                 orders.tanggal_transaksi,
                 orders.nama_pelanggan,
                 users.nama_lengkap AS kasir,
                 payments.metode_bayar,
                 payments.nominal_bayar
          FROM orders
          JOIN users ON orders.user_id = users.id
          JOIN payments ON orders.id = payments.order_id
          WHERE DATE(orders.tanggal_transaksi) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
          ORDER BY orders.tanggal_transaksi DESC";

$laporan = mysqli_query($conn, $query);
$total_pendapatan = 0;
?>

<style>
    :root {
        --color-primary: #10b981;
        --color-surface: #ffffff;
        --color-text: #0f172a;
        --color-text-muted: #64748b;
        --color-border: #e2e8f0;
        --radius: 8px;
    }

    .laporan-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .filter-form {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .input-date {
        padding: 6px 10px;
        border: 1px solid var(--color-border);
        border-radius: 6px;
        font-size: 12px;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        border: none;
    }
    .btn-dark { background-color: #1e293b; color: #ffffff; }
    .btn-success { background-color: var(--color-primary); color: #ffffff; }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
        background-color: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius);
    }

    .custom-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        text-align: left;
    }

    .custom-table th,
    .custom-table td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--color-border);
    }

    .custom-table th {
        background-color: #f8fafc;
        color: var(--color-text-muted);
        font-weight: 600;
    }

    .custom-table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }
    .custom-table tbody tr:hover {
        background-color: #f1f5f9;
    }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .print-only { display: none; }

    @media print {
        .no-print { display: none !important; }
        .print-only { display: block !important; }
        .table-responsive { border: none; }
        .custom-table th, .custom-table td { border-bottom: 1px solid #000; }
    }
</style>

<!-- HEADER & FORM FILTER -->
<div class="laporan-header no-print">
    <div>
        <h2 style="font-size: 18px; font-weight: 700; color: var(--color-text); margin: 0;">Laporan Penjualan</h2>
        <p style="font-size: 12px; color: var(--color-text-muted); margin-top: 4px;">Filter transaksi berdasarkan tanggal</p>
    </div>

    <div style="display: flex; gap: 8px; align-items: center;">
        <form method="GET" action="" class="filter-form">
            <input type="hidden" name="page" value="laporan">
            <input type="date" name="tgl_mulai" value="<?= $tgl_mulai ?>" class="input-date">
            <span style="font-size: 12px; color: var(--color-text-muted);">s/d</span>
            <input type="date" name="tgl_selesai" value="<?= $tgl_selesai ?>" class="input-date">
            <button type="submit" class="btn btn-dark">Filter</button>
        </form>

        <button onclick="window.print()" class="btn btn-success">Cetak Laporan</button>
    </div>
</div>

<!-- JUDUL KHUSUS HASIL CETAK -->
<div class="print-only text-center" style="margin-bottom: 20px;">
    <h1 style="font-size: 20px; font-weight: 700; margin: 0;">LAPORAN PENJUALAN FLASHPOS</h1>
    <p style="font-size: 12px; margin-top: 4px;">Periode: <?= $tgl_mulai ?> s/d <?= $tgl_selesai ?></p>
    <hr style="margin-top: 12px; border: 0.5px solid #000;">
</div>

<!-- TABEL RESPONSIVE -->
<div class="table-responsive">
    <table class="custom-table">
        <thead>
            <tr>
                <th>No Invoice</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Kasir</th>
                <th>Metode Bayar</th>
                <th class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($laporan) > 0) {
                while ($row = mysqli_fetch_assoc($laporan)) {
                    $total_pendapatan += $row['nominal_bayar'];
            ?>
                <tr>
                    <td style="font-weight: 600; color: var(--color-text);"><?= $row['no_invoice'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])) ?></td>
                    <td><?= $row['nama_pelanggan'] ?></td>
                    <td><?= $row['kasir'] ?></td>
                    <td style="text-transform: uppercase;"><?= $row['metode_bayar'] ?></td>
                    <td class="text-right" style="font-weight: 600; color: var(--color-primary);">
                        Rp <?= number_format($row['nominal_bayar'], 0, ',', '.') ?>
                    </td>
                </tr>
            <?php
                }
            } else {
            ?>
                <tr>
                    <td colspan="6" class="text-center" style="color: var(--color-text-muted);">Data transaksi tidak ditemukan.</td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: 700; background-color: #f8fafc;">
                <td colspan="5" class="text-right">TOTAL PENDAPATAN:</td>
                <td class="text-right" style="color: var(--color-primary); font-size: 14px;">
                    Rp <?= number_format($total_pendapatan, 0, ',', '.') ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>