<?php

require_once '../config/database.php';
require_once '../includes/functions.php';

wajibAdmin();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $nama_kategori = trim($_POST['nama_kategori'] ?? '');

        if (empty($nama_kategori)) {
            setFlash('Nama kategori tidak boleh kosong!', 'danger');
            header('Location: kategori.php');
            exit;
        }

        try {
            $stmt_cek = $pdo->prepare("SELECT COUNT(*) FROM kategori WHERE LOWER(nama_kategori) = LOWER(:nama)");
            $stmt_cek->execute([':nama' => $nama_kategori]);
            
            if ($stmt_cek->fetchColumn() > 0) {
                setFlash('Kategori dengan nama tersebut sudah ada!', 'danger');
            } else {

                $stmt_insert = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (:nama)");
                $stmt_insert->execute([':nama' => $nama_kategori]);
                setFlash('Kategori baru berhasil ditambahkan.', 'success');
            }
        } catch (PDOException $e) {
            setFlash('Gagal menambahkan kategori ke database.', 'danger');
        }

        header('Location: kategori.php');
        exit;
    }


    if ($aksi === 'hapus') {
        $kategori_id = (int)($_POST['kategori_id'] ?? 0);

        try {
            $stmt_hapus = $pdo->prepare("DELETE FROM kategori WHERE id = :id");
            $stmt_hapus->execute([':id' => $kategori_id]);

            setFlash('Kategori berhasil dihapus.', 'success');
        } catch (PDOException $e) {
            setFlash('Gagal menghapus kategori.', 'danger');
        }

        header('Location: kategori.php');
        exit;
    }
}


$query = "SELECT kategori.*, COUNT(laporan.id) AS total_laporan 
          FROM kategori 
          LEFT JOIN laporan ON kategori.id = laporan.kategori_id 
          GROUP BY kategori.id 
          ORDER BY kategori.nama_kategori ASC";
$stmt = $pdo->query($query);
$daftar_kategori = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Kelola Kategori - Aspiraku";
include '../includes/header.php';
?>

<main class="container" style="padding: 30px 20px;">

  
    <?php if (function_exists('getFlash')): ?>
        <?= getFlash() ?>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 25px; gap: 15px;">
        <div>
            <h1 style="color: #1E3B2F; font-size: 26px; margin-bottom: 6px;">Kelola Kategori Laporan</h1>
            <p style="color: #5F6B4E; font-size: 15px;">Atur kategori pengaduan dan aspirasi kampus (misal: Fasilitas, Akademik, Kebersihan).</p>
        </div>
        <a href="dashboard.php" class="btn" style="background: transparent; color: #1E3B2F; border: 1px solid #7F8C6A; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px;">
            &larr; Kembali ke Dashboard
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; align-items: start;">
        
       
        <div class="card" style="background: #FFFFFF; border: 1px solid #DDD5C0; border-radius: 8px; padding: 20px;">
            <h3 style="color: #1E3B2F; font-size: 18px; margin-bottom: 15px;">Tambah Kategori Baru</h3>
            
            <form method="POST" action="kategori.php">
                <input type="hidden" name="aksi" value="tambah">

                <div style="margin-bottom: 15px;">
                    <label for="nama_kategori" style="display: block; font-size: 13px; font-weight: bold; color: #1E3B2F; margin-bottom: 6px;">
                        Nama Kategori:
                    </label>
                    <input type="text" id="nama_kategori" name="nama_kategori" placeholder="Contoh: Perpustakaan" required
                           style="width: 100%; padding: 10px 12px; border: 1px solid #DDD5C0; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
                    <small style="color: #5F6B4E; font-size: 12px; margin-top: 4px; display: block;">
                        Pastikan nama kategori belum pernah dibuat sebelumnya.
                    </small>
                </div>

                <button type="submit" class="btn" style="background: #1E3B2F; color: #F5F0E6; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; width: 100%;">
                    + Tambah Kategori
                </button>
            </form>
        </div>

        
        <div class="table-responsive" style="background: #FFFFFF; border: 1px solid #DDD5C0; border-radius: 8px; padding: 20px; overflow-x: auto;">
            <h3 style="color: #1E3B2F; font-size: 18px; margin-bottom: 15px;">Daftar Kategori Tersedia</h3>
            
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                <thead>
                    <tr style="border-bottom: 2px solid #DDD5C0; color: #1E3B2F;">
                        <th style="padding: 10px 8px;">No</th>
                        <th style="padding: 10px 8px;">Nama Kategori</th>
                        <th style="padding: 10px 8px; text-align: center;">Jumlah Laporan</th>
                        <th style="padding: 10px 8px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_kategori)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px; color: #5F6B4E;">Belum ada kategori yang dibuat.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_kategori as $idx => $kat): ?>
                            <tr style="border-bottom: 1px solid #EEE5D0;">
                                <td style="padding: 12px 8px;"><?= $idx + 1 ?></td>
                                <td style="padding: 12px 8px; font-weight: 500; color: #1E3B2F;">
                                    <?= sanitize($kat['nama_kategori']) ?>
                                </td>
                                <td style="padding: 12px 8px; text-align: center;">
                                    <span style="background: #FAF7F0; border: 1px solid #DDD5C0; padding: 3px 8px; border-radius: 12px; font-size: 12px; color: #5F6B4E;">
                                        <?= (int)$kat['total_laporan'] ?> laporan
                                    </span>
                                </td>
                                <td style="padding: 12px 8px; text-align: center;">
                                    <form method="POST" action="kategori.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Laporan lama yang memakai kategori ini tidak akan terhapus.');" style="display: inline;">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="kategori_id" value="<?= (int)$kat['id'] ?>">
                                        <button type="submit" style="background: transparent; color: #8B2E2E; border: 1px solid #F7E0DC; padding: 4px 10px; border-radius: 4px; font-size: 12px; cursor: pointer;">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</main>

<?php include '../includes/footer.php'; ?>