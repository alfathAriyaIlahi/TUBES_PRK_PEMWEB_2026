# 💊 KlikObat — Sistem Informasi Katalog Farmasi & Edukasi Kesehatan
**Kelompok 22 | Tema 06: Fakultas Kedokteran**  
Praktikum Pemrograman Web 2026 — Teknik Informatika Universitas Lampung

---

## 1. Deskripsi Singkat Sistem
**KlikObat** adalah platform web informatif yang bertindak sebagai etalase digital untuk apotek atau klinik. Sistem ini berfokus pada katalog ketersediaan obat secara *real-time* menggunakan *Fetch API* (tanpa *reload* halaman) serta menyediakan edukasi kesehatan mengenai indikasi, aturan pakai, dan peringatan (kontraindikasi).

---

## 2. Identitas Kelompok 22

| No | NPM | Nama Lengkap | Peran / Modul |
| :---: | :---: | :--- | :--- |
| 1 | 2415061091 | Riffa Yudika Permana | Backend API Publik |
| 2 | 2415061041 | Irma Hayyu Sani | Autentikasi Admin & CRUD Kategori |
| 3 | 2415061081 | Ledi Daiyana Alfara | CRUD Obat (Admin) |
| 4 | 2415061090 | Rifki Yudika Perdana | Frontend Katalog (SPA Fetch API) |
| 5 | 2415061117 | Farel Raditya Surya Negara | Database & Konfigurasi Inti |
| 6 | 2455061009 | Rachel Inaya Allantama | Modal Edukasi, UI Admin Bersama & QA |

---

## 3. Teknologi yang Digunakan
- **Frontend:** HTML5, CSS3, Vanilla JavaScript (Fetch API)
- **Backend:** PHP Native (Penyedia JSON API & Manajemen Admin)
- **Database:** MySQL / MariaDB (`klikobat`)

---

## 4. Panduan Instalasi & Menjalankan Aplikasi
1. Pastikan **Apache** dan **MySQL** pada Laragon / XAMPP telah dijalankan.
2. Import file database `kelompok/kelompok_22/database/klikobat.sql` melalui **phpMyAdmin** (`http://localhost/phpmyadmin`) atau jalankan langsung dari ekstensi MySQL VS Code.
   - Database `klikobat` otomatis terbuat beserta **5 kategori**, **20 data obat**, dan **1 akun admin**.
3. Pastikan konfigurasi kredensial database pada `backend/koneksi.php` (atau `config/koneksi.php`) sesuai dengan pengaturan lokal (`host: localhost`, `user: root`, `pass: ""`, `db: klikobat`).
4. Akses sistem melalui browser:
   - **Halaman Katalog Publik:** `http://localhost/KlikObat%20TubesPemweb/TUBES_PRK_PEMWEB_2026/kelompok/kelompok_22/frontend/index.html`
   - **Akun Default Admin:**
     - **Username:** `admin`
     - **Password:** `password`