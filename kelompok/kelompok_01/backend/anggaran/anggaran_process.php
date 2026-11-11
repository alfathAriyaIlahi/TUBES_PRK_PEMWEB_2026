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
$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

try {

    // Menampilkan daftar anggaran
    if ($aksi === 'daftar') {

        $bulan = (int) ($_GET['bulan'] ?? date('n'));
        $tahun = (int) ($_GET['tahun'] ?? date('Y'));

        if ($bulan < 1 || $bulan > 12 || $tahun < 2000) {
            throw new Exception('Bulan atau tahun tidak valid.');
        }

        $stmt = $pdo->prepare(
            'CALL sp_anggaran_daftar(?, ?, ?)'
        );

        $stmt->execute([
            $idKeluarga,
            $bulan,
            $tahun
        ]);

        $data = $stmt->fetchAll();
        $stmt->closeCursor();

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Aksi selain daftar wajib menggunakan POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metode permintaan tidak valid.');
    }

    // Menyimpan atau memperbarui anggaran
    if ($aksi === 'simpan') {

        $idKategori = (int) ($_POST['id_kategori'] ?? 0);
        $bulan = (int) ($_POST['bulan'] ?? 0);
        $tahun = (int) ($_POST['tahun'] ?? 0);
        $jumlah = (float) ($_POST['jumlah'] ?? 0);

        if (
            $idKategori <= 0 ||
            $bulan < 1 || $bulan > 12 ||
            $tahun < 2000 ||
            $jumlah <= 0
        ) {
            throw new Exception('Data anggaran tidak valid.');
        }

        $stmt = $pdo->prepare(
            'CALL sp_anggaran_simpan(?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $idKeluarga,
            $idKategori,
            $bulan,
            $tahun,
            $jumlah
        ]);

        $stmt->closeCursor();

        $_SESSION['success'] = 'Anggaran berhasil disimpan.';
    }

    // Menghapus anggaran
    elseif ($aksi === 'hapus') {

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            throw new Exception('ID anggaran tidak valid.');
        }

        $stmt = $pdo->prepare(
            'CALL sp_anggaran_hapus(?, ?)'
        );

        $stmt->execute([
            $id,
            $idKeluarga
        ]);

        $stmt->closeCursor();

        $_SESSION['success'] = 'Anggaran berhasil dihapus.';
    }

    else {
        throw new Exception('Aksi tidak dikenali.');
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../../pages/anggaran.php');
exit;