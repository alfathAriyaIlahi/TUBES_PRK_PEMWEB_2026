<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];

    if (in_array($status, ['fakta', 'hoaks'])) {
        try {
            $stmt = $pdo->prepare("UPDATE lapor_hoaks SET status = :status WHERE id = :id");
            $stmt->execute([
                ':status' => $status,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            die("Gagal update status: " . $e->getMessage());
        }
    }
}

header("Location: kelola_hoaks.php");
exit;
?>