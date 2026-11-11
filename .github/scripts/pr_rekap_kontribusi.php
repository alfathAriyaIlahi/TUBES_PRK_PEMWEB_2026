<?php
/**
 * Script CI/CD Rekapitulasi Kontribusi Anggota pada Pull Request
 * Dijalankan otomatis oleh GitHub Actions (pr-validation.yml)
 */

if (php_sapi_name() !== 'cli') {
    die("Script ini hanya dapat dijalankan melalui CLI.\n");
}

$prNumber = getenv('PR_NUMBER');
$baseRef  = getenv('BASE_REF') ?: 'main';
$repo     = getenv('REPO');

if (!$prNumber) {
    echo "ℹ️ PR_NUMBER tidak disetel. Mode pengujian lokal dijalankan.\n";
    $prNumber = 'TEST';
}

echo "🔍 Memeriksa perubahan file pada PR #$prNumber terhadap origin/$baseRef...\n";

// 1. Tentukan git ref perbandingan (origin/$baseRef atau $baseRef)
exec("git rev-parse --verify origin/$baseRef 2>&1", $chkOut, $chkRet);
$compareRef = ($chkRet === 0) ? "origin/$baseRef" : $baseRef;

// Dapatkan daftar file yang diubah
exec("git diff --name-only $compareRef...HEAD", $changedFiles, $retFiles);
if ($retFiles !== 0 || empty($changedFiles)) {
    echo "⚠️ Gagal mendapatkan daftar perubahan file atau tidak ada file yang berubah.\n";
    exit(0);
}

// 2. Deteksi folder kelompok yang diubah
$kelompokFolder = null;
foreach ($changedFiles as $file) {
    if (preg_match('#^kelompok/(kelompok_\d+)/#', trim($file), $matches)) {
        $kelompokFolder = $matches[1];
        break;
    }
}

$targetFolder = $kelompokFolder ? "kelompok/$kelompokFolder" : "kelompok/";
echo "📁 Target Folder Kelompok: $targetFolder\n";

// 3. Ambil log commit numstat dalam rentang PR untuk folder kelompok tersebut
$cmdNumstat = "git log --no-merges --numstat --pretty=format:\"COMMIT_META:%H|%an|%ae|%ad|%s\" --date=short $compareRef...HEAD -- \"$targetFolder/\"";
exec($cmdNumstat, $numstatOutput, $retNumstat);

$authors = [];
$commits = [];
$currentAuthor = null;
$currentEmail = null;

if (!empty($numstatOutput)) {
    foreach ($numstatOutput as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        if (str_starts_with($line, 'COMMIT_META:')) {
            $meta = substr($line, strlen('COMMIT_META:'));
            $parts = explode('|', $meta, 5);
            $hash = $parts[0] ?? '';
            $name = $parts[1] ?? 'Unknown';
            $email = $parts[2] ?? '';
            $date = $parts[3] ?? '';
            $msg = $parts[4] ?? '';

            $currentAuthor = $name;
            $currentEmail = $email;
            $authorKey = strtolower(trim($email ?: $name));

            if (!isset($authors[$authorKey])) {
                $authors[$authorKey] = [
                    'name' => $name,
                    'email' => $email,
                    'commits' => 0,
                    'added' => 0,
                    'deleted' => 0,
                    'files' => []
                ];
            }
            $authors[$authorKey]['commits']++;

            if (!isset($commits[$hash])) {
                $commits[$hash] = [
                    'hash' => substr($hash, 0, 7),
                    'author' => $name,
                    'date' => $date,
                    'message' => $msg
                ];
            }
        } elseif ($currentAuthor !== null && preg_match('/^(\d+|-)\s+(\d+|-)\s+(.+)$/', $line, $m)) {
            $authorKey = strtolower(trim($currentEmail ?: $currentAuthor));
            $add = ($m[1] === '-') ? 0 : (int)$m[1];
            $del = ($m[2] === '-') ? 0 : (int)$m[2];
            $file = $m[3];

            $authors[$authorKey]['added'] += $add;
            $authors[$authorKey]['deleted'] += $del;
            if (!in_array($file, $authors[$authorKey]['files'])) {
                $authors[$authorKey]['files'][] = $file;
            }
        }
    }
}

$totalCommits = count($commits);

// Urutkan kontributor berdasarkan jumlah commit
uasort($authors, fn($a, $b) => $b['commits'] <=> $a['commits']);

// 4. Susun pesan komentar Markdown
$kelompokTitle = $kelompokFolder ? strtoupper($kelompokFolder) : "KELOMPOK";
$body = "### 📊 Rekapitulasi Kontribusi Anggota $kelompokTitle pada PR #$prNumber\n\n";

if ($totalCommits === 0) {
    $body .= "_Tidak ditemukan commit non-merge baru pada folder `$targetFolder` dalam Pull Request ini._\n";
} else {
    $body .= "Berikut adalah rincian kontribusi kode dari anggota kelompok yang diajukan dalam Pull Request ini:\n\n";
    $body .= "| Kontributor | Email / Kontak | Jumlah Commit | % Kontribusi | Baris Ditambah (+) | Baris Dihapus (-) |\n";
    $body .= "| :--- | :--- | :---: | :---: | :---: | :---: |\n";

    foreach ($authors as $a) {
        $pct = $totalCommits > 0 ? round(($a['commits'] / $totalCommits) * 100, 1) : 0;
        $body .= sprintf(
            "| **%s** | %s | %d | %.1f%% | +%s | -%s |\n",
            htmlspecialchars($a['name']),
            $a['email'] ? '`' . htmlspecialchars($a['email']) . '`' : '-',
            $a['commits'],
            $pct,
            number_format($a['added']),
            number_format($a['deleted'])
        );
    }

    $body .= "\n<details>\n";
    $body .= "<summary>🔍 <b>Klik untuk melihat daftar rincian commit message</b></summary>\n\n";
    foreach ($commits as $c) {
        $body .= "- `" . $c['hash'] . "` (" . $c['date'] . ") **" . htmlspecialchars($c['author']) . "**: " . htmlspecialchars($c['message']) . "\n";
    }
    $body .= "</details>\n\n";
}

$body .= "> 🤖 *Laporan ini digenerate secara otomatis oleh CI/CD GitHub Actions untuk transparansi dan evaluasi kontribusi anggota kelompok.*";

// Simpan pesan ke file sementara
$commentFile = sys_get_temp_dir() . '/pr_comment_' . $prNumber . '.md';
file_put_contents($commentFile, $body);

echo "\n--- KONTEN KOMENTAR PR ---\n";
echo $body . "\n";
echo "---------------------------\n\n";

// 5. Kirim komentar ke PR menggunakan GitHub CLI jika di lingkungan GitHub Actions
if ($prNumber !== 'TEST' && getenv('GH_TOKEN')) {
    $repoArg = $repo ? "--repo $repo" : "";
    $cmd = sprintf('gh pr comment %s --body-file="%s" %s', escapeshellarg($prNumber), $commentFile, $repoArg);
    echo "🚀 Mengirim komentar ke Pull Request #$prNumber...\n";
    exec($cmd, $ghOut, $ghRet);
    if ($ghRet === 0) {
        echo "✅ Komentar kontribusi berhasil dikirim ke PR #$prNumber.\n";
    } else {
        echo "⚠️ Gagal mengirim komentar via GitHub CLI (Kode keluar: $ghRet).\n";
        echo implode("\n", $ghOut) . "\n";
    }
} else {
    echo "ℹ️ Melewati pengiriman komentar GitHub CLI (bukan lingkungan GitHub Actions live).\n";
}

@unlink($commentFile);
