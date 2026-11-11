<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Wajib login
wajibLogin();

$userId = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id <= 0) {
    setFlash('danger', 'ID laporan tidak valid.');
    session_write_close();
    header('Location: /user/laporan_saya.php');
    exit;
}

// Ambil data laporan milik user
try {
    $stmt = $pdo->prepare("
        SELECT id, user_id, kategori_id, jenis, judul, isi, lokasi, foto, is_public, status 
        FROM laporan 
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->execute([
        ':id'      => $id,
        ':user_id' => $userId
    ]);
    $laporan = $stmt->fetch();

    if (!$laporan) {
        setFlash('danger', 'Laporan tidak ditemukan atau Anda tidak memiliki hak akses.');
        session_write_close();
        header('Location: /user/laporan_saya.php');
        exit;
    }

    // Hanya laporan berstatus 'menunggu' yang boleh diedit
    if ($laporan['status'] !== 'menunggu') {
        setFlash('danger', 'Laporan yang sedang diproses atau sudah selesai tidak dapat diedit kembali.');
        session_write_close();
        header('Location: /user/laporan_saya.php');
        exit;
    }

} catch (PDOException $e) {
    error_log("Error mengambil data laporan untuk edit: " . $e->getMessage());
    setFlash('danger', 'Terjadi kesalahan sistem.');
    session_write_close();
    header('Location: /user/laporan_saya.php');
    exit;
}

// Ambil daftar kategori aktif dari database
try {
    $stmtKat = $pdo->query("SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
    $daftarKategori = $stmtKat->fetchAll();
} catch (PDOException $e) {
    $daftarKategori = [];
}

// Inisialisasi variabel formulir
$judul       = $_POST['judul'] ?? $laporan['judul'];
$kategori_id = $_POST['kategori_id'] ?? $laporan['kategori_id'];
$lokasi      = $_POST['lokasi'] ?? $laporan['lokasi'];
$isi         = $_POST['isi'] ?? $laporan['isi'];
$is_public   = isset($_POST['is_public']) ? 1 : (empty($_POST) ? (int)$laporan['is_public'] : 0);
$errors      = [];

// Proses formulir saat metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasiCsrf();

    // Validasi Judul
    $judulTrim = trim($judul);
    if ($judulTrim === '') {
        $errors[] = 'Judul laporan wajib diisi.';
    } elseif (mb_strlen($judulTrim) < 5 || mb_strlen($judulTrim) > 255) {
        $errors[] = 'Judul laporan minimal 5 karakter dan maksimal 255 karakter.';
    }

    // Validasi Kategori
    if (empty($kategori_id)) {
        $errors[] = 'Silakan pilih kategori yang sesuai.';
    } else {
        $stmtCekKat = $pdo->prepare("SELECT id FROM kategori WHERE id = :id");
        $stmtCekKat->execute([':id' => $kategori_id]);
        if (!$stmtCekKat->fetch()) {
            $errors[] = 'Kategori yang dipilih tidak valid.';
        }
    }

    // Validasi Lokasi
    $lokasiTrim = trim($lokasi);
    if ($lokasiTrim === '') {
        $errors[] = 'Lokasi kejadian atau objek aspirasi wajib diisi.';
    } elseif (mb_strlen($lokasiTrim) > 255) {
        $errors[] = 'Lokasi maksimal 255 karakter.';
    }

    // Validasi Isi Laporan
    $isiTrim = trim($isi);
    if ($isiTrim === '') {
        $errors[] = 'Isi laporan wajib diisi.';
    } elseif (mb_strlen($isiTrim) < 15) {
        $errors[] = 'Isi laporan terlalu singkat, jelaskan minimal 15 karakter.';
    }

    // Validasi Foto Baru (Jika pengguna mengunggah foto pengganti)
    $namaFotoBaru = $laporan['foto']; // Nilai default foto lama

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $fileError = $_FILES['foto']['error'];
        $fileSize  = $_FILES['foto']['size'];
        $tmpName   = $_FILES['foto']['tmp_name'];
        $fileName  = $_FILES['foto']['name'];

        if ($fileError !== UPLOAD_ERR_OK) {
            $errors[] = 'Terjadi kesalahan saat mengunggah foto pengganti.';
        } else {
            // Maksimal 2 MB
            $maxFileSize = 2 * 1024 * 1024;
            if ($fileSize > $maxFileSize) {
                $errors[] = 'Ukuran foto maksimal adalah 2 MB.';
            }

            // Ekstensi JPG, JPEG, PNG
            $ekstensi = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $ekstensiDiizinkan = ['jpg', 'jpeg', 'png'];

            if (!in_array($ekstensi, $ekstensiDiizinkan, true)) {
                $errors[] = 'Format foto harus berupa JPG, JPEG, atau PNG.';
            }

            // Validasi MIME type asli
            if (empty($errors) && function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $tmpName);
                finfo_close($finfo);

                $mimeDiizinkan = ['image/jpeg', 'image/png'];
                if (!in_array($mimeType, $mimeDiizinkan, true)) {
                    $errors[] = 'Berkas yang diunggah bukan format gambar yang valid.';
                }
            }

            // Simpan foto baru jika valid
            if (empty($errors)) {
                $namaFotoBaru = 'laporan_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ekstensi;
                $folderTujuan = __DIR__ . '/../assets/uploads/';

                if (!is_dir($folderTujuan)) {
                    mkdir($folderTujuan, 0755, true);
                }

                $pathTujuan = $folderTujuan . $namaFotoBaru;
                if (!move_uploaded_file($tmpName, $pathTujuan)) {
                    $errors[] = 'Gagal menyimpan foto baru ke server.';
                    $namaFotoBaru = $laporan['foto'];
                } else {
                    // Hapus foto lama jika ada dan file fisiknya ada
                    if (!empty($laporan['foto'])) {
                        $pathFotoLama = $folderTujuan . $laporan['foto'];
                        if (file_exists($pathFotoLama) && is_file($pathFotoLama)) {
                            unlink($pathFotoLama);
                        }
                    }
                }
            }
        }
    }

    // Jika tidak ada error, lakukan pembaruan data
    if (empty($errors)) {
        try {
            $sqlUpdate = "
                UPDATE laporan 
                SET judul = :judul,
                    kategori_id = :kategori_id,
                    lokasi = :lokasi,
                    isi = :isi,
                    foto = :foto,
                    is_public = :is_public
                WHERE id = :id AND user_id = :user_id AND status = 'menunggu'
            ";

            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':judul'       => $judulTrim,
                ':kategori_id' => $kategori_id,
                ':lokasi'      => $lokasiTrim,
                ':isi'         => $isiTrim,
                ':foto'        => $namaFotoBaru,
                ':is_public'   => $is_public,
                ':id'          => $id,
                ':user_id'     => $userId
            ]);

            setFlash('success', 'Laporan Anda berhasil diperbarui.');
            session_write_close();
            header('Location: /user/laporan_saya.php');
            exit;

        } catch (PDOException $e) {
            error_log("Error mengupdate laporan: " . $e->getMessage());
            $errors[] = 'Terjadi kesalahan sistem saat memperbarui laporan. Silakan coba kembali.';
        }
    }
}

