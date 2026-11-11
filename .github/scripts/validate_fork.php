<?php
/**
 * Script Validasi Kepemilikan Fork terhadap Folder Kelompok
 * Tubes Praktikum Pemrograman Web 2026 - LabKom TI Unila
 */

if (php_sapi_name() !== 'cli') {
    die("Script ini hanya dapat dijalankan melalui CLI.\n");
}

$forkOwner = strtolower(trim(getenv('HEAD_REPO_OWNER') ?: ($argv[1] ?? '')));
$groupTouched = strtolower(trim(getenv('GROUP_TOUCHED') ?: ($argv[2] ?? '')));

if (empty($forkOwner) || empty($groupTouched)) {
    echo "⚠️ Informasi fork owner atau folder kelompok tidak lengkap. Melewati validasi fork.\n";
    exit(0);
}

$mappingFile = __DIR__ . '/../fork_mapping.json';
if (!file_exists($mappingFile)) {
    echo "ℹ️ File pemetaan .github/fork_mapping.json tidak ditemukan. Melewati validasi fork.\n";
    exit(0);
}

$mapping = json_decode(file_get_contents($mappingFile), true);
$forkToGroup = $mapping['fork_to_group'] ?? [];
$groupToFork = $mapping['group_to_fork'] ?? [];

echo "🔍 Memeriksa kepemilikan fork:\n";
echo "   - Pemilik Fork (Head Repo Owner) : @$forkOwner\n";
echo "   - Folder Kelompok yang Diubah    : $groupTouched\n";
echo "--------------------------------------------------------\n";

// Kasus 1: Fork owner sudah terdaftar resmi
if (isset($forkToGroup[$forkOwner])) {
    $expectedGroup = $forkToGroup[$forkOwner];
    if ($expectedGroup !== $groupTouched) {
        echo "❌ DITOLAK: Pelanggaran Batas Kelompok!\n";
        echo "   Repositori fork '@$forkOwner' terdaftar resmi untuk $expectedGroup.\n";
        echo "   Anda dilarang mengubah file di dalam folder '$groupTouched'!\n";
        echo "--------------------------------------------------------\n";
        exit(1);
    }
    echo "✅ Validasi Lolos: Repositori fork '@$forkOwner' sah merupakan pemilik folder '$groupTouched'.\n";
    exit(0);
}

// Kasus 2: Folder kelompok yang ingin diubah sudah dimiliki oleh fork lain
if (isset($groupToFork[$groupTouched])) {
    $officialOwner = $groupToFork[$groupTouched];
    echo "❌ DITOLAK: Akses Kelompok Tidak Sah!\n";
    echo "   Folder '$groupTouched' sudah terdaftar resmi di bawah fork milik ketua: @$officialOwner.\n";
    echo "   Anda mengirimkan PR dari fork lain (@$forkOwner).\n";
    echo "   Silakan kerjakan kode di dalam repositori fork ketua kelompok Anda (@$officialOwner) sebagai kolaborator.\n";
    echo "--------------------------------------------------------\n";
    exit(1);
}

// Kasus 3: Fork baru dan kelompok baru yang belum terdaftar (Auto-allow untuk pendaftaran baru)
echo "ℹ️ Fork @$forkOwner dan $groupTouched belum terdaftar di mapping. Diizinkan sebagai entri baru.\n";
exit(0);
