<?php
require_once '../auth.php';
require_once '../../config/koneksi.php';

$pesan = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = trim($_POST['nama_kategori']);

    if (!empty($nama_kategori)) {
        $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
        $stmt->bind_param("s", $nama_kategori);
        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $pesan = "Gagal menambah kategori (Mungkin nama sudah ada).";
        }
        $stmt->close();
    } else {
        $pesan = "Nama kategori tidak boleh kosong!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Tambah Kategori</title></head>
<body style="font-family: Arial, sans-serif; margin: 20px;">
    <h2>Tambah Kategori Obat</h2>
    <?php if(!empty($pesan)) echo "<p style='color:red;'>$pesan</p>"; ?>
    <form method="POST">
        <label>Nama Kategori:</label><br>
        <input type="text" name="nama_kategori" required style="padding: 6px; width: 300px;"><br><br>
        <button type="submit" style="padding: 8px 15px;">Simpan</button>
        <a href="index.php">Kembali</a>
    </form>
</body>
</html>