# Panduan Desain Ruang Hukum (Kelompok 25)

Semua halaman memakai header, footer, dan `assets/css/style.css` yang sama supaya tampilannya seragam. Cukup ikuti aturan di bawah.

## Warna
| Nama | Kode | Dipakai untuk |
|---|---|---|
| Navy | `#0F2747` | Navbar, judul, tombol utama |
| Emas | `#C9A24B` | Tombol aksi utama (`btn-gold`), garis aksen |
| Gading | `#F7F5F0` | Latar halaman |
| Tinta | `#1B2433` | Teks isi |

## Font
- Judul: **Playfair Display** (otomatis untuk `h1`-`h4`)
- Isi: **Inter**

## Komponen siap pakai
- Kartu: `<div class="card-rh p-4">` (tambah `hoverable` untuk kartu yang bisa diklik)
- Tombol utama: `btn btn-navy`, tombol aksi: `btn btn-gold`
- Judul bagian: `<h2 class="section-title">`
- Status konsultasi: `<span class="badge-status badge-menunggu">` (juga `badge-dijawab`, `badge-selesai`, `badge-ditutup`)
- Kartu angka (dashboard): `<div class="card-rh stat-card p-3"><div class="angka">12</div>Pertanyaan</div>`
- Ikon: Bootstrap Icons, contoh `<i class="bi bi-chat-dots"></i>`
- Grid dan formulir: Bootstrap 5 (`container`, `row`, `col-md-6`, `form-control`)

## Kerangka setiap halaman
```php
<?php
require_once __DIR__ . '/../config/app.php';   // dari folder klien/pengacara/admin/auth
require_role('klien');                          // hapus baris ini untuk halaman publik
$judul = 'Judul Halaman';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container page-head">
  <h2>Judul</h2>
  ... isi halaman ...
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```
Untuk file di folder paling luar (`kelompok_25/`), pakai `__DIR__ . '/config/app.php'` (tanpa `../`).

## Aturan wajib
1. Tampilkan data dari database/pengguna dengan `<?= e($data) ?>`.
2. Setiap `<form method="post">` wajib berisi `<?= csrf_field() ?>`, dan di PHP cek `csrf_valid()`.
3. Query database wajib pakai `prepare()` dan `execute([...])`, jangan menempel variabel ke teks SQL.
4. Alamat tautan dan gambar pakai `url('path')`, jangan menulis `localhost/...` langsung.
5. Pesan sukses atau gagal pakai `flash('success', '...')` lalu `redirect(...)`.

## Rancangan tampilan per halaman
| Halaman | File | Isi |
|---|---|---|
| Beranda | `index.php` | Hero navy dengan tombol emas, 3 keunggulan, bidang hukum (kartu), pengacara unggulan, artikel terbaru, ajakan daftar |
| Daftar pengacara | `pengacara.php` | Filter bidang, kartu pengacara (foto, nama, bidang, pengalaman, rating) |
| Artikel | `artikel.php`, `artikel_detail.php` | Daftar kartu artikel dan halaman baca |
| FAQ | `faq.php` | Akordeon Bootstrap |
| Dashboard klien | `klien/dashboard.php` | Kartu angka, tombol "Ajukan Konsultasi", tabel riwayat dengan badge status |
| Dashboard pengacara | `pengacara/dashboard.php` | Kartu angka, daftar pertanyaan masuk, tombol jawab |
| Dashboard admin | `admin/dashboard.php` | Kartu angka, tabel pengguna, antrean verifikasi pengacara, kelola artikel |

## Diagram database
```
users (id, nama, email, password, role, telepon, foto, status)
  |-- 1:1 -- profil_pengacara (user_id, bidang_id, bio, pengalaman_tahun, kota, no_izin)
  |-- 1:N -- konsultasi (klien_id, pengacara_id, bidang_id, judul, isi, lampiran, status)
                  |-- 1:N -- balasan (konsultasi_id, pengirim_id, isi)
                  |-- 1:1 -- ulasan (konsultasi_id, klien_id, pengacara_id, rating, komentar)
  |-- 1:N -- artikel (penulis_id, bidang_id, judul, slug, ringkasan, isi, gambar, status)
bidang_hukum (id, nama, deskripsi)  <-- dipakai profil_pengacara, konsultasi, artikel
faq (id, pertanyaan, jawaban, urutan)
```
Alur status konsultasi: `menunggu` (klien kirim) -> `dijawab` (pengacara membalas) -> `selesai` (klien menutup/memberi rating) atau `ditutup`.
