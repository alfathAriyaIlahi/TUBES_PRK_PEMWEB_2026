<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// ===============================
// PENGATURAN
// ===============================
$PPN_PERSEN     = 0;     // contoh: 11 untuk PPN 11%
$SERVICE_PERSEN = 0;     // contoh: 5 untuk service charge 5%
$WAJIB_LOGIN    = false; // ubah ke true jika sistem login sudah siap

// Hak akses. Role diambil dari session, jika belum ada dianggap admin.
$role      = $_SESSION['role'] ?? 'admin';
$boleh_void = in_array($role, ['admin', 'owner'], true);

if ($WAJIB_LOGIN && empty($_SESSION['user_id'])) {
    echo '<div class="p-6 text-red-600">Silakan login terlebih dahulu.</div>';
    exit;
}

$user_id = (int) ($_SESSION['user_id'] ?? 1);

// Redirect menggunakan JavaScript karena layout sudah mengeluarkan HTML.
function redirectKasir($url) {
    echo '<script>window.location.href = ' . json_encode($url) . ';</script>';
    exit;
}

// Keranjang belanja.
if (!isset($_SESSION['keranjang_kasir'])) {
    $_SESSION['keranjang_kasir'] = [];
}

$error = '';

// ===============================
// FUNGSI BANTU
// ===============================

// Menghitung total keranjang (sebelum diskon/pajak).
function totalKeranjang($keranjang) {
    $total = 0;
    foreach ($keranjang as $item) {
        $total += $item['harga'] * $item['qty'];
    }
    return $total;
}

// Menghitung diskon, service charge, pajak, dan total akhir.
function hitungTotal($subtotal, $diskon, $svc_persen, $ppn_persen) {
    $diskon  = min(max(0, $diskon), $subtotal);
    $dasar   = $subtotal - $diskon;
    $service = round($dasar * $svc_persen / 100);
    $pajak   = round(($dasar + $service) * $ppn_persen / 100);

    return [
        'subtotal' => (float) $subtotal,
        'diskon'   => (float) $diskon,
        'service'  => (float) $service,
        'pajak'    => (float) $pajak,
        'total'    => (float) ($dasar + $service + $pajak),
    ];
}

// Membuat nomor invoice unik.
function buatInvoice($conn) {
    do {
        $invoice = 'INV-' . date('YmdHis') . '-' . random_int(100, 999);

        $stmt = mysqli_prepare($conn,
            "SELECT id FROM orders WHERE no_invoice = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "s", $invoice);
        mysqli_stmt_execute($stmt);
        $ada = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    } while ($ada);

    return $invoice;
}

// Mengambil total tagihan, total dibayar, dan sisa dari tabel payments.
// Harus dipanggil di dalam transaksi (FOR UPDATE).
function hitungPembayaran($conn, $order_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT o.total_tagihan, o.status_order,
                COALESCE((SELECT SUM(nominal_bayar)
                          FROM payments WHERE order_id = o.id), 0) AS dibayar
         FROM orders o
         WHERE o.id = ?
         FOR UPDATE"
    );
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$row) {
        throw new Exception('Transaksi tidak ditemukan.');
    }

    if ($row['status_order'] === 'batal') {
        throw new Exception('Transaksi ini sudah dibatalkan.');
    }

    $total   = (float) $row['total_tagihan'];
    $dibayar = (float) $row['dibayar'];

    return [
        'total'   => $total,
        'dibayar' => $dibayar,
        'sisa'    => max(0, round($total - $dibayar, 2)),
    ];
}

