
<?php

session_start();

require_once __DIR__ . '/../../config/database.php';

// Memastikan pengguna sudah login
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu.';
    header('Location: ../../login.php');
    exit;
}

$idKeluarga = $_SESSION['user']['id_keluarga'];
$idPengguna = $_SESSION['user']['id_pengguna'];

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

try {

    // Menampilkan daftar pengeluaran
    if ($aksi === 'daftar') {
        $stmt = $pdo->prepare('CALL sp_pengeluaran_daftar(?)');
        $stmt->execute([$idKeluarga]);

        $data = $stmt->fetchAll();
        $stmt->closeCursor();

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Memastikan aksi lainnya menggunakan POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../../pages/pengeluaran.php');
        exit;
    }

    // Menambahkan pengeluaran
    if ($aksi === 'tambah') {

        $idKategori = (int) ($_POST['id_kategori'] ?? 0);
        $jumlah = (float) ($_POST['jumlah'] ?? 0);
        $tanggal = $_POST['tanggal'] ?? '';
        $keterangan = trim($_POST['keterangan'] ?? '');

        if ($idKategori <= 0 || $jumlah <= 0 || $tanggal === '') {
            throw new Exception('Data pengeluaran belum lengkap atau tidak valid.');
        }

        $stmt = $pdo->prepare(
            'CALL sp_pengeluaran_tambah(?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $idKeluarga,
            $idPengguna,
            $idKategori,
            $jumlah,
            $tanggal,
            $keterangan
        ]);

        $stmt->closeCursor();

        $_SESSION['success'] = 'Pengeluaran berhasil ditambahkan.';
    }

    // Mengubah pengeluaran
    elseif ($aksi === 'ubah') {

        $id = (int) ($_POST['id'] ?? 0);
        $idKategori = (int) ($_POST['id_kategori'] ?? 0);
        $jumlah = (float) ($_POST['jumlah'] ?? 0);
        $tanggal = $_POST['tanggal'] ?? '';
        $keterangan = trim($_POST['keterangan'] ?? '');

        if ($id <= 0 || $idKategori <= 0 || $jumlah <= 0 || $tanggal === '') {
            throw new Exception('Data perubahan pengeluaran tidak valid.');
        }

        $stmt = $pdo->prepare(
            'CALL sp_pengeluaran_ubah(?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $id,
            $idKeluarga,
            $idKategori,
            $jumlah,
            $tanggal,
            $keterangan
        ]);

        $stmt->closeCursor();

        $_SESSION['success'] = 'Pengeluaran berhasil diperbarui.';
    }

    // Menghapus pengeluaran
    elseif ($aksi === 'hapus') {

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            throw new Exception('ID pengeluaran tidak valid.');
        }

        $stmt = $pdo->prepare(
            'CALL sp_pengeluaran_hapus(?, ?)'
        );

        $stmt->execute([
            $id,
            $idKeluarga
        ]);

        $stmt->closeCursor();

        $_SESSION['success'] = 'Pengeluaran berhasil dihapus.';
    }

    else {
        throw new Exception('Aksi tidak dikenali.');
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../../pages/pengeluaran.php');
exit;

