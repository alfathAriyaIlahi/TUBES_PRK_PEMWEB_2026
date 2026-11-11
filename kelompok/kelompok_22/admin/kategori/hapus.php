<?php
require_once '../auth.php';
require_once '../../config/koneksi.php';

$id = $_GET['id'] ?? null;

if ($id) {
    // 1. Cek apakah kategori masih digunakan di tabel obat
    $cek = $conn->prepare("SELECT COUNT(*) as total FROM obat WHERE id_kategori = ?");
    $cek->bind_param("i", $id);
    $cek->execute();
    $resultCek = $cek->get_result()->fetch_assoc();
    $cek->close();

    if ($resultCek['total'] > 0) {
        // Jika masih dipakai, gagalkan penghapusan
        echo "<script>alert('Gagal Hapus! Kategori ini masih digunakan oleh data obat.'); window.location='index.php';</script>";
    } else {
        // Jika aman, lakukan delete
        $del = $conn->prepare("DELETE FROM kategori WHERE id_kategori = ?");
        $del->bind_param("i", $id);
        $del->execute();
        $del->close();
        header("Location: index.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}