// Simpan pembayaran + update status order.
function simpanPembayaran($conn, $order_id, $metode, $nominal, $uang, $kembalian) {
    $cek = mysqli_query($conn,
        "SELECT COALESCE(MAX(urutan_split), 0) + 1 AS urutan
         FROM payments WHERE order_id = " . (int) $order_id
    );
    $urutan = (int) mysqli_fetch_assoc($cek)['urutan'];

    $payment = mysqli_prepare($conn,
        "INSERT INTO payments
        (order_id, urutan_split, metode_bayar, nominal_bayar, uang_diterima, kembalian)
        VALUES (?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($payment, "iisddd",
        $order_id, $urutan, $metode, $nominal, $uang, $kembalian);
    mysqli_stmt_execute($payment);

    $p = hitungPembayaran($conn, $order_id);
    $status = ($p['sisa'] <= 0) ? 'lunas' : 'sebagian';

    $upd = mysqli_prepare($conn,
        "UPDATE orders SET status_order = ?, tipe_pembayaran = 'split' WHERE id = ?"
    );
    mysqli_stmt_bind_param($upd, "si", $status, $order_id);
    mysqli_stmt_execute($upd);

    // Kalau sudah lunas, tandai semua item lunas.
    if ($status === 'lunas') {
        $updItem = mysqli_prepare($conn,
            "UPDATE order_items SET status_bayar_item = 'lunas' WHERE order_id = ?"
        );
        mysqli_stmt_bind_param($updItem, "i", $order_id);
        mysqli_stmt_execute($updItem);
    }

    return $status;
}

// ===============================
// TAMBAH PRODUK KE KERANJANG
// ===============================
if (isset($_POST['tambah'])) {
    $id  = (int) $_POST['product_id'];
    $qty = max(1, (int) ($_POST['qty'] ?? 1));

    $stmt = mysqli_prepare($conn,
        "SELECT id, nama_produk, harga, stok FROM products WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $produk = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($produk && $produk['stok'] > 0) {
        $jumlah = ($_SESSION['keranjang_kasir'][$id]['qty'] ?? 0) + $qty;

        if ($jumlah <= $produk['stok']) {
            $_SESSION['keranjang_kasir'][$id] = [
                'id'    => $id,
                'nama'  => $produk['nama_produk'],
                'harga' => (float) $produk['harga'],
                'qty'   => $jumlah
            ];
        } else {
            $error = 'Jumlah produk melebihi stok tersedia.';
        }
    } else {
        $error = 'Produk tidak tersedia atau stok habis.';
    }
}

// ===============================
// UBAH JUMLAH PRODUK
// ===============================
if (isset($_POST['update_qty'])) {
    $id  = (int) $_POST['product_id'];
    $qty = max(1, (int) $_POST['qty']);

    if (isset($_SESSION['keranjang_kasir'][$id])) {
        $stmt = mysqli_prepare($conn, "SELECT stok FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $produk = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($produk && $qty <= $produk['stok']) {
            $_SESSION['keranjang_kasir'][$id]['qty'] = $qty;
        } else {
            $error = 'Jumlah melebihi stok tersedia.';
        }
    }
}

// ===============================
// HAPUS PRODUK
// ===============================
if (isset($_POST['hapus'])) {
    $id = (int) $_POST['product_id'];
    unset($_SESSION['keranjang_kasir'][$id]);
}

// ===============================
// KOSONGKAN KERANJANG
// ===============================
if (isset($_POST['kosongkan'])) {
    $_SESSION['keranjang_kasir'] = [];
}

// ===============================
// CHECKOUT TRANSAKSI
// ===============================
if (isset($_POST['checkout'])) {
    $keranjang = $_SESSION['keranjang_kasir'];

    if (empty($keranjang)) {
        $error = 'Keranjang masih kosong.';
    } else {
        mysqli_begin_transaction($conn);

        try {
            $subtotal   = totalKeranjang($keranjang);
            $diskon_in  = (float) ($_POST['diskon'] ?? 0);
            $hitung     = hitungTotal($subtotal, $diskon_in, $SERVICE_PERSEN, $PPN_PERSEN);

            $invoice = buatInvoice($conn);

            $pelanggan = trim($_POST['nama_pelanggan'] ?? 'Umum');
            $pelanggan = $pelanggan ?: 'Umum';

            $meja = trim($_POST['nomor_meja'] ?? '-');
            $meja = $meja ?: '-';

            $stmt = mysqli_prepare($conn,
                "INSERT INTO orders
                (no_invoice, user_id, nama_pelanggan, nomor_meja,
                 subtotal, diskon, service_charge, pajak,
                 total_tagihan, tipe_pembayaran, status_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'full', 'pending')"
            );

            mysqli_stmt_bind_param(
                $stmt, "sissddddd",
                $invoice, $user_id, $pelanggan, $meja,
                $hitung['subtotal'], $hitung['diskon'],
                $hitung['service'], $hitung['pajak'], $hitung['total']
            );

            mysqli_stmt_execute($stmt);

            $new_order_id = mysqli_insert_id($conn);

            foreach ($keranjang as $item) {
                $product_id = (int) $item['id'];
                $qty        = (int) $item['qty'];
                $harga      = (float) $item['harga'];
                $sub_item   = $harga * $qty;

                // Periksa stok terbaru.
                $cek = mysqli_prepare($conn,
                    "SELECT stok FROM products WHERE id = ? FOR UPDATE"
                );
                mysqli_stmt_bind_param($cek, "i", $product_id);
                mysqli_stmt_execute($cek);

                $stok = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));

                if (!$stok || $stok['stok'] < $qty) {
                    throw new Exception(
                        'Stok produk ' . $item['nama'] . ' tidak mencukupi.'
                    );
                }

                // Simpan detail transaksi.
                $detail = mysqli_prepare($conn,
                    "INSERT INTO order_items
                    (order_id, product_id, qty, harga_satuan, subtotal)
                    VALUES (?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param(
                    $detail, "iiidd",
                    $new_order_id, $product_id, $qty, $harga, $sub_item
                );
                mysqli_stmt_execute($detail);

                // Kurangi stok produk.
                $update = mysqli_prepare($conn,
                    "UPDATE products SET stok = stok - ? WHERE id = ?"
                );
                mysqli_stmt_bind_param($update, "ii", $qty, $product_id);
                mysqli_stmt_execute($update);
            }

            mysqli_commit($conn);

            $_SESSION['keranjang_kasir'] = [];

            redirectKasir('index.php?page=kasir&order=' . $new_order_id);

        } catch (Throwable $e) {
            mysqli_rollback($conn);
            $error = 'Checkout gagal: ' . $e->getMessage();
        }
    }
}

// ===============================
// PEMBAYARAN NOMINAL (CICILAN)
// ===============================
if (isset($_POST['bayar_nominal'])) {
    $order_id_post = (int) $_POST['order_id'];
    $uang   = (float) ($_POST['uang_diterima'] ?? 0);
    $metode = $_POST['metode_bayar'] ?? 'tunai';

    if (!in_array($metode, ['tunai', 'qris', 'debit'], true)) {
        $metode = 'tunai';
    }

    mysqli_begin_transaction($conn);

    try {
        if ($uang <= 0) {
            throw new Exception('Nominal pembayaran harus lebih dari 0.');
        }

        $p = hitungPembayaran($conn, $order_id_post);

        if ($p['sisa'] <= 0) {
            throw new Exception('Transaksi ini sudah lunas.');
        }

        // Non-tunai tidak boleh melebihi sisa tagihan (tidak ada kembalian).
        if ($metode !== 'tunai' && $uang > $p['sisa']) {
            throw new Exception(
                'Pembayaran QRIS/debit tidak boleh melebihi sisa tagihan.'
            );
        }

        $nominal   = min($uang, $p['sisa']);
        $kembalian = $uang - $nominal;

        simpanPembayaran($conn, $order_id_post, $metode, $nominal, $uang, $kembalian);

        mysqli_commit($conn);

        redirectKasir('index.php?page=kasir&order=' . $order_id_post . '&sukses=bayar');

    } catch (Throwable $e) {
        mysqli_rollback($conn);
        $error = $e->getMessage();
    }
}

// ===============================
// PEMBAYARAN SPLIT BILL (PILIH ITEM)
// ===============================
if (isset($_POST['bayar_split'])) {
    $order_id_post = (int) $_POST['order_id'];
    $item_ids = array_map('intval', $_POST['item_ids'] ?? []);

    $metode = $_POST['metode_bayar'] ?? 'tunai';
    $uang   = (float) ($_POST['uang_diterima'] ?? 0);

    if (!in_array($metode, ['tunai', 'qris', 'debit'], true)) {
        $metode = 'tunai';
    }

    if (empty($item_ids)) {
        $error = 'Pilih minimal satu item yang ingin dibayar.';
    } else {
        mysqli_begin_transaction($conn);

        try {
            $p = hitungPembayaran($conn, $order_id_post);

            if ($p['sisa'] <= 0) {
                throw new Exception('Transaksi ini sudah lunas.');
            }

            // Ambil semua item order untuk menghitung faktor pajak/diskon.
            $stmt = mysqli_prepare($conn,
                "SELECT id, subtotal, status_bayar_item
                 FROM order_items WHERE order_id = ? FOR UPDATE"
            );
            mysqli_stmt_bind_param($stmt, "i", $order_id_post);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);

            $semua_subtotal = 0;
            $belum_ids      = [];
            $pilih_subtotal = 0;
            $valid_ids      = [];

            while ($it = mysqli_fetch_assoc($res)) {
                $semua_subtotal += (float) $it['subtotal'];

                if ($it['status_bayar_item'] === 'belum') {
                    $belum_ids[] = (int) $it['id'];

                    if (in_array((int) $it['id'], $item_ids, true)) {
                        $pilih_subtotal += (float) $it['subtotal'];
                        $valid_ids[]     = (int) $it['id'];
                    }
                }
            }

            if (empty($valid_ids)) {
                throw new Exception('Item tidak tersedia atau sudah lunas.');
            }

            // Nominal item sudah termasuk porsi diskon, service, dan pajak.
            $faktor = $semua_subtotal > 0 ? $p['total'] / $semua_subtotal : 1;
            $nominal = round($pilih_subtotal * $faktor);

            // Jika semua item tersisa dipilih, nominal = sisa tagihan (hindari selisih pembulatan).
            if (count($valid_ids) === count($belum_ids)) {
                $nominal = $p['sisa'];
            }

            $nominal = min($nominal, $p['sisa']);

            if ($metode !== 'tunai' && abs($uang - $nominal) > 0.001) {
                throw new Exception(
                    'Pembayaran QRIS/debit harus pas: ' . number_format($nominal, 0, ',', '.') . '.'
                );
            }

            if ($uang < $nominal) {
                throw new Exception('Uang yang diterima kurang dari total pembayaran.');
            }

            $kembalian = $uang - $nominal;

            simpanPembayaran($conn, $order_id_post, $metode, $nominal, $uang, $kembalian);

            // Tandai item yang dipilih sebagai lunas.
            foreach ($valid_ids as $item_id) {
                $update = mysqli_prepare($conn,
                    "UPDATE order_items SET status_bayar_item = 'lunas'
                     WHERE id = ? AND order_id = ?"
                );
                mysqli_stmt_bind_param($update, "ii", $item_id, $order_id_post);
                mysqli_stmt_execute($update);
            }

            mysqli_commit($conn);

            redirectKasir('index.php?page=kasir&order=' . $order_id_post . '&sukses=bayar');

        } catch (Throwable $e) {
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}

// ===============================
// BATALKAN ORDER (VOID)
// ===============================
if (isset($_POST['void_order'])) {
    $order_id_post = (int) $_POST['order_id'];

    mysqli_begin_transaction($conn);

    try {
        if (!$boleh_void) {
            throw new Exception('Anda tidak memiliki izin untuk membatalkan order.');
        }

        $stmt = mysqli_prepare($conn,
            "SELECT id, status_order FROM orders WHERE id = ? FOR UPDATE"
        );
        mysqli_stmt_bind_param($stmt, "i", $order_id_post);
        mysqli_stmt_execute($stmt);
        $ord = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$ord) {
            throw new Exception('Transaksi tidak ditemukan.');
        }

        if ($ord['status_order'] === 'batal') {
            throw new Exception('Transaksi sudah dibatalkan sebelumnya.');
        }

        // Order yang sudah ada pembayaran tidak boleh dibatalkan langsung.
        $stmt = mysqli_prepare($conn,
            "SELECT COUNT(*) AS jml FROM payments WHERE order_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $order_id_post);
        mysqli_stmt_execute($stmt);
        $jml = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['jml'];

        if ($jml > 0) {
            throw new Exception(
                'Order sudah ada pembayaran, tidak bisa dibatalkan. Lakukan refund secara manual.'
            );
        }

        // Kembalikan stok.
        $stmt = mysqli_prepare($conn,
            "SELECT product_id, qty FROM order_items WHERE order_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $order_id_post);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        while ($it = mysqli_fetch_assoc($res)) {
            $upd = mysqli_prepare($conn,
                "UPDATE products SET stok = stok + ? WHERE id = ?"
            );
            $q = (int) $it['qty'];
            $pid = (int) $it['product_id'];
            mysqli_stmt_bind_param($upd, "ii", $q, $pid);
            mysqli_stmt_execute($upd);
        }

        $upd = mysqli_prepare($conn,
            "UPDATE orders SET status_order = 'batal' WHERE id = ?"
        );
        mysqli_stmt_bind_param($upd, "i", $order_id_post);
        mysqli_stmt_execute($upd);

        mysqli_commit($conn);

        redirectKasir('index.php?page=kasir&order=' . $order_id_post . '&sukses=void');

    } catch (Throwable $e) {
        mysqli_rollback($conn);
        $error = $e->getMessage();
    }
}

// ===============================
// AMBIL DATA PRODUK
// ===============================
$produk_result = mysqli_query($conn,
    "SELECT * FROM products WHERE stok > 0 ORDER BY nama_produk ASC"
);

// ===============================
// DAFTAR ORDER BELUM LUNAS
// ===============================
$order_belum_lunas = [];

$res = mysqli_query($conn,
    "SELECT o.id, o.no_invoice, o.nama_pelanggan, o.nomor_meja,
            o.total_tagihan, o.status_order,
            COALESCE((SELECT SUM(nominal_bayar)
                      FROM payments WHERE order_id = o.id), 0) AS dibayar
     FROM orders o
     WHERE o.status_order IN ('pending', 'sebagian')
     ORDER BY o.id DESC
     LIMIT 20"
);

while ($r = mysqli_fetch_assoc($res)) {
    $order_belum_lunas[] = $r;
}

// ===============================
// LAPORAN HARIAN
// ===============================
$laporan = [];
$laporan_total = 0;

$res = mysqli_query($conn,
    "SELECT metode_bayar,
            COUNT(*) AS jumlah,
            COALESCE(SUM(nominal_bayar), 0) AS total
     FROM payments
     WHERE DATE(created_at) = CURDATE()
     GROUP BY metode_bayar"
);

if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $laporan[] = $r;
        $laporan_total += (float) $r['total'];
    }
}

$res = mysqli_query($conn,
    "SELECT COUNT(*) AS jml FROM orders
     WHERE DATE(created_at) = CURDATE() AND status_order <> 'batal'"
);
$order_hari_ini = $res ? (int) mysqli_fetch_assoc($res)['jml'] : 0;

// ===============================
// AMBIL DETAIL TRANSAKSI
// ===============================
$order_id = (int) ($_GET['order'] ?? $_POST['order_id'] ?? 0);

$order = null;
$items = [];
$riwayat = [];
$total_lunas = 0;
$total_belum = 0;
$faktor_split = 1;

if ($order_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);

    $order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($order) {
        $stmt = mysqli_prepare($conn,
            "SELECT oi.*, p.nama_produk
             FROM order_items oi
             JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = ?
             ORDER BY oi.id ASC"
        );
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $semua_subtotal = 0;

        while ($item = mysqli_fetch_assoc($result)) {
            $items[] = $item;
            $semua_subtotal += (float) $item['subtotal'];
        }

        // Faktor untuk menghitung porsi item (termasuk diskon/pajak/service).
        $faktor_split = $semua_subtotal > 0
            ? (float) $order['total_tagihan'] / $semua_subtotal
            : 1;

        // Total sudah dibayar dihitung dari tabel payments.
        $stmt = mysqli_prepare($conn,
            "SELECT COALESCE(SUM(nominal_bayar), 0) AS dibayar
             FROM payments WHERE order_id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);

        $total_lunas = (float) mysqli_fetch_assoc(
            mysqli_stmt_get_result($stmt)
        )['dibayar'];

        $total_belum = max(0, round((float) $order['total_tagihan'] - $total_lunas, 2));

        // Riwayat pembayaran.
        $stmt = mysqli_prepare($conn,
            "SELECT * FROM payments WHERE order_id = ? ORDER BY urutan_split ASC"
        );
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);
        while ($r = mysqli_fetch_assoc($res)) {
            $riwayat[] = $r;
        }
    }
}

