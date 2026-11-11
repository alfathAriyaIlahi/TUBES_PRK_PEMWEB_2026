
<?php
if (!isset($judulHalaman)) {
    $judulHalaman = "Dashboard Admin";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($judulHalaman) ?> | KlikObat</title>
    <link rel="stylesheet" href="/TUBES_PRK_PEMWEB_2026/kelompok/kelompok_22/frontend/assets/css/admin.css">
</head>
<body>

<div class="admin-container">

    <?php require_once __DIR__ . "/sidebar.php"; ?>

    <main class="admin-main">
        <header class="admin-header">
            <h1><?= htmlspecialchars($judulHalaman) ?></h1>
            <div class="admin-user">Admin KlikObat</div>
        </header>
