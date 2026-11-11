Dokumentasi Endpoint API Publik (KlikObat)

1. Endpoint Kategori Obat
URL: /api/kategori.php

Method: GET

Deskripsi: Mengambil seluruh daftar kategori obat yang tersedia di database.

Parameter: Tidak ada.

Contoh Respons JSON:

JSON
{
  "status": "success",
  "message": "Data kategori berhasil diambil",
  "data": [
    {
      "id_kategori": 1,
      "nama_kategori": "Obat Bebas"
    },
    {
      "id_kategori": 2,
      "nama_kategori": "Obat Keras"
    }
  ]
}

2. Endpoint Daftar Obat (dengan Filter & Pencarian)
URL: /api/obat.php

Method: GET

Deskripsi: Mengambil daftar obat. Mendukung pencarian berdasarkan nama obat dan penyaringan berdasarkan ID kategori.

Parameter Query (URL):

id_kategori (opsional, integer): Menyaring obat berdasarkan kategori tertentu (contoh: api/obat.php?id_kategori=1).

q (opsional, string): Kata kunci pencarian nama obat (contoh: api/obat.php?q=paracetamol).

Contoh Respons JSON:

JSON
{
  "status": "success",
  "message": "Data obat berhasil diambil",
  "data": [
    {
      "id_obat": 1,
      "nama_obat": "Paracetamol 500mg",
      "id_kategori": 1,
      "stok": 25,
      "indikasi": "Meringankan rasa sakit pada sakit kepala, sakit gigi, dan menurunkan demam.",
      "aturan_pakai": "Dewasa: 3x sehari 1 tablet sesudah makan.",
      "peringatan": "Tidak boleh digunakan pada penderita gangguan fungsi hati berat."
    }
  ]
}

3. Endpoint Detail Obat
URL: /api/obat_detail.php

Method: GET

Deskripsi: Mengambil informasi detail dari satu obat spesifik untuk ditampilkan pada pop-up modal edukasi.

Parameter Query (URL):

id (wajib, integer): ID obat yang ingin diambil detailnya (contoh: api/obat_detail.php?id=1).

Contoh Respons JSON:

JSON
{
  "status": "success",
  "message": "Detail obat berhasil diambil",
  "data": {
    "id_obat": 1,
    "nama_obat": "Paracetamol 500mg",
    "id_kategori": 1,
    "stok": 25,
    "indikasi": "Meringankan rasa sakit pada sakit kepala, sakit gigi, dan menurunkan demam.",
    "aturan_pakai": "Dewasa: 3x sehari 1 tablet sesudah makan.",
    "peringatan": "Tidak boleh digunakan pada penderita gangguan fungsi hati berat."
  }
}