$order_batal = $order && $order['status_order'] === 'batal';
$subtotal_cart = totalKeranjang($_SESSION['keranjang_kasir']);
?>

<style>
    #struk { display: none; }

    @media print {
        body * { visibility: hidden; }
        #struk, #struk * { visibility: visible; }
        #struk {
            display: block;
            position: absolute;
            left: 0;
            top: 0;
            width: 80mm;
            padding: 4mm;
            font-family: monospace;
            font-size: 12px;
            color: #000;
        }
        #struk .garis { border-top: 1px dashed #000; margin: 4px 0; }
        #struk .baris { display: flex; justify-content: space-between; }
    }
</style>

<div class="p-6 space-y-6">

    <div>
        <h2 class="text-xl font-bold text-slate-800">Terminal Kasir</h2>
        <p class="text-sm text-slate-500">
            Kelola transaksi penjualan, cicilan, dan pembayaran split bill.
        </p>
    </div>

    <?php if ($error): ?>
        <div class="p-3 rounded-lg bg-red-50 text-red-700 text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['sukses']) && $_GET['sukses'] === 'void'): ?>
        <div class="p-3 rounded-lg bg-yellow-50 text-yellow-800 text-sm">
            Order berhasil dibatalkan dan stok produk dikembalikan.
        </div>
    <?php elseif (isset($_GET['sukses'])): ?>
        <?php
        $kembalian_terakhir = 0;
        if ($order_id > 0) {
            $stmt = mysqli_prepare($conn,
                "SELECT kembalian FROM payments
                 WHERE order_id = ? ORDER BY urutan_split DESC LIMIT 1"
            );
            mysqli_stmt_bind_param($stmt, "i", $order_id);
            mysqli_stmt_execute($stmt);
            $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            $kembalian_terakhir = (float) ($row['kembalian'] ?? 0);
        }
        ?>
        <div class="p-3 rounded-lg bg-green-50 text-green-700 text-sm">
            Pembayaran berhasil dicatat.
            <?php if ($kembalian_terakhir > 0): ?>
                <strong class="block text-lg mt-1">
                    Kembalian: <?= formatRupiah($kembalian_terakhir) ?>
                </strong>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- DAFTAR ORDER BELUM LUNAS -->
    <section class="bg-white border rounded-xl p-5">
        <h3 class="font-semibold mb-3">
            Order Belum Lunas
            <span class="text-xs text-slate-400">(<?= count($order_belum_lunas) ?>)</span>
        </h3>

        <?php if (empty($order_belum_lunas)): ?>
            <p class="text-sm text-slate-400">Tidak ada order yang belum lunas.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="p-2 text-left">Invoice</th>
                            <th class="p-2 text-left">Pelanggan</th>
                            <th class="p-2 text-left">Meja</th>
                            <th class="p-2 text-right">Tagihan</th>
                            <th class="p-2 text-right">Sisa</th>
                            <th class="p-2 text-center">Status</th>
                            <th class="p-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_belum_lunas as $o): ?>
                            <tr class="border-b">
                                <td class="p-2"><?= htmlspecialchars($o['no_invoice']) ?></td>
                                <td class="p-2"><?= htmlspecialchars($o['nama_pelanggan']) ?></td>
                                <td class="p-2"><?= htmlspecialchars($o['nomor_meja']) ?></td>
                                <td class="p-2 text-right"><?= formatRupiah($o['total_tagihan']) ?></td>
                                <td class="p-2 text-right text-orange-700">
                                    <?= formatRupiah(max(0, $o['total_tagihan'] - $o['dibayar'])) ?>
                                </td>
                                <td class="p-2 text-center text-xs">
                                    <?= strtoupper(htmlspecialchars($o['status_order'])) ?>
                                </td>
                                <td class="p-2 text-right">
                                    <a href="index.php?page=kasir&order=<?= (int) $o['id'] ?>"
                                       class="text-blue-600 text-xs font-semibold">
                                        Buka
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- DAFTAR PRODUK -->
        <section class="lg:col-span-2 bg-white border rounded-xl p-5">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h3 class="font-semibold">Daftar Produk</h3>

                <input type="text" id="cariProduk"
                       placeholder="Cari nama atau kode produk..."
                       class="border rounded-lg px-3 py-2 text-sm w-full sm:w-64">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4" id="listProduk">

                <?php while ($produk = mysqli_fetch_assoc($produk_result)): ?>

                    <div class="border rounded-lg p-4 produk-card"
                         data-cari="<?= htmlspecialchars(strtolower($produk['nama_produk'] . ' ' . $produk['kode_produk'])) ?>">

                        <h4 class="font-semibold">
                            <?= htmlspecialchars($produk['nama_produk']) ?>
                        </h4>

                        <p class="text-sm text-slate-500">
                            <?= htmlspecialchars($produk['kode_produk']) ?>
                        </p>

                        <p class="font-bold text-blue-600 mt-2">
                            <?= formatRupiah($produk['harga']) ?>
                        </p>

                        <p class="text-xs text-slate-400 mb-3">
                            Stok: <?= (int) $produk['stok'] ?>
                        </p>

                        <form method="POST" class="flex gap-2">
                            <input type="hidden" name="product_id"
                                   value="<?= (int) $produk['id'] ?>">

                            <input type="number" name="qty" value="1" min="1"
                                   max="<?= (int) $produk['stok'] ?>"
                                   class="w-16 border rounded-lg px-2 py-1">

                            <button type="submit" name="tambah"
                                    class="flex-1 bg-slate-900 text-white rounded-lg px-3 py-2 text-sm">
                                + Keranjang
                            </button>
                        </form>
                    </div>

                <?php endwhile; ?>

            </div>

            <p id="produkKosong" class="text-sm text-slate-400 hidden">
                Produk tidak ditemukan.
            </p>
        </section>

        <!-- KERANJANG -->
        <section class="bg-white border rounded-xl p-5">
            <h3 class="font-semibold mb-4">Keranjang Belanja</h3>

            <?php if (empty($_SESSION['keranjang_kasir'])): ?>

                <p class="text-sm text-slate-400">
                    Belum ada produk di keranjang.
                </p>

            <?php else: ?>

                <div class="space-y-4">

                    <?php foreach ($_SESSION['keranjang_kasir'] as $item): ?>

                        <div class="border-b pb-3">

                            <div class="font-medium text-sm">
                                <?= htmlspecialchars($item['nama']) ?>
                            </div>

                            <div class="text-xs text-slate-500">
                                <?= formatRupiah($item['harga']) ?>
                                × <?= (int) $item['qty'] ?>
                            </div>

                            <div class="flex justify-between items-center mt-2">

                                <strong class="text-sm">
                                    <?= formatRupiah($item['harga'] * $item['qty']) ?>
                                </strong>

                                <div class="flex gap-2">

                                    <form method="POST" class="flex gap-1">
                                        <input type="hidden" name="product_id"
                                               value="<?= (int) $item['id'] ?>">

                                        <input type="number" name="qty"
                                               value="<?= (int) $item['qty'] ?>"
                                               min="1"
                                               class="w-14 border rounded px-1 text-sm">

                                        <button name="update_qty"
                                                class="text-blue-600 text-xs">
                                            Ubah
                                        </button>
                                    </form>

                                    <form method="POST">
                                        <input type="hidden" name="product_id"
                                               value="<?= (int) $item['id'] ?>">

                                        <button name="hapus"
                                                class="text-red-500 text-xs">
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>

                </div>

                <div class="border-t mt-4 pt-4">

                    <form method="POST" class="space-y-3" id="formCheckout"
                          data-subtotal="<?= (float) $subtotal_cart ?>"
                          data-svc="<?= (float) $SERVICE_PERSEN ?>"
                          data-ppn="<?= (float) $PPN_PERSEN ?>">

                        <div class="flex justify-between text-sm">
                            <span>Subtotal</span>
                            <span><?= formatRupiah($subtotal_cart) ?></span>
                        </div>

                        <div class="flex justify-between items-center text-sm gap-2">
                            <span>Diskon (Rp)</span>
                            <input type="number" name="diskon" id="inpDiskon"
                                   min="0" step="1" value="0"
                                   class="w-32 border rounded-lg p-1 text-right">
                        </div>

                        <?php if ($SERVICE_PERSEN > 0): ?>
                            <div class="flex justify-between text-sm">
                                <span>Service (<?= $SERVICE_PERSEN ?>%)</span>
                                <span id="outService">-</span>
                            </div>
                        <?php endif; ?>

                        <?php if ($PPN_PERSEN > 0): ?>
                            <div class="flex justify-between text-sm">
                                <span>PPN (<?= $PPN_PERSEN ?>%)</span>
                                <span id="outPajak">-</span>
                            </div>
                        <?php endif; ?>

                        <div class="flex justify-between font-bold text-lg border-t pt-2">
                            <span>Total</span>
                            <span id="outTotal"><?= formatRupiah($subtotal_cart) ?></span>
                        </div>

                        <input name="nama_pelanggan"
                               placeholder="Nama pelanggan (opsional)"
                               class="w-full border rounded-lg p-2 text-sm">

                        <input name="nomor_meja"
                               placeholder="Nomor meja (opsional)"
                               class="w-full border rounded-lg p-2 text-sm">

                        <button name="checkout"
                                class="w-full bg-blue-600 text-white rounded-lg p-3 font-semibold">
                            Checkout Transaksi
                        </button>

                    </form>

                    <form method="POST" class="mt-2">
                        <button name="kosongkan"
                                class="w-full border rounded-lg p-2 text-sm text-red-600">
                            Kosongkan Keranjang
                        </button>
                    </form>

                </div>

            <?php endif; ?>

        </section>
    </div>

    <!-- DETAIL TRANSAKSI DAN PEMBAYARAN -->
    <?php if ($order): ?>

        <section class="bg-white border rounded-xl p-5">

            <div class="flex flex-wrap justify-between gap-3 border-b pb-4 mb-4">

                <div>
                    <h3 class="font-bold text-lg">Detail Transaksi</h3>

                    <p class="text-sm text-slate-500">
                        <?= htmlspecialchars($order['no_invoice']) ?>
                        | Meja <?= htmlspecialchars($order['nomor_meja']) ?>
                    </p>

                    <p class="text-sm text-slate-500">
                        Pelanggan: <?= htmlspecialchars($order['nama_pelanggan']) ?>
                        | Kasir ID: <?= (int) $order['user_id'] ?>
                    </p>
                </div>

                <div class="flex items-start gap-2">
                    <span class="text-sm font-semibold px-3 py-1 rounded-full
                                 <?= $order_batal ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700' ?>">
                        <?= strtoupper(htmlspecialchars($order['status_order'])) ?>
                    </span>

                    <?php if (!$order_batal): ?>
                        <button type="button" onclick="window.print()"
                                class="text-sm border rounded-lg px-3 py-1">
                            Cetak Struk
                        </button>
                    <?php endif; ?>
                </div>

            </div>

            <!-- RINCIAN TAGIHAN -->
            <?php if ((float) $order['diskon'] > 0 || (float) $order['service_charge'] > 0 || (float) $order['pajak'] > 0): ?>
                <div class="text-sm text-slate-600 mb-4 space-y-1">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span><?= formatRupiah($order['subtotal']) ?></span>
                    </div>
                    <?php if ((float) $order['diskon'] > 0): ?>
                        <div class="flex justify-between">
                            <span>Diskon</span>
                            <span>- <?= formatRupiah($order['diskon']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ((float) $order['service_charge'] > 0): ?>
                        <div class="flex justify-between">
                            <span>Service</span>
                            <span><?= formatRupiah($order['service_charge']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ((float) $order['pajak'] > 0): ?>
                        <div class="flex justify-between">
                            <span>PPN</span>
                            <span><?= formatRupiah($order['pajak']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- RINGKASAN PEMBAYARAN -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">

                <div class="bg-slate-50 rounded-lg p-4">
                    <p class="text-sm text-slate-500">Total Tagihan</p>
                    <strong><?= formatRupiah($order['total_tagihan']) ?></strong>
                </div>

                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-slate-500">Sudah Dibayar</p>
                    <strong class="text-green-700"><?= formatRupiah($total_lunas) ?></strong>
                </div>

                <div class="bg-orange-50 rounded-lg p-4">
                    <p class="text-sm text-slate-500">Sisa Tagihan</p>
                    <strong class="text-orange-700">
                        <?= formatRupiah($order_batal ? 0 : $total_belum) ?>
                    </strong>
                </div>

            </div>

            <!-- RIWAYAT PEMBAYARAN -->
            <?php if ($riwayat): ?>
                <div class="mb-5">
                    <h4 class="font-semibold mb-2">Riwayat Pembayaran</h4>
                    <ul class="text-sm space-y-1">
                        <?php foreach ($riwayat as $r): ?>
                            <li class="flex justify-between border-b py-1">
                                <span>
                                    #<?= (int) $r['urutan_split'] ?>
                                    · <?= strtoupper(htmlspecialchars($r['metode_bayar'])) ?>
                                </span>
                                <span>
                                    <?= formatRupiah($r['nominal_bayar']) ?>
                                    <span class="text-xs text-slate-400">
                                        (Diterima <?= formatRupiah($r['uang_diterima']) ?>,
                                        Kembali <?= formatRupiah($r['kembalian']) ?>)
                                    </span>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($order_batal): ?>

                <div class="bg-red-50 text-red-700 p-4 rounded-lg">
                    Transaksi ini sudah dibatalkan.
                </div>

            <?php elseif ($total_belum > 0): ?>

                <!-- BAYAR SESUAI NOMINAL (CICILAN) -->
                <form method="POST"
                      class="space-y-3 border rounded-lg p-4 bg-slate-50 mb-6 form-bayar"
                      data-mode="nominal"
                      data-sisa="<?= (float) $total_belum ?>">

                    <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">

                    <h4 class="font-semibold">Bayar Sesuai Nominal (Cicilan)</h4>
                    <p class="text-xs text-slate-500">
                        Masukkan nominal berapa pun. Jika kurang dari sisa tagihan,
                        status menjadi SEBAGIAN. QRIS/debit tidak boleh melebihi sisa tagihan.
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="quick-pas border rounded-lg px-3 py-1 text-xs bg-white">
                            Uang Pas
                        </button>
                        <button type="button" class="quick-uang border rounded-lg px-3 py-1 text-xs bg-white" data-nilai="20000">20.000</button>
                        <button type="button" class="quick-uang border rounded-lg px-3 py-1 text-xs bg-white" data-nilai="50000">50.000</button>
                        <button type="button" class="quick-uang border rounded-lg px-3 py-1 text-xs bg-white" data-nilai="100000">100.000</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <select name="metode_bayar" class="border rounded-lg p-2 inp-metode">
                            <option value="tunai">Tunai</option>
                            <option value="qris">QRIS</option>
                            <option value="debit">Debit</option>
                        </select>

                        <input type="number" name="uang_diterima" min="1" step="1"
                               placeholder="Nominal dibayar (Sisa: <?= (int) $total_belum ?>)"
                               required
                               class="border rounded-lg p-2 inp-uang">

                        <button name="bayar_nominal"
                                class="bg-blue-600 text-white rounded-lg p-2 font-semibold">
                            Bayar Nominal
                        </button>
                    </div>

                    <p class="text-sm font-semibold text-green-700 out-kembalian"></p>
                </form>

                <!-- BAYAR PER ITEM (SPLIT BILL) -->
                <form method="POST" class="space-y-4 form-bayar"
                      data-mode="split"
                      data-sisa="<?= (float) $total_belum ?>"
                      data-faktor="<?= (float) $faktor_split ?>">

                    <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">

                    <h4 class="font-semibold">Atau Pilih Item yang Ingin Dibayar</h4>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="p-3 text-left">Pilih</th>
                                    <th class="p-3 text-left">Produk</th>
                                    <th class="p-3 text-center">Qty</th>
                                    <th class="p-3 text-right">Subtotal</th>
                                    <th class="p-3 text-center">Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr class="border-b">
                                        <td class="p-3">
                                            <?php if ($item['status_bayar_item'] === 'belum'): ?>
                                                <input type="checkbox" name="item_ids[]"
                                                       class="chk-item"
                                                       data-subtotal="<?= (float) $item['subtotal'] ?>"
                                                       value="<?= (int) $item['id'] ?>">
                                            <?php else: ?>
                                                <span class="text-green-600">Lunas</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="p-3"><?= htmlspecialchars($item['nama_produk']) ?></td>
                                        <td class="p-3 text-center"><?= (int) $item['qty'] ?></td>
                                        <td class="p-3 text-right"><?= formatRupiah($item['subtotal']) ?></td>

                                        <td class="p-3 text-center">
                                            <?php if ($item['status_bayar_item'] === 'lunas'): ?>
                                                <span class="text-green-600">Lunas</span>
                                            <?php else: ?>
                                                <span class="text-orange-600">Belum dibayar</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-sm">
                        Total item dipilih:
                        <strong class="out-nominal-split">Rp 0</strong>
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="quick-pas border rounded-lg px-3 py-1 text-xs bg-white">
                            Uang Pas
                        </button>
                        <button type="button" class="quick-uang border rounded-lg px-3 py-1 text-xs bg-white" data-nilai="50000">50.000</button>
                        <button type="button" class="quick-uang border rounded-lg px-3 py-1 text-xs bg-white" data-nilai="100000">100.000</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <select name="metode_bayar" class="border rounded-lg p-2 inp-metode">
                            <option value="tunai">Tunai</option>
                            <option value="qris">QRIS</option>
                            <option value="debit">Debit</option>
                        </select>

                        <input type="number" name="uang_diterima" min="0" step="1"
                               placeholder="Uang diterima" required
                               class="border rounded-lg p-2 inp-uang">

                        <button name="bayar_split"
                                class="bg-green-600 text-white rounded-lg p-2 font-semibold">
                            Proses Pembayaran
                        </button>
                    </div>

                    <p class="text-sm font-semibold text-green-700 out-kembalian"></p>

                    <p class="text-xs text-slate-500">
                        Item yang tidak dipilih tetap menjadi sisa tagihan.
                        QRIS/debit harus sama persis dengan total item dipilih.
                    </p>
                </form>

            <?php else: ?>

                <div class="bg-green-50 text-green-700 p-4 rounded-lg">
                    Transaksi sudah lunas. Terima kasih!
                </div>

            <?php endif; ?>

            <!-- BATALKAN ORDER -->
            <?php if (!$order_batal && $boleh_void && empty($riwayat)): ?>
                <form method="POST" class="mt-6 border-t pt-4"
                      onsubmit="return confirm('Batalkan order ini? Stok produk akan dikembalikan.');">
                    <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                    <button name="void_order"
                            class="border border-red-300 text-red-600 rounded-lg px-4 py-2 text-sm">
                        Batalkan Order
                    </button>
                </form>
            <?php endif; ?>

        </section>

        <!-- STRUK (hanya tampil saat dicetak) -->
        <?php if (!$order_batal): ?>
            <div id="struk">
                <div style="text-align:center">
                    <strong>STRUK PEMBAYARAN</strong><br>
                    <?= htmlspecialchars($order['no_invoice']) ?><br>
                    <?= htmlspecialchars($order['created_at'] ?? date('Y-m-d H:i:s')) ?><br>
                    Meja <?= htmlspecialchars($order['nomor_meja']) ?>
                    | <?= htmlspecialchars($order['nama_pelanggan']) ?>
                </div>

                <div class="garis"></div>

                <?php foreach ($items as $item): ?>
                    <div><?= htmlspecialchars($item['nama_produk']) ?></div>
                    <div class="baris">
                        <span><?= (int) $item['qty'] ?> x <?= formatRupiah($item['harga_satuan']) ?></span>
                        <span><?= formatRupiah($item['subtotal']) ?></span>
                    </div>
                <?php endforeach; ?>

                <div class="garis"></div>

                <?php if ((float) $order['diskon'] > 0 || (float) $order['service_charge'] > 0 || (float) $order['pajak'] > 0): ?>
                    <div class="baris"><span>Subtotal</span><span><?= formatRupiah($order['subtotal']) ?></span></div>
                    <?php if ((float) $order['diskon'] > 0): ?>
                        <div class="baris"><span>Diskon</span><span>-<?= formatRupiah($order['diskon']) ?></span></div>
                    <?php endif; ?>
                    <?php if ((float) $order['service_charge'] > 0): ?>
                        <div class="baris"><span>Service</span><span><?= formatRupiah($order['service_charge']) ?></span></div>
                    <?php endif; ?>
                    <?php if ((float) $order['pajak'] > 0): ?>
                        <div class="baris"><span>PPN</span><span><?= formatRupiah($order['pajak']) ?></span></div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="baris"><strong>TOTAL</strong><strong><?= formatRupiah($order['total_tagihan']) ?></strong></div>

                <div class="garis"></div>

                <?php foreach ($riwayat as $r): ?>
                    <div class="baris">
                        <span>Bayar <?= strtoupper(htmlspecialchars($r['metode_bayar'])) ?></span>
                        <span><?= formatRupiah($r['uang_diterima']) ?></span>
                    </div>
                    <?php if ((float) $r['kembalian'] > 0): ?>
                        <div class="baris">
                            <span>Kembalian</span>
                            <span><?= formatRupiah($r['kembalian']) ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="baris"><span>Sudah Dibayar</span><span><?= formatRupiah($total_lunas) ?></span></div>
                <div class="baris"><span>Sisa</span><span><?= formatRupiah($total_belum) ?></span></div>

                <div class="garis"></div>
                <div style="text-align:center">Terima kasih!</div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <!-- LAPORAN HARIAN -->
    <details class="bg-white border rounded-xl p-5">
        <summary class="font-semibold cursor-pointer">
            Laporan Hari Ini (<?= date('d/m/Y') ?>)
        </summary>

        <div class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between">
                <span>Jumlah order</span>
                <strong><?= $order_hari_ini ?></strong>
            </div>

            <?php if (empty($laporan)): ?>
                <p class="text-slate-400">Belum ada pembayaran hari ini.</p>
            <?php else: ?>
                <?php foreach ($laporan as $l): ?>
                    <div class="flex justify-between border-b py-1">
                        <span>
                            <?= strtoupper(htmlspecialchars($l['metode_bayar'])) ?>
                            <span class="text-xs text-slate-400">(<?= (int) $l['jumlah'] ?> transaksi)</span>
                        </span>
                        <span><?= formatRupiah($l['total']) ?></span>
                    </div>
                <?php endforeach; ?>

                <div class="flex justify-between font-bold text-base pt-2">
                    <span>Total Pemasukan</span>
                    <span><?= formatRupiah($laporan_total) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </details>

</div>

<script>
(function () {
    const rupiah = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

    // ---------- Cari produk ----------
    const cari = document.getElementById('cariProduk');
    if (cari) {
        cari.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            let tampil = 0;

            document.querySelectorAll('.produk-card').forEach(function (el) {
                const cocok = el.dataset.cari.includes(q);
                el.style.display = cocok ? '' : 'none';
                if (cocok) tampil++;
            });

            document.getElementById('produkKosong')
                .classList.toggle('hidden', tampil > 0);
        });
    }

    // ---------- Preview total checkout ----------
    const fc = document.getElementById('formCheckout');
    if (fc) {
        const subtotal = parseFloat(fc.dataset.subtotal);
        const svc = parseFloat(fc.dataset.svc);
        const ppn = parseFloat(fc.dataset.ppn);
        const inp = document.getElementById('inpDiskon');

        function hitung() {
            let diskon = parseFloat(inp.value) || 0;
            diskon = Math.min(Math.max(0, diskon), subtotal);

            const dasar = subtotal - diskon;
            const service = Math.round(dasar * svc / 100);
            const pajak = Math.round((dasar + service) * ppn / 100);

            const s = document.getElementById('outService');
            const p = document.getElementById('outPajak');
            if (s) s.textContent = rupiah(service);
            if (p) p.textContent = rupiah(pajak);

            document.getElementById('outTotal').textContent =
                rupiah(dasar + service + pajak);
        }

        inp.addEventListener('input', hitung);
        hitung();
    }

    // ---------- Form pembayaran: kembalian live + tombol cepat ----------
    document.querySelectorAll('.form-bayar').forEach(function (form) {
        const mode = form.dataset.mode;
        const sisa = parseFloat(form.dataset.sisa);
        const faktor = parseFloat(form.dataset.faktor || '1');
        const uang = form.querySelector('.inp-uang');
        const metode = form.querySelector('.inp-metode');
        const out = form.querySelector('.out-kembalian');
        const outSplit = form.querySelector('.out-nominal-split');
        const checks = form.querySelectorAll('.chk-item');

        // Nominal yang harus dibayar saat ini.
        function nominalSekarang() {
            if (mode === 'nominal') {
                return Math.min(parseFloat(uang.value) || 0, sisa);
            }

            let sub = 0, dipilih = 0;
            checks.forEach(function (c) {
                if (c.checked) {
                    sub += parseFloat(c.dataset.subtotal);
                    dipilih++;
                }
            });

            // Semua item tersisa dipilih: nominal = sisa tagihan.
            if (dipilih > 0 && dipilih === checks.length) return sisa;

            return Math.min(Math.round(sub * faktor), sisa);
        }

        function update() {
            const nominal = nominalSekarang();
            const diterima = parseFloat(uang.value) || 0;

            if (outSplit) outSplit.textContent = rupiah(nominal);

            if (diterima <= 0) { out.textContent = ''; return; }

            if (mode === 'split' && nominal === 0) {
                out.textContent = '';
                return;
            }

            const nonTunai = metode.value !== 'tunai';

            if (nonTunai) {
                const target = mode === 'nominal' ? diterima : nominal;
                if (mode === 'nominal' && diterima > sisa) {
                    out.className = 'text-sm font-semibold text-red-600 out-kembalian';
                    out.textContent = 'QRIS/debit tidak boleh melebihi sisa tagihan.';
                } else if (mode === 'split' && diterima !== target) {
                    out.className = 'text-sm font-semibold text-red-600 out-kembalian';
                    out.textContent = 'QRIS/debit harus pas: ' + rupiah(target);
                } else {
                    out.className = 'text-sm font-semibold text-green-700 out-kembalian';
                    out.textContent = 'Pembayaran pas, tanpa kembalian.';
                }
                return;
            }

            if (diterima < nominal) {
                out.className = 'text-sm font-semibold text-red-600 out-kembalian';
                out.textContent = 'Uang kurang ' + rupiah(nominal - diterima);
            } else {
                out.className = 'text-sm font-semibold text-green-700 out-kembalian';
                out.textContent = 'Kembalian: ' + rupiah(diterima - nominal);
            }
        }

        // Tombol "Uang Pas".
        form.querySelectorAll('.quick-pas').forEach(function (b) {
            b.addEventListener('click', function () {
                uang.value = mode === 'nominal' ? sisa : nominalSekarang();
                update();
            });
        });

        // Tombol nominal cepat.
        form.querySelectorAll('.quick-uang').forEach(function (b) {
            b.addEventListener('click', function () {
                uang.value = b.dataset.nilai;
                update();
            });
        });

        uang.addEventListener('input', update);
        metode.addEventListener('change', update);
        checks.forEach(c => c.addEventListener('change', update));
    });
})();
</script>