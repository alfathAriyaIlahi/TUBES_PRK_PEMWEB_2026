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

    // Menampilkan daftar target tabungan
    if ($aksi === 'daftar') {

        $stmt = $pdo->prepare('CALL sp_target_daftar(?)');
        $stmt->execute([$idKeluarga]);

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

    // Menambahkan target tabungan
    if ($aksi === 'tambah') {

        $nama = trim($_POST['nama'] ?? '');
        $nominal = (float) ($_POST['nominal'] ?? 0);
        $mulai = $_POST['mulai'] ?? '';
        $target = $_POST['target'] ?? '';
        $catatan = trim($_POST['catatan'] ?? '');

        if ($nama === '' || $nominal <= 0 || $mulai === '' || $target === '') {
            throw new Exception('Data target tabungan wajib diisi dengan benar.');
        }

        if ($target < $mulai) {
            throw new Exception('Tanggal target tidak boleh sebelum tanggal mulai.');
        }

        $stmt = $pdo->prepare(
            'CALL sp_target_tambah(?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $idKeluarga,
            $nama,
            $nominal,
            $mulai,
            $target,
            $catatan
        ]);

        $stmt->closeCursor();

        $_SESSION['success'] = 'Target tabungan berhasil ditambahkan.';
    }

    // Mengubah target tabungan
    elseif ($aksi === 'ubah') {

        $id = (int) ($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $nominal = (float) ($_POST['nominal'] ?? 0);
        $target = $_POST['target'] ?? '';
        $catatan = trim($_POST['catatan'] ?? '');

        if ($id <= 0 || $nama === '' || $nominal <= 0 || $target === '') {
            throw new Exception('Data perubahan target tidak valid.');
        }

        $stmt = $pdo->prepare(
            'CALL sp_target_ubah(?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $id,
            $idKeluarga,
            $nama,
            $nominal,
            $target,
            $catatan
        ]);

        $stmt->closeCursor();

        $_SESSION['success'] = 'Target tabungan berhasil diperbarui.';
    }

    // Menghapus target tabungan
    elseif ($aksi === 'hapus') {

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            throw new Exception('ID target tabungan tidak valid.');
        }

        $stmt = $pdo->prepare(
            'CALL sp_target_hapus(?, ?)'
        );

        $stmt->execute([
            $id,
            $idKeluarga
        ]);

        $stmt->closeCursor();

        $_SESSION['success'] = 'Target tabungan berhasil dihapus.';
    }

    else {
        throw new Exception('Aksi tidak dikenali.');
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../../pages/tabungan.php');
exit;