<?php
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/koneksi.php';

$response = ["status" => "error", "message" => "Gagal mengambil detail obat", "data" => null];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    try {
        // Tambahkan `gambar` pada klausa SELECT
        $query = "SELECT id_obat, nama_obat, id_kategori, gambar, stok, indikasi, aturan_pakai, peringatan FROM obat WHERE id_obat = ?";
        $stmt = $conn->prepare($query);
        
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Cek jika field gambar ada dan tidak kosong, gunakan nama filenya. Jika kosong, gunakan fallback default.jpg
            $row['gambar_url'] = !empty($row['gambar']) ? 'assets/images/obat/' . $row['gambar'] : 'assets/images/obat/default.jpg';

            $response["status"] = "success";
            $response["message"] = "Detail obat berhasil diambil";
            $response["data"] = $row;
        } else {
            $response["message"] = "Data obat tidak ditemukan";
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $response["message"] = $e->getMessage();
    }
} else {
    $response["message"] = "Parameter ID obat tidak ditemukan";
}

echo json_encode($response);
?>