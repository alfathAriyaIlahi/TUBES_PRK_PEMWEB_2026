<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

wajibLogin();

$userId = $_SESSION['user_id'];

// Mengambil filter dari URL
$filterJenis  = trim($_GET['jenis'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');

// Mengambil data laporan sesuai filter
try {
    $sql = "
        SELECT 
            l.id,
            l.jenis,
            l.judul,
            l.lokasi,
            l.foto,
            l.is_public,
            l.status,
            l.created_at,
            k.nama_kategori,
            (
                SELECT isi_tanggapan 
                FROM tanggapan t 
                WHERE t.laporan_id = l.id 
                ORDER BY t.created_at DESC 
                LIMIT 1
            ) AS tanggapan_terakhir
        FROM laporan l
        LEFT JOIN kategori k ON l.kategori_id = k.id
        WHERE l.user_id = :user_id
    ";

    $params = [':user_id' => $userId];

    // Filter berdasarkan Jenis jika dipilih
    if (!empty($filterJenis) && in_array($filterJenis, ['pengaduan', 'aspirasi'], true)) {
        $sql .= " AND l.jenis = :jenis";
        $params[':jenis'] = $filterJenis;
    }

    // Filter berdasarkan Status jika dipilih
    if (!empty($filterStatus) && in_array($filterStatus, ['menunggu', 'diproses', 'selesai'], true)) {
        $sql .= " AND l.status = :status";
        $params[':status'] = $filterStatus;
    }

    $sql .= " ORDER BY l.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $daftarLaporan = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Error mengambil laporan saya: " . $e->getMessage());
    $daftarLaporan = [];
}

$isFiltered = (!empty($filterJenis) || !empty($filterStatus));
$pageTitle  = 'Laporan Saya';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.85rem;">
        <div>
            <h1 class="card-title" style="margin-bottom: 0.25rem;">Daftar Laporan Saya</h1>
            <p style="color: var(--color-text-light); font-size: 0.95rem;">
                Pantau perkembangan tindak lanjut pengaduan dan aspirasi yang telah Anda sampaikan.
            </p>
        </div>
        <div>
            <a href="/user/kirim.php" class="btn btn-primary">+ Buat Laporan Baru</a>
        </div>
    </div>

    <!-- Formulir Filter Jenis & Status -->
    <form method="GET" action="/user/laporan_saya.php" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap; margin-bottom: 1.5rem; padding: 1rem; background-color: var(--color-bg); border-radius: var(--radius); border: 1px solid var(--color-border);">
        <div style="flex: 1; min-width: 170px;">
            <label for="filter-jenis" class="form-label" style="font-size: 0.88rem; margin-bottom: 0.3rem;">Jenis Laporan:</label>
            <select id="filter-jenis" name="jenis" class="form-control" style="padding: 0.5rem 0.75rem;">
                <option value="">-- Semua Jenis --</option>
                <option value="pengaduan" <?= ($filterJenis === 'pengaduan') ? 'selected' : '' ?>>Pengaduan</option>
                <option value="aspirasi" <?= ($filterJenis === 'aspirasi') ? 'selected' : '' ?>>Aspirasi</option>
            </select>
        </div>

        <div style="flex: 1; min-width: 170px;">
            <label for="filter-status" class="form-label" style="font-size: 0.88rem; margin-bottom: 0.3rem;">Status Laporan:</label>
            <select id="filter-status" name="status" class="form-control" style="padding: 0.5rem 0.75rem;">
                <option value="">-- Semua Status --</option>
                <option value="menunggu" <?= ($filterStatus === 'menunggu') ? 'selected' : '' ?>>Menunggu</option>
                <option value="diproses" <?= ($filterStatus === 'diproses') ? 'selected' : '' ?>>Diproses</option>
                <option value="selesai" <?= ($filterStatus === 'selesai') ? 'selected' : '' ?>>Selesai</option>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Filter</button>
            <?php if ($isFiltered): ?>
                <a href="/user/laporan_saya.php" class="btn btn-secondary" style="padding: 0.5rem 1rem;" title="Kembali ke semua laporan">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Tabel Data Laporan -->
    <?php if (empty($daftarLaporan)): ?>
        <div style="text-align: center; padding: 3rem 1rem;">
            <?php if ($isFiltered): ?>
                <p style="font-size: 1.05rem; color: var(--color-text-light); margin-bottom: 1rem;">
                    Tidak ada laporan yang sesuai dengan filter yang dipilih.
                </p>
                <a href="/user/laporan_saya.php" class="btn btn-secondary">Reset Filter</a>
            <?php else: ?>
                <p style="font-size: 1.1rem; color: var(--color-text-light); margin-bottom: 1.25rem;">
                    Anda belum pernah mengirimkan pengaduan ataupun aspirasi.
                </p>
                <a href="/user/kirim.php" class="btn btn-primary">Sampaikan Laporan Sekarang</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Judul & Lokasi</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Tanggapan Terakhir</th>
                        <th style="text-align: center; width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarLaporan as $index => $lap): ?>
                        <tr>
                            <td style="text-align: center;"><?= $index + 1 ?></td>
                            <td style="white-space: nowrap; font-size: 0.88rem;">
                                <?= date('d M Y, H:i', strtotime($lap['created_at'])) ?>
                            </td>
                            <td>
                                <span style="font-weight: 600; text-transform: capitalize;">
                                    <?= sanitize($lap['jenis']) ?>
                                </span>
                                <?php if (!$lap['is_public']): ?>
                                    <div style="font-size: 0.75rem; color: var(--color-text-light); font-style: italic;">(Privat)</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= sanitize($lap['judul']) ?></strong>
                                <div style="font-size: 0.85rem; color: var(--color-text-light); margin-top: 0.2rem;">
                                    📍 <?= sanitize($lap['lokasi']) ?>
                                </div>
                            </td>
                            <td>
                                <?= sanitize($lap['nama_kategori'] ?? '-') ?>
                            </td>
                            <td>
                                <?= badgeStatus($lap['status']) ?>
                            </td>
                            <td>
                                <?php if (!empty($lap['tanggapan_terakhir'])): ?>
                                    <span style="font-size: 0.9rem; color: var(--color-text);">
                                        "<?= sanitize(mb_strimwidth($lap['tanggapan_terakhir'], 0, 80, '...')) ?>"
                                    </span>
                                <?php else: ?>
                                    <span style="font-size: 0.85rem; color: var(--color-text-light); font-style: italic;">
                                        Belum ada tanggapan
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 0.4rem; justify-content: center; flex-wrap: wrap;">
                                    <a href="/detail.php?id=<?= (int)$lap['id'] ?>" class="btn btn-sm btn-secondary" title="Lihat detail">
                                        Lihat
                                    </a>

                                    <?php if ($lap['status'] === 'menunggu'): ?>
                                        <a href="/user/edit.php?id=<?= (int)$lap['id'] ?>" class="btn btn-sm btn-primary" title="Ubah laporan">
                                            Edit
                                        </a>

                                        <form action="/user/hapus.php" method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan ini? Tindakan ini tidak dapat dibatalkan.');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="id" value="<?= (int)$lap['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus laporan">
                                                Hapus
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
