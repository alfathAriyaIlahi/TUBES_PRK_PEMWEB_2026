# Pena Kampus

> **Portal Berita dan Informasi Kampus Berbasis Web**
> Menggunakan PHP Native dan MySQL

---

## 1. Identitas Proyek

| Keterangan | Informasi |
| :--- | :--- |
| **Nama Sistem** | Pena Kampus |
| **Kelompok** | 15 |
| **Tema** | Portal berita dan informasi Universitas Lampung |
| **Jenis Sistem** | Portal berita kampus dengan CMS editorial |
| **Platform** | Website |
| **Bahasa Pemrograman** | PHP Native, HTML, CSS, JavaScript |
| **Database** | MySQL |
| **Framework Backend** | *Tidak menggunakan framework backend (PHP Native)* |

---

## 2. Deskripsi Sistem

**Pena Kampus** adalah portal berita dan informasi kampus berbasis web yang menampilkan berita tingkat universitas beserta berita dari delapan fakultas di lingkungan Universitas Lampung.

Sistem ini memiliki dua bagian utama:

1. **Portal publik** — menampilkan berita unggulan, berita terbaru, berita populer, kanal fakultas, pencarian, filter kategori, serta detail artikel dengan komentar.
2. **CMS editorial** — memungkinkan administrator dan editor mengelola berita (draft, publikasi, arsip), kategori, fakultas, komentar, pesan kontak, dan akun pengguna.

Dengan adanya Pena Kampus, penyebaran informasi kampus diharapkan menjadi lebih terstruktur, cepat, dan mudah dikelola.

---

## 3. Latar Belakang

Informasi kampus selama ini tersebar di berbagai media dan tidak terpusat, sehingga menyulitkan sivitas akademika untuk memperoleh berita resmi. Di sisi lain, pengelolaan berita yang belum terkomputerisasi menyulitkan redaksi dalam mengatur publikasi, kategori, dan moderasi komentar.

Oleh karena itu, diperlukan sistem yang mengintegrasikan penyajian berita publik dengan pengelolaan editorial dalam satu platform. **Pena Kampus** dikembangkan sebagai solusi berbasis web yang menyediakan portal berita dan manajemen artikel, kategori, fakultas, komentar, serta pesan kontak secara terintegrasi.

---

## 4. Tujuan Sistem

Tujuan pengembangan Pena Kampus adalah:

- Menyediakan portal berita kampus yang mudah diakses publik.
- Menampilkan berita unggulan, terbaru, dan populer.
- Menyediakan pencarian dan filter berita berdasarkan kategori dan fakultas.
- Menyediakan halaman detail artikel dengan jumlah pembaca, artikel terkait, dan komentar.
- Membantu pengelolaan berita oleh editor (draft, publikasi, arsip).
- Membantu pengelolaan kategori dan fakultas.
- Menyediakan modul login, logout, dan hak akses admin/editor.
- Menyediakan dashboard ringkas untuk memantau aktivitas portal.
- Menyediakan moderasi komentar dan inbox pesan kontak.

---

## 5. Fitur Utama Sistem

### A. Beranda dan Landing Page 

- Menampilkan halaman utama portal.
- Menyediakan navbar dan informasi portal.
- Menampilkan berita unggulan.
- Menampilkan berita terbaru dan populer.
- Menampilkan kanal delapan fakultas.
- Menyediakan halaman Tentang Kami dan Kontak.
- Mengelola informasi fakultas (admin).

### B. Daftar Berita, Kategori, dan Pencarian 

- Menampilkan katalog berita.
- Mencari berita berdasarkan kata kunci.
- Memfilter berita berdasarkan kategori.
- Memfilter berita berdasarkan fakultas.
- Menyortir berita.
- Menyediakan pagination.
- Menampilkan halaman berita per fakultas.
- Mengelola kategori (admin).

### C. Detail Berita dan Interaksi Pembaca 

- Menampilkan halaman detail artikel.
- Menampilkan berita terkait.
- Menghitung jumlah pembaca artikel.
- Menerima dan menampilkan komentar yang dimoderasi.
- Menyediakan formulir kontak dan inbox pesan (admin).

### D. Autentikasi dan Admin Dashboard 

- Login dan logout administrator/editor.
- Mengelola session pengguna.
- Mengatur hak akses berdasarkan role.
- Menyediakan dashboard ringkas admin.
- Mengelola akun pengguna.
- Memoderasi komentar.

### E. Manajemen Berita (CMS) 

- CRUD berita.
- Editor artikel.
- Upload gambar artikel.
- Mengelola status draft, published, dan archived.
- Publikasi berita.
- Mengatur akun pengguna (toggle aktif/nonaktif).
- Merancang struktur database dan data awal.

---

## 6. Teknologi yang Digunakan

