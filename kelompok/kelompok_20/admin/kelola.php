<?php

require_once '../config/database.php';
require_once '../includes/functions.php';


wajibAdmin();


$admin_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

        if ($aksi === 'update_status') {
        $laporan_id    = (int)($_POST['laporan_id'] ?? 0);
        $status_baru   = trim($_POST['status'] ?? '');
        $isi_tanggapan = trim($_POST['isi_tanggapan'] ?? '');

        $status_valid = ['menunggu', 'diproses', 'selesai'];
        if (!in_array($status_baru, $status_valid)) {
            setFlash('Status laporan tidak valid!', 'danger');
            header('Location: kelola.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE laporan SET status = :status WHERE id = :id");
            $stmt->execute([
                ':status' => $status_baru,
                ':id'     => $laporan_id
            ]);

            if (!empty($isi_tanggapan)) {
                $stmt_tanggapan = $pdo->prepare("INSERT INTO tanggapan (laporan_id, admin_id, isi_tanggapan) 
                                                 VALUES (:laporan_id, :admin_id, :isi_tanggapan)");
                $stmt_tanggapan->execute([
                    ':laporan_id'    => $laporan_id,
                    ':admin_id'      => $admin_id,
                    ':isi_tanggapan' => $isi_tanggapan
                ]);
            }

            setFlash('Status dan tanggapan berhasil diperbarui.', 'success');
        } catch (PDOException $e) {
            setFlash('Terjadi kesalahan database saat memperbarui laporan.', 'danger');
        }

        header('Location: kelola.php');
        exit;
    }


    if ($aksi === 'hapus_laporan') {
        $laporan_id = (int)($_POST['laporan_id'] ?? 0);

        try {
            $stmt_foto = $pdo->prepare("SELECT foto FROM laporan WHERE id = :id");
            $stmt_foto->execute([':id' => $laporan_id]);
            $foto = $stmt_foto->fetchColumn();

            if (!empty($foto)) {
                $path_foto = '../assets/uploads/' . $foto;
                if (file_exists($path_foto)) {
                    unlink($path_foto); 
                }
            }

            $stmt_hapus = $pdo->prepare("DELETE FROM laporan WHERE id = :id");
            $stmt_hapus->execute([':id' => $laporan_id]);

            setFlash('Laporan berhasil dihapus.', 'success');
        } catch (PDOException $e) {
            setFlash('Gagal menghapus laporan.', 'danger');
        }

        header('Location: kelola.php');
        exit;
    }
}


$filter_status = $_GET['status'] ?? 'semua';
$filter_jenis  = $_GET['jenis'] ?? 'semua';


$sql = "SELECT laporan.*, users.nama AS nama_pelapor, kategori.nama_kategori 
        FROM laporan 
        JOIN users ON laporan.user_id = users.id 
        LEFT JOIN kategori ON laporan.kategori_id = kategori.id 
        WHERE 1=1";

$params = [];

if ($filter_status !== 'semua' && in_array($filter_status, ['menunggu', 'diproses', 'selesai'])) {
    $sql .= " AND laporan.status = :status";
    $params[':status'] = $filter_status;
}

if ($filter_jenis !== 'semua' && in_array($filter_jenis, ['pengaduan', 'aspirasi'])) {
    $sql .= " AND laporan.jenis = :jenis";
    $params[':jenis'] = $filter_jenis;
}

$sql .= " ORDER BY laporan.created_at DESC";

$stmt_laporan = $pdo->prepare($sql);
$stmt_laporan->execute($params);
$daftar_laporan = $stmt_laporan->fetchAll(PDO::FETCH_ASSOC);


$title = "Kelola Laporan - Aspiraku";
include '../includes/header.php';
?>

<main class="container" style="padding: 30px 20px;">

  
    <?php if (function_exists('getFlash')): ?>
        <?= getFlash() ?>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 25px; gap: 15px;">
        <div>
            <h1 style="color: #1E3B2F; font-size: 26px; margin-bottom: 6px;">Kelola Semua Laporan</h1>
            <p style="color: #5F6B4E; font-size: 15px;">Tinjau pengaduan & aspirasi, ubah status penanganan, dan berikan tanggapan resmi.</p>
        </div>
        <a href="dashboard.php" class="btn" style="background: transparent; color: #1E3B2F; border: 1px solid #7F8C6A; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px;">
            &larr; Kembali ke Dashboard
        </a>
    </div>


    <div class="card" style="background: #FFFFFF; border: 1px solid #DDD5C0; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <form method="GET" action="kelola.php" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <div>
                <label for="filter-status" style="display: block; font-size: 13px; font-weight: bold; color: #1E3B2F; margin-bottom: 5px;">Filter Status:</label>
                <select id="filter-status" name="status" style="padding: 8px 12px; border: 1px solid #DDD5C0; border-radius: 6px; background: #FFFFFF; color: #1E3B2F;">
                    <option value="semua" <?= $filter_status === 'semua' ? 'selected' : '' ?>>Semua Status</option>
                    <option value="menunggu" <?= $filter_status === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                    <option value="diproses" <?= $filter_status === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                    <option value="selesai" <?= $filter_status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>

            <div>
                <label for="filter-jenis" style="display: block; font-size: 13px; font-weight: bold; color: #1E3B2F; margin-bottom: 5px;">Filter Jenis:</label>
                <select id="filter-jenis" name="jenis" style="padding: 8px 12px; border: 1px solid #DDD5C0; border-radius: 6px; background: #FFFFFF; color: #1E3B2F;">
                    <option value="semua" <?= $filter_jenis === 'semua' ? 'selected' : '' ?>>Semua Jenis</option>
                    <option value="pengaduan" <?= $filter_jenis === 'pengaduan' ? 'selected' : '' ?>>Pengaduan</option>
                    <option value="aspirasi" <?= $filter_jenis === 'aspirasi' ? 'selected' : '' ?>>Aspirasi</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn" style="background: #1E3B2F; color: #F5F0E6; border: none; padding: 8px 18px; border-radius: 6px; cursor: pointer; font-size: 14px;">
                    Terapkan Filter
                </button>
                <a href="kelola.php" class="btn" style="background: #EEE5D0; color: #1E3B2F; text-decoration: none; padding: 8px 14px; border-radius: 6px; font-size: 14px;">
                    Reset
                </a>
            </div>
        </form>
    </div>


    <div class="table-responsive" style="background: #FFFFFF; border: 1px solid #DDD5C0; border-radius: 8px; padding: 15px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid #DDD5C0; color: #1E3B2F;">
                    <th style="padding: 10px 8px;">No</th>
                    <th style="padding: 10px 8px;">Pelapor</th>
                    <th style="padding: 10px 8px;">Jenis & Kategori</th>
                    <th style="padding: 10px 8px;">Judul Laporan</th>
                    <th style="padding: 10px 8px;">Status</th>
                    <th style="padding: 10px 8px;">Tanggal</th>
                    <th style="padding: 10px 8px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($daftar_laporan)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: #5F6B4E;">
                            Tidak ada laporan yang sesuai dengan filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftar_laporan as $idx => $row): ?>
                        <tr style="border-bottom: 1px solid #EEE5D0;">
                            <td style="padding: 12px 8px; vertical-align: top;"><?= $idx + 1 ?></td>
                            <td style="padding: 12px 8px; vertical-align: top; font-weight: 500; color: #1E3B2F;">
                                <?= sanitize($row['nama_pelapor']) ?>
                            </td>
                            <td style="padding: 12px 8px; vertical-align: top;">
                                <span style="display: block; font-weight: 500;"><?= ucfirst(sanitize($row['jenis'])) ?></span>
                                <span style="font-size: 12px; color: #5F6B4E;"><?= sanitize($row['nama_kategori'] ?? '-') ?></span>
                            </td>
                            <td style="padding: 12px 8px; vertical-align: top;">
                                <strong><?= sanitize($row['judul']) ?></strong>
                                <p style="margin: 4px 0 0; font-size: 13px; color: #5F6B4E;">
                                    <?= nl2br(sanitize(mb_strimwidth($row['isi'], 0, 80, '...'))) ?>
                                </p>
                                <?php if (!empty($row['foto'])): ?>
                                    <span style="font-size: 11px; color: #7F8C6A;">📷 Ada lampiran foto</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 8px; vertical-align: top;">
                                <?php if (function_exists('badgeStatus')): ?>
                                    <?= badgeStatus($row['status']) ?>
                                <?php else: ?>
                                    <span class="badge badge-<?= sanitize($row['status']) ?>" style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background: #DCE2D0; color: #1E3B2F;">
                                        <?= ucfirst(sanitize($row['status'])) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 8px; vertical-align: top; font-size: 13px; color: #5F6B4E; white-space: nowrap;">
                                <?= date('d M Y', strtotime($row['created_at'])) ?>
                            </td>
                            <td style="padding: 12px 8px; vertical-align: top; text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center; flex-direction: column;">
                                    
                        
                                    <button type="button" onclick="document.getElementById('form-kelola-<?= $row['id'] ?>').style.display = document.getElementById('form-kelola-<?= $row['id'] ?>').style.display === 'none' ? 'table-row' : 'none'" 
                                            class="btn" style="background: #1E3B2F; color: #F5F0E6; border: none; padding: 6px 10px; border-radius: 4px; font-size: 12px; cursor: pointer;">
                                        Tindak Lanjuti
                                    </button>

                                    <form method="POST" action="kelola.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan ini? Tindakan ini tidak dapat dibatalkan.');" style="display: inline;">
                                        <input type="hidden" name="aksi" value="hapus_laporan">
                                        <input type="hidden" name="laporan_id" value="<?= (int)$row['id'] ?>">
                                        <button type="submit" style="background: transparent; color: #8B2E2E; border: 1px solid #F7E0DC; padding: 5px 10px; border-radius: 4px; font-size: 12px; cursor: pointer; width: 100%;">
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                        <tr id="form-kelola-<?= $row['id'] ?>" style="display: none; background: #FAF7F0;">
                            <td colspan="7" style="padding: 20px; border-bottom: 2px solid #DDD5C0;">
                                <div style="background: #FFFFFF; border: 1px solid #DDD5C0; border-radius: 6px; padding: 16px;">
                                    <h4 style="color: #1E3B2F; margin-bottom: 10px; font-size: 15px;">
                                        Tindak Lanjuti Laporan #<?= (int)$row['id'] ?>: <?= sanitize($row['judul']) ?>
                                    </h4>

                                    <form method="POST" action="kelola.php">
                                        <input type="hidden" name="aksi" value="update_status">
                                        <input type="hidden" name="laporan_id" value="<?= (int)$row['id'] ?>">

                                        <div style="margin-bottom: 12px;">
                                            <label style="display: block; font-size: 13px; font-weight: bold; color: #1E3B2F; margin-bottom: 4px;">Ubah Status:</label>
                                            <select name="status" style="padding: 8px 12px; border: 1px solid #DDD5C0; border-radius: 4px; width: 220px; background: #FFFFFF;">
                                                <option value="menunggu" <?= $row['status'] === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                                <option value="diproses" <?= $row['status'] === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                                <option value="selesai" <?= $row['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                            </select>
                                        </div>

                                        <div style="margin-bottom: 12px;">
                                            <label style="display: block; font-size: 13px; font-weight: bold; color: #1E3B2F; margin-bottom: 4px;">
                                                Tulis Tanggapan Resmi (Opsional):
                                            </label>
                                            <textarea name="isi_tanggapan" rows="3" placeholder="Tuliskan respon resmi atau tindak lanjut dari pihak kampus..." style="width: 100%; max-width: 650px; padding: 10px; border: 1px solid #DDD5C0; border-radius: 4px; font-family: inherit; font-size: 13px; box-sizing: border-box;"></textarea>
                                        </div>

                                        <div style="display: flex; gap: 8px;">
                                            <button type="submit" class="btn" style="background: #1E3B2F; color: #F5F0E6; border: none; padding: 8px 16px; border-radius: 4px; font-size: 13px; cursor: pointer;">
                                                Simpan Perubahan
                                            </button>
                                            <button type="button" onclick="document.getElementById('form-kelola-<?= $row['id'] ?>').style.display='none'" style="background: transparent; border: 1px solid #DDD5C0; color: #5F6B4E; padding: 8px 14px; border-radius: 4px; font-size: 13px; cursor: pointer;">
                                                Batal
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<?php include '../includes/footer.php'; ?>