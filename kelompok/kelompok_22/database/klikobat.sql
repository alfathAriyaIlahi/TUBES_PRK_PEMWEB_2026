-- ==========================================================
-- DATABASE: klikobat (Kelompok 22)
-- Sistem Informasi KlikObat - Katalog Farmasi & Edukasi
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `klikobat`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `klikobat`;

DROP TABLE IF EXISTS `obat`;
DROP TABLE IF EXISTS `kategori`;
DROP TABLE IF EXISTS `admin`;

-- 1. Struktur Tabel kategori
CREATE TABLE `kategori` (
  `id_kategori` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_kategori`),
  UNIQUE KEY `uk_nama_kategori` (`nama_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Struktur Tabel obat
CREATE TABLE `obat` (
  `id_obat` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_obat` VARCHAR(150) NOT NULL,
  `id_kategori` INT(11) NOT NULL,
  `stok` INT(11) NOT NULL DEFAULT 0,
  `indikasi` TEXT NOT NULL,
  `aturan_pakai` TEXT NOT NULL,
  `peringatan` TEXT NOT NULL,
  PRIMARY KEY (`id_obat`),
  KEY `idx_id_kategori` (`id_kategori`),
  KEY `idx_nama_obat` (`nama_obat`),
  CONSTRAINT `fk_obat_kategori`
    FOREIGN KEY (`id_kategori`)
    REFERENCES `kategori` (`id_kategori`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Struktur Tabel admin
CREATE TABLE `admin` (
  `id_admin` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Seed Data: 5 Kategori Obat
INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Pereda Nyeri & Demam'),
(2, 'Obat Batuk, Pilek & Flu'),
(3, 'Saluran Pencernaan'),
(4, 'Vitamin & Suplemen'),
(5, 'Antiseptik & Perawatan Luka');

-- 5. Seed Data: 20 Obat (Bagian 1: Obat 1 - 10)
INSERT INTO `obat` (`id_obat`, `nama_obat`, `id_kategori`, `stok`, `indikasi`, `aturan_pakai`, `peringatan`) VALUES
(1, 'Paracetamol 500 mg (Strip 10 Tablet)', 1, 45,
 'Meredakan demam serta nyeri ringan hingga sedang seperti sakit kepala, sakit gigi, dan nyeri otot.',
 'Dewasa: 1-2 tablet, 3-4 kali sehari sesudah makan. Maksimal 8 tablet per hari.',
 'Hati-hati penggunaan pada penderita gangguan fungsi hati dan ginjal. Jangan melebihi dosis yang dianjurkan.'),

(2, 'Ibuprofen 400 mg (Strip 10 Tablet)', 1, 8,
 'Mengatasi nyeri inflamasi ringan hingga sedang, nyeri haid (dismenore), sakit gigi, nyeri sendi, dan menurunkan demam.',
 'Dewasa: 1 tablet, 3 kali sehari segera sesudah makan.',
 'Tidak dianjurkan untuk penderita tukak lambung, ibu hamil trimester akhir, dan pasien dengan riwayat alergi OAINS.'),

(3, 'Asam Mefenamat 500 mg (Strip 10 Kaplet)', 1, 25,
 'Meredakan nyeri akut dan kronik ringan sampai sedang sehubungan dengan sakit kepala, sakit gigi, dan nyeri pasca operasi.',
 'Dewasa: Dosis awal 1 kaplet, dilanjutkan 1/2 - 1 kaplet tiap 6 jam sesudah makan.',
 'Jangan digunakan lebih dari 7 hari. Kontraindikasi pada pasien radang usus dan gangguan ginjal berat.'),

(4, 'Sanmol Sirup 60 ml', 1, 0,
 'Menurunkan panas demam dan meredakan rasa sakit pada anak-anak seperti sakit kepala dan nyeri tumbuh gigi.',
 'Anak 1-2 tahun: 5 ml (1 sendok takar), 3-4 kali sehari. Anak 2-6 tahun: 5-10 ml, 3-4 kali sehari.',
 'Kocok dahulu sebelum diminum. Hubungi dokter bila demam tidak turun setelah 2 hari penggunaan.'),

(5, 'OBH Combi Batuk Berdahak Menthol 100 ml', 2, 30,
 'Meredakan batuk berdahak yang disertai gejala flu seperti demam, sakit kepala, hidung tersumbat, dan bersin-bersin.',
 'Dewasa & anak di atas 12 tahun: 3 kali sehari 3 sendok takar (15 ml) sesudah makan.',
 'Dapat menyebabkan kantuk. Jangan mengendarai kendaraan bermotor atau menjalankan mesin setelah meminum obat ini.'),

(6, 'Siladex Mucolytic & Expectorant 60 ml', 2, 5,
 'Mengencerkan dahak pada saluran pernapasan dan mempermudah pengeluaran dahak saat batuk.',
 'Dewasa & anak di atas 10 tahun: 3 kali sehari 1 sendok takar (5 ml) sesudah makan.',
 'Bebas gula dan alkohol. Hati-hati penggunaan pada penderita tukak lambung dan ibu hamil trimester pertama.'),

(7, 'Tremenza Sirup 60 ml', 2, 18,
 'Meringankan gejala-gejala flu karena alergi pada saluran pernapasan bagian atas seperti hidung tersumbat dan bersin.',
 'Dewasa: 3-4 kali sehari 2 sendok takar (10 ml). Anak 6-12 tahun: 3-4 kali sehari 1 sendok takar (5 ml).',
 'Tidak dianjurkan untuk penderita hipertensi berat, penyakit jantung koroner, dan pasien yang mengonsumsi antidepresan MAOI.'),

(8, 'Degrol Hisap Dequalinium (Strip 10 Tablet)', 2, 0,
 'Tablet hisap antiseptik untuk membantu meredakan radang tenggorokan, sariawan, dan infeksi ringan rongga mulut.',
 'Hisap perlahan 1 tablet setiap 3-4 jam. Maksimal 8 tablet sehari.',
 'Hindari penggunaan jangka panjang secara terus-menerus. Tidak untuk anak di bawah usia 6 tahun.'),

(9, 'Promag Tablet Kunyah (Strip 12 Tablet)', 3, 50,
 'Meringankan gejala sakit maag dan kelebihan asam lambung seperti perih, mual, kembung, dan nyeri ulu hati.',
 'Dewasa: 1-2 tablet dikunyah, 3-4 kali sehari. Diminum 1 jam sebelum makan atau 2 jam setelah makan dan menjelang tidur.',
 'Beri jeda minimal 2 jam jika sedang mengonsumsi antibiotik tetrasiklin atau suplemen zat besi.'),

(10, 'Mylanta Cair 150 ml', 3, 7,
 'Mengurangi gejala kelebihan asam lambung, gastritis, tukak lambung, dan tukak usus dua belas jari.',
 'Dewasa: 1-2 sendok takar (5-10 ml), 3-4 kali sehari di antara waktu makan dan sebelum tidur.',
 'Tidak dianjurkan digunakan terus-menerus lebih dari 2 minggu kecuali atas petunjuk dokter.');

 -- Seed Data: 20 Obat (Bagian 2: Obat 11 - 20)
INSERT INTO `obat` (`id_obat`, `nama_obat`, `id_kategori`, `stok`, `indikasi`, `aturan_pakai`, `peringatan`) VALUES
(11, 'New Diatabs Attapulgite (Strip 4 Tablet)', 3, 35,
 'Pengobatan simtomatik pada diare non-spesifik dengan cara menyerap racun dan bakteri penyebab diare di saluran cerna.',
 'Dewasa: 2 tablet setiap selesai buang air besar. Maksimal 12 tablet dalam 24 jam.',
 'Jangan diberikan pada pasien konstipasi (sembelit). Tetap cukupi kebutuhan cairan tubuh (oralit) selama diare.'),

(12, 'Oralit 200 Garam Elektrolit (Sachet)', 3, 60,
 'Mencegah dan mengobati dehidrasi akibat diare atau muntah-muntah dengan menggantikan cairan dan elektrolit tubuh.',
 'Larutkan 1 sachet ke dalam 200 ml air matang hangat. Minum setiap kali selesai buang air besar.',
 'Larutan yang sudah dibuat tidak boleh digunakan lagi setelah lewat dari 24 jam.'),

(13, 'Vitamin C IPI 50 mg (Botol 45 Tablet)', 4, 22,
 'Membantu memenuhi kebutuhan Vitamin C harian, menjaga daya tahan tubuh, serta membantu pemulihan pasca sakit.',
 'Dewasa: 1-2 tablet hisap sehari sesudah makan.',
 'Simpan di tempat kering di bawah suhu 30 derajat Celsius dan terhindar dari sinar matahari langsung.'),

(14, 'Imboost Force (Strip 10 Kaplet)', 4, 4,
 'Suplemen imunomodulator (Echinacea & Zinc) untuk membantu memelihara daya tahan tubuh dan mempercepat penyembuhan.',
 'Dewasa: 1 kaplet, 3 kali sehari sesudah makan.',
 'Tidak boleh digunakan oleh penderita penyakit autoimun, sklerosis multipel, tuberkulosis, dan AIDS. Maksimal konsumsi 8 minggu.'),

(15, 'Sangobion Kapsul Penambah Darah (Strip 10 Kapsul)', 4, 28,
 'Suplemen zat besi, vitamin, dan mineral untuk mengatasi anemia (kurang darah) serta membantu pembentukan sel darah merah.',
 'Dewasa: 1 kapsul sehari pada waktu atau sesudah makan.',
 'Dapat menyebabkan feses berwarna hitam yang merupakan hal normal akibat kandungan zat besi.'),

(16, 'Neurobion Forte (Strip 10 Tablet)', 4, 0,
 'Suplemen vitamin neurotropik (B1, B6, B12) untuk menjaga kesehatan fungsi saraf serta mengatasi kesemutan dan kebas.',
 'Dewasa: 1 tablet sehari sesudah makan, ditelan utuh dengan air putih.',
 'Tidak dianjurkan untuk anak-anak karena tingginya dosis vitamin B di dalam tablet.'),

(17, 'Betadine Antiseptic Solution 15 ml', 5, 19,
 'Cairan antiseptik Povidone-Iodine 10% untuk membunuh kuman penyebab infeksi pada luka gores, luka bakar ringan, dan luka lecet.',
 'Bersihkan luka terlebih dahulu, lalu oleskan Betadine secukupnya 1-2 kali sehari pada area yang luka.',
 'Hanya untuk pemakaian luar. Hentikan pemakaian jika timbul kemerahan, iritasi, atau pembengkakan pada kulit.'),

(18, 'Hansaplast Kain Elastis (Amplop 10 Lembar)', 5, 40,
 'Plester penutup luka berbahan kain lentur yang mengikuti pergerakan kulit untuk melindungi luka dari kotoran dan bakteri.',
 'Bersihkan dan keringkan kulit di sekitar luka, lalu tempelkan plester tanpa diregangkan. Ganti plester setiap hari.',
 'Hanya untuk sekali pakai. Jangan gunakan pada luka terbuka yang mengalami perdarahan hebat.'),

(19, 'Rivanol Kompres Luka 100 ml', 5, 3,
 'Cairan antiseptik Etakridina Laktat 0,1% untuk membersihkan luka dan mengompres luka bernanah atau borok.',
 'Tuangkan pada kapas atau kasa steril, lalu usapkan atau kompreskan pada bagian luka.',
 'Hanya untuk penggunaan luar. Jauhkan dari jangkauan anak-anak dan simpan di tempat sejuk.'),

(20, 'Bioplacenton Gel 15 gr', 5, 14,
 'Mengobati luka bakar ringan, luka terinfeksi, dan membantu mempercepat regenerasi jaringan kulit baru.',
 'Oleskan tipis-tipis pada area luka 4-6 kali sehari sesuai kebutuhan.',
 'Obat keras terbatas. Hindari kontak langsung dengan mata dan jangan digunakan oleh pasien yang alergi terhadap Neomycin.');

-- 6. Seed Data: 1 Akun Admin Default (Username: admin | Password: password)
INSERT INTO `admin` (`id_admin`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');