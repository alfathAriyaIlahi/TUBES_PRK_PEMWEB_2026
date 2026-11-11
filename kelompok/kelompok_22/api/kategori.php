<?php

header("Content-Type: application/json; charset=UTF-8");

require_once '../config/koneksi.php';

$response = ["status" => "error", "message" => "Gagal mengambil data kategori", "data" => []];

try {
    $query = "SELECT id_kategori, nama_kategori FROM kategori";
    $stmt = $conn->prepare($query);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $kategori = [];
    while ($row = $result->fetch_assoc()) {
        $kategori[] = $row;
    }
    
    $response["status"] = "success";
    $response["message"] = "Data kategori berhasil diambil";
    $response["data"] = $kategori;
    
    $stmt->close();
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
?>