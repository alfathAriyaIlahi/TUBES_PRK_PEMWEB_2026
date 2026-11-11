<?php
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/koneksi.php';

$response = ["status" => "error", "message" => "Gagal mengambil data obat", "data" => []];

try {
    // Tambahkan `gambar` pada klausa SELECT
    $query = "SELECT id_obat, nama_obat, id_kategori, gambar, stok, indikasi, aturan_pakai, peringatan FROM obat WHERE 1=1";
    $params = [];
    $types = "";

    if (isset($_GET['id_kategori']) && !empty($_GET['id_kategori'])) {
        $query .= " AND id_kategori = ?";
        $params[] = $_GET['id_kategori'];
        $types .= "i";
    }

    if (isset($_GET['q']) && !empty($_GET['q'])) {
        $query .= " AND (nama_obat LIKE ? OR indikasi LIKE ?)";
        
        $keyword = "%" . $_GET['q'] . "%";
        
        $params[] = $keyword;
        $params[] = $keyword;
        
        $types .= "ss";
    }

    $stmt = $conn->prepare($query);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $obat = [];
    while ($row = $result->fetch_assoc()) {
        // Cek jika field gambar ada dan tidak kosong, gunakan nama filenya. Jika kosong, gunakan fallback default.jpg
        $row['gambar_url'] = !empty($row['gambar']) ? 'assets/images/obat/' . $row['gambar'] : 'assets/images/obat/default.jpg';
        
        $obat[] = $row;
    }
    
    $response["status"] = "success";
    $response["message"] = "Data obat berhasil diambil";
    $response["data"] = $obat;
    
    $stmt->close();
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
?>