# Frontend Katalog KlikObat

Dokumen ini menjelaskan struktur dan perilaku halaman katalog publik di `frontend/`.

## Teknologi dan berkas

Frontend menggunakan HTML5, CSS3, dan Vanilla JavaScript tanpa framework.

- `frontend/index.html` — struktur halaman, navigasi, pencarian, kategori, area katalog, serta modal edukasi obat.
- `frontend/assets/css/style.css` — gaya visual, layout responsif, kartu obat, status stok, state loading, dan modal.
- `frontend/assets/js/katalog.js` — pemuatan data, fallback lokal, filter, pencarian, render kartu, dan interaksi modal.

## Alur pemuatan data

Saat halaman dibuka, `katalog.js` mengambil kategori dari `../api/kategori.php` dan daftar obat dari `../api/obat.php` menggunakan Fetch API. Respons API diharapkan memiliki array pada properti `data`.

Jika salah satu endpoint gagal, timeout, mengembalikan JSON tidak valid, atau mengirim `status: "error"`, frontend menggunakan fallback lokal untuk kategori dan obat. Fallback tersebut mengikuti data seed pada `database/klikobat.sql`. Dalam kondisi ini, halaman menampilkan pemberitahuan bahwa data lokal digunakan dan jumlah stok mungkin tidak terbaru.

Untuk melihat stok terkini dari database, jalankan halaman melalui server PHP yang dapat mengakses API dan database. Jika hanya dibuka lewat server file statis seperti Live Server, permintaan PHP dapat gagal sehingga katalog beralih ke fallback lokal.

## Pencarian dan filter kategori

Setelah daftar obat dimuat, pencarian berlangsung di browser dengan debounce 300 ms. Pencarian lokal mencocokkan nama obat, indikasi, dan nama kategori. Tombol bersihkan mengosongkan kata kunci; tombol `/` pada keyboard memfokuskan kolom pencarian.

Kategori dirender dari data API atau fallback. Memilih kartu kategori memperbarui filter katalog tanpa memuat ulang halaman. Filter kategori dan kata kunci dapat digunakan bersamaan. Detail parameter pencarian API `q` tersedia di [`api.md`](api.md); pencarian interaktif halaman saat ini difilter di sisi frontend setelah data daftar dimuat.

## Informasi pada kartu dan status stok

Setiap kartu menampilkan kategori, ilustrasi botol SVG generik, nama obat, indikasi, aturan pakai, jumlah stok, dan tombol detail. Ilustrasi adalah dekorasi generik, bukan foto atau representasi kemasan obat tertentu.

Status stok dihitung berdasarkan jumlah unit:

- **Tersedia** jika stok lebih dari 5 unit.
- **Menipis** jika stok 1–5 unit.
- **Habis** jika stok 0 unit.

## Detail edukasi obat

Modal detail hanya terbuka melalui tombol **Detail** pada kartu. Modal menampilkan kategori, stok, indikasi, aturan pakai, dan peringatan. Modal dapat ditutup melalui tombol silang, klik pada backdrop di luar kotak modal, atau tombol `Escape`.

Saat modal terbuka, scroll halaman dikunci dan lebar scrollbar dikompensasikan untuk mencegah layout bergeser. Ketika modal ditutup, penguncian scroll serta kompensasi lebar dipulihkan.

## State katalog

Saat daftar sedang dimuat, halaman menampilkan skeleton kartu. Jika filter atau pencarian tidak menemukan obat, halaman menampilkan empty state dengan tombol untuk menghapus filter dan menampilkan kembali semua obat.

## Batasan data

Skema obat saat ini tidak menyediakan harga, keranjang, atau URL foto produk. Karena itu, halaman katalog tidak menampilkan harga atau keranjang dan tidak mengasumsikan aset foto produk tersedia. Informasi aturan pakai dan peringatan ditampilkan untuk edukasi, bukan sebagai pengganti saran tenaga kesehatan.
