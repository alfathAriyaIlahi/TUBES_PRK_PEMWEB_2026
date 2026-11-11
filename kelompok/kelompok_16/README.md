# RUMDIS (Forum Diskusi Online Bantuan Hukum)

Platform diskusi masalah hukum di Indonesia, terinspirasi dari Threads dan Reddit.
Sistem **Guest First, Login Optional**: pengunjung otomatis mendapat identitas seperti `Pengguna-4821`
(tersimpan di database, dikenali lewat PHP Session). Pengguna bisa mendaftar kapan saja.
Saat mendaftar, postingan dan jawaban lamanya ikut pindah ke akun baru.

## Fitur

- Beranda: feed, filter kategori, urutan (terbaru, terpopuler, banyak dijawab), form posting
- Pencarian AJAX (isi, username, kategori) dengan debounce
- Detail postingan dan jawaban (kirim jawaban via AJAX)
- Like / unlike via AJAX tanpa reload
- Profil, Jawaban Saya, Login, Register, Logout
- Edit dan hapus postingan serta jawaban milik sendiri
- Diskusi trending dan mode gelap

## Teknologi

HTML5, CSS3 (Grid + Flexbox), JavaScript Vanilla (Fetch API), PHP Native, MySQL (PDO), PHP Session.
Tanpa framework, tanpa Composer, tanpa npm.

## Keamanan

PDO prepared statements, `htmlspecialchars()` (fungsi `e()`), validasi frontend dan backend,
CSRF token, pemeriksaan kepemilikan data, `session_regenerate_id()` setelah login/daftar,
`password_hash()` dan `password_verify()`.

## Instalasi (XAMPP)

1. Buka XAMPP Control Panel, jalankan **Apache** dan **MySQL**.
2. Ekstrak ZIP, lalu letakkan folder `ruang-hukum` di `C:\xampp\htdocs\`.
3. Buka `http://localhost/phpmyadmin`.
4. Klik tab **Import**, pilih file `database/ruang_hukum.sql`, lalu klik **Import**.
   (File ini sudah membuat database `ruang_hukum` otomatis.)
5. Buka `config/database.php`. Nilai default XAMPP (`root`, password kosong) biasanya sudah benar.
6. Buka `http://localhost/ruang-hukum/`.

Akun contoh: `budi_santoso` / `password123` (juga siti_rahma, andi_pratama, dewi_lestari, rizky_hukum, maya_putri).

## Struktur Folder

```
ruang-hukum/
├── index.php, search.php, detail-post.php, edit-post.php
├── profile.php, my-answers.php, login.php, register.php, logout.php
├── config/      database.php, session.php
├── includes/    init, functions, header, footer, post_card, reply_item
├── api/         like.php, reply.php, search.php   (JSON)
├── process/     create/update/delete post, delete reply, login, register
├── assets/      css, js, images
└── database/    ruang_hukum.sql
```

## Catatan

Isi diskusi bersifat informasi umum, bukan nasihat hukum resmi.
