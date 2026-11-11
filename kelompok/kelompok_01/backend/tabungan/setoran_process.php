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

    // Menambahkan setoran tabungan
    if ($aksi === 'tambah') {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Metode permintaan tidak valid.');
        }

        $idTarget = (int) ($_POST['id_target'] ?? 0);
        $jumlah = (float) ($_POST['jumlah'] ?? 0);
        $tanggal = $_POST['tanggal'] ?? '';
        $keterangan = trim($_POST['keterangan'] ?? '');

        if ($idTarget <= 0 || $jumlah <= 0 || $tanggal === '') {
            throw new Exception('Data setoran wajib diisi dengan benar.');
        }

        // Memastikan format tanggal valid
        $tanggalObj = DateTime::createFromFormat('!Y-m-d', $tanggal);

        if (!$tanggalObj || $tanggalObj->format('Y-m-d') !== $tanggal) {
            throw new Exception('Format tanggal setoran tidak valid.');
        }

        // Memastikan target dimiliki keluarga pengguna
        $cekTarget = $pdo->prepare(
            'SELECT id_target FROM target_tabungan
             WHERE id_target = ? AND id_keluarga = ?'
        );

        $cekTarget->execute([$idTarget, $idKeluarga]);
        $target = $cekTarget->fetch();
        $cekTarget->closeCursor();

        if (!$target) {
            throw new Exception('Target tabungan tidak ditemukan.');
        }

        // Menyimpan setoran menggunakan stored procedure
        $stmt = $pdo->prepare(
            'CALL sp_setoran_tambah(?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $idTarget,
            $idPengguna,
            $jumlah,
            $tanggal,
            $keterangan
        ]);

        $stmt->closeCursor();

        $_SESSION['success'] = 'Setoran tabungan berhasil ditambahkan.';
    }

    // Menampilkan riwayat setoran
    elseif ($aksi === 'daftar') {

        $idTarget = (int) ($_GET['id_target'] ?? 0);

        if ($idTarget <= 0) {
            throw new Exception('ID target tabungan tidak valid.');
        }

        // Memastikan target dimiliki keluarga pengguna
        $cekTarget = $pdo->prepare(
            'SELECT id_target FROM target_tabungan
             WHERE id_target = ? AND id_keluarga = ?'
        );

        $cekTarget->execute([$idTarget, $idKeluarga]);
        $target = $cekTarget->fetch();
        $cekTarget->closeCursor();

        if (!$target) {
            throw new Exception('Target tabungan tidak ditemukan.');
        }

        $stmt = $pdo->prepare('CALL sp_setoran_daftar(?)');
        $stmt->execute([$idTarget]);

        $data = $stmt->fetchAll();
        $stmt->closeCursor();

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    else {
        throw new Exception('Aksi tidak dikenali.');
    }

} catch (Exception $e) {
    if ($aksi === 'daftar') {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }

    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../../pages/tabungan.php');
exit;