| Teknologi | Kegunaan |
| :--- | :--- |
| **HTML5** | Membuat struktur halaman website |
| **CSS3** | Mengatur tampilan website (CSS statis, tanpa framework runtime) |
| **JavaScript** | Menambahkan interaksi dan rendering halaman (vanilla) |
| **PHP Native** | Mengembangkan proses backend dan logika sistem |
| **MySQL** | Menyimpan dan mengelola data |
| **PDO** | Koneksi database dengan prepared statement |
| **Google Fonts** | Font Inter dan Material Symbols |
| **Git & GitHub** | Mengelola versi kode dan kolaborasi tim |
| **XAMPP** | Menjalankan server lokal dan database |

---

## 7. Persyaratan Sistem

Untuk menjalankan Pena Kampus pada lingkungan lokal, diperlukan:

- Sistem operasi yang mendukung server PHP (misalnya Windows).
- XAMPP (Apache dan MySQL/MariaDB).
- PHP versi 8.2 atau lebih baru.
- MySQL atau MariaDB.
- Browser modern (seperti Google Chrome atau Mozilla Firefox).
- Git (opsional) untuk mengambil dan mengelola kode proyek.

---

## 8. Panduan Instalasi dan Menjalankan Sistem

### Langkah 1 — Letakkan Proyek

Salin folder `pena_kampus` ke direktori server lokal:

```text
C:\xampp\htdocs\pena_kampus
```

### Langkah 2 — Jalankan Web Server

1. Buka aplikasi **XAMPP Control Panel**.
2. Klik tombol **Start** pada **Apache** dan **MySQL**.
3. Pastikan kedua service berstatus *Running*.

### Langkah 3 — Buat Database

1. Buka **phpMyAdmin** melalui browser (`http://localhost/phpmyadmin`).
2. Gunakan database bernama `pena_kampus` (buat baru dengan nama ini bila belum ada).
3. Impor file `sql/database.sql` ke dalam database `pena_kampus`.
4. Catatan: file `database.sql` sudah berisi `CREATE DATABASE pena_kampus` dan `USE pena_kampus`, jadi nama database **wajib** `pena_kampus`.

### Langkah 4 — Konfigurasi Database

Sesuaikan konfigurasi koneksi database pada file:

```text
config/config.php
```

Pastikan host, username, password, dan nama database sesuai dengan konfigurasi MySQL lokal Anda.

### Langkah 5 — Jalankan Website

Buka browser dan akses alamat berikut:

```text
http://localhost/pena_kampus/
```

### Akun Demo

| Role | Email | Password |
| :--- | :--- | :--- |
| Administrator | `admin@penakampus.test` | `Admin123!` |
| Editor FISIP | `editor.fisip@penakampus.test` | `Admin123!` |

---

## 9. Kolaborasi Git dan GitHub

Pengembangan Pena Kampus menggunakan Git dan GitHub untuk mengelola versi kode serta mendukung kolaborasi antaranggota.

### Alur Kerja Kolaborasi

1. Setiap anggota mengerjakan fitur pada branch masing-masing.
2. Branch fitur dibuat dari branch `staging`.
3. Perubahan kode **hanya dilakukan** di folder kelompok masing-masing.
4. Setiap anggota melakukan *commit* dan *push* ke repository fork kelompok.
5. Setiap anggota membuat *Pull Request* menuju branch `staging`.
6. Ketua kelompok melakukan review dan merge perubahan.
7. Setelah seluruh fitur terintegrasi dan diuji, ketua kelompok membuat *Pull Request* final ke repository utama.

**Contoh alur branch Anggota 1:**

```bash
git checkout staging
git pull origin staging
git checkout -b feat/kelompok-15-beranda
```

**Contoh commit dan push:**

```bash
git add .
git commit -m "feat(kelompok): menambahkan fitur beranda"
git push origin feat/kelompok-15-beranda
```

---

## 10. Keamanan Sistem

Pengembangan Pena Kampus memperhatikan beberapa aspek keamanan, antara lain:

- Password pengguna disimpan menggunakan fungsi hashing PHP (`password_hash()`).
- Validasi session untuk membatasi akses halaman administrator.
- Penggunaan token CSRF pada setiap form.
- Penggunaan *prepared statements* untuk mencegah serangan *SQL Injection*.
- Escape output untuk mencegah serangan *XSS*.
- Pengaturan hak akses berdasarkan role pengguna.

---

## 11. Batasan Pengembangan

Sistem Pena Kampus dikembangkan dengan batasan sebagai berikut:

1. Sistem berbasis website dan dijalankan pada lingkungan server lokal.
2. Backend menggunakan PHP Native tanpa framework backend.
3. Database menggunakan MySQL atau MariaDB.
4. Sistem difokuskan pada portal berita dan manajemen editorial kampus.
5. Fitur yang dikembangkan mengikuti ruang lingkup tugas besar Praktikum Pemrograman Web 2026.

---

<div align="center">

**Pena Kampus – Portal Berita dan Informasi Kampus Berbasis Web**

Kelompok 15 • Teknik Informatika • Universitas Lampung

*Praktikum Pemrograman Web 2026*

</div>
