
<?php
// Menampilkan judul halaman
echo "<h1>Selamat Datang di PHP!</h1>";

// Variabel
$nama = "Nayla";
$nim = "2415061000";
$jurusan = "Teknik Informatika";

// Menampilkan data
echo "<h2>Data Mahasiswa</h2>";
echo "Nama: " . $nama . "<br>";
echo "NIM: " . $nim . "<br>";
echo "Jurusan: " . $jurusan . "<br>";

// Operasi matematika
$angka1 = 10;
$angka2 = 5;
$hasil = $angka1 + $angka2;

echo "<h2>Perhitungan PHP</h2>";
echo "$angka1 + $angka2 = $hasil";

// Percabangan
echo "<h2>Percabangan</h2>";
$nilai = 85;

if ($nilai >= 75) {
    echo "Selamat, Anda lulus!";
} else {
    echo "Maaf, Anda belum lulus.";
}

// Perulangan
echo "<h2>Perulangan</h2>";

for ($i = 1; $i <= 5; $i++) {
    echo "Angka ke-$i <br>";
}
?>
