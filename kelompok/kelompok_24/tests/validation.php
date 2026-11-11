<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function expect_error(callable $operation, string $message): void
{
    try {
        $operation();
    } catch (InputError) {
        return;
    }
    throw new RuntimeException($message);
}

$registration = validate_registration([
    'fullName' => '  Siti Rahma  ',
    'nim' => ' 12345678 ',
    'password' => 'secure-password',
    'passwordConfirm' => 'secure-password',
]);
expect($registration['fullName'] === 'Siti Rahma', 'Nama registrasi harus dinormalisasi.');
expect($registration['nim'] === '12345678', 'NPM harus dinormalisasi.');

expect_error(
    static fn () => normalize_nim('12A456'),
    'NPM yang tidak valid harus ditolak.',
);
expect_error(
    static fn () => validate_registration([
        'fullName' => 'Siti Rahma',
        'nim' => '12345678',
        'password' => 'short',
        'passwordConfirm' => 'short',
    ]),
    'Kata sandi pendek harus ditolak.',
);
expect_error(
    static fn () => validate_registration([
        'fullName' => 'Siti Rahma',
        'nim' => '12345678',
        'password' => str_repeat('é', 37),
        'passwordConfirm' => str_repeat('é', 37),
    ]),
    'Kata sandi di atas batas bcrypt harus ditolak.',
);

$report = validate_report([
    'title' => 'Permohonan pendampingan',
    'category' => REPORT_CATEGORIES[2],
    'incidentDate' => '2025-02-01',
    'chronology' => 'Penjelasan kejadian yang cukup panjang.',
    'anonymous' => 'true',
]);
expect($report['anonymous'] === true, 'Pilihan anonim harus dipertahankan.');
expect($report['category'] === 'Akademik & Etik', 'Kategori laporan harus dipertahankan.');

$validReport = [
    'title' => 'Laporan',
    'category' => REPORT_CATEGORIES[0],
    'incidentDate' => '2025-02-01',
    'chronology' => 'Penjelasan kejadian yang cukup panjang.',
];
expect_error(
    static fn () => validate_report(array_replace($validReport, ['category' => 'Kategori palsu'])),
    'Kategori yang tidak dikenal harus ditolak.',
);
expect_error(
    static fn () => validate_report(array_replace($validReport, ['incidentDate' => '2025-02-30'])),
    'Tanggal kalender yang tidak valid harus ditolak.',
);
expect_error(
    static fn () => validate_report(array_replace($validReport, ['chronology' => 'Terlalu singkat'])),
    'Kronologi pendek harus ditolak.',
);

$announcement = validate_announcement([
    'title' => 'Jam layanan',
    'body' => 'Layanan tersedia pada jam kerja.',
    'isPublished' => 'on',
]);
expect($announcement['isPublished'], 'Status publikasi harus dikonversi dari checkbox.');
expect(
    validate_decision(['decision' => 'approved', 'note' => 'Sudah ditinjau.'])['decision'] === 'approved',
    'Keputusan admin yang didukung harus diterima.',
);
expect_error(
    static fn () => validate_decision(['decision' => 'pending']),
    'Keputusan di luar daftar harus ditolak.',
);

fwrite(STDOUT, "Seluruh pengujian validasi PHP berhasil.\n");
