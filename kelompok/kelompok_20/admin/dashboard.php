<?php
// admin/dashboard.php
require_once '../config/database.php';
require_once '../includes/functions.php';


wajibAdmin();


$q_total = $pdo->query("SELECT COUNT(*) FROM laporan");
$total_laporan = $q_total->fetchColumn();

$q_menunggu = $pdo->query("SELECT COUNT(*) FROM laporan WHERE status = 'menunggu'");
$total_menunggu = $q_menunggu->fetchColumn();

$q_diproses = $pdo->query("SELECT COUNT(*) FROM laporan WHERE status = 'diproses'");
$total_diproses = $q_diproses->fetchColumn();

$q_selesai = $pdo->query("SELECT COUNT(*) FROM laporan WHERE status = 'selesai'");
$total_selesai = $q_selesai->fetchColumn();


$q_terbaru = $pdo->query("SELECT laporan.*, users.nama AS nama_pelapor 
                          FROM laporan 
                          JOIN users ON laporan.user_id = users.id 
                          ORDER BY laporan.created_at DESC 
                          LIMIT 5");
$list_terbaru = $q_terbaru->fetchAll(PDO::FETCH_ASSOC);

// Judul halaman untuk header
$title = "Dashboard Admin - Aspiraku";
include '../includes/header.php';
?>

<main class="container" style="padding: 30px 20px;">
    

    <?php if (function_exists('getFlash')): ?>
        <?= getFlash() ?>
    <?php endif; ?>

    <div style="margin-bottom: 25px;">
        <h1 style="color: #1E3B2F; font-size: 26px; margin-bottom: 6px;">Dashboard Admin</h1>
        <p style="color: #5F6B4E; font-size: 15px;">
            Selamat datang, <strong><?= sanitize($_SESSION['nama'] ?? 'Admin') ?></strong>! Berikut adalah ringkasan aspirasi & pengaduan kampus saat ini.
        </p>
    </div>


    <div style="margin-bottom: 30px; display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="kelola.php" class="btn" style="background: #1E3B2F; color: #F5F0E6; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
            Kelola Semua Laporan &rarr;
        </a>
        <a href="kategori.php" class="btn btn-secondary" style="background: transparent; color: #1E3B2F; border: 1px solid #7F8C6A; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
            Kelola Kategori
        </a>
    </div>


    <h3 style="color: #1E3B2F; margin-bottom: 15px; font-size: 18px;">Statistik Laporan</h3>
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 35px;">
        
        <div class="card" style="background: #FFFFFF; border: 1px solid #DDD5C0; border-radius: 8px; padding: 20px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: #1E3B2F; margin-bottom: 4px;">
                <?= (int)$total_laporan ?>
            </div>
            <div style="font-size: 12px; font-weight: bold; color: #5F6B4E; letter-spacing: 0.5px;">
                TOTAL LAPORAN
            </div>
        </div>

        <div class="card" style="background: #FFFFFF; border: 1px solid #DDD5C0; border-radius: 8px; padding: 20px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: #6B5420; margin-bottom: 4px;">
                <?= (int)$total_menunggu ?>
            </div>
            <div style="font-size: 12px; font-weight: bold; color: #5F6B4E; letter-spacing: 0.5px;">
                MENUNGGU
            </div>
        </div>

        <div class="card" style="background: #FFFFFF; border: 1px solid #DDD5C0; border-radius: 8px; padding: 20px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: #34402A; margin-bottom: 4px;">
                <?= (int)$total_diproses ?>
            </div>
            <div style="font-size: 12px; font-weight: bold; color: #5F6B4E; letter-spacing: 0.5px;">
                DIPROSES
            </div>
        </div>

        <div class="card" style="background: #FFFFFF; border: 1px solid #DDD5C0; border-radius: 8px; padding: 20px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold; color: #1E3B2F; margin-bottom: 4px;">
                <?= (int)$total_selesai ?>
            </div>
            <div style="font-size: 12px; font-weight: bold; color: #5F6B4E; letter-spacing: 0.5px;">
                SELESAI
            </div>
        </div>

    </div>


    <h3 style="color: #1E3B2F; margin-bottom: 15px; font-size: 18px;">5 Laporan Terbaru</h3>
    <div class="table-responsive" style="background: #FFFFFF; border: 1px solid #DDD5C0; border-radius: 8px; padding: 15px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid #DDD5C0; color: #1E3B2F;">
                    <th style="padding: 10px 8px;">No</th>
                    <th style="padding: 10px 8px;">Pelapor</th>
                    <th style="padding: 10px 8px;">Judul Laporan</th>
                    <th style="padding: 10px 8px;">Jenis</th>
                    <th style="padding: 10px 8px;">Status</th>
                    <th style="padding: 10px 8px;">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($list_terbaru)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #5F6B4E;">Belum ada laporan yang masuk.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($list_terbaru as $i => $row): ?>
                        <tr style="border-bottom: 1px solid #EEE5D0;">
                            <td style="padding: 10px 8px;"><?= $i + 1 ?></td>
                            <td style="padding: 10px 8px; font-weight: 500; color: #1E3B2F;">
                                <?= sanitize($row['nama_pelapor']) ?>
                            </td>
                            <td style="padding: 10px 8px;">
                                <a href="../detail.php?id=<?= (int)$row['id'] ?>" style="color: #1E3B2F; text-decoration: underline;">
                                    <?= sanitize($row['judul']) ?>
                                </a>
                            </td>
                            <td style="padding: 10px 8px;">
                                <?= ucfirst(sanitize($row['jenis'])) ?>
                            </td>
                            <td style="padding: 10px 8px;">
                                <?php if (function_exists('badgeStatus')): ?>
                                    <?= badgeStatus($row['status']) ?>
                                <?php else: ?>
                                    <span class="badge badge-<?= sanitize($row['status']) ?>" style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background: #DCE2D0; color: #1E3B2F;">
                                        <?= ucfirst(sanitize($row['status'])) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px 8px; color: #5F6B4E; white-space: nowrap;">
                                <?= date('d M Y H:i', strtotime($row['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<?php include '../includes/footer.php'; ?>