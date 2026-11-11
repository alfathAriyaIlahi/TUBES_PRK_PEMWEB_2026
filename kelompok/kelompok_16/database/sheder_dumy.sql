INSERT INTO categories (id, name, slug) VALUES
(1, 'Hukum Pidana',          'pidana'),
(2, 'Hukum Perdata',         'perdata'),
(3, 'Hukum Keluarga',        'keluarga'),
(4, 'Hukum Ketenagakerjaan', 'ketenagakerjaan'),
(5, 'Hukum Digital',         'digital'),
(6, 'Hukum Konsumen',        'konsumen');

INSERT INTO users (id, username, email, password_hash, is_guest, avatar_color, created_at) VALUES
(1, 'budi_santoso', 'budi@guugel.com', '$2y$10$SQw5mJtmGNP9ucvuyj2tVePV.Fza0heUOkYF78mPclz178COzMi5.', 0, '#253B68', DATE_SUB(NOW(), INTERVAL 60 DAY)),
(2, 'siti_rahma',   'siti@gmaial.com', '$2y$10$SQw5mJtmGNP9ucvuyj2tVePV.Fza0heUOkYF78mPclz178COzMi5.', 0, '#16A085', DATE_SUB(NOW(), INTERVAL 55 DAY)),
(3, 'andi_pratama', 'andi@etiya.com', '$2y$10$SQw5mJtmGNP9ucvuyj2tVePV.Fza0heUOkYF78mPclz178COzMi5.', 0, '#8E44AD', DATE_SUB(NOW(), INTERVAL 50 DAY)),
(4, 'dewi_lestari', 'dewi@eef.com', '$2y$10$SQw5mJtmGNP9ucvuyj2tVePV.Fza0heUOkYF78mPclz178COzMi5.', 0, '#D35400', DATE_SUB(NOW(), INTERVAL 45 DAY)),
(5, 'rizky_hukum',  'rizky@example.com','$2y$10$SQw5mJtmGNP9ucvuyj2tVePV.Fza0heUOkYF78mPclz178COzMi5.', 0, '#526B91', DATE_SUB(NOW(), INTERVAL 40 DAY)),
(6, 'maya_putri',   'maya@example.com', '$2y$10$SQw5mJtmGNP9ucvuyj2tVePV.Fza0heUOkYF78mPclz178COzMi5.', 0, '#2C7A7B', DATE_SUB(NOW(), INTERVAL 35 DAY)),
(7, 'Pengguna-6769', NULL, NULL, 1, '#B7791F', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(8, 'Pengguna-9999', NULL, NULL, 1, '#253B68', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(9, 'Pengguna-911', NULL, NULL, 1, '#16A085', DATE_SUB(NOW(), INTERVAL 10 DAY));

INSERT INTO posts (id, user_id, category_id, content, created_at) VALUES
(1, 7, 1, 'Saya mendapat surat panggilan dari polisi sebagai saksi dalam sebuah kasus. Apakah saya wajib datang? Apa yang sebaiknya saya siapkan?', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(2, 8, 2, 'Saya mengontrak rumah dengan perjanjian tertulis satu tahun. Di bulan ketiga pemilik tiba-tiba menaikkan harga sewa tanpa persetujuan saya. Apakah ini boleh dilakukan?', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(3, 4, 3, 'Setelah putusan cerai dari pengadilan sudah keluar, bagaimana langkah mengurus akta cerai dan pembaruan data di KK?', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(4, 9, 4, 'Perusahaan tempat saya bekerja tidak membayar upah lembur selama beberapa bulan. Langkah awal apa yang sebaiknya saya lakukan?', DATE_SUB(NOW(), INTERVAL 9 HOUR)),
(5, 3, 5, 'Foto pribadi saya disebarkan di media sosial tanpa izin. Ke mana saya bisa melapor dan apa dasar hukumnya?', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6, 2, 6, 'Saya membeli barang secara online, barang yang datang tidak sesuai deskripsi tetapi penjual menolak pengembalian dana. Apa hak saya sebagai konsumen?', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(7, 6, 1, 'Apa bedanya penipuan dengan wanprestasi? Teman saya tidak membayar utang tetapi bilang ini urusan perdata saja.', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(8, 7, 4, 'Kontrak kerja saya (PKWT) akan berakhir bulan depan dan tidak diperpanjang. Apakah saya berhak atas kompensasi?', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(9, 1, 3, 'Soal hak asuh anak setelah perceraian: siapa yang biasanya mendapat hak asuh dan apa saja yang dipertimbangkan hakim?', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(10, 8, 5, 'Saya menulis ulasan jujur tentang sebuah toko di Google Maps lalu diancam akan dilaporkan karena pencemaran nama baik. Apakah ulasan bisa dipidana?', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(11, 9, 2, 'Tanah warisan orang tua belum dibagi. Apakah salah satu ahli waris boleh menjual tanah itu sendiri tanpa persetujuan yang lain?', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(12, 6, 6, 'Aplikasi pinjaman online menagih dengan kata-kata kasar dan menghubungi semua kontak saya. Apakah itu dibenarkan?', DATE_SUB(NOW(), INTERVAL 7 DAY));

INSERT INTO replies (post_id, user_id, content, created_at) VALUES
(1, 5, 'Pada prinsipnya panggilan resmi sebagai saksi adalah kewajiban hukum. Pastikan dulu surat panggilannya sah (ada identitas penyidik, nomor perkara, waktu, dan tempat), lalu datang sesuai jadwal.', DATE_SUB(NOW(), INTERVAL 25 MINUTE)),
(1, 2, 'Bawa KTP dan surat panggilan. Kalau ragu, kamu boleh berkonsultasi dulu dengan LBH atau advokat sebelum hadir.', DATE_SUB(NOW(), INTERVAL 20 MINUTE)),
(2, 4, 'Perjanjian yang dibuat secara sah mengikat kedua pihak seperti undang-undang (Pasal 1338 KUHPerdata). Perubahan harga sewa umumnya perlu kesepakatan kedua pihak.', DATE_SUB(NOW(), INTERVAL 100 MINUTE)),
(2, 1, 'Cek isi perjanjian, mungkin ada klausul penyesuaian harga. Kalau tidak ada, sampaikan keberatan secara tertulis dan simpan buktinya.', DATE_SUB(NOW(), INTERVAL 90 MINUTE)),
(3, 6, 'Biasanya setelah putusan berkekuatan hukum tetap, ajukan permohonan akta cerai ke pengadilan yang memutus, lalu bawa ke Disdukcapil untuk memperbarui status di KK.', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(4, 5, 'Kumpulkan bukti dulu: slip gaji, catatan jam kerja, dan surat perintah lembur. Setelah itu ajukan keberatan tertulis ke HRD.', DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(4, 1, 'Jika tidak selesai di internal, bisa dilanjutkan ke mediasi lewat Dinas Tenaga Kerja setempat.', DATE_SUB(NOW(), INTERVAL 7 HOUR)),
(4, 7, 'Saya juga pernah mengalami hal serupa. Catatan harian jam masuk dan pulang sangat membantu.', DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(5, 2, 'Simpan bukti (tangkapan layar dan tautan), laporkan konten ke platform agar diturunkan, dan pertimbangkan melapor ke kepolisian bagian siber.', DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(5, 5, 'Penyebaran data pribadi tanpa izin berkaitan dengan UU Pelindungan Data Pribadi dan UU ITE. Konsultasikan dengan LBH untuk pasal yang paling tepat.', DATE_SUB(NOW(), INTERVAL 18 HOUR)),
(5, 8, 'Jangan hapus chat atau akun pelaku sebelum bukti disimpan ya.', DATE_SUB(NOW(), INTERVAL 15 HOUR)),
(6, 4, 'UU Perlindungan Konsumen memberi hak atas barang yang sesuai informasi. Ajukan komplain lewat platform, simpan bukti, dan bila buntu bisa ke BPSK.', DATE_SUB(NOW(), INTERVAL 22 HOUR)),
(6, 6, 'Kalau belanja lewat marketplace, gunakan fitur pengajuan pengembalian dana sebelum dana dilepas ke penjual.', DATE_SUB(NOW(), INTERVAL 21 HOUR)),
(7, 1, 'Singkatnya: wanprestasi adalah ingkar janji dalam hubungan perdata, sedangkan penipuan butuh unsur tipu muslihat sejak awal. Ini perlu dilihat dari fakta kasusnya.', DATE_SUB(NOW(), INTERVAL 40 HOUR)),
(8, 3, 'Untuk PKWT, umumnya pekerja berhak atas uang kompensasi saat kontrak berakhir. Periksa kontrak dan ketentuan yang berlaku di perusahaanmu.', DATE_SUB(NOW(), INTERVAL 60 HOUR)),
(9, 2, 'Hakim mempertimbangkan kepentingan terbaik anak. Faktor seperti usia anak, kedekatan, dan kemampuan merawat ikut dinilai.', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(9, 5, 'Untuk yang beragama Islam, ada pengaturan di Kompilasi Hukum Islam. Intinya tetap pada kepentingan anak.', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(10, 1, 'Ulasan yang jujur dan sesuai fakta berbeda dengan tuduhan palsu. Simpan bukti pengalamanmu dan tetap gunakan bahasa yang sopan.', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(11, 3, 'Tanah warisan yang belum dibagi umumnya milik bersama seluruh ahli waris, jadi penjualan perlu persetujuan semua ahli waris.', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(11, 6, 'Sebaiknya bagi warisan dulu secara resmi, bisa lewat musyawarah dan dicatat di notaris.', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(12, 4, 'Penagihan dengan ancaman dan penyebaran data ke kontak bisa dilaporkan ke OJK (jika pinjol berizin) atau ke polisi. Simpan bukti pesan dan rekaman.', DATE_SUB(NOW(), INTERVAL 6 DAY));

INSERT INTO likes (post_id, user_id) VALUES
(1, 2), (1, 3), (1, 5), (1, 8),
(2, 1), (2, 4), (2, 6), (2, 9),
(3, 1), (3, 2), (3, 9),
(4, 3), (4, 4), (4, 5), (4, 6), (4, 7),
(5, 1), (5, 2), (5, 4), (5, 6), (5, 8), (5, 9),
(6, 5), (6, 6), (6, 7),
(7, 1), (7, 3), (7, 8),
(8, 2), (8, 4), (8, 9),
(9, 2), (9, 5), (9, 6),
(10, 2), (10, 3), (10, 7),
(11, 4), (11, 8),
(12, 1), (12, 3), (12, 9);