<?php
require_once '../auth.php';
require_once '../../config/koneksi.php';

// Ambil data kategori
$result = $conn->query("SELECT * FROM kategori ORDER BY id_kategori DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Kategori - KlikObat</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 20px;">
    <h2>Manajemen Kategori Obat</h2>
    <a href="tambah.php">+ Tambah Kategori Baru</a> | 
    <a href="../logout.php" style="color: red;">Logout</a>
    <hr>
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-top: 15px;">
        <thead>
            <tr style="background: #f4f4f4;">
                <th>No</th>
                <th>Nama Kategori</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['nama_kategori']); ?></td>
                <td>
                    <a href="ubah.php?id=<?= $row['id_kategori']; ?>">Ubah</a> | 
                    <a href="hapus.php?id=<?= $row['id_kategori']; ?>" onclick="return confirm('Yakin ingin menghapus kategori ini?');">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>