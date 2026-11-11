<?php
require_once '../auth.php';
require_once '../../config/koneksi.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

// Ambil data lama
$stmt = $conn->prepare("SELECT * FROM kategori WHERE id_kategori = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$kategori = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$kategori) {
    header("Location: index.php");
    exit;
}

$pesan = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = trim($_POST['nama_kategori']);

    if (!empty($nama_kategori)) {
        $update = $conn->prepare("UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?");
        $update->bind_param("si", $nama_kategori, $id);
        if ($update->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $pesan = "Gagal memperbarui kategori.";
        }
        $update->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Ubah Kategori</title></head>
<body style="font-family: Arial, sans-serif; margin: 20px;">
    <h2>Ubah Kategori Obat</h2>
    <?php if(!empty($pesan)) echo "<p style='color:red;'>$pesan</p>"; ?>
    <form method="POST">
        <label>Nama Kategori:</label><br>
        <input type="text" name="nama_kategori" value="<?= htmlspecialchars($kategori['nama_kategori']); ?>" required style="padding: 6px; width: 300px;"><br><br>
        <button type="submit" style="padding: 8px 15px;">Update</button>
        <a href="index.php">Kembali</a>
    </form>
</body>
</html>