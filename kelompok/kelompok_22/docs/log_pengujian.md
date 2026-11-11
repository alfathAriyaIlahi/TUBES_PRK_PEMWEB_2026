# Log Pengujian Fitur & QA (Quality Assurance) - KlikObat

## 1. Skenario Pengujian Frontend & Modal Edukasi

| No | Skenario Pengujian | Langkah Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | Buka Modal Detail Obat | Klik tombol "Detail" pada salah satu obat di katalog | Modal muncul dengan efek transisi halus dan menampilkan data obat | PASS |
| 2 | Fetch Detail Obat dari API | Buka detail obat yang tersedia | Sistem mengambil data dari `api/obat_detail.php` dan menampilkan indikasi, aturan pakai, & peringatan | PASS |
| 3 | Tutup Modal via Overlay / Tombol | Klik tombol silang, area di luar modal, atau tekan tombol `Esc` | Modal tertutup dan fokus kembali ke tombol detail sebelumnya | PASS |
| 4 | Responsivitas Layar HP | Buka modal pada layar browser ukuran HP (<= 640px) | Ukuran modal menyesuaikan layar HP dan teks tetap terbaca dengan jelas | PASS |

## 2. Skenario Pengujian Layout Admin

| No | Skenario Pengujian | Langkah Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | Navigasi Menu Sidebar | Klik menu "Data Obat" atau "Data Kategori" | Menu terpilih otomatis memiliki status kelas `active` | PASS |
| 2 | Konsistensi Tema UI Admin | Navigasi ke seluruh halaman admin | Warna, font, dan spacing konsisten dengan tema KlikObat (`admin.css`) | PASS |