$pageTitle = 'Edit Laporan';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width: 760px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem;">
        <div>
            <h1 class="card-title" style="margin-bottom: 0.35rem;">Edit Laporan</h1>
            <p style="color: var(--color-text-light); font-size: 0.95rem;">
                Perbarui informasi laporan Anda sebelum ditindaklanjuti oleh administrator.
            </p>
        </div>
        <div>
            <?= badgeStatus($laporan['status']) ?>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong style="display: block; margin-bottom: 0.4rem;">Perhatian! Data belum dapat diperbarui:</strong>
            <ul style="margin-left: 1.25rem; margin-bottom: 0;">
                <?php foreach ($errors as $error): ?>
                    <li><?= sanitize($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/user/edit.php?id=<?= (int)$id ?>" method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= (int)$id ?>">

        <!-- Jenis Laporan (Terkunci/Read-Only) -->
        <div class="form-group">
            <label class="form-label">Jenis Laporan</label>
            <div style="padding: 0.65rem 0.85rem; background-color: var(--color-bg); border: 1px solid var(--color-border); border-radius: var(--radius); display: flex; align-items: center; justify-content: space-between;">
                <span style="font-weight: 600; text-transform: capitalize;">
                    <?= sanitize($laporan['jenis']) ?>
                </span>
                <span style="font-size: 0.85rem; color: var(--color-text-light);">
                    ℹ️ Jenis laporan tidak dapat diubah setelah dikirim
                </span>
            </div>
        </div>

        <!-- Judul Laporan -->
        <div class="form-group">
            <label for="judul" class="form-label">Judul Laporan <span style="color: var(--color-danger);">*</span></label>
            <input type="text" id="judul" name="judul" class="form-control" 
                   value="<?= sanitize($judul) ?>" required maxlength="255">
        </div>

        <!-- Kategori & Lokasi -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="kategori_id" class="form-label">Kategori <span style="color: var(--color-danger);">*</span></label>
                <select id="kategori_id" name="kategori_id" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($daftarKategori as $kat): ?>
                        <option value="<?= (int)$kat['id'] ?>" <?= ((string)$kategori_id === (string)$kat['id']) ? 'selected' : '' ?>>
                            <?= sanitize($kat['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="lokasi" class="form-label">Lokasi Kejadian <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="lokasi" name="lokasi" class="form-control" 
                       value="<?= sanitize($lokasi) ?>" required maxlength="255">
            </div>
        </div>

        <!-- Isi Laporan -->
        <div class="form-group">
            <label for="isi" class="form-label">Isi Laporan / Keterangan Lengkap <span style="color: var(--color-danger);">*</span></label>
            <textarea id="isi" name="isi" class="form-control" rows="6" required><?= sanitize($isi) ?></textarea>
        </div>

        <!-- Foto Bukti -->
        <div class="form-group">
            <label for="foto" class="form-label">Foto Bukti / Pendukung</label>

            <?php if (!empty($laporan['foto'])): ?>
                <div style="margin-bottom: 0.85rem; padding: 0.75rem; background-color: var(--color-bg); border-radius: var(--radius); border: 1px solid var(--color-border); display: flex; align-items: center; gap: 1rem;">
                    <img src="/assets/uploads/<?= sanitize($laporan['foto']) ?>" alt="Foto Laporan" style="max-height: 80px; max-width: 120px; border-radius: 4px; border: 1px solid var(--color-border); object-fit: cover;">
                    <div style="font-size: 0.88rem;">
                        <strong>Foto saat ini terpasang.</strong>
                        <div style="color: var(--color-text-light); margin-top: 0.2rem;">Pilih file di bawah hanya jika ingin mengganti dengan foto baru.</div>
                    </div>
                </div>
            <?php endif; ?>

            <input type="file" id="foto" name="foto" class="form-control" accept=".jpg, .jpeg, .png">
            <small style="display: block; margin-top: 0.35rem; color: var(--color-text-light); font-size: 0.85rem;">
                Format: JPG, JPEG, atau PNG (Maksimal 2 MB). Kosongkan jika tidak ingin mengganti foto.
            </small>

            <!-- Pratinjau Foto Baru -->
            <div id="preview-box" style="display: none; margin-top: 0.85rem;">
                <p style="font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem;">Pratinjau Foto Pengganti:</p>
                <img id="preview-img" src="" alt="Pratinjau" style="max-height: 200px; border-radius: var(--radius); border: 1px solid var(--color-border); display: block;">
            </div>
        </div>

        <!-- Opsi Publik/Privat -->
        <div class="form-group" style="margin-top: 1.5rem; padding: 0.85rem 1rem; background-color: var(--color-bg); border-radius: var(--radius); border: 1px solid var(--color-border);">
            <label style="display: flex; align-items: flex-start; gap: 0.65rem; cursor: pointer; margin-bottom: 0;">
                <input type="checkbox" name="is_public" value="1" <?= $is_public ? 'checked' : '' ?> style="margin-top: 0.25rem;">
                <div>
                    <strong>Publikasikan Laporan Ini</strong>
                    <div style="font-size: 0.85rem; color: var(--color-text-light); margin-top: 0.15rem;">
                        Jika dicentang, laporan dapat dilihat di halaman Laporan Publik. Jika tidak dicentang, hanya Anda dan Admin yang dapat melihatnya.
                    </div>
                </div>
            </label>
        </div>

        <!-- Tombol simpan -->
        <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem; justify-content: flex-end;">
            <a href="/user/laporan_saya.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>

<!-- Pratinjau Gambar -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fotoInput = document.getElementById('foto');
    const previewBox = document.getElementById('preview-box');
    const previewImg = document.getElementById('preview-img');

    if (fotoInput && previewBox && previewImg) {
        fotoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                if (file.type.match(/^image\/(jpeg|png)$/)) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        previewBox.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewBox.style.display = 'none';
                    previewImg.src = '';
                }
            } else {
                previewBox.style.display = 'none';
                previewImg.src = '';
            }
        });
    }
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

